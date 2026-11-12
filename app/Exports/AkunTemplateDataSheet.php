<?php

namespace App\Exports;

use App\Support\JenisAkun;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AkunTemplateDataSheet implements FromArray, ShouldAutoSize, WithStyles, WithTitle
{
    public function array(): array
    {
        return [
            [
                'kode_akun',
                'jenis',
                'nama_akun',
                'kelompok',
                'subkelompok',
                'uraian',
                'saldo_normal',
                'saldo_awal',
            ],
            [
                '1101',
                JenisAkun::ASSETS,
                'Kas Besar',
                'Aset Lancar',
                'Kas dan Setara Kas',
                'Kas utama sekolah',
                'debit',
                10_000_000,
            ],
            [
                '1105',
                JenisAkun::ASSETS,
                'Piutang SPP',
                'Aset Lancar',
                'Piutang',
                'Piutang tagihan siswa',
                'debit',
                0,
            ],
            [
                '4101',
                JenisAkun::PENDAPATAN,
                'Pendapatan SPP',
                'Pendapatan',
                'Pendapatan Operasional',
                'Pendapatan bulanan',
                'kredit',
                0,
            ],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Data Akun';
    }
}
