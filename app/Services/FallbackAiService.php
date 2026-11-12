<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Tries AI provider slots in order; on any Throwable from a slot, continues to the next.
 *
 * @phpstan-type SlotService array{slot: int, label: string, provider: string, model: string, service: SumopodAIService}
 */
class FallbackAiService
{
    /** @var list<SlotService> */
    protected array $slots;

    /**
     * @param  list<SlotService>  $slots
     */
    public function __construct(array $slots)
    {
        if ($slots === []) {
            throw new \InvalidArgumentException('FallbackAiService requires at least one slot.');
        }

        $this->slots = array_values($slots);
    }

    /**
     * @return list<SlotService>
     */
    public function slots(): array
    {
        return $this->slots;
    }

    /**
     * @return array{slot: int, label: string, provider: string, model: string}
     */
    public function primaryMeta(): array
    {
        $first = $this->slots[0];

        return [
            'slot' => $first['slot'],
            'label' => $first['label'],
            'provider' => $first['provider'],
            'model' => $first['model'],
        ];
    }

    /**
     * @return array<string>
     */
    public function generateFeedbackSuggestions(
        string $anakName,
        string $kegiatanTitle,
        string $matrikulasiLabel,
        string $scoreLabel,
        ?string $personaPrefix = null
    ): array {
        return $this->run(
            fn (SumopodAIService $ai) => $ai->generateFeedbackSuggestions(
                $anakName,
                $kegiatanTitle,
                $matrikulasiLabel,
                $scoreLabel,
                $personaPrefix
            )
        );
    }

    /**
     * @param  array{hari_kerja: int, hadir: int, terlambat: int, telat_pulang: int, tanpa_check_out: int, persentase: float}|null  $rekap
     * @return array<string>
     */
    public function generateMonevGuruCatatanSuggestions(
        string $guruName,
        string $kriteriaNama,
        string $kriteriaDeskripsi,
        int $skor,
        string $periodeLabel,
        ?string $personaPrefix = null,
        ?array $rekap = null
    ): array {
        return $this->run(
            fn (SumopodAIService $ai) => $ai->generateMonevGuruCatatanSuggestions(
                $guruName,
                $kriteriaNama,
                $kriteriaDeskripsi,
                $skor,
                $periodeLabel,
                $personaPrefix,
                $rekap
            )
        );
    }

    /**
     * @param  array<int, array{nama: string, skor: int, catatan: string, bobot: int}>  $penilaianItems
     * @return array{catatan: array<string>, rekomendasi: array<string>}
     */
    public function generateMonevGuruRingkasanSuggestions(
        string $guruName,
        string $periodeLabel,
        array $penilaianItems,
        ?string $judul = null,
        ?string $personaPrefix = null
    ): array {
        return $this->run(
            fn (SumopodAIService $ai) => $ai->generateMonevGuruRingkasanSuggestions(
                $guruName,
                $periodeLabel,
                $penilaianItems,
                $judul,
                $personaPrefix
            )
        );
    }

    /**
     * @param  array<string, mixed>  $stats
     */
    public function generateMonevSummary(
        string $anakName,
        string $kelasName,
        string $periodeLabel,
        array $stats,
        ?string $personaPrefix = null
    ): string {
        return $this->run(
            fn (SumopodAIService $ai) => $ai->generateMonevSummary(
                $anakName,
                $kelasName,
                $periodeLabel,
                $stats,
                $personaPrefix
            )
        );
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     */
    public function chatCompletion(array $messages, int $maxTokens = 1024): string
    {
        return $this->run(
            fn (SumopodAIService $ai) => $ai->chatCompletion($messages, $maxTokens)
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function generatePersonaFields(string $sekolahName, string $scope, ?string $brief = null): array
    {
        return $this->run(
            fn (SumopodAIService $ai) => $ai->generatePersonaFields($sekolahName, $scope, $brief)
        );
    }

    /**
     * Run callback across slots; returns [result, meta] of the slot that succeeded.
     *
     * @template T
     *
     * @param  callable(SumopodAIService): T  $callback
     * @return array{0: T, 1: array{slot: int, label: string, provider: string, model: string}}
     */
    public function runWithMeta(callable $callback): array
    {
        $lastError = null;

        foreach ($this->slots as $index => $slot) {
            try {
                $result = $callback($slot['service']);

                return [
                    $result,
                    [
                        'slot' => $slot['slot'],
                        'label' => $slot['label'],
                        'provider' => $slot['provider'],
                        'model' => $slot['model'],
                    ],
                ];
            } catch (\Throwable $e) {
                $lastError = $e;
                $hasNext = isset($this->slots[$index + 1]);

                Log::warning('AI provider slot failed, '.($hasNext ? 'trying next' : 'no more slots'), [
                    'slot' => $slot['slot'],
                    'label' => $slot['label'],
                    'provider' => $slot['provider'],
                    'model' => $slot['model'],
                    'message' => $e->getMessage(),
                ]);

                if (! $hasNext) {
                    throw $e;
                }
            }
        }

        throw $lastError ?? new \RuntimeException('Semua slot AI gagal.');
    }

    /**
     * @template T
     *
     * @param  callable(SumopodAIService): T  $callback
     * @return T
     */
    protected function run(callable $callback): mixed
    {
        return $this->runWithMeta($callback)[0];
    }
}
