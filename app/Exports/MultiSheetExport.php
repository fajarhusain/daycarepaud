<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MultiSheetExport implements WithMultipleSheets
{
    /**
     * @param  list<array{headings: list<string>, rows: list<list<mixed>>, title: string}>  $sheets
     */
    public function __construct(
        protected array $sheets
    ) {}

    public function sheets(): array
    {
        return array_map(
            fn (array $sheet) => new ArraySheetExport($sheet['headings'], $sheet['rows'], $sheet['title']),
            $this->sheets
        );
    }
}
