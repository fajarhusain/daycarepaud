<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSekolah;
use App\Models\Concerns\LogsScopedActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BiayaBulananSiswa extends Model
{
    use BelongsToSekolah, LogsScopedActivity;

    protected $fillable = [
        'sekolah_id',
        'anak_id',
        'biaya_bulanan_sekolah_id',
        'biaya_bulanan',
    ];

    protected function casts(): array
    {
        return [
            'biaya_bulanan' => 'decimal:2',
        ];
    }

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }

    public function anak(): BelongsTo
    {
        return $this->belongsTo(Anak::class);
    }

    public function biayaBulananSekolah(): BelongsTo
    {
        return $this->belongsTo(BiayaBulananSekolah::class);
    }

    public function getBiayaBulananFormatted(): string
    {
        return 'Rp '.number_format($this->biaya_bulanan, 0, ',', '.');
    }
}
