<?php

namespace App\Support;

class AiChatDataSource
{
    public const MONEV = 'access_monev';

    public const PENCAPAIAN = 'access_pencapaian';

    public const PRESENSI = 'access_presensi';

    public const KESEHATAN = 'access_kesehatan';

    public const AGENDA = 'access_agenda';

    public const KEGIATAN_RUTIN = 'access_kegiatan_rutin';

    public const MENU_MAKANAN = 'access_menu_makanan';

    public const INCLUDE_TANGGAL = 'include_tanggal';

    /**
     * @return list<string>
     */
    public static function toggleKeys(): array
    {
        return [
            self::MONEV,
            self::PENCAPAIAN,
            self::PRESENSI,
            self::KESEHATAN,
            self::AGENDA,
            self::KEGIATAN_RUTIN,
            self::MENU_MAKANAN,
            self::INCLUDE_TANGGAL,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::MONEV => 'Monev',
            self::PENCAPAIAN => 'Pencapaian',
            self::PRESENSI => 'Kehadiran (Presensi)',
            self::KESEHATAN => 'Kesehatan',
            self::AGENDA => 'Agenda Belajar',
            self::KEGIATAN_RUTIN => 'Kegiatan Rutin',
            self::MENU_MAKANAN => 'Menu Makanan',
            self::INCLUDE_TANGGAL => 'Tanggal & hari ini',
        ];
    }

    public static function label(string $key): string
    {
        return self::labels()[$key] ?? $key;
    }
}
