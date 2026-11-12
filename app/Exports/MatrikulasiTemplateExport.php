<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MatrikulasiTemplateExport implements FromArray, ShouldAutoSize, WithStyles, WithTitle
{
    /** @return list<string> */
    public static function headings(): array
    {
        return ['aspek', 'indikator', 'deskripsi', 'tujuan', 'strategi'];
    }

    public function array(): array
    {
        return [
            self::headings(),
            [
                'Kognitif',
                'Mampu menghitung 1–10',
                'Anak dapat menyebutkan dan menunjuk angka 1 sampai 10 secara berurutan.',
                'Anak mengenal konsep bilangan dasar.',
                'Permainan menghitung benda sehari-hari, lagu angka.',
            ],
        ];
    }

    public function title(): string
    {
        return 'Matrikulasi';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
