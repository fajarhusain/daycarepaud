<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSekolah;
use App\Models\Concerns\LogsScopedActivity;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pengajar extends Model
{
    use BelongsToSekolah, LogsScopedActivity;

    protected $fillable = [
        'user_id',
        'sekolah_id',
        'presensi_shift_id',
        'name',
        'jabatan',
        'education_history',
        'nik',
        'alamat',
        'phone',
        'pendidikan',
        'jenis_kelamin',
        'photo',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }

    public function presensiShift(): BelongsTo
    {
        return $this->belongsTo(PresensiShift::class);
    }

    public function gajiPengajars(): HasMany
    {
        return $this->hasMany(GajiPengajar::class);
    }

    public function kelas()
    {
        return $this->belongsToMany(Kelas::class, 'kelas_pengajar');
    }

    public function waliKelas(): HasMany
    {
        return $this->hasMany(Kelas::class, 'wali_kelas_id');
    }

    /** Kelas dari pivot mengampu + kelas yang di-wali. */
    public function accessibleKelasIds(): array
    {
        return $this->kelas()->pluck('kelas.id')
            ->merge($this->waliKelas()->pluck('id'))
            ->unique()
            ->values()
            ->all();
    }

    public function accessibleKelas(): Collection
    {
        $ids = $this->accessibleKelasIds();
        if ($ids === []) {
            return new Collection;
        }

        return Kelas::whereIn('id', $ids)->orderBy('name')->get();
    }

    public function monevGuruEvaluasis(): HasMany
    {
        return $this->hasMany(MonevGuruEvaluasi::class);
    }
}
