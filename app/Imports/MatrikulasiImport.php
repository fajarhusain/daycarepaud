<?php

namespace App\Imports;

use App\Models\Matrikulasi;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MatrikulasiImport implements ToCollection, WithHeadingRow
{
    public int $successCount = 0;

    /** @var array<int, string> */
    public array $failedRows = [];

    /** @var array<int, string> */
    public array $validRows = [];

    public function __construct(
        protected int $sekolahId,
        protected bool $dryRun = false,
    ) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;

            if ($this->isEmptyRow($row)) {
                continue;
            }

            $data = $this->normalizeRow($row);

            $validator = Validator::make($data, [
                'aspek' => ['nullable', 'string', 'max:255'],
                'indikator' => ['required', 'string', 'max:255'],
                'deskripsi' => ['required', 'string'],
                'tujuan' => ['nullable', 'string'],
                'strategi' => ['nullable', 'string'],
            ]);

            if ($validator->fails()) {
                $this->failedRows[$rowNumber] = implode(' ', $validator->errors()->all());

                continue;
            }

            $validated = $validator->validated();

            try {
                if ($this->dryRun) {
                    $this->validRows[$rowNumber] = $this->previewRow($validated);
                    $this->successCount++;

                    continue;
                }

                Matrikulasi::create([
                    'sekolah_id' => $this->sekolahId,
                    'aspek' => $validated['aspek'],
                    'indicator' => $validated['indikator'],
                    'description' => $validated['deskripsi'],
                    'tujuan' => $validated['tujuan'],
                    'strategi' => $validated['strategi'],
                ]);

                $this->successCount++;
            } catch (\Throwable $e) {
                $this->failedRows[$rowNumber] = $e->getMessage();
            }
        }
    }

    protected function isEmptyRow(Collection $row): bool
    {
        $data = $this->normalizeRow($row);

        return ($data['indikator'] ?? '') === '' && ($data['deskripsi'] ?? '') === '';
    }

    protected function normalizeRow(Collection $row): array
    {
        return [
            'aspek' => $this->nullableCell($this->pickRowValue($row, 'aspek')),
            'indikator' => $this->requiredCell($this->pickRowValue($row, 'indikator')),
            'deskripsi' => $this->requiredCell($this->pickRowValue($row, 'deskripsi')),
            'tujuan' => $this->nullableCell($this->pickRowValue($row, 'tujuan')),
            'strategi' => $this->nullableCell($this->pickRowValue($row, 'strategi')),
        ];
    }

    protected function pickRowValue(Collection $row, string $key): mixed
    {
        $aliases = match ($key) {
            'aspek' => ['aspek', 'aspek_bidang', 'aspek_faktor'],
            default => [$key],
        };

        foreach ($aliases as $alias) {
            $value = $row[$alias] ?? null;
            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    protected function nullableCell(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = is_string($value) ? trim($value) : trim((string) $value);

        return $text === '' || $text === '-' ? null : $text;
    }

    protected function requiredCell(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = is_string($value) ? trim($value) : trim((string) $value);

        return $text === '' ? null : $text;
    }

    protected function previewRow(array $validated): string
    {
        $parts = [];

        if ($validated['aspek'] ?? null) {
            $parts[] = $validated['aspek'];
        }

        $parts[] = $validated['indikator'];

        return implode(' · ', $parts);
    }
}
