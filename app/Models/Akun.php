<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSekolah;
use App\Models\Concerns\LogsScopedActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Akun extends Model
{
    use BelongsToSekolah, LogsScopedActivity;

    protected $fillable = [
        'sekolah_id',
        'tipe',
        'kode',
        'nama',
        'snp',
        'komponen',
        'uraian',
        'jenis',
        'kategori_arus_kas',
        'saldo_normal',
        'induk_id',
        'is_aktif',
        'deskripsi',
    ];

    protected $casts = [
        'is_aktif' => 'boolean',
    ];

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }

    public function induk(): BelongsTo
    {
        return $this->belongsTo(Akun::class, 'induk_id');
    }

    public function anak(): HasMany
    {
        return $this->hasMany(Akun::class, 'induk_id');
    }

    public function cashflows(): HasMany
    {
        return $this->hasMany(Cashflow::class);
    }

    public function jurnalLines(): HasMany
    {
        return $this->hasMany(JurnalLine::class);
    }

    public function scopeAktif($q)
    {
        $q->where('is_aktif', true);
    }

    public function scopeByJenis($q, string $jenis)
    {
        $q->where('jenis', $jenis);
    }

    public function scopeSistem($q)
    {
        $q->where('tipe', 'sistem');
    }

    public function scopeRkas($q)
    {
        $q->where('tipe', 'rkas');
    }

    public function isSistem(): bool
    {
        return $this->tipe === 'sistem';
    }

    /** Akun masih direferensikan transaksi atau konfigurasi  jangan hard-delete. */
    public function masihTerpakai(): bool
    {
        if ($this->jurnalLines()->exists()) {
            return true;
        }

        $id = $this->id;

        if (AkuntansiSetting::where('sekolah_id', $this->sekolah_id)
            ->where(function ($q) use ($id) {
                $q->where('akun_kas_id', $id)
                    ->orWhere('akun_piutang_id', $id)
                    ->orWhere('akun_pendapatan_id', $id)
                    ->orWhere('akun_untuk_in', $id)
                    ->orWhere('akun_untuk_out', $id)
                    ->orWhere('akun_beban_gaji_id', $id)
                    ->orWhere('akun_kas_gaji_id', $id);
            })->exists()) {
            return true;
        }

        if (GajiPengajar::where('sekolah_id', $this->sekolah_id)
            ->where(function ($q) use ($id) {
                $q->where('akun_kas_id', $id)->orWhere('akun_beban_id', $id);
            })
            ->exists()) {
            return true;
        }

        if (SumberDana::where('sekolah_id', $this->sekolah_id)->where('akun_id', $id)->exists()) {
            return true;
        }

        if (AkuntansiTabunganAkun::where('sekolah_id', $this->sekolah_id)->where('akun_id', $id)->exists()) {
            return true;
        }

        if (RkasLine::where('akun_id', $id)->exists()) {
            return true;
        }

        if (static::where('induk_id', $id)->exists()) {
            return true;
        }

        return false;
    }

    public function getLabelAttribute(): string
    {
        return "{$this->kode}  ".($this->uraian ?? $this->nama);
    }
}
