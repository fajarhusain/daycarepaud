<?php

namespace App\Models;

use App\Models\Concerns\LogsScopedActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RkasLineAnggaran extends Model
{
    use LogsScopedActivity;

    protected $fillable = [
        'rkas_line_id',
        'sumber_dana_id',
        'nominal',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
    ];

    public function rkasLine(): BelongsTo
    {
        return $this->belongsTo(RkasLine::class);
    }

    public function sumberDana(): BelongsTo
    {
        return $this->belongsTo(SumberDana::class);
    }
}
