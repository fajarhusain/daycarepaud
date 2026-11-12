<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\DownloadsExcel;
use App\Http\Controllers\Controller;
use App\Models\Akun;
use App\Models\Rkas;
use App\Models\RkasRealisasi;
use App\Models\SumberDana;
use App\Services\RkasRealisasiService;
use App\Services\RkasService;
use App\Support\PaginationPerPage;
use App\Support\TahunAjaran;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class RkasController extends Controller
{
    use DownloadsExcel;

    public function __construct(
        private RkasService $rkasService,
        private RkasRealisasiService $realisasiService,
    ) {}

    public function index(Request $request)
    {
        $sekolahId = auth()->user()->sekolah_id;
        $tahunAjaran = $request->input('tahun_ajaran', TahunAjaran::current()['tahun_ajaran']);

        $query = Rkas::query()
            ->where('sekolah_id', $sekolahId)
            ->where('tahun_ajaran', $tahunAjaran)
            ->orderBy('semester')
            ->withCount('lines');

        if ($request->filled('semester')) {
            $query->where('semester', (int) $request->input('semester'));
        }

        $status = (string) $request->input('status', '');
        if ($status === 'draft' || $status === 'final') {
            $query->where('status', $status);
        }

        $rkasList = $query->get();

        $tahunOptions = TahunAjaran::options();

        return view('admin.rkas.index', compact('rkasList', 'tahunAjaran', 'tahunOptions'));
    }

    public function export(Request $request)
    {
        $sekolahId = auth()->user()->sekolah_id;
        $tahunAjaran = $request->input('tahun_ajaran', TahunAjaran::current()['tahun_ajaran']);

        $exportQuery = Rkas::query()
            ->where('sekolah_id', $sekolahId)
            ->where('tahun_ajaran', $tahunAjaran)
            ->orderBy('semester')
            ->withCount('lines');

        if ($request->filled('semester')) {
            $exportQuery->where('semester', (int) $request->input('semester'));
        }

        $status = (string) $request->input('status', '');
        if ($status === 'draft' || $status === 'final') {
            $exportQuery->where('status', $status);
        }

        $rows = $exportQuery->get()
            ->map(fn (Rkas $r) => [
                $r->tahun_ajaran.' Semester '.$r->semester,
                $r->isFinal() ? 'Final' : 'Draft',
                $r->lines_count,
                $r->synced_at?->format('Y-m-d H:i') ?? '-',
            ])
            ->all();

        return $this->downloadExcel(
            ['Periode', 'Status', 'Baris', 'Sync Terakhir'],
            $rows,
            'rkas-'.str_replace('/', '-', $tahunAjaran).'.xlsx',
            'RKAS'
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'tahun_ajaran' => 'required|string',
            'semester' => 'required|in:1,2',
        ]);

        $exists = Rkas::where('sekolah_id', auth()->user()->sekolah_id)
            ->where('tahun_ajaran', $request->tahun_ajaran)
            ->where('semester', $request->semester)
            ->exists();

        if ($exists) {
            return back()->withErrors(['semester' => 'RKAS untuk periode ini sudah ada.']);
        }

        $rkas = $this->rkasService->create(
            auth()->user()->sekolah_id,
            $request->tahun_ajaran,
            (int) $request->semester,
        );

        return redirect()->route('admin.rkas.edit', $rkas)->with('success', 'RKAS berhasil dibuat.');
    }

    public function edit(Request $request, Rkas $rka)
    {
        abort_if($rka->sekolah_id !== auth()->user()->sekolah_id, 403);

        $rka->load(['lines.anggarans', 'lines.akun']);

        $sumberDanas = SumberDana::where('sekolah_id', $rka->sekolah_id)->aktif()->orderBy('urutan')->get();
        $akunBelanja = Akun::where('sekolah_id', $rka->sekolah_id)->aktif()->rkas()->where('jenis', \App\Support\JenisAkun::BEBAN)->orderBy('kode')->paginate(PaginationPerPage::resolve($request, 'belanja_per_page'), ['*'], 'belanja_page')->withQueryString();
        $selected = $rka->lines->keyBy('akun_id');

        return view('admin.rkas.edit', compact('rka', 'sumberDanas', 'akunBelanja', 'selected'));
    }

    public function update(Request $request, Rkas $rka)
    {
        abort_if($rka->sekolah_id !== auth()->user()->sekolah_id, 403);

        $request->validate(['lines' => 'array']);

        $this->rkasService->saveLines($rka, $request->input('lines', []));

        return redirect()->route('admin.rkas.edit', $rka)->with('success', 'Anggaran RKAS berhasil disimpan.');
    }

    public function finalize(Rkas $rka)
    {
        abort_if($rka->sekolah_id !== auth()->user()->sekolah_id, 403);
        $this->rkasService->finalize($rka);

        return back()->with('success', 'RKAS ditandai final.');
    }

    public function reopen(Rkas $rka)
    {
        abort_if($rka->sekolah_id !== auth()->user()->sekolah_id, 403);
        $this->rkasService->reopen($rka);

        return back()->with('success', 'RKAS dibuka kembali untuk diedit.');
    }

    public function sync(Rkas $rka)
    {
        abort_if($rka->sekolah_id !== auth()->user()->sekolah_id, 403);
        $result = $this->realisasiService->sync($rka);

        return back()->with('success', "Realisasi disync. {$result['unallocated']['count']} transaksi belum dialokasikan.");
    }

    public function laporan(Request $request)
    {
        $sekolahId = auth()->user()->sekolah_id;
        $tahunAjaran = $request->input('tahun_ajaran', TahunAjaran::current()['tahun_ajaran']);
        $semester = (int) $request->input('semester', TahunAjaran::current()['semester']);

        $rka = Rkas::where('sekolah_id', $sekolahId)
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->first();

        if (! $rka) {
            return view('admin.rkas.laporan', [
                'rka' => null,
                'rows' => collect(),
                'sumberDanas' => collect(),
                'health' => null,
                'tahunAjaran' => $tahunAjaran,
                'semester' => $semester,
                'tahunOptions' => TahunAjaran::options(),
            ]);
        }

        $laporan = $this->realisasiService->buildLaporan($rka);
        $rka->load(['lines.akun', 'lines.realisasis.sumberDana']);

        $allRows = $laporan['rows'];
        $perPage = PaginationPerPage::resolve($request);
        $page = $request->integer('page', 1);
        $paginatedRows = new \Illuminate\Pagination\LengthAwarePaginator(
            $allRows->forPage($page, $perPage)->values(),
            $allRows->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        $laporan['rows'] = $paginatedRows;

        return view('admin.rkas.laporan', array_merge($laporan, [
            'rka' => $rka,
            'tahunAjaran' => $tahunAjaran,
            'semester' => $semester,
            'tahunOptions' => TahunAjaran::options(),
        ]));
    }

    public function exportLaporan(Request $request)
    {
        $sekolahId = auth()->user()->sekolah_id;
        $tahunAjaran = $request->input('tahun_ajaran', TahunAjaran::current()['tahun_ajaran']);
        $semester = (int) $request->input('semester', TahunAjaran::current()['semester']);

        $rka = Rkas::where('sekolah_id', $sekolahId)
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->firstOrFail();

        $laporan = $this->realisasiService->buildLaporan($rka);
        $sumberDanas = $laporan['sumberDanas'];

        $headings = ['Kode', 'Uraian'];
        foreach ($sumberDanas as $sd) {
            $headings[] = $sd->kode.' Anggaran';
            $headings[] = $sd->kode.' Realisasi';
            $headings[] = $sd->kode.' %';
        }
        $headings[] = 'Total Anggaran';
        $headings[] = 'Total Realisasi';
        $headings[] = 'Sisa';

        $rows = $laporan['rows']->map(function (array $row) use ($sumberDanas) {
            $line = [$row['akun']->kode, $row['akun']->nama];
            foreach ($sumberDanas as $sd) {
                $cell = $row['cells'][$sd->id] ?? ['anggaran' => 0, 'realisasi' => 0, 'persen' => null];
                $line[] = $cell['anggaran'];
                $line[] = $cell['realisasi'];
                $line[] = $cell['persen'] ?? '-';
            }
            $line[] = $row['total_anggaran'];
            $line[] = $row['total_realisasi'];
            $line[] = $row['total_sisa'];

            return $line;
        })->all();

        $currencyColumns = [];
        $base = 2;
        foreach ($sumberDanas as $i => $sd) {
            $currencyColumns[] = $base + ($i * 3);
            $currencyColumns[] = $base + ($i * 3) + 1;
        }
        $lastStart = $base + ($sumberDanas->count() * 3);
        $currencyColumns[] = $lastStart;
        $currencyColumns[] = $lastStart + 1;
        $currencyColumns[] = $lastStart + 2;

        return $this->downloadExcel(
            $headings,
            $rows,
            'laporan-rkas-'.str_replace('/', '-', $tahunAjaran)."-sem{$semester}.xlsx",
            'Laporan RKAS',
            $currencyColumns,
        );
    }

    public function updateRealisasi(Request $request, Rkas $rka)
    {
        abort_if($rka->sekolah_id !== auth()->user()->sekolah_id, 403);

        $request->validate([
            'realisasi' => 'array',
            'realisasi.*.nominal_manual' => 'nullable|numeric|min:0',
            'realisasi.*.catatan' => 'nullable|string|max:500',
        ]);

        foreach ($request->input('realisasi', []) as $id => $data) {
            $realisasi = RkasRealisasi::whereHas('rkasLine', fn ($q) => $q->where('rkas_id', $rka->id))->find($id);
            if (! $realisasi) {
                continue;
            }

            $manual = $data['nominal_manual'] ?? null;
            $realisasi->update([
                'nominal_manual' => $manual !== '' && $manual !== null ? $manual : null,
                'catatan' => $data['catatan'] ?? null,
            ]);
        }

        return back()->with('success', 'Koreksi realisasi disimpan.');
    }

    public function exportPdf(Request $request)
    {
        $sekolahId = auth()->user()->sekolah_id;
        $tahunAjaran = $request->input('tahun_ajaran', TahunAjaran::current()['tahun_ajaran']);
        $semester = (int) $request->input('semester', TahunAjaran::current()['semester']);

        $rka = Rkas::where('sekolah_id', $sekolahId)
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->firstOrFail();

        $laporan = $this->realisasiService->buildLaporan($rka);

        $pdf = Pdf::loadView('admin.rkas.pdf', array_merge($laporan, ['rka' => $rka]));

        return $pdf->download("rkas-{$tahunAjaran}-sem{$semester}.pdf");
    }

    public function destroy(Rkas $rka)
    {
        abort_if($rka->sekolah_id !== auth()->user()->sekolah_id, 403);
        abort_if($rka->isFinal(), 422, 'RKAS final tidak bisa dihapus.');

        $rka->delete();

        return redirect()->route('admin.rkas.index')->with('success', 'RKAS berhasil dihapus.');
    }
}
