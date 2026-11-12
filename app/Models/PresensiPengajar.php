<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSekolah;
use App\Models\Concerns\LogsScopedActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PresensiPengajar extends Model
{
    use BelongsToSekolah, LogsScopedActivity;

    public const SUMBER_SELF = 'self';

    public const SUMBER_MANUAL = 'manual';

    protected $fillable = [
        'sekolah_id',
        'pengajar_id',
        'presensi_shift_id',
        'tanggal',
        'hadir',
        'status',
        'check_in_at',
        'check_out_at',
        'check_in_latitude',
        'check_in_longitude',
        'check_out_latitude',
        'check_out_longitude',
        'check_in_accuracy',
        'check_out_accuracy',
        'jarak_meter',
        'di_luar_area',
        'sumber',
        'terlambat_masuk',
        'menit_telat_masuk',
        'telat_pulang',
        'menit_telat_pulang',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'hadir' => 'boolean',
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
        'check_in_latitude' => 'float',
        'check_in_longitude' => 'float',
        'check_out_latitude' => 'float',
        'check_out_longitude' => 'float',
        'check_in_accuracy' => 'float',
        'check_out_accuracy' => 'float',
        'jarak_meter' => 'float',
        'di_luar_area' => 'boolean',
        'terlambat_masuk' => 'boolean',
        'menit_telat_masuk' => 'integer',
        'telat_pulang' => 'boolean',
        'menit_telat_pulang' => 'integer',
    ];

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }

    public function pengajar(): BelongsTo
    {
        return $this->belongsTo(Pengajar::class);
    }

    public function presensiShift(): BelongsTo
    {
        return $this->belongsTo(PresensiShift::class);
    }

    public function sudahCheckIn(): bool
    {
        return $this->check_in_at !== null;
    }

    public function sudahCheckOut(): bool
    {
        return $this->check_out_at !== null;
    }
}
