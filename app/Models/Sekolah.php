<?php

namespace App\Models;

use App\Models\Concerns\LogsScopedActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Sekolah extends Model
{
    use LogsScopedActivity;

    public const STATUS_PENDING = 'pending';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_SUSPENDED = 'suspended';

    public const STATUS_REJECTED = 'rejected';

    public const RADIUS_DEFAULT_METER = 100;

    protected $fillable = [
        'lembaga_id',
        'name',
        'address',
        'phone',
        'nisn',
        'location_coordinate',
        'radius_m',
        'photo',
        'status',
        'slug',
    ];

    protected $casts = [
        'radius_m' => 'integer',
    ];

    /** Radius geofence presensi guru (meter). */
    public function radiusMeter(): int
    {
        return $this->radius_m !== null && $this->radius_m > 0
            ? (int) $this->radius_m
            : self::RADIUS_DEFAULT_METER;
    }

    public function isOperational(): bool
    {
        return ($this->status ?? self::STATUS_ACTIVE) === self::STATUS_ACTIVE;
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function lembaga(): BelongsTo
    {
        return $this->belongsTo(Lembaga::class);
    }

    public function aiPersonas(): HasMany
    {
        return $this->hasMany(SekolahAiPersona::class);
    }

    public function aiChatDataAccess(): HasOne
    {
        return $this->hasOne(SekolahAiChatDataAccess::class);
    }

    public function aiToken(): HasOne
    {
        return $this->hasOne(SekolahAiToken::class);
    }

    public function aiSettings(): HasOne
    {
        return $this->hasOne(SekolahAiSetting::class);
    }

    public function aiTokenTransactions(): HasMany
    {
        return $this->hasMany(SekolahAiTokenTransaction::class);
    }

    public function akuns(): HasMany
    {
        return $this->hasMany(Akun::class);
    }

    public function jurnals(): HasMany
    {
        return $this->hasMany(Jurnal::class);
    }

    public function akuntansiSetting(): HasOne
    {
        return $this->hasOne(AkuntansiSetting::class);
    }

    public function jamKerjas(): HasMany
    {
        return $this->hasMany(SekolahJamKerja::class)->orderBy('hari');
    }

    public function presensiShifts(): HasMany
    {
        return $this->hasMany(PresensiShift::class)->orderBy('sort_order')->orderBy('id');
    }

    public function defaultPresensiShift(): HasOne
    {
        return $this->hasOne(PresensiShift::class)->where('is_default', true);
    }

    public function hariLiburs(): HasMany
    {
        return $this->hasMany(SekolahHariLibur::class)->orderBy('tanggal');
    }
}
