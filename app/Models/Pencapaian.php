<?php

namespace App\Models;

use App\Models\Concerns\LogsScopedActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pencapaian extends Model
{
    use LogsScopedActivity;

    protected $guarded = [];

    public function anak(): BelongsTo
    {
        return $this->belongsTo(Anak::class);
    }

    public function pengajar(): BelongsTo
    {
        return $this->belongsTo(Pengajar::class);
    }

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(Kegiatan::class);
    }

    public function matrikulasi(): BelongsTo
    {
        return $this->belongsTo(Matrikulasi::class);
    }
}
