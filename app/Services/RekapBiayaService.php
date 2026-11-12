<?php

namespace App\Services;

use App\Models\AkuntansiSetting;
use App\Models\BiayaBulananSiswa;
use App\Models\Diskon;
use App\Models\PembayaranBulanan;
use App\Models\Presensi;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RekapBiayaService
{
    public function hitungHariEfektif(int $bulan, int $tahun): int
    {
        $start = Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $end = (clone $start)->endOfMonth();
        $hariEfektif = 0;

        while ($start->lte($end)) {
            if ($start->isWeekday()) {
                $hariEfektif++;
            }
            $start->addDay();
        }

        return $hariEfektif;
    }

    public function hitungHariHadir(int $anakId, int $bulan, int $tahun): int
    {
        $start = Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $end = (clone $start)->endOfMonth();

        return Presensi::where('anak_id', $anakId)
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->where('hadir', true)
            ->count();
    }

    public function getBiayaBulanan(int $anakId, int $biayaBulananSekolahId): ?float
    {
        $siswaBiaya = BiayaBulananSiswa::where('anak_id', $anakId)
            ->where('biaya_bulanan_sekolah_id', $biayaBulananSekolahId)
            ->first();

        if ($siswaBiaya) {
            return (float) $siswaBiaya->biaya_bulanan;
        }

        return null;
    }

    /** Siswa yang sudah ditambahkan di menu Biaya Bulanan (jenis biaya aktif). */
    public function getSiswaDenganBiayaBulanan(int $sekolahId): Collection
    {
        return BiayaBulananSiswa::where('sekolah_id', $sekolahId)
            ->whereHas('biayaBulananSekolah', fn ($q) => $q->where('is_aktif', true))
            ->whereHas('anak', fn ($q) => $q->where('status', 'approved'))
            ->with(['anak.kelas', 'biayaBulananSekolah'])
            ->get();
    }

    public function hitungBiaya(PembayaranBulanan $pembayaran): PembayaranBulanan
    {
        if ($pembayaran->is_tagihan_tambahan) {
            $subtotal = 0.0;
            $biaya = 0.0;
        } else {
            $biaya = $this->getBiayaBulanan(
                $pembayaran->anak_id,
                $pembayaran->biaya_bulanan_sekolah_id
            );

            if ($biaya === null) {
                $biaya = (float) $pembayaran->biaya_per_hari;
            }

            $subtotal = $biaya;
        }

        $totalBiayaTambahan = (float) $pembayaran->items()->sum('jumlah');

        $nilaiDiskon = 0.0;
        if ($pembayaran->diskon_id) {
            $diskon = Diskon::find($pembayaran->diskon_id);
            if ($diskon && $diskon->is_aktif) {
                $nilaiDiskon = $diskon->hitungDiskon($subtotal);
            }
        } elseif ((float) $pembayaran->nilai_diskon > 0 || filled($pembayaran->diskon_keterangan)) {
            $nilaiDiskon = (float) $pembayaran->nilai_diskon;
        }

        $total = max(0, $subtotal + $totalBiayaTambahan - $nilaiDiskon);

        $pembayaran->update([
            'biaya_per_hari' => round($biaya, 2),
            'subtotal' => round($subtotal, 2),
            'nilai_diskon' => round($nilaiDiskon, 2),
            'total_bayar' => round($total, 2),
        ]);

        return $pembayaran->fresh();
    }

    /**
     * @param  array<string, int|null>  $diskonPerTagihan  "{anak_id}_{biaya_id}" => diskon_id
     * @param  list<string>  $selectedKeys  "{anak_id}_{biaya_id}"  kosong = generate semua + cleanup
     * @param  array<string, list<array{nama_item: string, jumlah: float}>>  $biayaTambahan  "{anak_id}_{biaya_id}" => [{nama_item, jumlah}]
     * @param  array<string, array{nominal: float, keterangan?: string}>  $diskonManualPerTagihan
     */
    public function generateTagihan(
        int $sekolahId,
        int $bulan,
        int $tahun,
        array $diskonPerTagihan = [],
        array $selectedKeys = [],
        array $biayaTambahan = [],
        array $diskonManualPerTagihan = []
    ): Collection {
        $assignments = $this->getSiswaDenganBiayaBulanan($sekolahId);
        $hariEfektif = $this->hitungHariEfektif($bulan, $tahun);
        $pembayarans = collect();
        $validKeys = [];
        $generateAll = $selectedKeys === [];

        foreach ($assignments as $assignment) {
            $anak = $assignment->anak;
            $biaya = $assignment->biayaBulananSekolah;
            if (! $anak || ! $biaya) {
                continue;
            }

            $key = $anak->id.'_'.$biaya->id;
            $validKeys[] = $key;

            if (! $generateAll && ! in_array($key, $selectedKeys, true)) {
                continue;
            }

            $biayaBulanan = (float) $assignment->biaya_bulanan;
            $hariHadir = $this->hitungHariHadir($anak->id, $bulan, $tahun);

            $existingForPeriod = PembayaranBulanan::where('anak_id', $anak->id)
                ->where('biaya_bulanan_sekolah_id', $biaya->id)
                ->where('periode_bulan', $bulan)
                ->where('periode_tahun', $tahun)
                ->orderByDesc('id')
                ->get();

            $pending = $existingForPeriod->first(fn (PembayaranBulanan $p) => $p->status === 'pending');
            if ($pending) {
                $isTagihanTambahan = (bool) $pending->is_tagihan_tambahan;
            } else {
                $isTagihanTambahan = $existingForPeriod->isNotEmpty();
            }

            $subtotal = $isTagihanTambahan ? 0.0 : $biayaBulanan;
            $diskonId = null;
            $diskonKeterangan = null;
            $nilaiDiskon = 0.0;

            $manual = $diskonManualPerTagihan[$key] ?? null;
            $manualNominal = (float) ($manual['nominal'] ?? 0);
            if ($manualNominal > 0) {
                $nilaiDiskon = min($manualNominal, $subtotal);
                $diskonKeterangan = trim((string) ($manual['keterangan'] ?? '')) ?: null;
            } elseif (($diskonPerTagihan[$key] ?? null) && ! $isTagihanTambahan) {
                $diskonId = (int) $diskonPerTagihan[$key];
                $diskon = Diskon::find($diskonId);
                if ($diskon && $diskon->is_aktif) {
                    $nilaiDiskon = $diskon->hitungDiskon($subtotal);
                } else {
                    $diskonId = null;
                }
            }

            $totalTambahan = 0;
            $items = $biayaTambahan[$key] ?? [];
            foreach ($items as $item) {
                $totalTambahan += (float) ($item['jumlah'] ?? 0);
            }

            $total = max(0, $subtotal + $totalTambahan - $nilaiDiskon);

            $attributes = [
                'sekolah_id' => $sekolahId,
                'hari_efektif' => $isTagihanTambahan ? 0 : $hariEfektif,
                'hari_hadir' => $isTagihanTambahan ? 0 : $hariHadir,
                'biaya_per_hari' => $isTagihanTambahan ? 0 : round($biayaBulanan, 2),
                'subtotal' => round($subtotal, 2),
                'diskon_id' => $diskonId,
                'nilai_diskon' => round($nilaiDiskon, 2),
                'diskon_keterangan' => $diskonKeterangan,
                'total_bayar' => round($total, 2),
                'is_tagihan_tambahan' => $isTagihanTambahan,
            ];

            if ($pending) {
                $pending->update($attributes + ['status' => 'pending']);
                $pembayaran = $pending->fresh();
            } else {
                $pembayaran = PembayaranBulanan::create([
                    'anak_id' => $anak->id,
                    'biaya_bulanan_sekolah_id' => $biaya->id,
                    'periode_bulan' => $bulan,
                    'periode_tahun' => $tahun,
                    'status' => 'pending',
                ] + $attributes);
            }

            $pembayaran->items()->delete();
            foreach ($items as $item) {
                $namaItem = trim($item['nama_item'] ?? '');
                $jumlahItem = (float) ($item['jumlah'] ?? 0);
                if ($namaItem !== '' && $jumlahItem > 0) {
                    $pembayaran->items()->create([
                        'nama_item' => $namaItem,
                        'jumlah' => $jumlahItem,
                    ]);
                }
            }

            if ($total > 0 && $pembayaran->status === 'pending') {
                app(AkuntansiService::class)->sinkronkanJurnalTagihanGenerate(
                    $pembayaran->fresh(['anak']),
                    auth()->id() ?? 1
                );
            }

            $pembayarans->push($pembayaran);
        }

        if ($generateAll) {
            PembayaranBulanan::where('sekolah_id', $sekolahId)
                ->where('periode_bulan', $bulan)
                ->where('periode_tahun', $tahun)
                ->where('status', 'pending')
                ->get()
                ->each(function (PembayaranBulanan $pembayaran) use ($validKeys) {
                    $key = $pembayaran->anak_id.'_'.$pembayaran->biaya_bulanan_sekolah_id;
                    if (! in_array($key, $validKeys, true)) {
                        $pembayaran->delete();
                    }
                });
        }

        return $pembayarans;
    }

    public function approvePembayaran(PembayaranBulanan $pembayaran, int $approvedBy, ?string $catatan = null): PembayaranBulanan
    {
        $pembayaran->update([
            'status' => 'approved',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
            'catatan_admin' => $catatan,
        ]);

        return $pembayaran->fresh();
    }

    public function rejectPembayaran(PembayaranBulanan $pembayaran, int $approvedBy, ?string $catatan = null): PembayaranBulanan
    {
        $pembayaran->update([
            'status' => 'rejected',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
            'catatan_admin' => $catatan,
        ]);

        return $pembayaran->fresh();
    }
}
