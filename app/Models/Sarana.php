<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSekolah;
use App\Models\Concerns\LogsScopedActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sarana extends Model
{
    use BelongsToSekolah, LogsScopedActivity;

    protected $fillable = [
        'sekolah_id',
        'name',
        'condition',
        'quantity',
        'lokasi',
        'jenis',
        'photo',
    ];

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }
}
