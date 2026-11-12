<?php

namespace App\Exports;

use App\Models\Kelas;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AnakTemplateKelasSheet implements FromArray, ShouldAutoSize, WithStyles, WithTitle
{
    public function __construct(
        protected int $sekolahId
    ) {}

    public function array(): array
    {
        $rows = [['nama_kelas']];

        $kelas = Kelas::where('sekolah_id', $this->sekolahId)->orderBy('name')->pluck('name');

        foreach ($kelas as $name) {
            $rows[] = [$name];
        }

        if ($kelas->isEmpty()) {
            $rows[] = ['(belum ada kelas terdaftar)'];
        }

        return $rows;
    }

    public function title(): string
    {
        return 'Daftar Kelas';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
