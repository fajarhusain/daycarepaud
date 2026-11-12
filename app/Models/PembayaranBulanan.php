<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSekolah;
use App\Models\Concerns\LogsScopedActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PembayaranBulanan extends Model
{
    use BelongsToSekolah, LogsScopedActivity;

    protected $fillable = [
        'sekolah_id',
        'anak_id',
        'biaya_bulanan_sekolah_id',
        'periode_bulan',
        'periode_tahun',
        'hari_efektif',
        'hari_hadir',
        'biaya_per_hari',
        'subtotal',
        'diskon_id',
        'nilai_diskon',
        'diskon_keterangan',
        'is_tagihan_tambahan',
        'total_bayar',
        'status',
        'bukti_transfer',
        'catatan_admin',
        'approved_by',
        'approved_at',
        'jurnal_id',
    ];

    protected function casts(): array
    {
        return [
            'periode_bulan' => 'integer',
            'periode_tahun' => 'integer',
            'hari_efektif' => 'integer',
            'hari_hadir' => 'integer',
            'biaya_per_hari' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'nilai_diskon' => 'decimal:2',
            'is_tagihan_tambahan' => 'boolean',
            'total_bayar' => 'decimal:2',
            'approved_at' => 'datetime',
        ];
    }

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }

    public function anak(): BelongsTo
    {
        return $this->belongsTo(Anak::class);
    }

    public function biayaBulananSekolah(): BelongsTo
    {
        return $this->belongsTo(BiayaBulananSekolah::class);
    }

    public function diskon(): BelongsTo
    {
        return $this->belongsTo(Diskon::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(PembayaranBulananDetail::class);
    }

    public function jurnal(): BelongsTo
    {
        return $this->belongsTo(Jurnal::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PembayaranBulananItem::class);
    }

    public function getPeriodeLabel(): string
    {
        $bulan = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
        ];

        return ($bulan[$this->periode_bulan] ?? '').' '.$this->periode_tahun;
    }

    public function getTotalBiayaTambahan(): float
    {
        return (float) $this->items()->sum('jumlah');
    }

    public function getTotalBiayaTambahanFormatted(): string
    {
        return 'Rp '.number_format($this->getTotalBiayaTambahan(), 0, ',', '.');
    }

    public function getTotalFormatted(): string
    {
        return 'Rp '.number_format($this->total_bayar, 0, ',', '.');
    }

    public function getSubtotalFormatted(): string
    {
        return 'Rp '.number_format($this->subtotal, 0, ',', '.');
    }

    public function getNilaiDiskonFormatted(): string
    {
        return 'Rp '.number_format($this->nilai_diskon, 0, ',', '.');
    }

    public function getBiayaPerHariFormatted(): string
    {
        return 'Rp '.number_format($this->biaya_per_hari, 0, ',', '.');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function canBeDeleted(): bool
    {
        return ! $this->isApproved();
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu',
            'approved' => 'Lunas',
            'rejected' => 'Ditolak',
            default => ucfirst($this->status),
        };
    }
}
