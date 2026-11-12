<?php

use App\Models\Akun;
use App\Models\AkuntansiSetting;
use App\Models\Sekolah;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('akuns', function (Blueprint $table) {
            if (! Schema::hasColumn('akuns', 'tipe')) {
                $table->enum('tipe', ['sistem', 'rkas'])->default('rkas')->after('sekolah_id');
            }
            if (! Schema::hasColumn('akuns', 'snp')) {
                $table->string('snp')->nullable()->after('nama');
            }
            if (! Schema::hasColumn('akuns', 'komponen')) {
                $table->string('komponen')->nullable()->after('snp');
            }
            if (! Schema::hasColumn('akuns', 'uraian')) {
                $table->text('uraian')->nullable()->after('komponen');
            }
        });

        if (Schema::hasTable('kode_rekening_akun_mappings')) {
            Schema::dropIfExists('kode_rekening_akun_mappings');
        }

        if (Schema::hasColumn('cashflows', 'kode_rekening_id')) {
            Schema::table('cashflows', function (Blueprint $table) {
                $table->dropConstrainedForeignId('kode_rekening_id');
            });
        }

        if ($this->hasIndex('akuns', 'akuns_sekolah_id_kode_unique')) {
            if (! $this->hasIndex('akuns', 'akuns_sekolah_id_index')) {
                Schema::table('akuns', function (Blueprint $table) {
                    $table->index('sekolah_id', 'akuns_sekolah_id_index');
                });
            }

            Schema::table('akuns', function (Blueprint $table) {
                $table->dropUnique(['sekolah_id', 'kode']);
            });
        }

        if (! $this->hasIndex('akuns', 'akuns_sekolah_id_kode_snp_komponen_unique')) {
            Schema::table('akuns', function (Blueprint $table) {
                $table->unique(['sekolah_id', 'kode', 'snp', 'komponen'], 'akuns_sekolah_id_kode_snp_komponen_unique');
            });
        }

        $this->migrateKodeRekeningToAkuns();

        if (Schema::hasColumn('rkas_lines', 'kode_rekening_id')) {
            if (! Schema::hasColumn('rkas_lines', 'akun_id')) {
                Schema::table('rkas_lines', function (Blueprint $table) {
                    $table->foreignId('akun_id')->nullable()->after('rkas_id')->constrained('akuns')->cascadeOnDelete();
                });
            }

            if (Schema::hasTable('kode_rekenings')) {
                $this->migrateRkasLines();
            }

            if ($this->hasForeignKey('rkas_lines', 'rkas_lines_kode_rekening_id_foreign')) {
                Schema::table('rkas_lines', function (Blueprint $table) {
                    $table->dropForeign(['kode_rekening_id']);
                });
            }

            if ($this->hasIndex('rkas_lines', 'rkas_lines_rkas_id_kode_rekening_id_unique')) {
                if (! $this->hasIndex('rkas_lines', 'rkas_lines_rkas_id_index')) {
                    Schema::table('rkas_lines', function (Blueprint $table) {
                        $table->index('rkas_id', 'rkas_lines_rkas_id_index');
                    });
                }

                Schema::table('rkas_lines', function (Blueprint $table) {
                    $table->dropUnique(['rkas_id', 'kode_rekening_id']);
                });
            }

            Schema::table('rkas_lines', function (Blueprint $table) {
                $table->dropColumn('kode_rekening_id');
            });

            if (! $this->hasIndex('rkas_lines', 'rkas_lines_rkas_id_akun_id_unique')) {
                Schema::table('rkas_lines', function (Blueprint $table) {
                    $table->unique(['rkas_id', 'akun_id']);
                });
            }
        }

        if (Schema::hasTable('kode_rekenings')) {
            Schema::dropIfExists('kode_rekenings');
        }

        $this->deactivateLegacyPsakAkuns();
        $this->refreshAkuntansiSettings();
    }

    public function down(): void
    {
        // ponytail: one-way migration
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $indexes = collect(DB::select("SHOW INDEX FROM `{$table}`"))->pluck('Key_name');

        return $indexes->contains($indexName);
    }

    private function hasForeignKey(string $table, string $constraintName): bool
    {
        $rows = DB::select(
            'SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_TYPE = ? AND CONSTRAINT_NAME = ?',
            [$table, 'FOREIGN KEY', $constraintName],
        );

        return count($rows) > 0;
    }

    private function migrateKodeRekeningToAkuns(): void
    {
        $systemAkuns = [
            ['kode' => 'SYS.KAS', 'nama' => 'Kas', 'jenis' => 'aset', 'kategori_arus_kas' => 'operasi', 'saldo_normal' => 'debit'],
            ['kode' => 'SYS.BANK', 'nama' => 'Bank', 'jenis' => 'aset', 'kategori_arus_kas' => 'operasi', 'saldo_normal' => 'debit'],
            ['kode' => 'SYS.PIUTANG', 'nama' => 'Piutang SPP', 'jenis' => 'aset', 'kategori_arus_kas' => 'operasi', 'saldo_normal' => 'debit'],
            ['kode' => 'SYS.PDM', 'nama' => 'Pendapatan Diterima di Muka', 'jenis' => 'liabilitas', 'kategori_arus_kas' => 'operasi', 'saldo_normal' => 'kredit'],
        ];

        $pendapatan = [
            ['kode' => 'P.01', 'nama' => 'Pendapatan SPP', 'uraian' => 'Pendapatan SPP / Iuran Bulanan'],
            ['kode' => 'P.02', 'nama' => 'Pendapatan Pendaftaran', 'uraian' => 'Pendapatan Uang Pangkal / Pendaftaran'],
            ['kode' => 'P.03', 'nama' => 'Pendapatan BOS', 'uraian' => 'Pendapatan BOS'],
            ['kode' => 'P.04', 'nama' => 'Pendapatan BOSDA', 'uraian' => 'Pendapatan BOSDA'],
            ['kode' => 'P.05', 'nama' => 'Pendapatan Komite', 'uraian' => 'Pendapatan Komite Sekolah'],
            ['kode' => 'P.06', 'nama' => 'Pendapatan Swadaya', 'uraian' => 'Pendapatan Swadaya Masyarakat'],
            ['kode' => 'P.99', 'nama' => 'Pendapatan Lain-lain', 'uraian' => 'Pendapatan Lain-lain'],
        ];

        $belanjaRows = file_exists(database_path('data/kode_rekening_belanja.php'))
            ? require database_path('data/kode_rekening_belanja.php')
            : [];

        foreach (Sekolah::all() as $sekolah) {
            foreach ($systemAkuns as $row) {
                Akun::firstOrCreate(
                    ['sekolah_id' => $sekolah->id, 'kode' => $row['kode']],
                    $row + ['sekolah_id' => $sekolah->id, 'tipe' => 'sistem', 'is_aktif' => true],
                );
            }

            if (Schema::hasTable('kode_rekenings')) {
                $belanjaFromKr = DB::table('kode_rekenings')->where('jenis', 'belanja')->get();
                if ($belanjaFromKr->isNotEmpty()) {
                    foreach ($belanjaFromKr as $kr) {
                        $this->upsertRkasAkun($sekolah->id, $kr->kode, $kr->snp, $kr->komponen, $kr->uraian, 'beban');
                    }
                } else {
                    foreach ($belanjaRows as $row) {
                        $nama = Str::limit($row['uraian'], 80, '');
                        $this->upsertRkasAkun($sekolah->id, $row['kode'], $row['snp'], $row['komponen'], $row['uraian'], 'beban', $nama);
                    }
                }
                foreach (DB::table('kode_rekenings')->where('jenis', 'pendapatan')->get() as $kr) {
                    $this->upsertRkasAkun($sekolah->id, $kr->kode, $kr->snp, $kr->komponen, $kr->uraian, 'pendapatan', $kr->uraian);
                }
            } else {
                foreach ($belanjaRows as $row) {
                    $nama = Str::limit($row['uraian'], 80, '');
                    $this->upsertRkasAkun($sekolah->id, $row['kode'], $row['snp'], $row['komponen'], $row['uraian'], 'beban', $nama);
                }
            }

            foreach ($pendapatan as $row) {
                $this->upsertRkasAkun($sekolah->id, $row['kode'], null, 'Pendapatan', $row['uraian'], 'pendapatan', $row['nama']);
            }
        }
    }

    private function upsertRkasAkun(int $sekolahId, string $kode, ?string $snp, ?string $komponen, string $uraian, string $jenis, ?string $nama = null): void
    {
        $saldo = $jenis === 'pendapatan' ? 'kredit' : 'debit';
        $nama ??= Str::limit($uraian, 80, '');

        Akun::updateOrCreate(
            [
                'sekolah_id' => $sekolahId,
                'kode' => $kode,
                'snp' => $snp,
                'komponen' => $komponen,
            ],
            [
                'nama' => $nama,
                'uraian' => $uraian,
                'jenis' => $jenis,
                'tipe' => 'rkas',
                'kategori_arus_kas' => 'operasi',
                'saldo_normal' => $saldo,
                'is_aktif' => true,
            ],
        );
    }

    private function migrateRkasLines(): void
    {
        if (! Schema::hasTable('kode_rekenings')) {
            return;
        }

        $lines = DB::table('rkas_lines')->whereNull('akun_id')->get();
        foreach ($lines as $line) {
            $rkas = DB::table('rkas')->where('id', $line->rkas_id)->first();
            if (! $rkas) {
                continue;
            }

            $kr = DB::table('kode_rekenings')->where('id', $line->kode_rekening_id)->first();
            if (! $kr) {
                continue;
            }

            $akun = Akun::where('sekolah_id', $rkas->sekolah_id)
                ->where('kode', $kr->kode)
                ->when($kr->snp, fn ($q) => $q->where('snp', $kr->snp))
                ->when($kr->komponen, fn ($q) => $q->where('komponen', $kr->komponen))
                ->first();

            if ($akun) {
                DB::table('rkas_lines')->where('id', $line->id)->update(['akun_id' => $akun->id]);
            }
        }

        DB::table('rkas_lines')->whereNull('akun_id')->delete();
    }

    private function deactivateLegacyPsakAkuns(): void
    {
        Akun::where('tipe', '!=', 'sistem')
            ->where(function ($q) {
                $q->where('kode', 'like', '1-%')
                    ->orWhere('kode', 'like', '2-%')
                    ->orWhere('kode', 'like', '3-%')
                    ->orWhere('kode', 'like', '4-%')
                    ->orWhere('kode', 'like', '5-%');
            })
            ->update(['is_aktif' => false]);
    }

    private function refreshAkuntansiSettings(): void
    {
        foreach (Sekolah::all() as $sekolah) {
            AkuntansiSetting::forSekolah($sekolah->id);
        }
    }
};
