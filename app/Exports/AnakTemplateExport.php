<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AnakTemplateExport implements WithMultipleSheets
{
    public function __construct(
        protected int $sekolahId
    ) {}

    public function sheets(): array
    {
        return [
            new AnakTemplateDataSheet,
            new AnakTemplateKelasSheet($this->sekolahId),
        ];
    }
}
