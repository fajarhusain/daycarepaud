<?php

namespace App\Models;

use App\Models\Concerns\LogsScopedActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PembayaranBulananDetail extends Model
{
    use LogsScopedActivity;

    protected $fillable = [
        'pembayaran_bulanan_id',
        'field_name',
        'old_value',
        'new_value',
        'edited_by',
        'edited_at',
    ];

    protected function casts(): array
    {
        return [
            'edited_at' => 'datetime',
        ];
    }

    public function pembayaranBulanan(): BelongsTo
    {
        return $this->belongsTo(PembayaranBulanan::class);
    }

    public function editedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by');
    }
}
