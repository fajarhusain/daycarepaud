<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSekolah;
use App\Models\Concerns\LogsScopedActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Presensi extends Model
{
    use BelongsToSekolah, LogsScopedActivity;

    protected $fillable = [
        'sekolah_id',
        'kelas_id',
        'anak_id',
        'tanggal',
        'hadir',
        'status',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'hadir' => 'boolean',
        ];
    }

    /** @return list<string> */
    public static function statusOptions(): array
    {
        return ['hadir', 'izin', 'sakit', 'alpha'];
    }

    /** @return array<string, string> */
    public static function statusLabels(): array
    {
        return [
            'hadir' => 'Hadir',
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            'alpha' => 'Alpha',
        ];
    }

    public static function hadirFromStatus(string $status): bool
    {
        return $status === 'hadir';
    }

    public static function labelForStatus(?string $status): string
    {
        if ($status === null || $status === '') {
            return '-';
        }

        return self::statusLabels()[$status] ?? ucfirst($status);
    }

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }

    public function anak(): BelongsTo
    {
        return $this->belongsTo(Anak::class);
    }
}
