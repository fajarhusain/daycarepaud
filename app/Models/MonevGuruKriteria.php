<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSekolah;
use App\Models\Concerns\LogsScopedActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MonevGuruKriteria extends Model
{
    use BelongsToSekolah, LogsScopedActivity;

    protected $fillable = [
        'sekolah_id',
        'nama',
        'deskripsi',
        'bobot',
        'urutan',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'bobot' => 'integer',
            'urutan' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }

    public function penilaianItems(): HasMany
    {
        return $this->hasMany(MonevGuruPenilaianItem::class);
    }

    /** Kriteria kedisiplinan/kehadiran: Saran AI dilengkapi rekap presensi. */
    public function usesPresensiContext(): bool
    {
        return str_contains(mb_strtolower($this->nama), 'kehadiran');
    }

    /** @return array<int, array{nama: string, deskripsi: string|null, bobot: int, urutan: int}> */
    public static function defaultRows(): array
    {
        return [
            ['nama' => 'Perencanaan & persiapan pembelajaran', 'deskripsi' => 'Rencana kegiatan, media pembelajaran, dan persiapan materi.', 'bobot' => 20, 'urutan' => 1],
            ['nama' => 'Pelaksanaan kegiatan intrakurikuler', 'deskripsi' => 'Implementasi kegiatan belajar sesuai kurikulum.', 'bobot' => 25, 'urutan' => 2],
            ['nama' => 'Evaluasi & dokumentasi siswa', 'deskripsi' => 'Kelengkapan input pencapaian dan dokumentasi perkembangan siswa.', 'bobot' => 20, 'urutan' => 3],
            ['nama' => 'Kedisiplinan & kehadiran', 'deskripsi' => 'Kehadiran, ketepatan waktu, dan kedisiplinan kerja.', 'bobot' => 15, 'urutan' => 4],
            ['nama' => 'Interaksi dengan siswa & orang tua', 'deskripsi' => 'Komunikasi efektif dengan siswa dan orang tua.', 'bobot' => 10, 'urutan' => 5],
            ['nama' => 'Pengembangan profesional', 'deskripsi' => 'Partisipasi pelatihan, refleksi, dan peningkatan kompetensi.', 'bobot' => 10, 'urutan' => 6],
        ];
    }

    public static function seedDefaultsForSekolah(int $sekolahId): void
    {
        foreach (self::defaultRows() as $row) {
            self::query()->firstOrCreate(
                [
                    'sekolah_id' => $sekolahId,
                    'nama' => $row['nama'],
                ],
                [
                    'deskripsi' => $row['deskripsi'],
                    'bobot' => $row['bobot'],
                    'urutan' => $row['urutan'],
                    'is_active' => true,
                ]
            );
        }
    }
}
