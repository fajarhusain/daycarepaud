<?php

namespace App\Support;

class JenisAkun
{
    public const ASSETS = 'Assets';

    public const LIABILITAS = 'Liabilitas';

    public const MODAL = 'Modal';

    public const PENDAPATAN = 'Pendapatan';

    public const BEBAN = 'Beban';

    public const ALL = [
        self::ASSETS,
        self::LIABILITAS,
        self::MODAL,
        self::PENDAPATAN,
        self::BEBAN,
    ];

    public static function normalize(string $jenis): string
    {
        return match (strtolower(trim($jenis))) {
            'aset', 'asset', 'assets' => self::ASSETS,
            'liabilitas', 'liability', 'liabilities' => self::LIABILITAS,
            'ekuitas', 'modal', 'equity' => self::MODAL,
            'pendapatan', 'income', 'revenue' => self::PENDAPATAN,
            'beban', 'expense', 'expenses', 'biaya' => self::BEBAN,
            default => trim($jenis),
        };
    }
}
