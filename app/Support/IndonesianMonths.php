<?php

namespace App\Support;

class IndonesianMonths
{
    /** @var array<int, string> */
    public const NAMES = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    public static function label(int $bulan, int $tahun): string
    {
        return (self::NAMES[$bulan] ?? (string) $bulan).' '.$tahun;
    }
}
