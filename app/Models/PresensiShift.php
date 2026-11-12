<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSekolah;
use App\Models\Concerns\LogsScopedActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PresensiShift extends Model
{
    use BelongsToSekolah, LogsScopedActivity;

    public const RADIUS_DEFAULT_METER = 100;

    protected $fillable = [
        'sekolah_id',
        'name',
        'location_coordinate',
        'radius_m',
        'is_default',
        'sort_order',
    ];

    protected $casts = [
        'radius_m' => 'integer',
        'is_default' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function radiusMeter(): int
    {
        return $this->radius_m !== null && $this->radius_m > 0
            ? (int) $this->radius_m
            : self::RADIUS_DEFAULT_METER;
    }

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }

    public function jamKerjas(): HasMany
    {
        return $this->hasMany(SekolahJamKerja::class)->orderBy('hari');
    }

    public function pengajars(): HasMany
    {
        return $this->hasMany(Pengajar::class);
    }
}
