<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSekolah;
use App\Models\Concerns\LogsScopedActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kegiatan extends Model
{
    use BelongsToSekolah, LogsScopedActivity;

    protected $fillable = [
        'sekolah_id',
        'pengajar_id',
        'kelas_id',
        'date',
        'title',
        'description',
        'photo',
        'photos',
    ];

    protected $casts = [
        'date' => 'date',
        'photos' => 'array',
    ];

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }

    public function pengajar(): BelongsTo
    {
        return $this->belongsTo(Pengajar::class);
    }

    public function matrikulasis(): BelongsToMany
    {
        return $this->belongsToMany(Matrikulasi::class, 'kegiatan_matrikulasi');
    }

    public function pencapaians(): HasMany
    {
        return $this->hasMany(Pencapaian::class);
    }
}
