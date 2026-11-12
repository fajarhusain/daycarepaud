<?php

namespace App\Services;

use App\Models\Akun;
use App\Models\JurnalLine;
use App\Support\JenisAkun;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class LaporanKeuanganService
{
    public function __construct(
        private AkuntansiService $akuntansiService
    ) {}

    /**
     * @return array{tipe: string, bulan: int, tahun: int, start: string, end: string, neracaSampai: string, label: string}
     */
    public function resolvePeriode(Request $request): array
    {
        $tipe = $request->input('tipe', 'bulanan') === 'tahunan' ? 'tahunan' : 'bulanan';
        $tahun = (int) $request->input('tahun', now()->year);
        $bulan = (int) $request->input('bulan', now()->month);

        if ($tipe === 'tahunan') {
            return [
                'tipe' => 'tahunan',
                'bulan' => $bulan,
                'tahun' => $tahun,
                'start' => "{$tahun}-01-01",
                'end' => "{$tahun}-12-31",
                'neracaSampai' => "{$tahun}-12-31",
                'label' => "Tahun {$tahun}",
            ];
        }

        $start = sprintf('%04d-%02d-01', $tahun, $bulan);
        $end = date('Y-m-t', strtotime($start));
        $label = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F Y');

        return [
            'tipe' => 'bulanan',
            'bulan' => $bulan,
            'tahun' => $tahun,
            'start' => $start,
            'end' => $end,
            'neracaSampai' => $end,
            'label' => $label,
        ];
    }

    /**
     * @return Collection<int, array{akun: Akun, debit: float, kredit: float, saldo: float}>
     */
    public function mutasiJenisAkun(string $jenis, int $sekolahId, string $start, string $end): Collection
    {
        $akuns = Akun::where('sekolah_id', $sekolahId)
            ->where('jenis', $jenis)
            ->where('is_aktif', true)
            ->orderBy('kode')
            ->get();

        return $akuns->map(function (Akun $akun) use ($start, $end, $jenis) {
            $agg = JurnalLine::where('akun_id', $akun->id)
                ->whereHas('jurnal', fn ($q) => $q->whereBetween('tanggal', [$start, $end]))
                ->selectRaw('COALESCE(SUM(debit), 0) as total_debit, COALESCE(SUM(kredit), 0) as total_kredit')
                ->first();

            $debit = (float) ($agg->total_debit ?? 0);
            $kredit = (float) ($agg->total_kredit ?? 0);

            if ($jenis === JenisAkun::PENDAPATAN) {
                $saldo = $kredit - $debit;
            } else {
                $saldo = $debit - $kredit;
            }

            return [
                'akun' => $akun,
                'debit' => $debit,
                'kredit' => $kredit,
                'saldo' => $saldo,
            ];
        });
    }

    /**
     * @return array{
     *   pendapatan: Collection,
     *   beban: Collection,
     *   totalPendapatan: float,
     *   totalBeban: float,
     *   surplusDefisit: float,
     *   grupPendapatan: Collection,
     *   grupBeban: Collection
     * }
     */
    public function buildLabaRugi(int $sekolahId, string $start, string $end, bool $sembunyikanNol = true): array
    {
        $pendapatan = $this->mutasiJenisAkun(JenisAkun::PENDAPATAN, $sekolahId, $start, $end)
            ->map(fn ($row) => ['akun' => $row['akun'], 'saldo' => $row['saldo']]);

        $beban = $this->mutasiJenisAkun(JenisAkun::BEBAN, $sekolahId, $start, $end)
            ->map(fn ($row) => ['akun' => $row['akun'], 'saldo' => $row['saldo']]);

        if ($sembunyikanNol) {
            $pendapatan = $pendapatan->filter(fn ($r) => abs($r['saldo']) >= 0.005)->values();
            $beban = $beban->filter(fn ($r) => abs($r['saldo']) >= 0.005)->values();
        }

        $totalPendapatan = (float) $pendapatan->sum('saldo');
        $totalBeban = (float) $beban->sum('saldo');

        return [
            'pendapatan' => $pendapatan,
            'beban' => $beban,
            'totalPendapatan' => $totalPendapatan,
            'totalBeban' => $totalBeban,
            'surplusDefisit' => $totalPendapatan - $totalBeban,
            'grupPendapatan' => $this->kelompokkanAkun($pendapatan),
            'grupBeban' => $this->kelompokkanAkun($beban),
        ];
    }

    public function surplusTahunBerjalan(int $sekolahId, int $tahun, string $sampaiTanggal): float
    {
        $start = "{$tahun}-01-01";
        if ($sampaiTanggal < $start) {
            return 0.0;
        }
        $end = min($sampaiTanggal, "{$tahun}-12-31");
        $pl = $this->buildLabaRugi($sekolahId, $start, $end, false);

        return $pl['surplusDefisit'];
    }

    /**
     * @return array{
     *   aset: Collection,
     *   liabilitas: Collection,
     *   ekuitas: Collection,
     *   grupAset: Collection,
     *   grupLiabilitas: Collection,
     *   grupEkuitas: Collection,
     *   totalAset: float,
     *   totalLiabilitas: float,
     *   totalEkuitas: float,
     *   surplusBerjalan: float,
     *   totalPasiva: float
     * }
     */
    public function buildNeraca(int $sekolahId, string $neracaSampai, int $tahun): array
    {
        $aset = $this->barisNeracaJenis($sekolahId, JenisAkun::ASSETS, $neracaSampai);
        $liabilitas = $this->barisNeracaJenis($sekolahId, JenisAkun::LIABILITAS, $neracaSampai);
        $ekuitas = $this->barisNeracaJenis($sekolahId, JenisAkun::MODAL, $neracaSampai);

        $sembunyikanNol = true;
        if ($sembunyikanNol) {
            $aset = $aset->filter(fn ($r) => abs($r['saldo']) >= 0.005)->values();
            $liabilitas = $liabilitas->filter(fn ($r) => abs($r['saldo']) >= 0.005)->values();
            $ekuitas = $ekuitas->filter(fn ($r) => abs($r['saldo']) >= 0.005)->values();
        }

        $surplusBerjalan = $this->surplusTahunBerjalan($sekolahId, $tahun, $neracaSampai);
        $totalAset = (float) $aset->sum('saldo');
        $totalLiabilitas = (float) $liabilitas->sum('saldo');
        $totalEkuitas = (float) $ekuitas->sum('saldo');
        $totalPasiva = $totalLiabilitas + $totalEkuitas + $surplusBerjalan;

        return [
            'aset' => $aset,
            'liabilitas' => $liabilitas,
            'ekuitas' => $ekuitas,
            'grupAset' => $this->kelompokkanAkun($aset),
            'grupLiabilitas' => $this->kelompokkanAkun($liabilitas),
            'grupEkuitas' => $this->kelompokkanAkun($ekuitas),
            'totalAset' => $totalAset,
            'totalLiabilitas' => $totalLiabilitas,
            'totalEkuitas' => $totalEkuitas,
            'surplusBerjalan' => $surplusBerjalan,
            'totalPasiva' => $totalPasiva,
        ];
    }

    /**
     * @return array{
     *   akun: Akun,
     *   saldoAwal: float,
     *   mutasi: Collection<int, array{line: JurnalLine, debit: float, kredit: float, saldo: float}>,
     *   saldoAkhir: float
     * }
     */
    public function buildBukuBesar(Akun $akun, string $start, string $end): array
    {
        $saldoAwal = $this->akuntansiService->saldoAkunSebelum($akun->id, $start);
        $berjalan = $saldoAwal;
        $mutasi = collect();

        foreach ($this->akuntansiService->barisJurnalAkun($akun->id, $start, $end) as $line) {
            $debit = (float) $line->debit;
            $kredit = (float) $line->kredit;
            if ($akun->saldo_normal === 'debit') {
                $berjalan += $debit - $kredit;
            } else {
                $berjalan += $kredit - $debit;
            }
            $mutasi->push([
                'line' => $line,
                'debit' => $debit,
                'kredit' => $kredit,
                'saldo' => $berjalan,
            ]);
        }

        $saldoAkhir = $this->akuntansiService->saldoAkun($akun->id, $end);

        return [
            'akun' => $akun,
            'saldoAwal' => $saldoAwal,
            'mutasi' => $mutasi,
            'saldoAkhir' => $saldoAkhir,
        ];
    }

    /**
     * @param  Collection<int, array{akun: Akun, saldo: float}>  $baris
     * @return Collection<string, Collection<string, Collection<int, array{akun: Akun, saldo: float}>>>>
     */
    private function kelompokkanAkun(Collection $baris): Collection
    {
        return $baris
            ->groupBy(fn ($row) => $row['akun']->snp ?: 'Lainnya')
            ->map(fn ($bySnp) => $bySnp
                ->groupBy(fn ($row) => $row['akun']->komponen ?: '')
                ->map(fn ($items) => $items->values()));
    }

    /**
     * @return Collection<int, array{akun: Akun, saldo: float}>
     */
    private function barisNeracaJenis(int $sekolahId, string $jenis, string $sampaiTanggal): Collection
    {
        return Akun::where('sekolah_id', $sekolahId)
            ->where('jenis', $jenis)
            ->where('is_aktif', true)
            ->orderBy('kode')
            ->get()
            ->map(fn (Akun $a) => [
                'akun' => $a,
                'saldo' => $this->akuntansiService->saldoAkun($a->id, $sampaiTanggal),
            ]);
    }
}
