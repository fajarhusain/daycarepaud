<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AnakTemplateDataSheet implements FromArray, ShouldAutoSize, WithColumnFormatting, WithEvents, WithStyles, WithTitle
{
    public function array(): array
    {
        return [
            [
                'nama_lengkap_anak',
                'nama_panggilan',
                'kelas',
                'nik_anak',
                'jenis_kelamin',
                'tanggal_lahir',
                'alamat',
                'nik_bapak',
                'nama_bapak',
                'nik_ibu',
                'nama_ibu',
                'nama_wali',
                'email_wali',
            ],
            [
                'Budi Santoso',
                'Budi',
                'Kelas A',
                '3201010101010001',
                'Laki-laki',
                '2020-05-15',
                'Jl. Contoh No. 1, Kota Contoh',
                '3201010101010002',
                'Bapak Santoso',
                '3201010101010003',
                'Ibu Santoso',
                'Bapak Santoso',
                'bapak.santoso@contoh.com',
            ],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_TEXT,
            'H' => NumberFormat::FORMAT_TEXT,
            'J' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                foreach (['D', 'H', 'J'] as $column) {
                    $sheet->getStyle("{$column}2:{$column}1000")
                        ->getNumberFormat()
                        ->setFormatCode(NumberFormat::FORMAT_TEXT);

                    foreach (['2'] as $row) {
                        $cell = "{$column}{$row}";
                        $value = $sheet->getCell($cell)->getValue();
                        if ($value !== null && $value !== '') {
                            $sheet->setCellValueExplicit($cell, (string) $value, DataType::TYPE_STRING);
                        }
                    }
                }
            },
        ];
    }

    public function title(): string
    {
        return 'Data Siswa';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
