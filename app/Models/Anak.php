<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSekolah;
use App\Models\Concerns\LogsScopedActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Anak extends Model
{
    use BelongsToSekolah, LogsScopedActivity;

    protected $fillable = [
        'user_id',
        'sekolah_id',
        'kelas_id',
        'name',
        'nickname',
        'dob',
        'parent_name',
        'photo',
        'status',
        'catatan_ortu',
        'catatan_admin',
        'nik',
        'alamat',
        'jenis_kelamin',
        'nik_bapak',
        'nama_bapak',
        'nik_ibu',
        'nama_ibu',
    ];

    protected $casts = [
        'dob' => 'date',
    ];

    public function getAgeAttribute()
    {
        if (! $this->dob) {
            return '-';
        }

        $diff = $this->dob->diff(now());
        $parts = [];
        if ($diff->y > 0) {
            $parts[] = $diff->y.' thn';
        }
        if ($diff->m > 0) {
            $parts[] = $diff->m.' bln';
        }

        return count($parts) > 0 ? implode(' ', $parts) : '0 bln';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function kesehatans(): HasMany
    {
        return $this->hasMany(Kesehatan::class);
    }

    public function pencapaians(): HasMany
    {
        return $this->hasMany(Pencapaian::class);
    }

    /**
     * Nama panggilan untuk AI feedback: nickname jika ada, else nama lengkap.
     */
    public function displayName(): string
    {
        $nick = $this->nickname ? trim($this->nickname) : null;

        return $nick ?: $this->name;
    }
}
