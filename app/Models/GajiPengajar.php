<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSekolah;
use App\Models\Concerns\LogsScopedActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GajiPengajar extends Model
{
    use BelongsToSekolah, LogsScopedActivity;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_APPROVED = 'approved';

    protected $fillable = [
        'sekolah_id',
        'pengajar_id',
        'periode_bulan',
        'periode_tahun',
        'komponen',
        'total_gaji',
        'akun_kas_id',
        'sumber_dana_id',
        'akun_beban_id',
        'status',
        'approved_by',
        'approved_at',
        'jurnal_id',
        'cashflow_id',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'komponen' => 'array',
            'total_gaji' => 'decimal:2',
            'periode_bulan' => 'integer',
            'periode_tahun' => 'integer',
            'approved_at' => 'datetime',
        ];
    }

    public function pengajar(): BelongsTo
    {
        return $this->belongsTo(Pengajar::class);
    }

    public function akunKas(): BelongsTo
    {
        return $this->belongsTo(Akun::class, 'akun_kas_id');
    }

    public function akunBeban(): BelongsTo
    {
        return $this->belongsTo(Akun::class, 'akun_beban_id');
    }

    public function sumberDana(): BelongsTo
    {
        return $this->belongsTo(SumberDana::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function jurnal(): BelongsTo
    {
        return $this->belongsTo(Jurnal::class);
    }

    public function cashflow(): BelongsTo
    {
        return $this->belongsTo(Cashflow::class);
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function getTotalFormatted(): string
    {
        return 'Rp '.number_format((float) $this->total_gaji, 0, ',', '.');
    }

    public function periodeLabel(): string
    {
        return sprintf('%02d/%d', (int) $this->periode_bulan, (int) $this->periode_tahun);
    }
}
