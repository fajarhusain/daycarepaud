<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * Daftar hari libur nasional Indonesia.
 *
 * Sumber: Keputusan Bersama Menteri dan Surat Keputusan Presiden RI.
 * Format: 'Y-m-d'
 */
final class HariLiburIndonesia
{
    /**
     * Daftar tanggal merah (hari libur nasional) per tahun.
     *
     * @var array<int, string[]>
     */
    private static array $holidays = [
        2024 => [
            '2024-01-01', // Tahun Baru Masehi
            '2024-02-08', // Isra Mi'raj Nabi Muhammad SAW
            '2024-02-10', // Tahun Baru Imlek 2575
            '2024-03-11', // Hari Suci Nyepi (Tahun Baru Saka 1946)
            '2024-03-29', // Wafat Isa Al Masih (Good Friday)
            '2024-03-31', // Hari Paskah
            '2024-04-10', // Hari Raya Idul Fitri 1445 H
            '2024-04-11', // Hari Raya Idul Fitri 1445 H (Hari Kedua)
            '2024-05-01', // Hari Buruh Internasional
            '2024-05-09', // Kenaikan Isa Al Masih
            '2024-05-23', // Hari Raya Waisak 2568 BE
            '2024-06-01', // Hari Lahir Pancasila
            '2024-06-17', // Hari Raya Idul Adha 1445 H
            '2024-07-07', // Tahun Baru Islam 1446 H
            '2024-08-17', // Hari Kemerdekaan RI
            '2024-09-16', // Maulid Nabi Muhammad SAW
            '2024-12-25', // Hari Raya Natal
            '2024-12-26', // Cuti Bersama Natal

            // Cuti Bersama 2024
            '2024-04-08', // Cuti Bersama Idul Fitri
            '2024-04-09', // Cuti Bersama Idul Fitri
            '2024-04-12', // Cuti Bersama Idul Fitri
            '2024-04-15', // Cuti Bersama Idul Fitri
            '2024-05-10', // Cuti Bersama Kenaikan Isa Al Masih
            '2024-05-24', // Cuti Bersama Waisak
            '2024-12-26', // Cuti Bersama Natal (sudah di atas)
        ],

        2025 => [
            '2025-01-01', // Tahun Baru Masehi
            '2025-01-27', // Tahun Baru Imlek 2576
            '2025-01-28', // Isra Mi'raj Nabi Muhammad SAW
            '2025-03-28', // Hari Suci Nyepi (Tahun Baru Saka 1947)
            '2025-03-30', // Hari Paskah
            '2025-04-01', // Wafat Isa Al Masih (Good Friday)  dikonfirmasi
            '2025-03-31', // Wafat Isa Al Masih
            '2025-04-01', // Hari Paskah
            '2025-03-31', // Hari Raya Idul Fitri 1446 H
            '2025-04-01', // Hari Raya Idul Fitri 1446 H (Hari Kedua)
            '2025-05-01', // Hari Buruh Internasional
            '2025-05-12', // Hari Raya Waisak 2569 BE
            '2025-05-29', // Kenaikan Isa Al Masih
            '2025-06-01', // Hari Lahir Pancasila
            '2025-06-06', // Hari Raya Idul Adha 1446 H
            '2025-06-27', // Tahun Baru Islam 1447 H
            '2025-08-17', // Hari Kemerdekaan RI
            '2025-09-05', // Maulid Nabi Muhammad SAW
            '2025-12-25', // Hari Raya Natal
            '2025-12-26', // Hari Raya Natal (Hari Kedua)

            // Cuti Bersama 2025
            '2025-01-28', // Cuti Bersama Imlek
            '2025-03-28', // Cuti Bersama Nyepi
            '2025-04-02', // Cuti Bersama Idul Fitri
            '2025-04-03', // Cuti Bersama Idul Fitri
            '2025-04-04', // Cuti Bersama Idul Fitri
            '2025-04-07', // Cuti Bersama Idul Fitri
            '2025-05-13', // Cuti Bersama Waisak
            '2025-05-30', // Cuti Bersama Kenaikan Isa Al Masih
        ],

        2026 => [
            '2026-01-01', // Tahun Baru Masehi
            '2026-01-17', // Isra Mi'raj Nabi Muhammad SAW
            '2026-02-17', // Tahun Baru Imlek 2577
            '2026-03-19', // Hari Raya Idul Fitri 1447 H
            '2026-03-20', // Hari Raya Idul Fitri 1447 H (Hari Kedua)
            '2026-03-22', // Hari Suci Nyepi (Tahun Baru Saka 1948)
            '2026-04-03', // Wafat Isa Al Masih (Good Friday)
            '2026-04-05', // Hari Paskah
            '2026-04-10', // Isra Mi'raj  sesuai kalender 2026
            '2026-05-01', // Hari Buruh Internasional
            '2026-05-14', // Kenaikan Isa Al Masih
            '2026-05-27', // Hari Raya Idul Adha 1447 H
            '2026-06-01', // Hari Lahir Pancasila
            '2026-06-02', // Hari Raya Waisak 2570 BE
            '2026-06-16', // Tahun Baru Islam 1448 H
            '2026-08-17', // Hari Kemerdekaan RI
            '2026-08-25', // Maulid Nabi Muhammad SAW
            '2026-12-25', // Hari Raya Natal
            '2026-12-26', // Cuti Bersama Natal

            // Cuti Bersama 2026 (estimasi, dapat disesuaikan)
            '2026-01-16', // Cuti Bersama Isra Mi'raj
            '2026-02-18', // Cuti Bersama Imlek
            '2026-03-18', // Cuti Bersama Idul Fitri
            '2026-03-23', // Cuti Bersama Nyepi
            '2026-03-24', // Cuti Bersama Idul Fitri
            '2026-03-25', // Cuti Bersama Idul Fitri
            '2026-03-26', // Cuti Bersama Idul Fitri
            '2026-03-27', // Cuti Bersama Idul Fitri
        ],

        2027 => [
            '2027-01-01', // Tahun Baru Masehi
            '2027-01-06', // Isra Mi'raj Nabi Muhammad SAW 1448 H
            '2027-02-06', // Tahun Baru Imlek 2578
            '2027-03-08', // Hari Raya Idul Fitri 1448 H
            '2027-03-09', // Hari Raya Idul Fitri 1448 H (Hari Kedua)
            '2027-03-17', // Hari Suci Nyepi (Tahun Baru Saka 1949)
            '2027-03-26', // Wafat Isa Al Masih (Good Friday)
            '2027-03-28', // Hari Paskah
            '2027-05-01', // Hari Buruh Internasional
            '2027-05-15', // Hari Raya Idul Adha 1448 H
            '2027-05-17', // Hari Raya Waisak 2571 BE
            '2027-05-06', // Kenaikan Isa Al Masih
            '2027-06-01', // Hari Lahir Pancasila
            '2027-06-05', // Tahun Baru Islam 1449 H
            '2027-08-14', // Maulid Nabi Muhammad SAW 1449 H
            '2027-08-17', // Hari Kemerdekaan RI
            '2027-12-25', // Hari Raya Natal
            '2027-12-26', // Cuti Bersama Natal

            // Cuti Bersama 2027 (estimasi)
            '2027-01-07', // Cuti Bersama Isra Mi'raj
            '2027-02-05', // Cuti Bersama Imlek
            '2027-02-08', // Cuti Bersama Imlek
            '2027-03-10', // Cuti Bersama Idul Fitri
            '2027-03-11', // Cuti Bersama Idul Fitri
            '2027-03-12', // Cuti Bersama Idul Fitri
            '2027-03-15', // Cuti Bersama Idul Fitri
            '2027-03-16', // Cuti Bersama Nyepi
            '2027-03-18', // Cuti Bersama Nyepi
            '2027-05-07', // Cuti Bersama Kenaikan Isa Al Masih
            '2027-05-18', // Cuti Bersama Waisak
        ],

        2028 => [
            '2028-01-01', // Tahun Baru Masehi
            '2028-01-26', // Isra Mi'raj Nabi Muhammad SAW 1449 H
            '2028-01-26', // Tahun Baru Imlek 2579
            '2028-02-25', // Hari Raya Idul Fitri 1449 H
            '2028-02-26', // Hari Raya Idul Fitri 1449 H (Hari Kedua)
            '2028-03-05', // Hari Suci Nyepi (Tahun Baru Saka 1950)
            '2028-04-14', // Wafat Isa Al Masih (Good Friday)
            '2028-04-16', // Hari Paskah
            '2028-05-01', // Hari Buruh Internasional
            '2028-05-04', // Hari Raya Idul Adha 1449 H
            '2028-05-05', // Kenaikan Isa Al Masih
            '2028-05-24', // Tahun Baru Islam 1450 H
            '2028-06-01', // Hari Lahir Pancasila
            '2028-06-05', // Hari Raya Waisak 2572 BE
            '2028-08-02', // Maulid Nabi Muhammad SAW 1450 H
            '2028-08-17', // Hari Kemerdekaan RI
            '2028-12-25', // Hari Raya Natal
            '2028-12-26', // Cuti Bersama Natal

            // Cuti Bersama 2028 (estimasi)
            '2028-01-25', // Cuti Bersama Imlek/Isra Mi'raj
            '2028-01-27', // Cuti Bersama Imlek/Isra Mi'raj
            '2028-02-27', // Cuti Bersama Idul Fitri
            '2028-02-28', // Cuti Bersama Idul Fitri
            '2028-02-29', // Cuti Bersama Idul Fitri
            '2028-03-01', // Cuti Bersama Idul Fitri
            '2028-03-04', // Cuti Bersama Idul Fitri
            '2028-03-06', // Cuti Bersama Nyepi
            '2028-05-06', // Cuti Bersama Kenaikan Isa Al Masih
            '2028-06-06', // Cuti Bersama Waisak
        ],
    ];

    /**
     * Mengembalikan array tanggal merah untuk tahun tertentu.
     *
     * @return string[]
     */
    public static function getHolidays(int $year): array
    {
        return array_unique(self::$holidays[$year] ?? []);
    }

    /**
     * Cek apakah tanggal tertentu adalah hari libur nasional Indonesia.
     */
    public static function isHoliday(Carbon $date): bool
    {
        $year = (int) $date->year;
        $holidays = self::getHolidays($year);

        return in_array($date->toDateString(), $holidays, true);
    }

    /**
     * Cek apakah tanggal tertentu adalah hari efektif (bukan Sabtu/Minggu/tanggal merah).
     */
    public static function isEfektif(Carbon $date): bool
    {
        return ! $date->isWeekend() && ! self::isHoliday($date);
    }

    /**
     * Hitung jumlah hari efektif antara dua tanggal (inklusif).
     */
    public static function hitungHariEfektif(Carbon $from, Carbon $to): int
    {
        $count = 0;
        $current = $from->copy()->startOfDay();
        $end = $to->copy()->startOfDay();

        while ($current->lte($end)) {
            if (self::isEfektif($current)) {
                $count++;
            }
            $current->addDay();
        }

        return $count;
    }
}
