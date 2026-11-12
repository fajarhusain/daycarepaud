<?php

namespace App\Services;

use App\Models\Akun;
use App\Models\AkuntansiSetting;
use App\Models\Cashflow;
use App\Models\Jurnal;
use App\Models\JurnalLine;
use App\Models\PembayaranBulanan;
use App\Support\JenisAkun;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class AkuntansiService
{
    /**
     * Jurnal untuk cashflow manual.
     * Pemasukan: Debit Kas / Kredit akun_counter (dari setting)
     * Pengeluaran: Debit akun_counter / Kredit Kas
     */
    public function buatJurnalDariCashflow(Cashflow $cashflow): Jurnal
    {
        $setting = AkuntansiSetting::forSekolah($cashflow->sekolah_id);
        $kas = $cashflow->akun_id
            ? Akun::find($cashflow->akun_id)
            : $setting->akunKas;
        $counter = $cashflow->akun_lawan_id
            ? Akun::find($cashflow->akun_lawan_id)
            : ($cashflow->type === 'in' ? $setting->akunUntukIn : $setting->akunUntukOut);

        if (! $kas || ! $counter) {
            throw new \RuntimeException(
                'Konfigurasi akuntansi belum lengkap. Pastikan akun kas dan counter telah dipilih.'
            );
        }

        return DB::transaction(function () use ($cashflow, $kas, $counter) {
            $jurnal = $this->insertJurnal([
                'sekolah_id' => $cashflow->sekolah_id,
                'tanggal' => $cashflow->date,
                'deskripsi' => 'Auto: Cashflow '.$cashflow->type.' - '.($cashflow->description),
                'created_by' => auth()->id(),
                'source' => 'auto-cashflow',
            ]);

            $this->createLines($jurnal, [
                [$kas->id, $cashflow->type === 'in' ? $cashflow->amount : 0, $cashflow->type === 'in' ? 0 : $cashflow->amount],
                [$counter->id, $cashflow->type === 'in' ? 0 : $cashflow->amount, $cashflow->type === 'in' ? $cashflow->amount : 0],
            ]);

            $cashflow->update(['jurnal_id' => $jurnal->id]);

            return $jurnal;
        });
    }

    /**
     * Accrual: jurnal saat generate tagihan.
     * Debit Piutang SPP / Kredit Pendapatan SPP
     */
    /**
     * Accrual: jurnal tagihan saat generate / generate ulang (pending).
     * Debit Piutang SPP / Kredit Pendapatan SPP
     */
    public function sinkronkanJurnalTagihanGenerate(PembayaranBulanan $pembayaran, int $userId): ?Jurnal
    {
        $setting = AkuntansiSetting::forSekolah($pembayaran->sekolah_id);
        if (! $setting->isAccrual() || $pembayaran->status !== 'pending') {
            return null;
        }

        if ($pembayaran->total_bayar <= 0) {
            $this->hapusJurnalTagihan($pembayaran);

            return null;
        }

        $this->assertAkunSppLengkap($setting);
        $pembayaran->loadMissing('anak');

        $lines = [
            [$setting->akun_piutang_id, $pembayaran->total_bayar, 0],
            [$setting->akun_pendapatan_id, 0, $pembayaran->total_bayar],
        ];

        return DB::transaction(function () use ($pembayaran, $setting, $userId, $lines) {
            $existing = $pembayaran->jurnal_id
                ? Jurnal::with('lines')->find($pembayaran->jurnal_id)
                : null;

            if ($existing && str_contains($existing->deskripsi, 'Tagihan')) {
                $existing->lines()->delete();
                $existing->update([
                    'deskripsi' => 'Auto: Tagihan '.$pembayaran->getPeriodeLabel().' - '.($pembayaran->anak->name ?? 'Siswa'),
                ]);
                $this->createLines($existing, $lines);

                return $existing;
            }

            $jurnal = $this->insertJurnal([
                'sekolah_id' => $pembayaran->sekolah_id,
                'tanggal' => now(),
                'deskripsi' => 'Auto: Tagihan '.$pembayaran->getPeriodeLabel().' - '.($pembayaran->anak->name ?? 'Siswa'),
                'created_by' => $userId,
                'source' => 'auto-pembayaran',
                'sourceable_type' => PembayaranBulanan::class,
                'sourceable_id' => $pembayaran->id,
            ]);

            $this->createLines($jurnal, $lines);
            $pembayaran->update(['jurnal_id' => $jurnal->id]);

            return $jurnal;
        });
    }

    /**
     * Jurnal saat approve.
     * Cash:  Debit Kas / Kredit Pendapatan SPP
     * Accrual: Debit Kas / Kredit Piutang SPP
     */
    public function buatJurnalSaatApprove(PembayaranBulanan $pembayaran, int $userId): ?Jurnal
    {
        $setting = AkuntansiSetting::forSekolah($pembayaran->sekolah_id);
        $this->assertAkunSppLengkap($setting);
        if (! $setting->akun_kas_id) {
            throw new \RuntimeException('Akun kas belum diatur di Pengaturan Akuntansi.');
        }

        return DB::transaction(function () use ($pembayaran, $setting, $userId) {
            // Kunci baris agar approve ganda tidak membuat jurnal/cashflow dobel.
            $locked = PembayaranBulanan::whereKey($pembayaran->id)->lockForUpdate()->firstOrFail();
            if ($locked->isApproved()) {
                throw new \RuntimeException('Tagihan sudah lunas.');
            }

            $pembayaran->loadMissing('anak');
            $total = (float) $pembayaran->total_bayar;
            $tagihan = $this->jurnalTagihan($pembayaran);

            if ($total <= 0) {
                // Tidak ada uang masuk: tidak perlu jurnal/cashflow, bersihkan jurnal tagihan sisa.
                $this->hapusJurnalTagihan($pembayaran);

                return null;
            }

            // Mode piutang tanpa jurnal tagihan (mis. dibuat saat mode tunai): akui dulu piutangnya,
            // supaya pelunasan di bawah tidak mengkredit piutang yang belum pernah didebit.
            if (! $tagihan && $setting->isAccrual()) {
                $this->sinkronkanJurnalTagihanGenerate($pembayaran, $userId);
                $tagihan = $this->jurnalTagihan($pembayaran);
            }

            // Bila tagihan sudah diakui sebagai piutang, pelunasan harus menutup piutang, apa pun mode
            // pencatatan saat ini. Kalau tidak, pendapatan tercatat dua kali.
            if ($tagihan) {
                $this->sesuaikanJurnalTagihan($tagihan, $setting, $total);
                $akunKredit = $setting->akun_piutang_id;
            } else {
                $akunKredit = $setting->akun_pendapatan_id;
            }

            $deskripsi = 'Auto: Pembayaran '.$pembayaran->getPeriodeLabel().' - '.($pembayaran->anak->name ?? 'Siswa');

            $jurnal = $this->insertJurnal([
                'sekolah_id' => $pembayaran->sekolah_id,
                'tanggal' => now(),
                'deskripsi' => $deskripsi,
                'created_by' => $userId,
                'source' => 'auto-pembayaran',
                'sourceable_type' => PembayaranBulanan::class,
                'sourceable_id' => $pembayaran->id,
            ]);

            $this->createLines($jurnal, [
                [$setting->akun_kas_id, $total, 0],
                [$akunKredit, 0, $total],
            ]);

            // ponytail: jurnal tagihan (generate) tetap di jurnal_id; jurnal pelunasan hanya lewat sourceable + cashflow
            if (! $tagihan) {
                $pembayaran->update(['jurnal_id' => $jurnal->id]);
            }

            Cashflow::create([
                'sekolah_id' => $pembayaran->sekolah_id,
                'akun_id' => $setting->akun_kas_id,
                'jurnal_id' => $jurnal->id,
                'type' => 'in',
                'amount' => $total,
                'description' => 'Pembayaran '.$pembayaran->getPeriodeLabel().' - '.($pembayaran->anak->name ?? 'Siswa'),
                'date' => now(),
            ]);

            return $jurnal;
        });
    }

    /** Jurnal tagihan (Dr Piutang / Cr Pendapatan) milik pembayaran, jika ada. */
    private function jurnalTagihan(PembayaranBulanan $pembayaran): ?Jurnal
    {
        return Jurnal::query()
            ->where('sekolah_id', $pembayaran->sekolah_id)
            ->where('sourceable_type', PembayaranBulanan::class)
            ->where('sourceable_id', $pembayaran->id)
            ->where('deskripsi', 'like', 'Auto: Tagihan%')
            ->first();
    }

    private function hapusJurnalTagihan(PembayaranBulanan $pembayaran): void
    {
        $tagihan = $this->jurnalTagihan($pembayaran);
        if ($tagihan) {
            $this->hapusJurnal($tagihan);
        }
    }

    /** Samakan nominal jurnal tagihan dengan total bayar (mis. diskon diubah setelah tagihan dibuat). */
    private function sesuaikanJurnalTagihan(Jurnal $tagihan, AkuntansiSetting $setting, float $total): void
    {
        $tagihan->lines()->delete();
        $this->createLines($tagihan, [
            [$setting->akun_piutang_id, $total, 0],
            [$setting->akun_pendapatan_id, 0, $total],
        ]);
    }

    /** Kembalikan tagihan lunas ke Menunggu; hapus jurnal pelunasan & cashflow (jurnal tagihan accrual tetap). */
    public function batalkanPelunasanPembayaran(PembayaranBulanan $pembayaran): void
    {
        if (! $pembayaran->isApproved()) {
            throw new \RuntimeException('Hanya tagihan berstatus lunas yang bisa dibatalkan.');
        }

        DB::transaction(function () use ($pembayaran) {
            $pelunasanJurnals = Jurnal::query()
                ->where('sekolah_id', $pembayaran->sekolah_id)
                ->where('sourceable_type', PembayaranBulanan::class)
                ->where('sourceable_id', $pembayaran->id)
                ->where('deskripsi', 'like', 'Auto: Pembayaran%')
                ->get();

            foreach ($pelunasanJurnals as $jurnal) {
                Cashflow::where('jurnal_id', $jurnal->id)->delete();
                $this->hapusJurnal($jurnal);
            }

            $pembayaran->update([
                'status' => 'pending',
                'approved_by' => null,
                'approved_at' => null,
            ]);
        });
    }

    public function hapusJurnal(Jurnal $jurnal): void
    {
        DB::transaction(function () use ($jurnal) {
            Cashflow::where('jurnal_id', $jurnal->id)->update(['jurnal_id' => null]);
            PembayaranBulanan::where('jurnal_id', $jurnal->id)->update(['jurnal_id' => null]);
            $jurnal->lines()->delete();
            $jurnal->delete();
        });
    }

    public function jurnalTerikatPembayaranLunas(Jurnal $jurnal): bool
    {
        if (PembayaranBulanan::where('jurnal_id', $jurnal->id)->where('status', 'approved')->exists()) {
            return true;
        }

        if ($jurnal->sourceable_type !== PembayaranBulanan::class || ! $jurnal->sourceable_id) {
            return false;
        }

        $pembayaran = PembayaranBulanan::find($jurnal->sourceable_id);

        return $pembayaran && $pembayaran->isApproved();
    }

    /** Hanya untuk tagihan pending  bersihkan jurnal tagihan/pelunasan terkait sebelum hapus record. */
    public function hapusJurnalUntukPembayaranPending(PembayaranBulanan $pembayaran): void
    {
        if (! $pembayaran->canBeDeleted()) {
            throw new \RuntimeException('Tagihan lunas atau ditolak tidak boleh dihapus.');
        }

        $jurnalIds = Jurnal::query()
            ->where('sekolah_id', $pembayaran->sekolah_id)
            ->where(function ($q) use ($pembayaran) {
                $q->where(function ($q2) use ($pembayaran) {
                    $q2->where('sourceable_type', PembayaranBulanan::class)
                        ->where('sourceable_id', $pembayaran->id);
                });
                if ($pembayaran->jurnal_id) {
                    $q->orWhere('id', $pembayaran->jurnal_id);
                }
            })
            ->pluck('id')
            ->unique();

        foreach ($jurnalIds as $id) {
            $jurnal = Jurnal::find($id);
            if ($jurnal) {
                Cashflow::where('jurnal_id', $jurnal->id)->delete();
                $this->hapusJurnal($jurnal);
            }
        }
    }

    /**
     * Jurnal pembuka: akun ini lawan akun sistem "Saldo Awal" (3999).
     * Nominal 0 menghapus jurnal pembuka akun tersebut.
     */
    public function simpanSaldoAwal(Akun $akun, float $nominal): void
    {
        $nominal = round(max($nominal, 0), 2);

        $existing = Jurnal::where('source', 'saldo-awal')
            ->where('sourceable_type', Akun::class)
            ->where('sourceable_id', $akun->id)
            ->first();

        if ($nominal <= 0) {
            if ($existing) {
                $this->hapusJurnal($existing);
            }

            return;
        }

        $lawan = $this->akunPenyeimbangSaldoAwal((int) $akun->sekolah_id);
        if ($lawan->id === $akun->id) {
            throw new \RuntimeException('Akun Saldo Awal tidak bisa diisi nominal pembuka.');
        }

        $debit = $akun->saldo_normal === 'debit' ? $nominal : 0;
        $kredit = $akun->saldo_normal === 'debit' ? 0 : $nominal;

        DB::transaction(function () use ($akun, $existing, $lawan, $debit, $kredit) {
            if ($existing) {
                $existing->lines()->delete();
                $existing->update([
                    'deskripsi' => 'Saldo awal '.$akun->kode.'  '.$akun->nama,
                ]);
                $jurnal = $existing;
            } else {
                $jurnal = $this->insertJurnal([
                    'sekolah_id' => $akun->sekolah_id,
                    'tanggal' => now()->toDateString(),
                    'deskripsi' => 'Saldo awal '.$akun->kode.'  '.$akun->nama,
                    'created_by' => auth()->id(),
                    'source' => 'saldo-awal',
                    'sourceable_type' => Akun::class,
                    'sourceable_id' => $akun->id,
                ]);
            }

            $this->createLines($jurnal, [
                [$akun->id, $debit, $kredit],
                [$lawan->id, $kredit, $debit],
            ]);
        });
    }

    private function akunPenyeimbangSaldoAwal(int $sekolahId): Akun
    {
        $lawan = Akun::where('sekolah_id', $sekolahId)
            ->where('kode', '3999')
            ->where('snp', 'Ekuitas')
            ->where('komponen', 'Saldo Awal')
            ->first();

        if ($lawan) {
            return $lawan;
        }

        return Akun::create([
            'sekolah_id' => $sekolahId,
            'tipe' => 'sistem',
            'kode' => '3999',
            'nama' => 'Saldo Awal',
            'jenis' => JenisAkun::MODAL,
            'snp' => 'Ekuitas',
            'komponen' => 'Saldo Awal',
            'uraian' => 'Penyeimbang saldo awal akun',
            'saldo_normal' => 'kredit',
            'kategori_arus_kas' => 'pendanaan',
            'is_aktif' => true,
        ]);
    }

    public function validasiSaldo(array $lines): bool
    {
        $totalDebit = array_sum(array_column($lines, 'debit'));
        $totalKredit = array_sum(array_column($lines, 'kredit'));

        return bccomp((string) $totalDebit, (string) $totalKredit, 2) === 0
            && $totalDebit > 0;
    }

    public function generateNoJurnal(int $sekolahId): string
    {
        // no_jurnal is unique across all sekolah, so the sekolah id is part of the number.
        $prefix = 'JNL-'.$sekolahId.'-'.now()->format('Ym').'-';
        $last = Jurnal::withoutGlobalScope('sekolah')
            ->where('no_jurnal', 'like', $prefix.'%')
            ->orderBy('no_jurnal', 'desc')
            ->lockForUpdate()
            ->value('no_jurnal');

        $num = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $num, 4, '0', STR_PAD_LEFT);
    }

    /** @param array<string, mixed> $attributes */
    private function insertJurnal(array $attributes): Jurnal
    {
        $sekolahId = (int) $attributes['sekolah_id'];
        $lastError = null;

        for ($attempt = 0; $attempt < 3; $attempt++) {
            try {
                $attributes['no_jurnal'] = $this->generateNoJurnal($sekolahId);

                return Jurnal::create($attributes);
            } catch (UniqueConstraintViolationException $e) {
                $lastError = $e;
            }
        }

        throw $lastError;
    }

    /**
     * Saldo akun dari jurnal_lines.
     * Untuk saldo_normal=debit: SUM(debit) - SUM(kredit)
     * Untuk saldo_normal=kredit: SUM(kredit) - SUM(debit)
     */
    public function saldoAkun(int $akunId, ?string $sampaiTanggal = null): float
    {
        $akun = Akun::findOrFail($akunId);

        $query = JurnalLine::where('akun_id', $akunId)
            ->whereHas('jurnal', function ($q) use ($sampaiTanggal) {
                if ($sampaiTanggal) {
                    $q->where('tanggal', '<=', $sampaiTanggal);
                }
            });

        $totalDebit = (float) (clone $query)->sum('debit');
        $totalKredit = (float) (clone $query)->sum('kredit');

        if ($akun->saldo_normal === 'debit') {
            return $totalDebit - $totalKredit;
        }

        return $totalKredit - $totalDebit;
    }

    /** Saldo sampai hari sebelum tanggal (untuk saldo awal buku besar). */
    public function saldoAkunSebelum(int $akunId, string $tanggal): float
    {
        $sebelum = date('Y-m-d', strtotime($tanggal.' -1 day'));

        return $this->saldoAkun($akunId, $sebelum);
    }

    /**
     * @return \Illuminate\Support\Collection<int, JurnalLine>
     */
    public function barisJurnalAkun(int $akunId, string $dari, string $sampai)
    {
        return JurnalLine::query()
            ->where('akun_id', $akunId)
            ->whereHas('jurnal', fn ($q) => $q->whereBetween('tanggal', [$dari, $sampai]))
            ->with(['jurnal' => fn ($q) => $q->select('id', 'no_jurnal', 'tanggal', 'deskripsi')])
            ->join('jurnals', 'jurnal_lines.jurnal_id', '=', 'jurnals.id')
            ->orderBy('jurnals.tanggal')
            ->orderBy('jurnals.no_jurnal')
            ->select('jurnal_lines.*')
            ->get();
    }

    /**
     * Riwayat mutasi jurnal terbaru untuk satu akun (detail akun / preview).
     *
     * @return \Illuminate\Support\Collection<int, JurnalLine>
     */
    public function riwayatJurnalAkun(int $akunId, int $limit = 50)
    {
        return JurnalLine::query()
            ->where('akun_id', $akunId)
            ->join('jurnals', 'jurnal_lines.jurnal_id', '=', 'jurnals.id')
            ->with(['jurnal' => fn ($q) => $q->select('id', 'no_jurnal', 'tanggal', 'deskripsi')])
            ->orderByDesc('jurnals.tanggal')
            ->orderByDesc('jurnals.no_jurnal')
            ->limit($limit)
            ->select('jurnal_lines.*')
            ->get();
    }

    /**
     * Saldo kartu tabungan: jurnal + cashflow yang belum punya jurnal (agar selaras dengan tabel mutasi).
     */
    public function saldoTabunganAkun(int $sekolahId, int $akunId): float
    {
        $saldo = $this->saldoAkun($akunId);

        Cashflow::where('sekolah_id', $sekolahId)
            ->whereNull('jurnal_id')
            ->where(function ($q) use ($akunId) {
                $q->where('akun_id', $akunId)->orWhere('akun_lawan_id', $akunId);
            })
            ->each(function (Cashflow $trx) use ($akunId, &$saldo) {
                $saldo += $this->netCashflowUntukAkun($trx, $akunId);
            });

        return $saldo;
    }

    /**
     * Total saldo per jenis akun (aset/liabilitas/ekuitas/pendapatan/beban)
     */
    public function saldoPerJenis(string $jenis, int $sekolahId, ?string $sampaiTanggal = null): float
    {
        $total = 0;
        $akuns = Akun::where('sekolah_id', $sekolahId)
            ->where('jenis', $jenis)
            ->where('is_aktif', true)
            ->get();

        foreach ($akuns as $akun) {
            $total += $this->saldoAkun($akun->id, $sampaiTanggal);
        }

        return $total;
    }

    public function getSetting(int $sekolahId): AkuntansiSetting
    {
        return AkuntansiSetting::forSekolah($sekolahId);
    }

    private function netCashflowUntukAkun(Cashflow $trx, int $akunId): float
    {
        $amount = (float) $trx->amount;

        if ((int) $trx->akun_id === $akunId) {
            return $trx->type === 'in' ? $amount : -$amount;
        }

        if ((int) $trx->akun_lawan_id === $akunId) {
            return $trx->type === 'out' ? $amount : -$amount;
        }

        return 0.0;
    }

    private function assertAkunSppLengkap(AkuntansiSetting $setting): void
    {
        if (! $setting->akun_piutang_id || ! $setting->akun_pendapatan_id) {
            throw new \RuntimeException('Lengkapi akun piutang SPP dan pendapatan SPP di Pengaturan Akuntansi.');
        }

        $nonaktif = Akun::whereIn('id', array_filter([
            $setting->akun_kas_id,
            $setting->akun_piutang_id,
            $setting->akun_pendapatan_id,
        ]))->where('is_aktif', false)->pluck('nama');

        if ($nonaktif->isNotEmpty()) {
            throw new \RuntimeException(
                'Akun nonaktif dipakai di Pengaturan Akuntansi: '.$nonaktif->implode(', ').'. Aktifkan atau ganti akunnya.'
            );
        }
    }

    /** @param array<array{int, float, float}> $lines [akun_id, debit, kredit] */
    private function createLines(Jurnal $jurnal, array $lines): void
    {
        foreach ($lines as [$akunId, $debit, $kredit]) {
            $jurnal->lines()->create([
                'akun_id' => $akunId,
                'debit' => $debit,
                'kredit' => $kredit,
            ]);
        }
    }
}
