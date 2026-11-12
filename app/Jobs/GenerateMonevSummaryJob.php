<?php

namespace App\Jobs;

use App\Models\Anak;
use App\Models\MonevGeneration;
use App\Models\MonevSummary;
use App\Models\User;
use App\Services\MonevSummaryService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateMonevSummaryJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 120;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        public string $generationId,
        public int $anakId,
        public int $tahun,
        public int $bulan,
        public string $sumber,
        public ?int $triggeredByUserId = null,
    ) {}

    public function handle(MonevSummaryService $service): void
    {
        $generation = MonevGeneration::find($this->generationId);

        if (! $generation || $generation->isFinished()) {
            return;
        }

        if ($generation->status === MonevGeneration::STATUS_PENDING) {
            $generation->update([
                'status' => MonevGeneration::STATUS_RUNNING,
                'started_at' => $generation->started_at ?? now(),
            ]);
        }

        $anak = Anak::find($this->anakId);

        if (! $anak) {
            $service->incrementGenerationFailed($generation, 'Siswa tidak ditemukan.');

            return;
        }

        if (! $service->anakMatchesGenerationScope($anak, $generation)) {
            $service->incrementGenerationFailed($generation, $anak->displayName().': Siswa di luar scope generate.');

            return;
        }

        try {
            $existing = MonevSummary::query()
                ->where('anak_id', $anak->id)
                ->where('tahun', $this->tahun)
                ->where('bulan', $this->bulan)
                ->first();

            if ($service->shouldSkipExistingSummary($existing, $this->sumber)) {
                $service->incrementGenerationSkipped($generation);

                return;
            }

            $by = $this->triggeredByUserId ? User::find($this->triggeredByUserId) : null;
            $ai = $service->resolveAiServiceForSekolah((int) $anak->sekolah_id);

            $service->generateForAnak($anak, $this->tahun, $this->bulan, $this->sumber, $by, $ai);
            $service->incrementGenerationCompleted($generation);
        } catch (\Throwable $e) {
            $service->incrementGenerationFailed(
                $generation,
                $anak->displayName().': '.$e->getMessage()
            );
        }
    }

    public function failed(?\Throwable $exception = null): void
    {
        $generation = MonevGeneration::find($this->generationId);

        if (! $generation || $generation->isFinished()) {
            return;
        }

        $service = app(MonevSummaryService::class);
        $anak = Anak::find($this->anakId);
        $name = $anak?->displayName() ?? 'Siswa #'.$this->anakId;
        $reason = $exception?->getMessage() ?? 'Proses generate terhenti.';

        $service->incrementGenerationFailed($generation, $name.': '.$reason);
    }
}
