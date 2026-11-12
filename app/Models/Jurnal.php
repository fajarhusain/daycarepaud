<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSekolah;
use App\Models\Concerns\LogsScopedActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Jurnal extends Model
{
    use BelongsToSekolah, LogsScopedActivity;

    protected $fillable = [
        'sekolah_id',
        'no_jurnal',
        'tanggal',
        'deskripsi',
        'created_by',
        'source',
        'sourceable_type',
        'sourceable_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JurnalLine::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sourceable(): MorphTo
    {
        return $this->morphTo();
    }

    public function cashflows(): HasMany
    {
        return $this->hasMany(Cashflow::class);
    }
}
