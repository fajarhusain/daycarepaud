<?php

namespace App\Services;

use App\Exceptions\InsufficientAiTokensException;
use App\Models\Sekolah;
use App\Models\SekolahAiSetting;
use App\Models\SekolahAiToken;
use App\Models\SekolahAiTokenTransaction;
use App\Models\User;
use App\Support\AiTokenFeature;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class AiTokenService
{
    public function getBalance(int $sekolahId): int
    {
        return (int) (SekolahAiToken::query()
            ->where('sekolah_id', $sekolahId)
            ->value('balance') ?? 0);
    }

    public function resolveFallback(int $sekolahId, string $feature): string
    {
        $settings = SekolahAiSetting::query()
            ->where('sekolah_id', $sekolahId)
            ->first();

        if ($settings === null) {
            return SekolahAiSetting::DEFAULT_FALLBACK;
        }

        return $settings->resolveFallback($feature);
    }

    public function resolveSettings(int $sekolahId): SekolahAiSetting
    {
        return SekolahAiSetting::query()->firstOrCreate(
            ['sekolah_id' => $sekolahId],
            [
                'chat_orangtua_enabled' => true,
                'fallback_monev' => null,
                'fallback_pencapaian' => null,
                'fallback_chat' => null,
                'fallback_persona' => null,
            ]
        );
    }

    public function isChatOrangTuaEnabled(int $sekolahId): bool
    {
        $settings = SekolahAiSetting::query()->where('sekolah_id', $sekolahId)->first();

        return $settings === null || $settings->chat_orangtua_enabled;
    }

    public function updateChatOrangTuaEnabled(int $sekolahId, bool $enabled): SekolahAiSetting
    {
        $settings = $this->resolveSettings($sekolahId);
        $settings->update(['chat_orangtua_enabled' => $enabled]);

        return $settings->fresh();
    }

    public function updateFallbacks(int $sekolahId, array $data): SekolahAiSetting
    {
        $settings = $this->resolveSettings($sekolahId);
        $updates = [];

        foreach (['fallback_monev', 'fallback_pencapaian', 'fallback_chat', 'fallback_persona'] as $field) {
            if (array_key_exists($field, $data)) {
                $updates[$field] = $data[$field];
            }
        }

        if ($updates !== []) {
            $settings->update($updates);
        }

        return $settings->fresh();
    }

    /**
     * Potong token secara atomik, jalankan callback, refund jika callback gagal.
     */
    public function runWithToken(
        int $sekolahId,
        string $type,
        ?User $by,
        array $metadata,
        ?string $description,
        callable $callback
    ): mixed {
        $this->consume($sekolahId, 1, $type, $by, $metadata, $description);

        try {
            return $callback();
        } catch (\Throwable $e) {
            if ($by !== null) {
                $this->topUp($sekolahId, 1, $by, 'Refund: AI gagal');
            }

            throw $e;
        }
    }

    public function topUp(int $sekolahId, int $amount, User $by, ?string $description = null): void
    {
        if ($amount < 1) {
            throw new \InvalidArgumentException('Jumlah top-up minimal 1 token.');
        }

        DB::transaction(function () use ($sekolahId, $amount, $by, $description) {
            $token = $this->lockOrCreateTokenRow($sekolahId);
            $token->increment('balance', $amount);

            SekolahAiTokenTransaction::create([
                'sekolah_id' => $sekolahId,
                'amount' => $amount,
                'type' => SekolahAiTokenTransaction::TYPE_TOPUP,
                'description' => $description ?? 'Top-up token AI',
                'metadata' => null,
                'created_by_user_id' => $by->id,
            ]);
        });
    }

    public function consume(
        int $sekolahId,
        int $amount,
        string $type,
        ?User $by = null,
        array $metadata = [],
        ?string $description = null
    ): void {
        if ($amount < 1) {
            throw new \InvalidArgumentException('Jumlah konsumsi minimal 1 token.');
        }

        $feature = AiTokenFeature::featureFromTransactionType($type);

        DB::transaction(function () use ($sekolahId, $amount, $type, $by, $metadata, $description, $feature) {
            $token = SekolahAiToken::query()
                ->where('sekolah_id', $sekolahId)
                ->lockForUpdate()
                ->first();

            $balance = (int) ($token?->balance ?? 0);

            if ($balance < $amount || $token === null) {
                throw new InsufficientAiTokensException(
                    $sekolahId,
                    $feature,
                    $this->resolveFallback($sekolahId, $feature)
                );
            }

            $token->decrement('balance', $amount);

            SekolahAiTokenTransaction::create([
                'sekolah_id' => $sekolahId,
                'amount' => -$amount,
                'type' => $type,
                'description' => $description,
                'metadata' => $metadata ?: null,
                'created_by_user_id' => $by?->id,
            ]);
        });
    }

    /**
     * @return LengthAwarePaginator<SekolahAiTokenTransaction>
     */
    public function paginateTransactions(int $lembagaId, ?int $sekolahId = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = SekolahAiTokenTransaction::query()
            ->with(['sekolah:id,name', 'createdBy:id,name'])
            ->whereHas('sekolah', fn ($q) => $q->where('lembaga_id', $lembagaId))
            ->latest('created_at');

        if ($sekolahId !== null) {
            $query->where('sekolah_id', $sekolahId);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * @return LengthAwarePaginator<SekolahAiTokenTransaction>
     */
    public function paginateTransactionsForSekolah(int $sekolahId, int $perPage = 20): LengthAwarePaginator
    {
        return SekolahAiTokenTransaction::query()
            ->with('createdBy:id,name')
            ->where('sekolah_id', $sekolahId)
            ->latest('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return array<int, array{sekolah: Sekolah, balance: int}>
     */
    public function schoolsWithBalances(int $lembagaId): array
    {
        $sekolahs = Sekolah::query()
            ->where('lembaga_id', $lembagaId)
            ->orderBy('name')
            ->get();

        $balances = SekolahAiToken::query()
            ->whereIn('sekolah_id', $sekolahs->pluck('id'))
            ->pluck('balance', 'sekolah_id');

        return $sekolahs->map(fn (Sekolah $sekolah) => [
            'sekolah' => $sekolah,
            'balance' => (int) ($balances[$sekolah->id] ?? 0),
        ])->all();
    }

    public function assertSekolahBelongsToLembaga(int $sekolahId, int $lembagaId): Sekolah
    {
        $sekolah = Sekolah::query()
            ->where('id', $sekolahId)
            ->where('lembaga_id', $lembagaId)
            ->first();

        if ($sekolah === null) {
            abort(404, 'Sekolah tidak ditemukan atau tidak termasuk lembaga Anda.');
        }

        return $sekolah;
    }

    protected function lockOrCreateTokenRow(int $sekolahId): SekolahAiToken
    {
        $token = SekolahAiToken::query()
            ->where('sekolah_id', $sekolahId)
            ->lockForUpdate()
            ->first();

        if ($token !== null) {
            return $token;
        }

        try {
            SekolahAiToken::create([
                'sekolah_id' => $sekolahId,
                'balance' => 0,
            ]);
        } catch (QueryException $e) {
            if (! $this->isUniqueConstraintViolation($e)) {
                throw $e;
            }
        }

        return SekolahAiToken::query()
            ->where('sekolah_id', $sekolahId)
            ->lockForUpdate()
            ->firstOrFail();
    }

    protected function isUniqueConstraintViolation(QueryException $e): bool
    {
        $code = (string) ($e->errorInfo[1] ?? '');

        return in_array($code, ['1062', '23505'], true);
    }
}
