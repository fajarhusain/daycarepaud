<?php

namespace App\Support;

use App\Models\SkalaPencapaian;
use Illuminate\Support\Collection;

final class LabelSkorPencapaian
{
    public const CODES = ['BB', 'MB', 'BSH', 'BSB'];

    private const LABELS = [
        'BB' => 'Belum Berkembang',
        'MB' => 'Mulai Berkembang',
        'BSH' => 'Berkembang Sesuai Harapan',
        'BSB' => 'Berkembang Sangat Baik',
    ];

    private const COLORS = [
        'BB' => '#FAD7D2',
        'MB' => '#FDE9BC',
        'BSH' => '#D0E8E8',
        'BSB' => '#C5E8C5',
    ];

    /** @var array<int, Collection<int, SkalaPencapaian>> */
    private static array $cacheBySekolah = [];

    public static function codesForSekolah(int $sekolahId): array
    {
        return self::activeForSekolah($sekolahId)
            ->pluck('code')
            ->all();
    }

    public static function optionsForSekolah(int $sekolahId): Collection
    {
        return self::activeForSekolah($sekolahId);
    }

    public static function label(?string $code, ?int $sekolahId = null): string
    {
        if ($code === null || $code === '') {
            return '';
        }

        if ($sekolahId !== null) {
            $row = self::allForSekolah($sekolahId)->firstWhere('code', $code);
            if ($row !== null) {
                return $row->label;
            }
        }

        return self::LABELS[$code] ?? $code;
    }

    public static function color(?string $code, ?int $sekolahId = null): string
    {
        if ($code === null || $code === '') {
            return '#eee';
        }

        if ($sekolahId !== null) {
            $row = self::allForSekolah($sekolahId)->firstWhere('code', $code);
            if ($row !== null) {
                return $row->color;
            }
        }

        return self::COLORS[$code] ?? '#eee';
    }

    public static function scoreLabelForAi(?string $code, ?int $sekolahId = null): string
    {
        if ($code === null || $code === '') {
            return '';
        }

        $label = self::label($code, $sekolahId);

        return $label.' ('.$code.')';
    }

    /** @return array<string, string> kode => warna latar */
    public static function colorsByCode(?int $sekolahId = null): array
    {
        if ($sekolahId === null) {
            return self::COLORS;
        }

        return self::allForSekolah($sekolahId)
            ->mapWithKeys(fn (SkalaPencapaian $s) => [$s->code => $s->color])
            ->all();
    }

    /** @return Collection<int, SkalaPencapaian> */
    private static function activeForSekolah(int $sekolahId): Collection
    {
        return self::allForSekolah($sekolahId)->where('is_active', true)->values();
    }

    /** @return Collection<int, SkalaPencapaian> */
    private static function allForSekolah(int $sekolahId): Collection
    {
        if (! isset(self::$cacheBySekolah[$sekolahId])) {
            self::$cacheBySekolah[$sekolahId] = SkalaPencapaian::query()
                ->where('sekolah_id', $sekolahId)
                ->orderBy('sort_order')
                ->orderBy('code')
                ->get();
        }

        return self::$cacheBySekolah[$sekolahId];
    }
}
