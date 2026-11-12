<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSekolah;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SekolahAiTokenTransaction extends Model
{
    use BelongsToSekolah;

    public const TYPE_TOPUP = 'topup';

    public const TYPE_MONEV = 'monev';

    public const TYPE_PENCAPAIAN = 'pencapaian';

    public const TYPE_CHAT = 'chat';

    public const TYPE_PERSONA = 'persona';

    public const UPDATED_AT = null;

    protected $fillable = [
        'sekolah_id',
        'amount',
        'type',
        'description',
        'metadata',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_TOPUP => 'Top-up',
            self::TYPE_MONEV => 'Monev',
            self::TYPE_PENCAPAIAN => 'Pencapaian',
            self::TYPE_CHAT => 'Chat',
            self::TYPE_PERSONA => 'Persona',
            default => $this->type,
        };
    }

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
