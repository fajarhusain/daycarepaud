<?php

namespace App\Imports;

use App\Models\Kelas;
use App\Models\User;
use App\Services\AnakRegistrationService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class AnakImport implements ToCollection, WithHeadingRow
{
    public int $successCount = 0;

    /** @var array<int, string> */
    public array $failedRows = [];

    /** @var array<int, string> */
    public array $validRows = [];

    /** @var array<string, User> */
    protected array $parentCache = [];

    /** @var array<string, int|null|false> */
    protected array $kelasCache = [];

    public function __construct(
        protected int $sekolahId,
        protected AnakRegistrationService $anakRegistration,
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
                'nama_lengkap_anak' => ['required', 'string', 'max:255'],
                'nama_panggilan' => ['nullable', 'string', 'max:50'],
                'kelas' => ['nullable', 'string', 'max:255'],
                'nik_anak' => ['nullable', 'string', 'max:50'],
                'jenis_kelamin' => ['nullable', 'in:Laki-laki,Perempuan'],
                'tanggal_lahir' => ['nullable', 'date', 'before:today'],
                'alamat' => ['nullable', 'string'],
                'nik_bapak' => ['nullable', 'string', 'max:50'],
                'nama_bapak' => ['nullable', 'string', 'max:255'],
                'nik_ibu' => ['nullable', 'string', 'max:50'],
                'nama_ibu' => ['nullable', 'string', 'max:255'],
                'nama_wali' => ['required', 'string', 'max:255'],
                'email_wali' => ['required', 'email', 'max:255'],
            ]);

            if ($validator->fails()) {
                $this->failedRows[$rowNumber] = implode(' ', $validator->errors()->all());

                continue;
            }

            $validated = $validator->validated();

            try {
                $kelasId = $this->resolveKelasId($validated['kelas'] ?? null);
                if ($validated['kelas'] ?? null) {
                    if ($kelasId === false) {
                        $this->failedRows[$rowNumber] = 'Kelas tidak ditemukan di sekolah ini.';

                        continue;
                    }
                }

                $parent = $this->resolveParent(
                    $validated['email_wali'],
                    $validated['nama_wali']
                );

                if ($this->dryRun) {
                    $this->validRows[$rowNumber] = $this->previewRow($validated, $kelasId, $parent);
                    $this->successCount++;

                    continue;
                }

                $this->anakRegistration->createApprovedForParent(
                    $parent,
                    [
                        'name' => $validated['nama_lengkap_anak'],
                        'nickname' => $validated['nama_panggilan'] ?? null,
                        'dob' => $validated['tanggal_lahir'] ?? null,
                        'nik' => $validated['nik_anak'] ?? null,
                        'alamat' => $validated['alamat'] ?? null,
                        'jenis_kelamin' => $validated['jenis_kelamin'] ?? null,
                        'kelas_id' => $kelasId,
                        'nik_bapak' => $validated['nik_bapak'] ?? null,
                        'nama_bapak' => $validated['nama_bapak'] ?? null,
                        'nik_ibu' => $validated['nik_ibu'] ?? null,
                        'nama_ibu' => $validated['nama_ibu'] ?? null,
                    ],
                    $this->sekolahId
                );

                $this->successCount++;
            } catch (\Throwable $e) {
                $this->failedRows[$rowNumber] = $e->getMessage();
            }
        }
    }

    protected function isEmptyRow(Collection $row): bool
    {
        $name = trim((string) ($row['nama_lengkap_anak'] ?? ''));
        $email = trim((string) ($row['email_wali'] ?? ''));

        return $name === '' && $email === '';
    }

    protected function normalizeRow(Collection $row): array
    {
        $data = [];
        foreach ([
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
        ] as $key) {
            $value = $row[$key] ?? null;
            if ($value === null || $value === '') {
                $data[$key] = null;

                continue;
            }

            $data[$key] = is_string($value) ? trim($value) : $value;
        }

        foreach (['nik_anak', 'nik_bapak', 'nik_ibu'] as $nikKey) {
            if ($data[$nikKey] !== null) {
                $data[$nikKey] = $this->normalizeNik($data[$nikKey]);
            }
        }

        if ($data['tanggal_lahir'] !== null) {
            $data['tanggal_lahir'] = $this->parseDate($data['tanggal_lahir']);
        }

        return $data;
    }

    protected function parseDate(mixed $value): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (is_numeric($value)) {
            try {
                return Carbon::instance(
                    Date::excelToDateTimeObject((float) $value)
                )->format('Y-m-d');
            } catch (\Throwable) {
                return (string) $value;
            }
        }

        return (string) $value;
    }

    protected function normalizeNik(mixed $value): string
    {
        if (is_int($value) || is_float($value)) {
            return number_format($value, 0, '', '');
        }

        $nik = trim((string) $value);

        if (preg_match('/^[\d,.]+[eE][+\-]?\d+$/', str_replace(' ', '', $nik))) {
            throw new \RuntimeException('NIK terbaca sebagai angka ilmiah. Unduh ulang template dan isi NIK sebagai teks.');
        }

        return $nik;
    }

    protected function previewRow(array $validated, ?int $kelasId, User $parent): string
    {
        $parts = [$validated['nama_lengkap_anak']];

        if ($validated['kelas'] ?? null) {
            $parts[] = $validated['kelas'];
        }

        $parts[] = $parent->exists
            ? 'wali: '.$parent->email.' (sudah ada)'
            : 'wali baru: '.$validated['email_wali'];

        return implode(' · ', $parts);
    }

    /**
     * @return int|null|false null = no kelas, false = not found
     */
    protected function resolveKelasId(?string $kelasName): int|null|false
    {
        if ($kelasName === null || $kelasName === '') {
            return null;
        }

        $key = mb_strtolower($kelasName);

        if (! array_key_exists($key, $this->kelasCache)) {
            $kelas = Kelas::where('sekolah_id', $this->sekolahId)
                ->whereRaw('LOWER(name) = ?', [$key])
                ->first();

            $this->kelasCache[$key] = $kelas?->id ?? false;
        }

        return $this->kelasCache[$key];
    }

    protected function resolveParent(string $email, string $name): User
    {
        $key = mb_strtolower($email);

        if (isset($this->parentCache[$key])) {
            return $this->parentCache[$key];
        }

        $existing = User::where('email', $email)
            ->where('sekolah_id', $this->sekolahId)
            ->first();

        if ($existing) {
            if (! $existing->hasRole('Orang Tua')) {
                throw new \RuntimeException('Email wali sudah digunakan oleh pengguna non-orang tua.');
            }

            $this->parentCache[$key] = $existing;

            return $existing;
        }

        if (User::where('email', $email)->exists()) {
            throw new \RuntimeException('Email wali sudah terdaftar di sekolah lain.');
        }

        if ($this->dryRun) {
            $placeholder = new User([
                'name' => $name,
                'email' => $email,
                'sekolah_id' => $this->sekolahId,
            ]);
            $this->parentCache[$key] = $placeholder;

            return $placeholder;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('password123'),
            'sekolah_id' => $this->sekolahId,
        ]);
        $user->assignRole('Orang Tua');

        $this->parentCache[$key] = $user;

        return $user;
    }
}
