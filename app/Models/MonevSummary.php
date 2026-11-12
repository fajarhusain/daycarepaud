<?php

namespace App\Models;

use App\Support\IndonesianMonths;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonevSummary extends Model
{
    public const SUMBER_OTOMATIS = 'otomatis';

    public const SUMBER_MANUAL = 'manual';

    protected $fillable = [
        'anak_id',
        'tahun',
        'bulan',
        'ringkasan',
        'data_snapshot',
        'sumber',
        'generated_at',
        'generated_by_user_id',
    ];

    protected $casts = [
        'data_snapshot' => 'array',
        'generated_at' => 'datetime',
        'tahun' => 'integer',
        'bulan' => 'integer',
    ];

    public function anak(): BelongsTo
    {
        return $this->belongsTo(Anak::class);
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by_user_id');
    }

    public function scopeForPeriode($query, int $tahun, int $bulan)
    {
        return $query->where('tahun', $tahun)->where('bulan', $bulan);
    }

    public function periodeLabel(): string
    {
        return IndonesianMonths::label($this->bulan, $this->tahun);
    }
}
