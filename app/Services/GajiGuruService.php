<?php

namespace App\Services;

use App\Models\Akun;
use App\Models\Cashflow;
use App\Models\GajiPengajar;
use App\Models\KomponenGaji;
use Illuminate\Support\Facades\DB;

class GajiGuruService
{
    public function __construct(private AkuntansiService $akuntansi) {}

    /**
     * @param  array<int, array{nama: string, jenis: string, nominal: float|int|string}>  $komponen
     */
    public function hitungTotal(array $komponen): float
    {
        $total = 0.0;

        foreach ($komponen as $row) {
            $nominal = (float) ($row['nominal'] ?? 0);
            $jenis = (string) ($row['jenis'] ?? KomponenGaji::JENIS_TAMBAH);

            if ($jenis === KomponenGaji::JENIS_POTONG) {
                $total -= abs($nominal);
            } else {
                $total += $nominal;
            }
        }

        return round(max(0, $total), 2);
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, array{nama: string, jenis: string, nominal: float|int|string}>  $komponen
     */
    public function store(array $data, array $komponen): GajiPengajar
    {
        $komponen = $this->normalizeKomponen($komponen);

        return GajiPengajar::create([
            ...$data,
            'komponen' => $komponen,
            'total_gaji' => $this->hitungTotal($komponen),
            'status' => GajiPengajar::STATUS_DRAFT,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, array{nama: string, jenis: string, nominal: float|int|string}>  $komponen
     */
    public function update(GajiPengajar $gaji, array $data, array $komponen): GajiPengajar
    {
        if (! $gaji->isDraft()) {
            throw new \RuntimeException('Slip gaji yang sudah disetujui tidak dapat diubah.');
        }

        $komponen = $this->normalizeKomponen($komponen);

        $gaji->update([
            ...$data,
            'komponen' => $komponen,
            'total_gaji' => $this->hitungTotal($komponen),
        ]);

        return $gaji->refresh();
    }

    public function approve(GajiPengajar $gaji, int $approvedByUserId): GajiPengajar
    {
        if (! $gaji->isDraft()) {
            throw new \RuntimeException('Slip gaji sudah disetujui.');
        }

        if ((float) $gaji->total_gaji <= 0) {
            throw new \RuntimeException('Total gaji harus lebih dari nol sebelum disetujui.');
        }

        if (! Akun::where('sekolah_id', $gaji->sekolah_id)->whereKey($gaji->akun_kas_id)->exists()
            || ! Akun::where('sekolah_id', $gaji->sekolah_id)->whereKey($gaji->akun_beban_id)->exists()) {
            throw new \RuntimeException(
                'Akun kas atau beban pada slip tidak valid. Simpan ulang slip draft setelah memperbaiki Pengaturan Akuntansi.'
            );
        }

        return DB::transaction(function () use ($gaji, $approvedByUserId) {
            $gaji = GajiPengajar::whereKey($gaji->id)->lockForUpdate()->firstOrFail();
            $gaji->loadMissing('pengajar');

            $deskripsi = sprintf(
                'Gaji %s %s',
                $gaji->pengajar->name,
                $gaji->periodeLabel()
            );

            $cashflow = Cashflow::create([
                'sekolah_id' => $gaji->sekolah_id,
                'date' => now()->toDateString(),
                'type' => 'out',
                'amount' => $gaji->total_gaji,
                'description' => $deskripsi,
                'akun_id' => $gaji->akun_kas_id,
                'akun_lawan_id' => $gaji->akun_beban_id,
                'sumber_dana_id' => $gaji->sumber_dana_id,
            ]);

            $jurnal = $this->akuntansi->buatJurnalDariCashflow($cashflow);

            $gaji->update([
                'status' => GajiPengajar::STATUS_APPROVED,
                'cashflow_id' => $cashflow->id,
                'jurnal_id' => $jurnal->id,
                'approved_by' => $approvedByUserId,
                'approved_at' => now(),
            ]);

            return $gaji->refresh();
        });
    }

    public function batalkanApprove(GajiPengajar $gaji): GajiPengajar
    {
        if (! $gaji->isApproved()) {
            throw new \RuntimeException('Hanya slip disetujui yang bisa dibatalkan.');
        }

        return DB::transaction(function () use ($gaji) {
            $gaji = GajiPengajar::whereKey($gaji->id)->lockForUpdate()->firstOrFail();
            $cashflow = $gaji->cashflow;

            if ($cashflow) {
                if ($cashflow->jurnal_id) {
                    $this->akuntansi->hapusJurnal($cashflow->jurnal);
                }
                $cashflow->delete();
            }

            $gaji->update([
                'status' => GajiPengajar::STATUS_DRAFT,
                'cashflow_id' => null,
                'jurnal_id' => null,
                'approved_by' => null,
                'approved_at' => null,
            ]);

            return $gaji->refresh();
        });
    }

    /**
     * @param  array<int, array{nama?: string, jenis?: string, nominal?: mixed}>  $komponen
     * @return array<int, array{nama: string, jenis: string, nominal: float}>
     */
    public function normalizeKomponen(array $komponen): array
    {
        $rows = [];

        foreach ($komponen as $row) {
            $nama = trim((string) ($row['nama'] ?? ''));
            if ($nama === '') {
                continue;
            }

            $jenis = (string) ($row['jenis'] ?? KomponenGaji::JENIS_TAMBAH);
            if (! in_array($jenis, [KomponenGaji::JENIS_TAMBAH, KomponenGaji::JENIS_POTONG], true)) {
                $jenis = KomponenGaji::JENIS_TAMBAH;
            }

            $rows[] = [
                'nama' => $nama,
                'jenis' => $jenis,
                'nominal' => round(abs((float) ($row['nominal'] ?? 0)), 2),
            ];
        }

        if ($rows === []) {
            throw new \InvalidArgumentException('Minimal satu komponen gaji diperlukan.');
        }

        return $rows;
    }
}
