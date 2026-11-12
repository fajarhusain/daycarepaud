<?php

namespace App\Support;

class Terbilang
{
    private const SATUAN = [
        '', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan',
        'sepuluh', 'sebelas', 'dua belas', 'tiga belas', 'empat belas', 'lima belas',
        'enam belas', 'tujuh belas', 'delapan belas', 'sembilan belas',
    ];

    public static function make(float|int|string $angka): string
    {
        $n = (int) round((float) $angka);
        if ($n < 0) {
            return 'minus '.self::make(abs($n));
        }
        if ($n === 0) {
            return 'nol rupiah';
        }

        return trim(self::convert($n)).' rupiah';
    }

    private static function convert(int $n): string
    {
        if ($n < 20) {
            return self::SATUAN[$n];
        }
        if ($n < 100) {
            $puluh = intdiv($n, 10);
            $sisa = $n % 10;

            return ($puluh === 1 ? 'se' : self::SATUAN[$puluh]).' puluh'.($sisa ? ' '.self::SATUAN[$sisa] : '');
        }
        if ($n < 200) {
            return 'seratus'.($n > 100 ? ' '.self::convert($n - 100) : '');
        }
        if ($n < 1000) {
            return self::SATUAN[intdiv($n, 100)].' ratus'.($n % 100 ? ' '.self::convert($n % 100) : '');
        }
        if ($n < 2000) {
            return 'seribu'.($n > 1000 ? ' '.self::convert($n - 1000) : '');
        }
        if ($n < 1_000_000) {
            return self::convert(intdiv($n, 1000)).' ribu'.($n % 1000 ? ' '.self::convert($n % 1000) : '');
        }
        if ($n < 1_000_000_000) {
            return self::convert(intdiv($n, 1_000_000)).' juta'.($n % 1_000_000 ? ' '.self::convert($n % 1_000_000) : '');
        }
        if ($n < 1_000_000_000_000) {
            return self::convert(intdiv($n, 1_000_000_000)).' miliar'.($n % 1_000_000_000 ? ' '.self::convert($n % 1_000_000_000) : '');
        }

        return self::convert(intdiv($n, 1_000_000_000_000)).' triliun'.($n % 1_000_000_000_000 ? ' '.self::convert($n % 1_000_000_000_000) : '');
    }
}
