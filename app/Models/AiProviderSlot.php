<?php

namespace App\Models;

use App\Models\Concerns\LogsScopedActivity;
use App\Services\SumopodAIService;
use App\Support\AiProvider;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiProviderSlot extends Model
{
    use LogsScopedActivity;

    public const MAX_SLOTS = 3;

    protected array $activityLogExcept = ['ai_api_key'];

    protected $fillable = [
        'lembaga_id',
        'slot',
        'ai_provider',
        'ai_api_key',
        'ai_model',
        'ai_base_url',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'slot' => 'integer',
            'is_enabled' => 'boolean',
        ];
    }

    public function lembaga(): BelongsTo
    {
        return $this->belongsTo(Lembaga::class);
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

    public function hasStoredApiKey(): bool
    {
        return filled($this->attributes['ai_api_key'] ?? null);
    }

    public function hasValidApiKey(): bool
    {
        return filled($this->ai_api_key);
    }

    public function apiKeyNeedsReentry(): bool
    {
        if (! $this->hasStoredApiKey()) {
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
        return AiProvider::label($this->ai_provider ?? 'sumopod');
    }

    public function slotLabel(): string
    {
        return match ((int) $this->slot) {
            1 => 'Slot 1 (Utama)',
            2 => 'Slot 2 (Backup 1)',
            3 => 'Slot 3 (Backup 2)',
            default => 'Slot '.$this->slot,
        };
    }

    public function toAiService(): SumopodAIService
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
}
