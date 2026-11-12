<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSekolah;
use App\Models\Concerns\LogsScopedActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KomponenGaji extends Model
{
    use BelongsToSekolah, LogsScopedActivity;

    public const JENIS_TAMBAH = 'tambah';

    public const JENIS_POTONG = 'potong';

    protected $fillable = [
        'sekolah_id',
        'nama',
        'jenis',
        'nominal_default',
        'urutan',
        'is_aktif',
    ];

    protected function casts(): array
    {
        return [
            'nominal_default' => 'decimal:2',
            'urutan' => 'integer',
            'is_aktif' => 'boolean',
        ];
    }

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }

    /** @param  Builder<KomponenGaji>  $query */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('is_aktif', true);
    }

    /** @return array<int, array{nama: string, jenis: string, nominal_default: float, urutan: int}> */
    public static function defaultRows(): array
    {
        return [
            ['nama' => 'Gaji Pokok', 'jenis' => self::JENIS_TAMBAH, 'nominal_default' => 0, 'urutan' => 1],
        ];
    }

    public static function seedDefaultsForSekolah(int $sekolahId): void
    {
        foreach (self::defaultRows() as $row) {
            self::query()->firstOrCreate(
                [
                    'sekolah_id' => $sekolahId,
                    'nama' => $row['nama'],
                ],
                [
                    'jenis' => $row['jenis'],
                    'nominal_default' => $row['nominal_default'],
                    'urutan' => $row['urutan'],
                    'is_aktif' => true,
                ]
            );
        }
    }
}
