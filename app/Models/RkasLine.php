<?php

namespace App\Models;

use App\Models\Concerns\LogsScopedActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RkasLine extends Model
{
    use LogsScopedActivity;

    protected $fillable = [
        'rkas_id',
        'akun_id',
        'volume',
        'satuan',
        'harga_satuan',
        'keterangan',
    ];

    protected $casts = [
        'volume' => 'decimal:2',
        'harga_satuan' => 'decimal:2',
    ];

    public function rkas(): BelongsTo
    {
        return $this->belongsTo(Rkas::class);
    }

    public function akun(): BelongsTo
    {
        return $this->belongsTo(Akun::class);
    }

    public function anggarans(): HasMany
    {
        return $this->hasMany(RkasLineAnggaran::class);
    }

    public function realisasis(): HasMany
    {
        return $this->hasMany(RkasRealisasi::class);
    }

    public function totalAnggaran(): float
    {
        return (float) $this->anggarans->sum('nominal');
    }
}
