<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSekolah;
use App\Models\Concerns\LogsScopedActivity;
use App\Support\JenisAkun;
use App\Support\StandardCoa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AkuntansiSetting extends Model
{
    use BelongsToSekolah, LogsScopedActivity;

    protected $table = 'akuntansi_settings';

    protected $fillable = [
        'sekolah_id',
        'metode_pencatatan',
        'akun_kas_id',
        'akun_piutang_id',
        'akun_pendapatan_id',
        'akun_untuk_in',
        'akun_untuk_out',
        'akun_beban_gaji_id',
        'sumber_dana_gaji_id',
        'akun_kas_gaji_id',
        'jenis_akun_aset',
    ];

    protected $casts = [
        'jenis_akun_aset' => 'array',
    ];

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }

    public function akunKas(): BelongsTo
    {
        return $this->belongsTo(Akun::class, 'akun_kas_id');
    }

    public function akunPiutang(): BelongsTo
    {
        return $this->belongsTo(Akun::class, 'akun_piutang_id');
    }

    public function akunPendapatan(): BelongsTo
    {
        return $this->belongsTo(Akun::class, 'akun_pendapatan_id');
    }

    public function akunUntukIn(): BelongsTo
    {
        return $this->belongsTo(Akun::class, 'akun_untuk_in');
    }

    public function akunUntukOut(): BelongsTo
    {
        return $this->belongsTo(Akun::class, 'akun_untuk_out');
    }

    public function akunBebanGaji(): BelongsTo
    {
        return $this->belongsTo(Akun::class, 'akun_beban_gaji_id');
    }

    public function sumberDanaGaji(): BelongsTo
    {
        return $this->belongsTo(SumberDana::class, 'sumber_dana_gaji_id');
    }

    public function akunKasGaji(): BelongsTo
    {
        return $this->belongsTo(Akun::class, 'akun_kas_gaji_id');
    }

    /** ponytail: fallback ke akun_kas_id jika kas gaji belum diisi (data lama / migrasi belum lengkap) */
    public function akunKasGajiEfektifId(): ?int
    {
        return $this->akun_kas_gaji_id ?? $this->akun_kas_id;
    }

    public function lengkapUntukGajiGuru(): bool
    {
        return $this->akunKasGajiEfektifId() && $this->akun_beban_gaji_id;
    }

    public function assertLengkapUntukGajiGuru(): void
    {
        if ($this->lengkapUntukGajiGuru()) {
            return;
        }

        throw new \RuntimeException(
            'Pengaturan Akun Gaji Guru belum lengkap. Atur Akun Kas/Bank dan Akun Beban di Pengaturan Akuntansi.'
        );
    }

    /**
     * @return array{akun_kas_id: int, akun_beban_id: int, sumber_dana_id: ?int}
     */
    public function akunUntukGajiGuru(): array
    {
        $this->assertLengkapUntukGajiGuru();

        $kasGajiId = $this->akunKasGajiEfektifId();

        return [
            'akun_kas_id' => (int) $kasGajiId,
            'akun_beban_id' => (int) $this->akun_beban_gaji_id,
            'sumber_dana_id' => $this->sumber_dana_gaji_id ? (int) $this->sumber_dana_gaji_id : null,
        ];
    }

    /** Jenis akun yang tampil di kolom Akun Aset saat catat transaksi. Kosong = aset. */
    public function jenisUntukAkunAset(): array
    {
        $list = array_values(array_filter(array_map(
            fn ($v) => trim((string) $v),
            $this->jenis_akun_aset ?? []
        )));

        return $list !== [] ? $list : [JenisAkun::ASSETS];
    }

    public function isAccrual(): bool
    {
        return $this->metode_pencatatan === 'accrual';
    }

    public function isCash(): bool
    {
        return $this->metode_pencatatan === 'cash';
    }

    public static function forSekolah(int $sekolahId): self
    {
        $kas = Akun::where('sekolah_id', $sekolahId)->where('kode', StandardCoa::DEFAULT_KAS)->first();
        $pendapatanLain = Akun::where('sekolah_id', $sekolahId)->where('kode', StandardCoa::DEFAULT_COUNTER_IN)->first();
        $bebanDefault = Akun::where('sekolah_id', $sekolahId)->where('kode', StandardCoa::DEFAULT_COUNTER_OUT)->first();
        $piutang = Akun::where('sekolah_id', $sekolahId)->where('kode', StandardCoa::DEFAULT_PIUTANG)->first();
        $pendapatanSpp = Akun::where('sekolah_id', $sekolahId)->where('kode', StandardCoa::DEFAULT_PENDAPATAN)->first();

        $attrs = array_filter([
            'metode_pencatatan' => 'cash',
            'akun_kas_id' => $kas?->id,
            'akun_piutang_id' => $piutang?->id,
            'akun_pendapatan_id' => $pendapatanSpp?->id,
            'akun_untuk_in' => $pendapatanLain?->id,
            'akun_untuk_out' => $bebanDefault?->id,
            'akun_beban_gaji_id' => $bebanDefault?->id,
            'akun_kas_gaji_id' => $kas?->id,
        ], fn ($v) => $v !== null);

        $setting = static::firstOrCreate(
            ['sekolah_id' => $sekolahId],
            $attrs + ['metode_pencatatan' => 'cash'],
        );

        if ($kas && $setting->akun_kas_id !== $kas->id) {
            $setting->update(array_filter([
                'akun_kas_id' => $kas->id,
                'akun_piutang_id' => $piutang?->id,
                'akun_pendapatan_id' => $pendapatanSpp?->id,
                'akun_untuk_in' => $pendapatanLain?->id,
                'akun_untuk_out' => $bebanDefault?->id,
                'akun_beban_gaji_id' => $bebanDefault?->id,
                'akun_kas_gaji_id' => $kas?->id,
            ], fn ($v) => $v !== null));
        }

        if (! $setting->akun_beban_gaji_id && $bebanDefault) {
            $setting->update(['akun_beban_gaji_id' => $bebanDefault->id]);
        }

        if (! $setting->akun_kas_gaji_id && $kas) {
            $setting->update(['akun_kas_gaji_id' => $kas->id]);
        }

        return $setting->fresh();
    }
}
