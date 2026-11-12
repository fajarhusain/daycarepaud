<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSekolah;
use App\Models\Concerns\LogsScopedActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MonevGuruEvaluasi extends Model
{
    use BelongsToSekolah, LogsScopedActivity;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_FINAL = 'final';

    protected $fillable = [
        'sekolah_id',
        'pengajar_id',
        'evaluator_user_id',
        'judul',
        'periode_mulai',
        'periode_selesai',
        'catatan_umum',
        'rekomendasi',
        'skor_keseluruhan',
        'status',
        'finalized_at',
    ];

    protected function casts(): array
    {
        return [
            'periode_mulai' => 'date',
            'periode_selesai' => 'date',
            'skor_keseluruhan' => 'integer',
            'finalized_at' => 'datetime',
        ];
    }

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }

    public function pengajar(): BelongsTo
    {
        return $this->belongsTo(Pengajar::class);
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(MonevGuruPenilaianItem::class);
    }

    public function scopeForSekolah(Builder $query, int $sekolahId): Builder
    {
        return $query->where('sekolah_id', $sekolahId);
    }

    public function scopeFinal(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_FINAL);
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isFinal(): bool
    {
        return $this->status === self::STATUS_FINAL;
    }

    public function periodeLabel(): string
    {
        $mulai = $this->periode_mulai->format('d M Y');
        $selesai = $this->periode_selesai->format('d M Y');

        return $mulai === $selesai ? $mulai : "{$mulai} – {$selesai}";
    }
}
