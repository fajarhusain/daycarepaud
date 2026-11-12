<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ponytail: FK anak_id sering memakai unique composite sebagai backing index  buat index sendiri dulu
        if (! $this->hasIndex('pembayaran_bulanans', 'pembayaran_bulanans_anak_id_index')) {
            Schema::table('pembayaran_bulanans', function (Blueprint $table) {
                $table->index('anak_id', 'pembayaran_bulanans_anak_id_index');
            });
        }

        if (! $this->hasIndex('pembayaran_bulanans', 'pembayaran_bulanans_biaya_bulanan_sekolah_id_index')) {
            Schema::table('pembayaran_bulanans', function (Blueprint $table) {
                $table->index('biaya_bulanan_sekolah_id', 'pembayaran_bulanans_biaya_bulanan_sekolah_id_index');
            });
        }

        if ($this->hasIndex('pembayaran_bulanans', 'pembayaran_bulanan_unique')) {
            Schema::table('pembayaran_bulanans', function (Blueprint $table) {
                $table->dropUnique('pembayaran_bulanan_unique');
            });
        }

        Schema::table('pembayaran_bulanans', function (Blueprint $table) {
            if (! Schema::hasColumn('pembayaran_bulanans', 'is_tagihan_tambahan')) {
                $table->boolean('is_tagihan_tambahan')->default(false)->after('periode_tahun');
            }
            if (! Schema::hasColumn('pembayaran_bulanans', 'diskon_keterangan')) {
                $table->string('diskon_keterangan')->nullable()->after('nilai_diskon');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pembayaran_bulanans', function (Blueprint $table) {
            if (Schema::hasColumn('pembayaran_bulanans', 'is_tagihan_tambahan')) {
                $table->dropColumn('is_tagihan_tambahan');
            }
            if (Schema::hasColumn('pembayaran_bulanans', 'diskon_keterangan')) {
                $table->dropColumn('diskon_keterangan');
            }
        });

        if (! $this->hasIndex('pembayaran_bulanans', 'pembayaran_bulanan_unique')) {
            Schema::table('pembayaran_bulanans', function (Blueprint $table) {
                $table->unique(
                    ['anak_id', 'biaya_bulanan_sekolah_id', 'periode_bulan', 'periode_tahun'],
                    'pembayaran_bulanan_unique'
                );
            });
        }
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $indexes = collect(DB::select("SHOW INDEX FROM `{$table}`"))->pluck('Key_name');

        return $indexes->contains($indexName);
    }
};
