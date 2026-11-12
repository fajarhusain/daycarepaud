<?php

namespace App\Models;

use App\Models\Concerns\LogsScopedActivity;
use Illuminate\Database\Eloquent\Model;

class Kesehatan extends Model
{
    use LogsScopedActivity;

    protected $fillable = [
        'anak_id',
        'berat_badan',
        'tinggi_badan',
        'lingkar_kepala',
        'gigi',
        'telinga',
        'kuku',
        'alergi',
        'tanggal_pemeriksaan',
    ];

    protected $casts = [
        'tanggal_pemeriksaan' => 'date',
    ];

    public function anak()
    {
        return $this->belongsTo(Anak::class);
    }
}
