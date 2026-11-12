<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSekolah;
use App\Models\Concerns\LogsScopedActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SekolahAiSetting extends Model
{
    use BelongsToSekolah, LogsScopedActivity;

    public const DEFAULT_FALLBACK = 'Maaf, fitur ini sedang terbatas.';

    protected $fillable = [
        'sekolah_id',
        'chat_orangtua_enabled',
        'fallback_monev',
        'fallback_pencapaian',
        'fallback_chat',
        'fallback_persona',
    ];

    protected function casts(): array
    {
        return [
            'chat_orangtua_enabled' => 'boolean',
        ];
    }

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }

    public function resolveFallback(string $feature): string
    {
        $column = match ($feature) {
            'monev' => 'fallback_monev',
            'pencapaian' => 'fallback_pencapaian',
            'chat' => 'fallback_chat',
            'persona' => 'fallback_persona',
            default => null,
        };

        if ($column === null) {
            return self::DEFAULT_FALLBACK;
        }

        $value = $this->{$column};

        return filled($value) ? trim($value) : self::DEFAULT_FALLBACK;
    }
}
