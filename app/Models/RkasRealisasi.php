<?php

namespace App\Models;

use App\Models\Concerns\LogsScopedActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RkasRealisasi extends Model
{
    use LogsScopedActivity;

    protected $fillable = [
        'rkas_line_id',
        'sumber_dana_id',
        'nominal_otomatis',
        'nominal_manual',
        'catatan',
    ];

    protected $casts = [
        'nominal_otomatis' => 'decimal:2',
        'nominal_manual' => 'decimal:2',
    ];

    public function rkasLine(): BelongsTo
    {
        return $this->belongsTo(RkasLine::class);
    }

    public function sumberDana(): BelongsTo
    {
        return $this->belongsTo(SumberDana::class);
    }

    public function efektif(): float
    {
        return (float) ($this->nominal_manual ?? $this->nominal_otomatis);
    }
}
