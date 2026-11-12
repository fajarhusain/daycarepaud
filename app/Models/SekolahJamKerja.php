<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSekolah;
use App\Models\Concerns\LogsScopedActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SekolahJamKerja extends Model
{
    use BelongsToSekolah, LogsScopedActivity;

    /** ISO: 1=Senin ... 7=Minggu */
    public const HARI = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
        7 => 'Minggu',
    ];

    protected $fillable = [
        'sekolah_id',
        'presensi_shift_id',
        'hari',
        'is_kerja',
        'jam_masuk',
        'jam_pulang',
        'toleransi_masuk_menit',
        'toleransi_pulang_menit',
    ];

    protected $casts = [
        'hari' => 'integer',
        'is_kerja' => 'boolean',
        'toleransi_masuk_menit' => 'integer',
        'toleransi_pulang_menit' => 'integer',
    ];

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }

    public function presensiShift(): BelongsTo
    {
        return $this->belongsTo(PresensiShift::class);
    }

    public function namaHari(): string
    {
        return self::HARI[$this->hari] ?? '-';
    }
}
