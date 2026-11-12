<?php

namespace App\Models;

use App\Models\Concerns\LogsScopedActivity;
use App\Services\FallbackAiService;
use App\Services\SumopodAIService;
use App\Support\AiProvider;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiSetting extends Model
{
    use LogsScopedActivity;

    protected array $activityLogExcept = ['ai_api_key'];

    /** Prevent recursive slot sync from syncLegacyFromSlot(). */
    protected static bool $syncingSlots = false;

    protected $fillable = [
        'lembaga_id',
        'ai_provider',
        'ai_api_key',
        'ai_model',
        'ai_base_url',
    ];

    public function lembaga(): BelongsTo
    {
        return $this->belongsTo(Lembaga::class);
    }

    public function slots(): HasMany
    {
        return $this->hasMany(AiProviderSlot::class, 'lembaga_id', 'lembaga_id')
            ->orderBy('slot');
    }

    /**
     * Enabled slots with a decryptable API key, ordered by slot number.
     *
     * @return Collection<int, AiProviderSlot>
     */
    public function enabledSlots(): Collection
    {
        return $this->slots()
            ->where('is_enabled', true)
            ->orderBy('slot')
            ->get()
            ->filter(fn (AiProviderSlot $slot) => $slot->hasValidApiKey())
            ->values();
    }

    /**
     * Resolve fallback chain from provider slots. Falls back to legacy columns
     * when no slots exist yet (tests / pre-migration fixtures).
     */
    public function resolveAiService(): ?FallbackAiService
    {
        $slotModels = $this->enabledSlots();

        if ($slotModels->isEmpty() && filled($this->ai_api_key)) {
            return new FallbackAiService([[
                'slot' => 1,
                'label' => 'Slot 1 (Utama)',
                'provider' => AiProvider::label($this->ai_provider ?? 'sumopod'),
                'model' => $this->ai_model ?? 'gpt-4o-mini',
                'service' => $this->legacyToAiService(),
            ]]);
        }

        if ($slotModels->isEmpty()) {
            return null;
        }

        $services = $slotModels->map(fn (AiProviderSlot $slot) => [
            'slot' => (int) $slot->slot,
            'label' => $slot->slotLabel(),
            'provider' => $slot->providerLabel(),
            'model' => $slot->ai_model ?? 'gpt-4o-mini',
            'service' => $slot->toAiService(),
        ])->all();

        return new FallbackAiService($services);
    }

    /**
     * @deprecated Prefer resolveAiService() for fallback chain support.
     */
    public function toAiService(): SumopodAIService|FallbackAiService
    {
        return $this->resolveAiService() ?? $this->legacyToAiService();
    }

    protected function legacyToAiService(): SumopodAIService
    {
        $baseUrl = AiProvider::resolveBaseUrl(
            $this->ai_provider ?? 'sumopod',
            $this->ai_base_url
        );

        return new SumopodAIService(
            $this->ai_api_key,
            $this->ai_model ?? 'gpt-4o-mini',
            $baseUrl
        );
    }

    /**
     * Sync legacy ai_settings columns from slot 1.
     * Keeps old fixtures / dashboards working while slots are source of truth.
     */
    public function syncLegacyFromSlot(AiProviderSlot $slot): void
    {
        if ((int) $slot->slot !== 1) {
            return;
        }

        static::$syncingSlots = true;

        try {
            $this->forceFill([
                'ai_provider' => $slot->ai_provider,
                'ai_model' => $slot->ai_model,
                'ai_base_url' => $slot->ai_base_url,
            ]);

            if ($slot->hasStoredApiKey()) {
                $this->attributes['ai_api_key'] = $slot->getAttributes()['ai_api_key'] ?? null;
            }

            $this->save();
        } finally {
            static::$syncingSlots = false;
        }
    }

    /**
     * Ensure slot 1 exists when creating AiSetting the legacy way (tests).
     */
    protected static function booted(): void
    {
        static::saved(function (AiSetting $setting): void {
            if (static::$syncingSlots) {
                return;
            }

            $hasSlot1 = AiProviderSlot::query()
                ->where('lembaga_id', $setting->lembaga_id)
                ->where('slot', 1)
                ->exists();

            if ($hasSlot1) {
                return;
            }

            if (! filled($setting->attributes['ai_api_key'] ?? null) && ! filled($setting->ai_model)) {
                return;
            }

            AiProviderSlot::query()->create([
                'lembaga_id' => $setting->lembaga_id,
                'slot' => 1,
                'ai_provider' => $setting->ai_provider ?? 'sumopod',
                'ai_api_key' => $setting->ai_api_key,
                'ai_model' => $setting->ai_model,
                'ai_base_url' => $setting->ai_base_url,
                'is_enabled' => true,
            ]);
        });
    }

    protected function aiApiKey(): Attribute
    {
        return Attribute::make(
            get: function (?string $value) {
                if ($value === null || $value === '') {
                    return null;
                }

                try {
                    return decrypt($value, false);
                } catch (DecryptException) {
                    return null;
                }
            },
            set: function (?string $value) {
                if ($value === null || trim($value) === '') {
                    return null;
                }

                return encrypt($value, false);
            },
        );
    }

    /** Kolom DB berisi nilai (mungkin tidak bisa didekripsi). */
    public function hasStoredApiKey(): bool
    {
        $slot = $this->slots()->where('is_enabled', true)->get()
            ->first(fn (AiProviderSlot $s) => $s->hasStoredApiKey());

        if ($slot) {
            return true;
        }

        return filled($this->attributes['ai_api_key'] ?? null);
    }

    /** API key berhasil didekripsi dan siap dipakai (slot atau legacy). */
    public function hasValidApiKey(): bool
    {
        if ($this->enabledSlots()->isNotEmpty()) {
            return true;
        }

        return filled($this->ai_api_key);
    }

    /** Data ada di DB tetapi gagal didekripsi (mis. APP_KEY berubah). */
    public function apiKeyNeedsReentry(): bool
    {
        $slot1 = $this->slots()->where('slot', 1)->first();
        if ($slot1) {
            return $slot1->apiKeyNeedsReentry();
        }

        if (! filled($this->attributes['ai_api_key'] ?? null)) {
            return false;
        }

        $raw = $this->attributes['ai_api_key'];

        try {
            decrypt($raw, false);

            return false;
        } catch (DecryptException) {
            return true;
        }
    }

    public function providerLabel(): string
    {
        $slot1 = $this->slots()->where('slot', 1)->where('is_enabled', true)->first();
        if ($slot1) {
            return $slot1->providerLabel();
        }

        return AiProvider::label($this->ai_provider ?? 'sumopod');
    }

    public function primaryModel(): ?string
    {
        $slot1 = $this->slots()->where('slot', 1)->where('is_enabled', true)->first();

        return $slot1?->ai_model ?? $this->ai_model;
    }

    public function configuredSlotCount(): int
    {
        return $this->enabledSlots()->count();
    }
}
