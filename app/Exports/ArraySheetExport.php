<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ArraySheetExport implements FromArray, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithTitle
{
    /**
     * @param  list<string>  $headings
     * @param  list<list<mixed>>  $rows
     * @param  list<int>  $currencyColumns  0-based column indexes formatted as #,##0
     */
    public function __construct(
        protected array $headings,
        protected array $rows,
        protected string $title = 'Data',
        protected array $currencyColumns = [],
    ) {}

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function columnFormats(): array
    {
        $formats = [];
        foreach ($this->currencyColumns as $index) {
            $formats[Coordinate::stringFromColumnIndex($index + 1)] = NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1;
        }

        return $formats;
    }
}
