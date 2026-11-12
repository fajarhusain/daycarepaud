<?php

namespace App\Support;

use Carbon\Carbon;

class TahunAjaran
{
    /** @return array{tahun_ajaran: string, semester: int} */
    public static function fromDate(Carbon $date): array
    {
        $year = $date->year;
        $month = $date->month;

        if ($month >= 7) {
            return [
                'tahun_ajaran' => $year.'/'.($year + 1),
                'semester' => 1,
            ];
        }

        return [
            'tahun_ajaran' => ($year - 1).'/'.$year,
            'semester' => 2,
        ];
    }

    public static function current(): array
    {
        return self::fromDate(now());
    }

    /** @return array{0: Carbon, 1: Carbon} */
    public static function periode(string $tahunAjaran, int $semester): array
    {
        [$startYear] = array_map('intval', explode('/', $tahunAjaran));

        if ($semester === 1) {
            return [
                Carbon::create($startYear, 7, 1)->startOfDay(),
                Carbon::create($startYear, 12, 31)->endOfDay(),
            ];
        }

        return [
            Carbon::create($startYear + 1, 1, 1)->startOfDay(),
            Carbon::create($startYear + 1, 6, 30)->endOfDay(),
        ];
    }

    public static function label(string $tahunAjaran, int $semester): string
    {
        $namaSem = $semester === 1 ? 'Semester 1 (Jul–Des)' : 'Semester 2 (Jan–Jun)';

        return "TA {$tahunAjaran}  {$namaSem}";
    }

    /** @return list<string> */
    public static function options(int $count = 3): array
    {
        $current = self::current();
        [$y1] = array_map('intval', explode('/', $current['tahun_ajaran']));
        $options = [];

        for ($i = -1; $i < $count; $i++) {
            $options[] = ($y1 + $i).'/'.($y1 + $i + 1);
        }

        return $options;
    }
}
