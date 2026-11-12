<?php

namespace App\Models;

use App\Models\Concerns\LogsScopedActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JurnalLine extends Model
{
    use LogsScopedActivity;

    public $timestamps = false;

    protected $fillable = [
        'jurnal_id',
        'akun_id',
        'debit',
        'kredit',
        'keterangan',
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'kredit' => 'decimal:2',
    ];

    public function jurnal(): BelongsTo
    {
        return $this->belongsTo(Jurnal::class);
    }

    public function akun(): BelongsTo
    {
        return $this->belongsTo(Akun::class);
    }

    public function scopeDebit($q)
    {
        $q->where('debit', '>', 0);
    }

    public function scopeKredit($q)
    {
        $q->where('kredit', '>', 0);
    }
}
