<?php

namespace App\Http\Controllers\AdminKelas;

use App\Http\Controllers\Concerns\HandlesMonevGeneration;
use App\Http\Controllers\Concerns\HandlesMonevPdfExport;
use App\Http\Controllers\Controller;
use App\Models\Anak;
use App\Models\Kelas;
use App\Models\MonevGeneration;
use App\Models\MonevSummary;
use App\Models\Pengajar;
use App\Services\AiTokenService;
use App\Services\MonevSummaryService;
use App\Support\AiTokenFeature;
use App\Support\PaginationPerPage;
use Illuminate\Http\Request;

class MonevController extends Controller
{
    use HandlesMonevGeneration;
    use HandlesMonevPdfExport;

    public function __construct(
        protected MonevSummaryService $monevService,
        protected AiTokenService $tokenService
    ) {}

    protected function waliKelasIds(): array
    {
        $pengajar = Pengajar::where('user_id', auth()->id())->firstOrFail();

        return Kelas::where('wali_kelas_id', $pengajar->id)->pluck('id')->toArray();
    }

    public function index(Request $request)
    {
        $kelasIds = $this->waliKelasIds();
        abort_if(empty($kelasIds), 403);

        [$tahun, $bulan] = $this->monevService->parsePeriodeFromRequest(
            $request->integer('tahun') ?: null,
            $request->integer('bulan') ?: null
        );

        $kelasList = Kelas::whereIn('id', $kelasIds)->orderBy('name')->get();
        $filterKelasId = $request->filled('kelas_id') && in_array((int) $request->kelas_id, $kelasIds, true)
            ? (int) $request->kelas_id
            : ($kelasList->count() === 1 ? (int) $kelasList->first()->id : null);

        $search = $request->string('search')->toString();
        $anaks = $this->monevService->paginateAnaksForKelasIds($kelasIds, $filterKelasId, $search, PaginationPerPage::resolve($request));

        $summaries = MonevSummary::query()
            ->whereIn('anak_id', $anaks->pluck('id'))
            ->forPeriode($tahun, $bulan)
            ->get()
            ->keyBy('anak_id');

        $manualKelasId = $filterKelasId ?? ($kelasList->count() === 1 ? (int) $kelasList->first()->id : null);
        $canManual = $manualKelasId
            ? $this->monevService->canManualTriggerForKelas($manualKelasId, $tahun, $bulan)
            : false;

        $user = auth()->user();
        $aiReady = $this->monevService->resolveAiServiceForUser($user) !== null;
        $isCurrentMonth = $this->monevService->isCurrentMonth($tahun, $bulan);
        $activeGeneration = null;
        if ($manualKelasId) {
            $activeGeneration = $this->monevService->findActiveGeneration(null, $manualKelasId)
                ?? $this->resolveSessionGeneration($request);
        }

        $sekolahId = (int) $user->sekolah_id;
        $tokenBalance = $this->tokenService->getBalance($sekolahId);
        $hasTokens = $tokenBalance > 0;
        $tokenFallbackMonev = $this->tokenService->resolveFallback($sekolahId, AiTokenFeature::MONEV);

        return view('adminkelas.monev.index', compact(
            'anaks',
            'summaries',
            'kelasList',
            'tahun',
            'bulan',
            'filterKelasId',
            'manualKelasId',
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

    public function show(Anak $anak, Request $request)
    {
        $kelasIds = $this->waliKelasIds();
        abort_unless(in_array((int) $anak->kelas_id, $kelasIds, true), 403);

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

        return view('adminkelas.monev.show', compact('anak', 'summary', 'tahun', 'bulan'));
    }

    public function exportPdf(Anak $anak, Request $request)
    {
        $kelasIds = $this->waliKelasIds();
        abort_unless(in_array((int) $anak->kelas_id, $kelasIds, true), 403);

        $summary = $this->resolveMonevSummaryForExport($anak, $request, $this->monevService);

        return $this->downloadMonevPdf($anak, $summary);
    }

    public function generate(Request $request)
    {
        $kelasIds = $this->waliKelasIds();
        abort_if(empty($kelasIds), 403);

        $kelasList = Kelas::whereIn('id', $kelasIds)->orderBy('name')->get();
        $filterKelasId = $request->filled('kelas_id') && in_array((int) $request->kelas_id, $kelasIds, true)
            ? (int) $request->kelas_id
            : null;

        $targetKelasId = $filterKelasId ?? ($kelasList->count() === 1 ? (int) $kelasList->first()->id : null);

        if (! $targetKelasId) {
            return back()->withErrors(['monev' => 'Pilih kelas terlebih dahulu untuk generate ringkasan.']);
        }

        $now = now();
        $tahun = (int) $now->year;
        $bulan = (int) $now->month;

        $anaks = $this->monevService->anaksForKelasIds($kelasIds, $targetKelasId);

        return $this->startBackgroundGeneration(
            $this->monevService,
            $anaks,
            $tahun,
            $bulan,
            null,
            $targetKelasId
        );
    }

    public function generationStatus(MonevGeneration $generation, Request $request)
    {
        return $this->respondGenerationStatus($generation, $request);
    }

    public function bulkGenerate(Request $request)
    {
        $kelasIds = $this->waliKelasIds();
        abort_if(empty($kelasIds), 403);

        $request->validate([
            'anak_ids' => 'required|array|min:1|max:100',
            'anak_ids.*' => 'integer|exists:anaks,id',
            'tahun' => 'required|integer|min:2000|max:2100',
            'bulan' => 'required|integer|min:1|max:12',
            'kelas_id' => 'nullable|integer|exists:kelas,id',
        ]);

        $targetKelasId = $this->resolveBulkKelasId($request, $kelasIds);
        if (! $targetKelasId) {
            return back()->withErrors(['monev' => 'Pilih kelas terlebih dahulu untuk bulk generate.']);
        }

        $tahun = (int) $request->tahun;
        $bulan = (int) $request->bulan;
        $anaks = $this->monevService->anaksByIdsForKelas($request->input('anak_ids', []), $kelasIds);

        if ($anaks->count() !== count(array_unique($request->input('anak_ids', [])))) {
            return back()->withErrors(['monev' => 'Beberapa siswa tidak valid untuk kelas Anda.']);
        }

        return $this->startBulkBackgroundGeneration(
            $this->monevService,
            $anaks,
            $tahun,
            $bulan,
            null,
            $targetKelasId
        );
    }

    public function bulkReset(Request $request)
    {
        $kelasIds = $this->waliKelasIds();
        abort_if(empty($kelasIds), 403);

        $request->validate([
            'anak_ids' => 'required|array|min:1|max:100',
            'anak_ids.*' => 'integer|exists:anaks,id',
            'tahun' => 'required|integer|min:2000|max:2100',
            'bulan' => 'required|integer|min:1|max:12',
        ]);

        $tahun = (int) $request->tahun;
        $bulan = (int) $request->bulan;
        $anaks = $this->monevService->anaksByIdsForKelas($request->input('anak_ids', []), $kelasIds);

        if ($anaks->count() !== count(array_unique($request->input('anak_ids', [])))) {
            return back()->withErrors(['monev' => 'Beberapa siswa tidak valid untuk kelas Anda.']);
        }

        $deleted = $this->monevService->resetSummaries($anaks, $tahun, $bulan);

        return back()->with('success', "Berhasil reset {$deleted} ringkasan untuk periode yang dipilih.");
    }

    protected function resolveBulkKelasId(Request $request, array $kelasIds): ?int
    {
        if ($request->filled('kelas_id') && in_array((int) $request->kelas_id, $kelasIds, true)) {
            return (int) $request->kelas_id;
        }

        if (count($kelasIds) === 1) {
            return (int) $kelasIds[0];
        }

        return null;
    }

    protected function canViewGeneration(MonevGeneration $generation): bool
    {
        if (! $generation->kelas_id) {
            return false;
        }

        return in_array((int) $generation->kelas_id, $this->waliKelasIds(), true);
    }
}
