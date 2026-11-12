<?php

namespace App\Services;

use App\Exceptions\MonevManualGenerationException;
use App\Jobs\GenerateMonevSummaryJob;
use App\Models\AiSetting;
use App\Models\Anak;
use App\Models\MonevGeneration;
use App\Models\MonevManualTrigger;
use App\Models\MonevSummary;
use App\Models\Sekolah;
use App\Models\SekolahAiPersona;
use App\Models\SekolahAiTokenTransaction;
use App\Models\User;
use App\Support\AiPersonaScope;
use App\Support\MonevSummaryPresenter;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MonevSummaryService
{
    public function __construct(
        protected MonevDataAggregator $aggregator,
        protected AiTokenService $tokenService
    ) {}

    public function resolveAiServiceForSekolah(int $sekolahId): ?FallbackAiService
    {
        $sekolah = Sekolah::find($sekolahId);
        $lembagaId = $sekolah?->lembaga_id;

        if (! $lembagaId) {
            return null;
        }

        $aiSetting = AiSetting::where('lembaga_id', $lembagaId)->first();

        return $aiSetting?->resolveAiService();
    }

    public function resolveAiServiceForUser(User $user): ?FallbackAiService
    {
        $lembagaId = $user->lembaga_id;

        if (! $lembagaId && $user->sekolah_id) {
            $lembagaId = $user->sekolah?->lembaga_id;
        }

        if (! $lembagaId) {
            return $user->sekolah_id
                ? $this->resolveAiServiceForSekolah((int) $user->sekolah_id)
                : null;
        }

        $aiSetting = AiSetting::where('lembaga_id', $lembagaId)->first();

        return $aiSetting?->resolveAiService();
    }

    public function assertCanStartSelectedGeneration(
        User $user,
        ?int $sekolahId = null,
        ?int $kelasId = null
    ): void {
        if (! $this->resolveAiServiceForUser($user)) {
            throw new MonevManualGenerationException(
                'Pengaturan AI belum dikonfigurasi. Minta admin lembaga mengisi API Key di menu Pengaturan AI.'
            );
        }

        if ($this->hasActiveGeneration($sekolahId, $kelasId)) {
            throw new MonevManualGenerationException('Masih ada proses generate yang berjalan. Tunggu hingga selesai.');
        }
    }

    public function assertCanStartManualGeneration(
        User $user,
        int $tahun,
        int $bulan,
        ?int $sekolahId = null,
        ?int $kelasId = null
    ): void {
        if (! $this->isCurrentMonth($tahun, $bulan)) {
            throw new MonevManualGenerationException('Generate manual hanya tersedia untuk bulan berjalan.');
        }

        if (! $this->resolveAiServiceForUser($user)) {
            throw new MonevManualGenerationException(
                'Pengaturan AI belum dikonfigurasi. Minta admin lembaga mengisi API Key di menu Pengaturan AI.'
            );
        }

        if ($kelasId) {
            if (! $this->canManualTriggerForKelas($kelasId, $tahun, $bulan)) {
                throw new MonevManualGenerationException(
                    'Ringkasan manual untuk kelas ini pada bulan berjalan sudah pernah digenerate atau sedang diproses.'
                );
            }

            return;
        }

        if (! $sekolahId || ! $this->canManualTriggerForSekolah($sekolahId, $tahun, $bulan)) {
            throw new MonevManualGenerationException(
                'Ringkasan manual untuk bulan ini sudah pernah digenerate atau sedang diproses.'
            );
        }
    }

    public function shouldSkipExistingSummary(?MonevSummary $existing, string $sumber): bool
    {
        if (! $existing) {
            return false;
        }

        if ($existing->sumber === MonevSummary::SUMBER_OTOMATIS && $sumber === MonevSummary::SUMBER_MANUAL) {
            return true;
        }

        if ($existing->sumber === MonevSummary::SUMBER_MANUAL && $sumber === MonevSummary::SUMBER_OTOMATIS) {
            return true;
        }

        return false;
    }

    public function countAnaksRequiringAiToken(
        Collection $anaks,
        int $tahun,
        int $bulan,
        string $sumber
    ): int {
        $count = 0;

        foreach ($anaks as $anak) {
            if (! $anak instanceof Anak) {
                continue;
            }

            $existing = MonevSummary::query()
                ->where('anak_id', $anak->id)
                ->where('tahun', $tahun)
                ->where('bulan', $bulan)
                ->first();

            if ($this->shouldSkipExistingSummary($existing, $sumber)) {
                continue;
            }

            $stats = $this->aggregator->aggregate($anak, $tahun, $bulan);

            if (($stats['total_entri'] ?? 0) > 0) {
                $count++;
            }
        }

        return $count;
    }

    public function assertSufficientTokensForAnaks(
        Collection $anaks,
        int $sekolahId,
        int $tahun,
        int $bulan,
        string $sumber
    ): void {
        $required = $this->countAnaksRequiringAiToken($anaks, $tahun, $bulan, $sumber);

        if ($required === 0) {
            return;
        }

        $balance = $this->tokenService->getBalance($sekolahId);

        if ($balance < $required) {
            throw new MonevManualGenerationException(
                "Token AI tidak cukup. Dibutuhkan {$required} token, tersedia {$balance}."
            );
        }
    }

    protected function resolveSekolahIdForTokenCheck(Collection $anaks, ?int $sekolahId): int
    {
        if ($sekolahId !== null && $sekolahId > 0) {
            return $sekolahId;
        }

        $first = $anaks->first();

        if ($first instanceof Anak) {
            return (int) $first->sekolah_id;
        }

        throw new MonevManualGenerationException('Tidak dapat menentukan sekolah untuk pengecekan token.');
    }

    public function anakMatchesGenerationScope(Anak $anak, MonevGeneration $generation): bool
    {
        if ($generation->kelas_id) {
            return (int) $anak->kelas_id === (int) $generation->kelas_id;
        }

        if ($generation->sekolah_id) {
            return (int) $anak->sekolah_id === (int) $generation->sekolah_id;
        }

        return false;
    }

    public function isCurrentMonth(int $tahun, int $bulan): bool
    {
        $now = now();

        return (int) $now->year === $tahun && (int) $now->month === $bulan;
    }

    public function hasActiveGeneration(?int $sekolahId = null, ?int $kelasId = null): bool
    {
        return $this->activeGenerationQuery($sekolahId, $kelasId)->exists();
    }

    public function findActiveGeneration(?int $sekolahId = null, ?int $kelasId = null): ?MonevGeneration
    {
        return $this->activeGenerationQuery($sekolahId, $kelasId)->latest()->first();
    }

    protected function activeGenerationQuery(?int $sekolahId, ?int $kelasId)
    {
        $query = MonevGeneration::query()
            ->whereIn('status', [MonevGeneration::STATUS_PENDING, MonevGeneration::STATUS_RUNNING]);

        if ($kelasId) {
            return $query->where('kelas_id', $kelasId)->whereNull('sekolah_id');
        }

        if ($sekolahId) {
            return $query->where('sekolah_id', $sekolahId)->whereNull('kelas_id');
        }

        return $query->whereRaw('1 = 0');
    }

    public function canManualTriggerForSekolah(int $sekolahId, int $tahun, int $bulan): bool
    {
        if (! $this->isCurrentMonth($tahun, $bulan)) {
            return false;
        }

        if ($this->hasActiveGeneration($sekolahId)) {
            return false;
        }

        return ! MonevManualTrigger::query()
            ->where('sekolah_id', $sekolahId)
            ->whereNull('kelas_id')
            ->where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->exists();
    }

    public function canManualTriggerForKelas(int $kelasId, int $tahun, int $bulan): bool
    {
        if (! $this->isCurrentMonth($tahun, $bulan)) {
            return false;
        }

        if ($this->hasActiveGeneration(null, $kelasId)) {
            return false;
        }

        return ! MonevManualTrigger::query()
            ->where('kelas_id', $kelasId)
            ->whereNull('sekolah_id')
            ->where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->exists();
    }

    public function recordManualTriggerForSekolah(int $sekolahId, int $tahun, int $bulan, User $user): void
    {
        MonevManualTrigger::create([
            'sekolah_id' => $sekolahId,
            'kelas_id' => null,
            'tahun' => $tahun,
            'bulan' => $bulan,
            'triggered_by_user_id' => $user->id,
            'triggered_at' => now(),
        ]);
    }

    public function recordManualTriggerForKelas(int $kelasId, int $tahun, int $bulan, User $user): void
    {
        MonevManualTrigger::create([
            'sekolah_id' => null,
            'kelas_id' => $kelasId,
            'tahun' => $tahun,
            'bulan' => $bulan,
            'triggered_by_user_id' => $user->id,
            'triggered_at' => now(),
        ]);
    }

    /**
     * @return Collection<int, Anak>
     */
    public function anaksForSekolah(int $sekolahId, ?int $kelasId = null, ?string $search = null): Collection
    {
        return $this->anaksForSekolahQuery($sekolahId, $kelasId, $search)->get();
    }

    /**
     * @return LengthAwarePaginator<int, Anak>
     */
    public function paginateAnaksForSekolah(
        int $sekolahId,
        ?int $kelasId = null,
        ?string $search = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        return $this->anaksForSekolahQuery($sekolahId, $kelasId, $search)
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return Builder<Anak>
     */
    protected function anaksForSekolahQuery(int $sekolahId, ?int $kelasId = null, ?string $search = null): Builder
    {
        $query = Anak::query()
            ->where('sekolah_id', $sekolahId)
            ->where('status', 'approved')
            ->with('kelas')
            ->orderBy('name');

        if ($kelasId) {
            $query->where('kelas_id', $kelasId);
        }

        auth()->user()?->applyScopedKelas($query);

        $this->applySearchFilter($query, $search);

        return $query;
    }

    /**
     * @param  array<int>  $kelasIds
     * @return Collection<int, Anak>
     */
    public function anaksForKelasIds(array $kelasIds, ?int $filterKelasId = null, ?string $search = null): Collection
    {
        return $this->anaksForKelasIdsQuery($kelasIds, $filterKelasId, $search)->get();
    }

    /**
     * @param  array<int>  $kelasIds
     * @return LengthAwarePaginator<int, Anak>
     */
    public function paginateAnaksForKelasIds(
        array $kelasIds,
        ?int $filterKelasId = null,
        ?string $search = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        return $this->anaksForKelasIdsQuery($kelasIds, $filterKelasId, $search)
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array<int>  $kelasIds
     * @return Builder<Anak>
     */
    protected function anaksForKelasIdsQuery(array $kelasIds, ?int $filterKelasId = null, ?string $search = null): Builder
    {
        if ($filterKelasId && in_array($filterKelasId, $kelasIds, true)) {
            $kelasIds = [$filterKelasId];
        }

        $query = Anak::query()
            ->whereIn('kelas_id', $kelasIds)
            ->where('status', 'approved')
            ->with('kelas')
            ->orderBy('name');

        $this->applySearchFilter($query, $search);

        return $query;
    }

    protected function applySearchFilter($query, ?string $search): void
    {
        $search = trim((string) $search);

        if ($search === '') {
            return;
        }

        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', '%'.$search.'%')
                ->orWhere('nickname', 'like', '%'.$search.'%');
        });
    }

    /**
     * @param  array<int>  $anakIds
     * @return Collection<int, Anak>
     */
    public function anaksByIdsForSekolah(array $anakIds, int $sekolahId): Collection
    {
        if ($anakIds === []) {
            return collect();
        }

        return Anak::query()
            ->where('sekolah_id', $sekolahId)
            ->where('status', 'approved')
            ->whereIn('id', $anakIds)
            ->with('kelas')
            ->orderBy('name')
            ->get();
    }

    /**
     * @param  array<int>  $anakIds
     * @param  array<int>  $kelasIds
     * @return Collection<int, Anak>
     */
    public function anaksByIdsForKelas(array $anakIds, array $kelasIds): Collection
    {
        if ($anakIds === []) {
            return collect();
        }

        return Anak::query()
            ->whereIn('kelas_id', $kelasIds)
            ->where('status', 'approved')
            ->whereIn('id', $anakIds)
            ->with('kelas')
            ->orderBy('name')
            ->get();
    }

    public function resetSummaries(Collection $anaks, int $tahun, int $bulan): int
    {
        if ($anaks->isEmpty()) {
            return 0;
        }

        return MonevSummary::query()
            ->whereIn('anak_id', $anaks->pluck('id'))
            ->where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->delete();
    }

    public function generateForAnak(
        Anak $anak,
        int $tahun,
        int $bulan,
        string $sumber,
        ?User $by = null,
        SumopodAIService|FallbackAiService|null $ai = null
    ): MonevSummary {
        $existing = MonevSummary::query()
            ->where('anak_id', $anak->id)
            ->where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->first();

        if ($this->shouldSkipExistingSummary($existing, $sumber)) {
            return $existing;
        }

        $stats = $this->aggregator->aggregate($anak, $tahun, $bulan);

        if ($stats['total_entri'] === 0) {
            $ringkasan = 'Belum ada data pencapaian matrikulasi pada periode ini.';

            return MonevSummary::updateOrCreate(
                [
                    'anak_id' => $anak->id,
                    'tahun' => $tahun,
                    'bulan' => $bulan,
                ],
                [
                    'ringkasan' => $ringkasan,
                    'data_snapshot' => $stats,
                    'sumber' => $sumber,
                    'generated_at' => now(),
                    'generated_by_user_id' => $by?->id,
                ]
            );
        }

        $ai = $ai ?? $this->resolveAiServiceForSekolah((int) $anak->sekolah_id);

        if (! $ai) {
            throw new \RuntimeException(
                'Pengaturan AI belum dikonfigurasi. Minta admin lembaga untuk mengisi API Key di menu Pengaturan AI.'
            );
        }

        $sekolahName = $anak->sekolah?->name ?? 'PAUD';
        $personaPrompt = SekolahAiPersona::resolveActivePrompt(
            (int) $anak->sekolah_id,
            AiPersonaScope::MONEV,
            $sekolahName
        );

        $aiService = $ai;

        return $this->tokenService->runWithToken(
            (int) $anak->sekolah_id,
            SekolahAiTokenTransaction::TYPE_MONEV,
            $by,
            [
                'anak_id' => $anak->id,
                'tahun' => $tahun,
                'bulan' => $bulan,
                'sumber' => $sumber,
            ],
            'Generate ringkasan monev: '.$anak->displayName(),
            function () use ($aiService, $stats, $personaPrompt, $anak, $tahun, $bulan, $sumber, $by) {
                $ringkasan = $aiService->generateMonevSummary(
                    $stats['anak_name'],
                    $stats['kelas_name'],
                    $stats['periode_label'],
                    $stats,
                    $personaPrompt
                );

                [$ringkasan, $stats] = $this->applyAspekSummariesToSnapshot($ringkasan, $stats);

                return MonevSummary::updateOrCreate(
                    [
                        'anak_id' => $anak->id,
                        'tahun' => $tahun,
                        'bulan' => $bulan,
                    ],
                    [
                        'ringkasan' => $ringkasan,
                        'data_snapshot' => $stats,
                        'sumber' => $sumber,
                        'generated_at' => now(),
                        'generated_by_user_id' => $by?->id,
                    ]
                );
            }
        );
    }

    /**
     * @param  array<string, mixed>  $stats
     * @return array{0: string, 1: array<string, mixed>}
     */
    protected function applyAspekSummariesToSnapshot(string $ringkasan, array $stats): array
    {
        [$ringkasan, $aspekSummaries] = MonevSummaryPresenter::splitRingkasanAndAspekSummaries($ringkasan);

        foreach ($aspekSummaries as $aspek => $points) {
            if (isset($stats['per_aspek'][$aspek])) {
                $stats['per_aspek'][$aspek]['ringkasan'] = $points;
            }
        }

        return [$ringkasan, $stats];
    }

    /**
     * @return array{generated: int, skipped: int, errors: array<string>}
     */
    public function generateForAnaks(
        Collection $anaks,
        int $tahun,
        int $bulan,
        string $sumber,
        ?User $by = null,
        SumopodAIService|FallbackAiService|null $ai = null
    ): array {
        $generated = 0;
        $skipped = 0;
        $errors = [];

        foreach ($anaks as $anak) {
            try {
                $existing = MonevSummary::query()
                    ->where('anak_id', $anak->id)
                    ->where('tahun', $tahun)
                    ->where('bulan', $bulan)
                    ->first();

                if ($this->shouldSkipExistingSummary($existing, $sumber)) {
                    $skipped++;

                    continue;
                }

                $this->generateForAnak($anak, $tahun, $bulan, $sumber, $by, $ai);
                $generated++;
            } catch (\Throwable $e) {
                $errors[] = $anak->displayName().': '.$e->getMessage();
            }
        }

        return compact('generated', 'skipped', 'errors');
    }

    public function parsePeriodeFromRequest(?int $tahun, ?int $bulan): array
    {
        $now = now();
        $tahun = $tahun && $tahun >= 2000 && $tahun <= 2100 ? $tahun : (int) $now->year;
        $bulan = $bulan && $bulan >= 1 && $bulan <= 12 ? $bulan : (int) $now->month;

        return [$tahun, $bulan];
    }

    public function previousMonthPeriode(): array
    {
        $prev = Carbon::now()->subMonth();

        return [(int) $prev->year, (int) $prev->month];
    }

    public function dispatchManualGeneration(
        Collection $anaks,
        int $tahun,
        int $bulan,
        User $by,
        ?int $sekolahId = null,
        ?int $kelasId = null
    ): MonevGeneration {
        $this->assertCanStartManualGeneration($by, $tahun, $bulan, $sekolahId, $kelasId);

        $aiCheckId = $sekolahId ?? (int) $anaks->first()?->sekolah_id;
        if ($aiCheckId && ! $this->resolveAiServiceForSekolah($aiCheckId)) {
            throw new MonevManualGenerationException('API Key AI belum dikonfigurasi. Minta admin lembaga untuk mengisi Pengaturan AI.');
        }

        $this->assertSufficientTokensForAnaks(
            $anaks,
            $this->resolveSekolahIdForTokenCheck($anaks, $sekolahId),
            $tahun,
            $bulan,
            MonevSummary::SUMBER_MANUAL
        );

        $generation = DB::transaction(function () use ($anaks, $tahun, $bulan, $by, $sekolahId, $kelasId) {
            if ($this->activeGenerationQuery($sekolahId, $kelasId)->lockForUpdate()->exists()) {
                throw new MonevManualGenerationException('Masih ada proses generate yang berjalan. Tunggu hingga selesai.');
            }

            try {
                if ($kelasId) {
                    $this->recordManualTriggerForKelas($kelasId, $tahun, $bulan, $by);
                } else {
                    $this->recordManualTriggerForSekolah($sekolahId, $tahun, $bulan, $by);
                }
            } catch (QueryException $e) {
                if ($this->isUniqueConstraintViolation($e)) {
                    throw new MonevManualGenerationException(
                        'Ringkasan manual untuk bulan ini sudah pernah digenerate atau sedang diproses.'
                    );
                }

                throw $e;
            }

            return MonevGeneration::create([
                'sekolah_id' => $kelasId ? null : $sekolahId,
                'kelas_id' => $kelasId,
                'tahun' => $tahun,
                'bulan' => $bulan,
                'sumber' => MonevSummary::SUMBER_MANUAL,
                'total' => $anaks->count(),
                'status' => MonevGeneration::STATUS_PENDING,
                'triggered_by_user_id' => $by->id,
            ]);
        });

        $this->dispatchJobsForGeneration($generation, $anaks, $tahun, $bulan, MonevSummary::SUMBER_MANUAL, $by);

        return $generation;
    }

    public function dispatchSelectedGeneration(
        Collection $anaks,
        int $tahun,
        int $bulan,
        User $by,
        ?int $sekolahId = null,
        ?int $kelasId = null
    ): MonevGeneration {
        $this->assertCanStartSelectedGeneration($by, $sekolahId, $kelasId);
        $this->assertSufficientTokensForAnaks(
            $anaks,
            $this->resolveSekolahIdForTokenCheck($anaks, $sekolahId),
            $tahun,
            $bulan,
            MonevSummary::SUMBER_MANUAL
        );

        $generation = DB::transaction(function () use ($anaks, $tahun, $bulan, $by, $sekolahId, $kelasId) {
            if ($this->activeGenerationQuery($sekolahId, $kelasId)->lockForUpdate()->exists()) {
                throw new MonevManualGenerationException('Masih ada proses generate yang berjalan. Tunggu hingga selesai.');
            }

            return MonevGeneration::create([
                'sekolah_id' => $kelasId ? null : $sekolahId,
                'kelas_id' => $kelasId,
                'tahun' => $tahun,
                'bulan' => $bulan,
                'sumber' => MonevSummary::SUMBER_MANUAL,
                'total' => $anaks->count(),
                'status' => MonevGeneration::STATUS_PENDING,
                'triggered_by_user_id' => $by->id,
            ]);
        });

        $this->dispatchJobsForGeneration($generation, $anaks, $tahun, $bulan, MonevSummary::SUMBER_MANUAL, $by);

        return $generation;
    }

    /**
     * @deprecated Use dispatchManualGeneration for manual flows.
     */
    public function dispatchGeneration(
        Collection $anaks,
        int $tahun,
        int $bulan,
        string $sumber,
        User $by,
        ?int $sekolahId = null,
        ?int $kelasId = null
    ): MonevGeneration {
        $generation = DB::transaction(function () use ($anaks, $tahun, $bulan, $sumber, $by, $sekolahId, $kelasId) {
            if ($this->activeGenerationQuery($sekolahId, $kelasId)->lockForUpdate()->exists()) {
                throw new MonevManualGenerationException('Masih ada proses generate yang berjalan. Tunggu hingga selesai.');
            }

            return MonevGeneration::create([
                'sekolah_id' => $kelasId ? null : $sekolahId,
                'kelas_id' => $kelasId,
                'tahun' => $tahun,
                'bulan' => $bulan,
                'sumber' => $sumber,
                'total' => $anaks->count(),
                'status' => MonevGeneration::STATUS_PENDING,
                'triggered_by_user_id' => $by->id,
            ]);
        });

        $this->dispatchJobsForGeneration($generation, $anaks, $tahun, $bulan, $sumber, $by);

        return $generation;
    }

    protected function dispatchJobsForGeneration(
        MonevGeneration $generation,
        Collection $anaks,
        int $tahun,
        int $bulan,
        string $sumber,
        User $by
    ): void {
        foreach ($anaks as $anak) {
            GenerateMonevSummaryJob::dispatch(
                $generation->id,
                $anak->id,
                $tahun,
                $bulan,
                $sumber,
                $by->id
            )->afterResponse();
        }
    }

    public function finalizeStaleGenerations(int $staleHours = 3): int
    {
        $cutoff = now()->subHours($staleHours);
        $finalized = 0;

        MonevGeneration::query()
            ->whereIn('status', [MonevGeneration::STATUS_PENDING, MonevGeneration::STATUS_RUNNING])
            ->where('updated_at', '<', $cutoff)
            ->orderBy('id')
            ->each(function (MonevGeneration $generation) use (&$finalized) {
                DB::transaction(function () use ($generation, &$finalized) {
                    $generation = MonevGeneration::lockForUpdate()->find($generation->id);

                    if (! $generation || $generation->isFinished()) {
                        return;
                    }

                    $remaining = $generation->total - $generation->processed();

                    if ($remaining > 0) {
                        $generation->failed += $remaining;
                        $generation->appendError('Proses generate timeout  beberapa siswa tidak selesai diproses.');
                    }

                    $status = $generation->failed > 0 && $generation->completed === 0 && $generation->skipped === 0
                        ? MonevGeneration::STATUS_FAILED
                        : MonevGeneration::STATUS_COMPLETED;

                    $generation->update([
                        'status' => $status,
                        'finished_at' => now(),
                    ]);

                    $finalized++;
                });
            });

        return $finalized;
    }

    public function incrementGenerationFailed(MonevGeneration $generation, string $message): void
    {
        DB::transaction(function () use ($generation, $message) {
            $generation = MonevGeneration::lockForUpdate()->find($generation->id);

            if (! $generation || $generation->isFinished()) {
                return;
            }

            $generation->appendError($this->sanitizeGenerationError($message));
            $generation->increment('failed');
            $generation->save();
            $this->finalizeGenerationIfDone($generation);
        });
    }

    public function incrementGenerationCompleted(MonevGeneration $generation): void
    {
        DB::transaction(function () use ($generation) {
            $generation = MonevGeneration::lockForUpdate()->find($generation->id);

            if (! $generation || $generation->isFinished()) {
                return;
            }

            $generation->increment('completed');
            $this->finalizeGenerationIfDone($generation);
        });
    }

    public function incrementGenerationSkipped(MonevGeneration $generation): void
    {
        DB::transaction(function () use ($generation) {
            $generation = MonevGeneration::lockForUpdate()->find($generation->id);

            if (! $generation || $generation->isFinished()) {
                return;
            }

            $generation->increment('skipped');
            $this->finalizeGenerationIfDone($generation);
        });
    }

    public function finalizeGenerationIfDone(MonevGeneration $generation): void
    {
        $generation->refresh();

        if ($generation->processed() < $generation->total) {
            return;
        }

        $status = $generation->failed > 0 && $generation->completed === 0 && $generation->skipped === 0
            ? MonevGeneration::STATUS_FAILED
            : MonevGeneration::STATUS_COMPLETED;

        $generation->update([
            'status' => $status,
            'finished_at' => now(),
        ]);
    }

    public function sanitizeGenerationError(string $message): string
    {
        $message = preg_replace('/AI API error:\s*\d+\s*\{.*$/s', 'AI API error.', $message) ?? $message;

        return mb_substr(trim($message), 0, 500);
    }

    protected function isUniqueConstraintViolation(QueryException $e): bool
    {
        $sqlState = $e->errorInfo[0] ?? '';

        return in_array($sqlState, ['23000', '23505'], true);
    }

    public function generationStatusPayload(MonevGeneration $generation): array
    {
        return [
            'id' => $generation->id,
            'status' => $generation->status,
            'total' => $generation->total,
            'completed' => $generation->completed,
            'skipped' => $generation->skipped,
            'failed' => $generation->failed,
            'processed' => $generation->processed(),
            'percent' => $generation->progressPercent(),
            'is_finished' => $generation->isFinished(),
            'errors' => array_map(
                fn (string $error) => $this->sanitizeGenerationError($error),
                $generation->errors ?? []
            ),
        ];
    }
}
