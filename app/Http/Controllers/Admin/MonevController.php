<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\DownloadsExcel;
use App\Http\Controllers\Concerns\HandlesMonevGeneration;
use App\Http\Controllers\Concerns\HandlesMonevPdfExport;
use App\Http\Controllers\Controller;
use App\Models\Anak;
use App\Models\Kelas;
use App\Models\MonevGeneration;
use App\Models\MonevSummary;
use App\Services\AiTokenService;
use App\Services\MonevSummaryService;
use App\Support\AiTokenFeature;
use App\Support\PaginationPerPage;
use Illuminate\Http\Request;

class MonevController extends Controller
{
    use HandlesMonevGeneration;
    use HandlesMonevPdfExport;
    use DownloadsExcel;

    public function __construct(
        protected MonevSummaryService $monevService,
        protected AiTokenService $tokenService
    ) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        $sekolahId = (int) $user->sekolah_id;
        [$tahun, $bulan] = $this->monevService->parsePeriodeFromRequest(
            $request->integer('tahun') ?: null,
            $request->integer('bulan') ?: null
        );

        $kelasList = $user->applyScopedKelas(Kelas::where('sekolah_id', $sekolahId)->orderBy('name'), 'id')->get();
        $filterKelasId = $request->filled('kelas_id') ? (int) $request->kelas_id : null;
        $search = $request->string('search')->toString();

        $anaks = $this->monevService->paginateAnaksForSekolah($sekolahId, $filterKelasId, $search, PaginationPerPage::resolve($request));

        $summaries = MonevSummary::query()
            ->whereIn('anak_id', $anaks->pluck('id'))
            ->forPeriode($tahun, $bulan)
            ->get()
            ->keyBy('anak_id');

        $canManual = $this->monevService->canManualTriggerForSekolah($sekolahId, $tahun, $bulan);
        $aiReady = $this->monevService->resolveAiServiceForUser($user) !== null;
        $isCurrentMonth = $this->monevService->isCurrentMonth($tahun, $bulan);
        $activeGeneration = $this->monevService->findActiveGeneration($sekolahId)
            ?? $this->resolveSessionGeneration($request);

        $tokenBalance = $this->tokenService->getBalance($sekolahId);
        $hasTokens = $tokenBalance > 0;
        $tokenFallbackMonev = $this->tokenService->resolveFallback($sekolahId, AiTokenFeature::MONEV);

        return view('admin.monev.index', compact(
            'anaks',
            'summaries',
            'kelasList',
            'tahun',
            'bulan',
            'filterKelasId',
            'canManual',
            'aiReady',
            'isCurrentMonth',
            'activeGeneration',
            'search',
            'tokenBalance',
            'hasTokens',
            'tokenFallbackMonev'
        ));
    }

    public function export(Request $request)
    {
        $user = auth()->user();
        $sekolahId = (int) $user->sekolah_id;
        [$tahun, $bulan] = $this->monevService->parsePeriodeFromRequest(
            $request->integer('tahun') ?: null,
            $request->integer('bulan') ?: null
        );

        $filterKelasId = $request->filled('kelas_id') ? (int) $request->kelas_id : null;
        $search = $request->string('search')->toString();
        $anaks = $this->monevService->anaksForSekolah($sekolahId, $filterKelasId, $search);

        $summaries = MonevSummary::query()
            ->whereIn('anak_id', $anaks->pluck('id'))
            ->forPeriode($tahun, $bulan)
            ->get()
            ->keyBy('anak_id');

        $rows = $anaks->map(function (Anak $anak) use ($summaries) {
            $summary = $summaries->get($anak->id);

            return [
                $anak->name,
                $anak->kelas?->name ?? '-',
                $summary ? ($summary->sumber === 'otomatis' ? 'Otomatis' : 'Manual') : 'Belum ada',
                $summary?->generated_at?->format('Y-m-d H:i') ?? '-',
            ];
        })->all();

        return $this->downloadExcel(
            ['Nama Siswa', 'Kelas', 'Status', 'Tanggal Generate'],
            $rows,
            sprintf('monev-%04d-%02d.xlsx', $tahun, $bulan),
            'Monev Matrikulasi'
        );
    }

    public function show(Anak $anak, Request $request)
    {
        $user = auth()->user();
        abort_unless((int) $anak->sekolah_id === (int) $user->sekolah_id, 403);

        [$tahun, $bulan] = $this->monevService->parsePeriodeFromRequest(
            $request->integer('tahun') ?: null,
            $request->integer('bulan') ?: null
        );

        $summary = MonevSummary::query()
            ->where('anak_id', $anak->id)
            ->forPeriode($tahun, $bulan)
            ->first();

        abort_unless($summary, 404);

        $anak->load('kelas');

        return view('admin.monev.show', compact('anak', 'summary', 'tahun', 'bulan'));
    }

    public function exportPdf(Anak $anak, Request $request)
    {
        $user = auth()->user();
        abort_unless((int) $anak->sekolah_id === (int) $user->sekolah_id, 403);

        $summary = $this->resolveMonevSummaryForExport($anak, $request, $this->monevService);

        return $this->downloadMonevPdf($anak, $summary);
    }

    public function generate(Request $request)
    {
        $user = auth()->user();
        $sekolahId = (int) $user->sekolah_id;
        $now = now();
        $tahun = (int) $now->year;
        $bulan = (int) $now->month;

        $request->validate([
            'kelas_id' => 'nullable|integer|exists:kelas,id',
        ]);

        $filterKelasId = null;
        if ($request->filled('kelas_id')) {
            $filterKelasId = (int) $request->kelas_id;
            abort_unless(
                Kelas::where('id', $filterKelasId)->where('sekolah_id', $sekolahId)->exists(),
                422
            );
        }

        $anaks = $this->monevService->anaksForSekolah($sekolahId, $filterKelasId);

        return $this->startBackgroundGeneration(
            $this->monevService,
            $anaks,
            $tahun,
            $bulan,
            $sekolahId,
            null
        );
    }

    public function generationStatus(MonevGeneration $generation, Request $request)
    {
        return $this->respondGenerationStatus($generation, $request);
    }

    public function bulkGenerate(Request $request)
    {
        $user = auth()->user();
        $sekolahId = (int) $user->sekolah_id;

        $request->validate([
            'anak_ids' => 'required|array|min:1|max:100',
            'anak_ids.*' => 'integer|exists:anaks,id',
            'tahun' => 'required|integer|min:2000|max:2100',
            'bulan' => 'required|integer|min:1|max:12',
        ]);

        $tahun = (int) $request->tahun;
        $bulan = (int) $request->bulan;
        $anaks = $this->monevService->anaksByIdsForSekolah($request->input('anak_ids', []), $sekolahId);

        if ($anaks->count() !== count(array_unique($request->input('anak_ids', [])))) {
            return back()->withErrors(['monev' => 'Beberapa siswa tidak valid untuk sekolah ini.']);
        }

        return $this->startBulkBackgroundGeneration(
            $this->monevService,
            $anaks,
            $tahun,
            $bulan,
            $sekolahId,
            null
        );
    }

    public function bulkReset(Request $request)
    {
        $user = auth()->user();
        $sekolahId = (int) $user->sekolah_id;

        $request->validate([
            'anak_ids' => 'required|array|min:1|max:100',
            'anak_ids.*' => 'integer|exists:anaks,id',
            'tahun' => 'required|integer|min:2000|max:2100',
            'bulan' => 'required|integer|min:1|max:12',
        ]);

        $tahun = (int) $request->tahun;
        $bulan = (int) $request->bulan;
        $anaks = $this->monevService->anaksByIdsForSekolah($request->input('anak_ids', []), $sekolahId);

        if ($anaks->count() !== count(array_unique($request->input('anak_ids', [])))) {
            return back()->withErrors(['monev' => 'Beberapa siswa tidak valid untuk sekolah ini.']);
        }

        $deleted = $this->monevService->resetSummaries($anaks, $tahun, $bulan);

        return back()->with('success', "Berhasil reset {$deleted} ringkasan untuk periode yang dipilih.");
    }

    protected function canViewGeneration(MonevGeneration $generation): bool
    {
        $user = auth()->user();

        return (int) $generation->sekolah_id === (int) $user->sekolah_id
            && $generation->kelas_id === null;
    }
}
