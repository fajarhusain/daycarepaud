<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSekolah;
use App\Models\Concerns\LogsScopedActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Diskon extends Model
{
    use BelongsToSekolah, LogsScopedActivity;

    protected $fillable = [
        'sekolah_id',
        'nama_diskon',
        'tipe',
        'nilai',
        'keterangan',
        'is_aktif',
    ];

    protected function casts(): array
    {
        return [
            'nilai' => 'decimal:2',
            'is_aktif' => 'boolean',
        ];
    }

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }

    public function pembayaran(): HasMany
    {
        return $this->hasMany(PembayaranBulanan::class);
    }

    public function getNilaiFormatted(): string
    {
        if ($this->tipe === 'persentase') {
            return $this->nilai.'%';
        }

        return 'Rp '.number_format($this->nilai, 0, ',', '.');
    }

    public function hitungDiskon(float $subtotal): float
    {
        if ($this->tipe === 'persentase') {
            return $subtotal * ($this->nilai / 100);
        }

        return min($this->nilai, $subtotal);
    }
}
