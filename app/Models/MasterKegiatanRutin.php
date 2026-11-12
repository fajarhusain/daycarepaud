<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSekolah;
use App\Models\Concerns\LogsScopedActivity;
use Illuminate\Database\Eloquent\Model;

class MasterKegiatanRutin extends Model
{
    use BelongsToSekolah, LogsScopedActivity;

    protected $fillable = [
        'sekolah_id',
        'pengajar_id',
        'matrikulasi_id',
        'nama_kegiatan',
        'aspek',
    ];

    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class);
    }

    public function pengajar()
    {
        return $this->belongsTo(Pengajar::class);
    }

    public function matrikulasi()
    {
        return $this->belongsTo(Matrikulasi::class);
    }

    public function kelas()
    {
        return $this->belongsToMany(Kelas::class, 'kelas_master_kegiatan_rutin');
    }

    public function kegiatanRutins()
    {
        return $this->hasMany(KegiatanRutin::class, 'master_kegiatan_rutin_id');
    }
}
