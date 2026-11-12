<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSekolah;
use App\Models\Concerns\LogsScopedActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkalaPencapaian extends Model
{
    use BelongsToSekolah, LogsScopedActivity;

    protected $fillable = [
        'sekolah_id',
        'code',
        'label',
        'color',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }

    /** @return array<int, array{code: string, label: string, color: string, sort_order: int}> */
    public static function defaultRows(): array
    {
        return [
            ['code' => 'BB', 'label' => 'Belum Berkembang', 'color' => '#FAD7D2', 'sort_order' => 1],
            ['code' => 'MB', 'label' => 'Mulai Berkembang', 'color' => '#FDE9BC', 'sort_order' => 2],
            ['code' => 'BSH', 'label' => 'Berkembang Sesuai Harapan', 'color' => '#D0E8E8', 'sort_order' => 3],
            ['code' => 'BSB', 'label' => 'Berkembang Sangat Baik', 'color' => '#C5E8C5', 'sort_order' => 4],
        ];
    }

    public static function seedDefaultsForSekolah(int $sekolahId): void
    {
        foreach (self::defaultRows() as $row) {
            self::query()->firstOrCreate(
                [
                    'sekolah_id' => $sekolahId,
                    'code' => $row['code'],
                ],
                [
                    'label' => $row['label'],
                    'color' => $row['color'],
                    'sort_order' => $row['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
