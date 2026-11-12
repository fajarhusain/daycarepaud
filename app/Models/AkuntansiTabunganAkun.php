<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSekolah;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AkuntansiTabunganAkun extends Model
{
    use BelongsToSekolah;

    protected $fillable = [
        'sekolah_id',
        'akun_id',
    ];

    public function akun(): BelongsTo
    {
        return $this->belongsTo(Akun::class);
    }
}
