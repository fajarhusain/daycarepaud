<?php

namespace App\Models;

use App\Models\Concerns\LogsScopedActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PembayaranBulananItem extends Model
{
    use LogsScopedActivity;

    protected $fillable = [
        'pembayaran_bulanan_id',
        'nama_item',
        'jumlah',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'decimal:2',
        ];
    }

    public function pembayaranBulanan(): BelongsTo
    {
        return $this->belongsTo(PembayaranBulanan::class);
    }
}
