<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AkunTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new AkunTemplateDataSheet,
            new AkunTemplatePetunjukSheet,
        ];
    }
}
