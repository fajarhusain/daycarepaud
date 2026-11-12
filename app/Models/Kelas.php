<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSekolah;
use App\Models\Concerns\LogsScopedActivity;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use BelongsToSekolah, LogsScopedActivity;

    protected $fillable = ['sekolah_id', 'name', 'description', 'wali_kelas_id'];

    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class);
    }

    public function waliKelas()
    {
        return $this->belongsTo(Pengajar::class, 'wali_kelas_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function anaks()
    {
        return $this->hasMany(Anak::class);
    }

    public function pengajars()
    {
        return $this->belongsToMany(Pengajar::class, 'kelas_pengajar');
    }

    public function masterKegiatanRutins()
    {
        return $this->belongsToMany(MasterKegiatanRutin::class, 'kelas_master_kegiatan_rutin');
    }
}
