<?php

namespace App\Http\Controllers\Concerns;

use App\Exceptions\MonevManualGenerationException;
use App\Models\MonevGeneration;
use App\Services\MonevSummaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

trait HandlesMonevGeneration
{
    protected function startBackgroundGeneration(
        MonevSummaryService $service,
        Collection $anaks,
        int $tahun,
        int $bulan,
        ?int $sekolahId,
        ?int $kelasId
    ): RedirectResponse {
        if ($anaks->isEmpty()) {
            return back()->withErrors(['monev' => 'Tidak ada siswa aktif dalam scope ini.']);
        }

        try {
            $generation = $service->dispatchManualGeneration(
                $anaks,
                $tahun,
                $bulan,
                auth()->user(),
                $sekolahId,
                $kelasId
            );
        } catch (MonevManualGenerationException $e) {
            return back()->withErrors(['monev' => $e->getMessage()]);
        }

        return back()->with('monev_generation_id', $generation->id);
    }

    protected function respondGenerationStatus(MonevGeneration $generation, Request $request): JsonResponse
    {
        abort_unless($this->canViewGeneration($generation), 403);

        $service = app(MonevSummaryService::class);

        return response()->json($service->generationStatusPayload($generation->fresh()));
    }

    protected function startBulkBackgroundGeneration(
        MonevSummaryService $service,
        Collection $anaks,
        int $tahun,
        int $bulan,
        ?int $sekolahId,
        ?int $kelasId
    ): RedirectResponse {
        if ($anaks->isEmpty()) {
            return back()->withErrors(['monev' => 'Pilih minimal satu siswa yang valid.']);
        }

        try {
            $generation = $service->dispatchSelectedGeneration(
                $anaks,
                $tahun,
                $bulan,
                auth()->user(),
                $sekolahId,
                $kelasId
            );
        } catch (MonevManualGenerationException $e) {
            return back()->withErrors(['monev' => $e->getMessage()]);
        }

        return back()->with('monev_generation_id', $generation->id);
    }

    protected function resolveSessionGeneration(Request $request): ?MonevGeneration
    {
        $generationId = $request->session()->get('monev_generation_id');

        if (! $generationId) {
            return null;
        }

        $generation = MonevGeneration::find($generationId);

        if (! $generation || $generation->isFinished() || ! $this->canViewGeneration($generation)) {
            return null;
        }

        return $generation;
    }

    abstract protected function canViewGeneration(MonevGeneration $generation): bool;
}
