<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSekolah;
use App\Models\Concerns\LogsScopedActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BiayaBulananSekolah extends Model
{
    use BelongsToSekolah, LogsScopedActivity;

    protected $fillable = [
        'sekolah_id',
        'nama_biaya',
        'nominal_default',
        'keterangan',
        'is_aktif',
    ];

    protected function casts(): array
    {
        return [
            'nominal_default' => 'decimal:2',
            'is_aktif' => 'boolean',
        ];
    }

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }

    public function siswaBiaya(): HasMany
    {
        return $this->hasMany(BiayaBulananSiswa::class);
    }

    public function pembayaran(): HasMany
    {
        return $this->hasMany(PembayaranBulanan::class);
    }

    public function getNominalDefaultFormatted(): string
    {
        return 'Rp '.number_format($this->nominal_default, 0, ',', '.');
    }
}
