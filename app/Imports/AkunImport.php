<?php

namespace App\Imports;

use App\Models\Akun;
use App\Services\AkuntansiService;
use App\Support\JenisAkun;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AkunImport implements ToCollection, WithHeadingRow
{
    public int $imported = 0;

    public int $ignored = 0;

    /** @var list<array{row: int, status: string, label: string, message: string}> */
    public array $rows = [];

    /** @var list<array{saldo_awal: float, saldo_normal: string}> */
    private array $openingPlanned = [];

    public function __construct(
        protected int $sekolahId,
        protected bool $dryRun = true,
        protected bool $ignoreDuplicates = false,
    ) {}

    public function collection(Collection $rows): void
    {
        $seen = [];
        $existing = [];
        foreach (Akun::where('sekolah_id', $this->sekolahId)->get(['kode', 'snp', 'komponen']) as $akun) {
            $existing[$this->key($akun->kode, $akun->snp, $akun->komponen)] = true;
        }

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;
            $data = $this->normalize($row);

            if ($data['kode'] === '' && $data['nama'] === '') {
                continue;
            }

            $error = $this->validate($data);
            $key = $this->key($data['kode'], $data['snp'], $data['komponen']);
            $label = trim($data['kode'].'  '.$data['nama'], ' ');

            if ($error) {
                $this->rows[] = ['row' => $rowNumber, 'status' => 'invalid', 'label' => $label, 'message' => $error];

                continue;
            }

            if (isset($seen[$key])) {
                $this->rows[] = [
                    'row' => $rowNumber,
                    'status' => 'duplicate',
                    'label' => $label,
                    'message' => 'Duplikat di file (baris '.$seen[$key].').',
                ];

                continue;
            }

            $seen[$key] = $rowNumber;

            if (isset($existing[$key])) {
                $this->rows[] = [
                    'row' => $rowNumber,
                    'status' => 'duplicate',
                    'label' => $label,
                    'message' => 'Sudah ada di database (kode + kelompok + subkelompok).',
                ];

                continue;
            }

            $this->rows[] = ['row' => $rowNumber, 'status' => 'ok', 'label' => $label, 'message' => 'Siap diimport.'];
            $this->openingPlanned[] = [
                'saldo_awal' => $data['saldo_awal'],
                'saldo_normal' => $data['saldo_normal'],
            ];

            if ($this->dryRun) {
                continue;
            }

            DB::transaction(function () use ($data) {
                $akun = Akun::create([
                    'sekolah_id' => $this->sekolahId,
                    'tipe' => 'rkas',
                    'kode' => $data['kode'],
                    'nama' => $data['nama'],
                    'jenis' => $data['jenis'],
                    'snp' => $data['snp'],
                    'komponen' => $data['komponen'],
                    'uraian' => $data['uraian'],
                    'saldo_normal' => $data['saldo_normal'],
                    'kategori_arus_kas' => $data['kategori_arus_kas'],
                    'is_aktif' => true,
                ]);

                if ($data['saldo_awal'] > 0) {
                    app(AkuntansiService::class)->simpanSaldoAwal($akun, $data['saldo_awal']);
                }
            });
            $existing[$key] = true;
            $this->imported++;
        }

        if (! $this->dryRun && $this->ignoreDuplicates) {
            $this->ignored = count(array_filter($this->rows, fn ($r) => $r['status'] === 'duplicate'));
        }
    }

    public function validCount(): int
    {
        return count(array_filter($this->rows, fn ($r) => $r['status'] === 'ok'));
    }

    public function duplicateCount(): int
    {
        return count(array_filter($this->rows, fn ($r) => $r['status'] === 'duplicate'));
    }

    public function invalidCount(): int
    {
        return count(array_filter($this->rows, fn ($r) => $r['status'] === 'invalid'));
    }

    /**
     * @return array{total_debit: float, total_kredit: float, balanced: bool, opening_rows: int}
     */
    public function openingTrialBalance(): array
    {
        $debit = 0.0;
        $kredit = 0.0;
        $openingRows = 0;

        foreach ($this->openingPlanned as $row) {
            $amount = (float) $row['saldo_awal'];
            if ($amount <= 0) {
                continue;
            }
            $openingRows++;
            if ($row['saldo_normal'] === 'debit') {
                $debit += $amount;
                $kredit += $amount;
            } else {
                $kredit += $amount;
                $debit += $amount;
            }
        }

        return [
            'total_debit' => round($debit, 2),
            'total_kredit' => round($kredit, 2),
            'balanced' => abs($debit - $kredit) < 0.01,
            'opening_rows' => $openingRows,
        ];
    }

    /** @return array{kode: string, nama: string, jenis: string, snp: ?string, komponen: ?string, uraian: ?string, saldo_normal: string, kategori_arus_kas: string} */
    private function normalize(Collection $row): array
    {
        $jenis = JenisAkun::normalize($this->cell($row, ['jenis']));
        $saldo = strtolower($this->cell($row, ['saldo_normal', 'saldo']));
        $snp = $this->cell($row, ['kelompok', 'snp']);
        $komponen = $this->cell($row, ['subkelompok', 'komponen']);

        return [
            'kode' => $this->cell($row, ['kode_akun', 'kode']),
            'nama' => $this->cell($row, ['nama_akun', 'nama']),
            'jenis' => $jenis,
            'snp' => $snp !== '' ? $snp : null,
            'komponen' => $komponen !== '' ? $komponen : null,
            'uraian' => ($u = $this->cell($row, ['uraian'])) !== '' ? $u : null,
            'saldo_normal' => in_array($saldo, ['debit', 'kredit'], true) ? $saldo : $this->defaultSaldo($jenis),
            'kategori_arus_kas' => $this->defaultArus($jenis),
            'saldo_awal' => $this->nominal($this->cell($row, ['saldo_awal', 'nominal', 'opening_balance'])),
        ];
    }

    /** @param array<string, mixed> $data */
    private function validate(array $data): ?string
    {
        if ($data['kode'] === '' || $data['nama'] === '') {
            return 'Kode dan nama wajib diisi.';
        }
        if (mb_strlen($data['kode']) > 20) {
            return 'Kode maksimal 20 karakter.';
        }
        if (mb_strlen($data['nama']) > 200) {
            return 'Nama maksimal 200 karakter.';
        }
        if (! in_array($data['jenis'], JenisAkun::ALL, true)) {
            return 'Jenis harus '.implode(', ', JenisAkun::ALL).'.';
        }
        if ($data['saldo_awal'] === null) {
            return 'Saldo awal harus angka (kosong = 0), contoh 7000000.';
        }
        if ($data['saldo_awal'] < 0) {
            return 'Saldo awal tidak boleh minus.';
        }
        if ($data['saldo_awal'] > 0 && in_array($data['jenis'], [JenisAkun::PENDAPATAN, JenisAkun::BEBAN], true)) {
            return 'Saldo awal hanya untuk akun neraca (Aset/Liabilitas/Modal). Pendapatan & Beban isi 0.';
        }

        return null;
    }

    private function cell(Collection $row, array $keys): string
    {
        foreach ($keys as $key) {
            $value = $row->get($key);
            if ($value !== null && trim((string) $value) !== '') {
                return trim((string) $value);
            }
        }

        return '';
    }

    private function nominal(string $raw): ?float
    {
        $raw = trim(str_replace(['Rp', 'rp', ' '], '', $raw));
        if ($raw === '') {
            return 0.0;
        }
        if (preg_match('/^\d{1,3}(\.\d{3})+$/', $raw)) {
            $raw = str_replace('.', '', $raw);
        } elseif (str_contains($raw, ',') && str_contains($raw, '.')) {
            $raw = str_replace('.', '', $raw);
            $raw = str_replace(',', '.', $raw);
        } elseif (str_contains($raw, ',')) {
            $raw = str_replace(',', '.', $raw);
        }
        if (! is_numeric($raw)) {
            return null;
        }

        return (float) $raw;
    }

    private function key(string $kode, ?string $snp, ?string $komponen): string
    {
        return mb_strtolower(trim($kode)).'|'.mb_strtolower(trim((string) $snp)).'|'.mb_strtolower(trim((string) $komponen));
    }

    private function defaultSaldo(string $jenis): string
    {
        return in_array($jenis, [JenisAkun::PENDAPATAN, JenisAkun::LIABILITAS, JenisAkun::MODAL], true) ? 'kredit' : 'debit';
    }

    private function defaultArus(string $jenis): string
    {
        return $jenis === JenisAkun::MODAL ? 'pendanaan' : 'operasi';
    }
}
