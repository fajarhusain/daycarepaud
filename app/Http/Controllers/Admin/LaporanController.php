<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\DownloadsExcel;
use App\Http\Controllers\Controller;
use App\Models\Akun;
use App\Models\Cashflow;
use App\Services\LaporanKeuanganService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    use DownloadsExcel;

    public function __construct(
        private LaporanKeuanganService $laporanService
    ) {}

    public function index()
    {
        return view('admin.laporan.index');
    }

    public function neraca(Request $request)
    {
        $sekolahId = auth()->user()->sekolah_id;
        $periode = $this->laporanService->resolvePeriode($request);
        $data = $this->laporanService->buildNeraca($sekolahId, $periode['neracaSampai'], $periode['tahun']);

        return view('admin.laporan.neraca', array_merge($data, ['periode' => $periode]));
    }

    public function neracaExport(Request $request)
    {
        $sekolahId = auth()->user()->sekolah_id;
        $periode = $this->laporanService->resolvePeriode($request);
        $data = $this->laporanService->buildNeraca($sekolahId, $periode['neracaSampai'], $periode['tahun']);

        $rows = $this->neracaRowsForExport($data);

        return $this->downloadExcel(
            ['Kelompok', 'Kode', 'Nama Akun', 'Jumlah (Rp)'],
            $rows,
            'neraca-'.$periode['tahun'].($periode['tipe'] === 'bulanan' ? '-'.str_pad((string) $periode['bulan'], 2, '0', STR_PAD_LEFT) : '').'.xlsx',
            'Neraca',
            [3],
        );
    }

    public function neracaPdf(Request $request)
    {
        $sekolahId = auth()->user()->sekolah_id;
        $periode = $this->laporanService->resolvePeriode($request);
        $data = $this->laporanService->buildNeraca($sekolahId, $periode['neracaSampai'], $periode['tahun']);

        $pdf = Pdf::loadView('admin.laporan.pdf.neraca', array_merge($data, [
            'periode' => $periode,
            'sekolah' => auth()->user()->sekolah,
        ]));

        return $pdf->download('neraca-'.$periode['tahun'].'.pdf');
    }

    public function labaRugi(Request $request)
    {
        $sekolahId = auth()->user()->sekolah_id;
        $periode = $this->laporanService->resolvePeriode($request);
        $data = $this->laporanService->buildLabaRugi($sekolahId, $periode['start'], $periode['end']);

        return view('admin.laporan.laba-rugi', array_merge($data, ['periode' => $periode]));
    }

    public function labaRugiExport(Request $request)
    {
        $sekolahId = auth()->user()->sekolah_id;
        $periode = $this->laporanService->resolvePeriode($request);
        $data = $this->laporanService->buildLabaRugi($sekolahId, $periode['start'], $periode['end']);

        $rows = [];
        $rows[] = ['Pendapatan', '', '', ''];
        foreach ($data['pendapatan'] as $item) {
            $rows[] = ['', $item['akun']->kode, $item['akun']->nama, $item['saldo']];
        }
        $rows[] = ['Total Pendapatan', '', '', $data['totalPendapatan']];
        $rows[] = ['Beban', '', '', ''];
        foreach ($data['beban'] as $item) {
            $rows[] = ['', $item['akun']->kode, $item['akun']->nama, $item['saldo']];
        }
        $rows[] = ['Total Beban', '', '', $data['totalBeban']];
        $rows[] = ['Surplus (Defisit)', '', '', $data['surplusDefisit']];

        return $this->downloadExcel(
            ['Kelompok', 'Kode', 'Nama Akun', 'Jumlah (Rp)'],
            $rows,
            'laba-rugi-'.$periode['tahun'].($periode['tipe'] === 'bulanan' ? '-'.str_pad((string) $periode['bulan'], 2, '0', STR_PAD_LEFT) : '').'.xlsx',
            'Laba Rugi',
            [3],
        );
    }

    public function labaRugiPdf(Request $request)
    {
        $sekolahId = auth()->user()->sekolah_id;
        $periode = $this->laporanService->resolvePeriode($request);
        $data = $this->laporanService->buildLabaRugi($sekolahId, $periode['start'], $periode['end']);

        $pdf = Pdf::loadView('admin.laporan.pdf.laba-rugi', array_merge($data, [
            'periode' => $periode,
            'sekolah' => auth()->user()->sekolah,
        ]));

        return $pdf->download('laba-rugi-'.$periode['tahun'].'.pdf');
    }

    public function bukuBesar(Request $request)
    {
        $sekolahId = auth()->user()->sekolah_id;
        $periode = $this->laporanService->resolvePeriode($request);

        $akunOptions = Akun::where('sekolah_id', $sekolahId)
            ->where('is_aktif', true)
            ->orderBy('kode')
            ->get()
            ->map(fn (Akun $a) => ['id' => $a->id, 'label' => $a->kode.'  '.$a->nama])
            ->values()
            ->all();

        $akunId = (int) $request->input('akun_id');
        $gl = null;
        if ($akunId > 0) {
            $akun = Akun::where('sekolah_id', $sekolahId)->where('id', $akunId)->firstOrFail();
            $gl = $this->laporanService->buildBukuBesar($akun, $periode['start'], $periode['end']);
        }

        return view('admin.laporan.buku-besar', [
            'periode' => $periode,
            'akunOptions' => $akunOptions,
            'akunId' => $akunId,
            'gl' => $gl,
        ]);
    }

    public function bukuBesarExport(Request $request)
    {
        $sekolahId = auth()->user()->sekolah_id;
        $periode = $this->laporanService->resolvePeriode($request);
        $akunId = (int) $request->input('akun_id');
        abort_if($akunId <= 0, 422, 'Pilih akun terlebih dahulu.');

        $akun = Akun::where('sekolah_id', $sekolahId)->where('id', $akunId)->firstOrFail();
        $gl = $this->laporanService->buildBukuBesar($akun, $periode['start'], $periode['end']);

        $rows = [['', '', 'Saldo awal', '', '', $gl['saldoAwal']]];
        foreach ($gl['mutasi'] as $row) {
            $j = $row['line']->jurnal;
            $rows[] = [
                $j?->tanggal?->format('Y-m-d') ?? '-',
                $j?->no_jurnal ?? '-',
                $j?->deskripsi ?? '-',
                $row['debit'],
                $row['kredit'],
                $row['saldo'],
            ];
        }
        $rows[] = ['', '', 'Saldo akhir', '', '', $gl['saldoAkhir']];

        return $this->downloadExcel(
            ['Tanggal', 'No. Jurnal', 'Keterangan', 'Debit', 'Kredit', 'Saldo'],
            $rows,
            'buku-besar-'.$akun->kode.'-'.$periode['start'].'.xlsx',
            'Buku Besar',
            [3, 4, 5],
        );
    }

    public function bukuBesarPdf(Request $request)
    {
        $sekolahId = auth()->user()->sekolah_id;
        $periode = $this->laporanService->resolvePeriode($request);
        $akunId = (int) $request->input('akun_id');
        abort_if($akunId <= 0, 422, 'Pilih akun terlebih dahulu.');

        $akun = Akun::where('sekolah_id', $sekolahId)->where('id', $akunId)->firstOrFail();
        $gl = $this->laporanService->buildBukuBesar($akun, $periode['start'], $periode['end']);

        $pdf = Pdf::loadView('admin.laporan.pdf.buku-besar', [
            'periode' => $periode,
            'sekolah' => auth()->user()->sekolah,
            'gl' => $gl,
        ]);

        return $pdf->download('buku-besar-'.$akun->kode.'.pdf');
    }

    /**
     * Laporan Arus Kas PSAK 2 (opsional, dari hub lama).
     */
    public function arusKas(Request $request)
    {
        $sekolahId = auth()->user()->sekolah_id;
        $bulan = (int) $request->input('bulan', now()->month);
        $tahun = (int) $request->input('tahun', now()->year);

        $items = Cashflow::where('sekolah_id', $sekolahId)
            ->whereYear('date', $tahun)
            ->whereMonth('date', $bulan)
            ->whereNotNull('akun_id')
            ->with('akun')
            ->get()
            ->groupBy(fn ($c) => $c->akun?->kategori_arus_kas ?? 'tanpa_kategori');

        $labelKategori = [
            'operasi' => 'Arus Kas dari Aktivitas Operasi',
            'investasi' => 'Arus Kas dari Aktivitas Investasi',
            'pendanaan' => 'Arus Kas dari Aktivitas Pendanaan',
            'tanpa_kategori' => 'Transaksi Tanpa Kategori',
        ];

        $saldoAwal = Cashflow::where('sekolah_id', $sekolahId)
            ->where('date', '<', "$tahun-".str_pad((string) $bulan, 2, '0', STR_PAD_LEFT).'-01')
            ->selectRaw('SUM(CASE WHEN type = "in" THEN amount ELSE 0 END) as total_in,
                         SUM(CASE WHEN type = "out" THEN amount ELSE 0 END) as total_out')
            ->first();
        $saldoAwalVal = ($saldoAwal->total_in ?? 0) - ($saldoAwal->total_out ?? 0);

        return view('admin.laporan.arus-kas', compact('items', 'labelKategori', 'bulan', 'tahun', 'saldoAwalVal'));
    }

    /** @param  array<string, mixed>  $data */
    private function neracaRowsForExport(array $data): array
    {
        $rows = [];
        $sections = [
            ['Aset', $data['aset']],
            ['Liabilitas', $data['liabilitas']],
            ['Ekuitas', $data['ekuitas']],
        ];
        foreach ($sections as [$judul, $items]) {
            $rows[] = [$judul, '', '', ''];
            foreach ($items as $item) {
                $rows[] = ['', $item['akun']->kode, $item['akun']->nama, $item['saldo']];
            }
        }
        $rows[] = ['Surplus (defisit) tahun berjalan', '', '', $data['surplusBerjalan']];
        $rows[] = ['Total Aset', '', '', $data['totalAset']];
        $rows[] = ['Total Liabilitas + Ekuitas + Surplus', '', '', $data['totalPasiva']];

        return $rows;
    }
}
