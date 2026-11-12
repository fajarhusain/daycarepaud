<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSekolah;
use App\Models\Concerns\LogsScopedActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Cashflow extends Model
{
    use BelongsToSekolah, LogsScopedActivity;

    protected $fillable = [
        'sekolah_id',
        'akun_id',
        'akun_lawan_id',
        'sumber_dana_id',
        'jurnal_id',
        'type',
        'amount',
        'description',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }

    public function akun(): BelongsTo
    {
        return $this->belongsTo(Akun::class);
    }

    public function akunLawan(): BelongsTo
    {
        return $this->belongsTo(Akun::class, 'akun_lawan_id');
    }

    /** Akun lawan tersimpan, atau lawan di jurnal bila kolomnya masih kosong. */
    public function akunLawanUntukForm(): ?Akun
    {
        if ($this->akunLawan) {
            return $this->akunLawan;
        }

        $kasId = $this->akun_id ? (int) $this->akun_id : null;

        $line = $this->jurnal?->lines?->first(function ($line) use ($kasId) {
            if ($kasId && (int) $line->akun_id === $kasId) {
                return false;
            }

            return $this->type === 'in'
                ? (float) $line->kredit > 0
                : (float) $line->debit > 0;
        });

        return $line?->akun;
    }

    /** @return array<string, mixed> */
    public function editFormPayload(): array
    {
        $lawan = $this->akunLawanUntukForm();

        return [
            'id' => $this->id,
            'type' => $this->type,
            'amount' => $this->amount,
            'description' => $this->description,
            'date' => $this->date?->format('Y-m-d'),
            'akun_id' => $this->akun_id ?? '',
            'akun_lawan_id' => $lawan?->id ?? '',
            'akun_lawan_id_label' => $lawan ? $lawan->kode.'  '.$lawan->nama : '',
            'sumber_dana_id' => $this->sumber_dana_id ?? '',
        ];
    }

    public function sumberDana(): BelongsTo
    {
        return $this->belongsTo(SumberDana::class);
    }

    public function jurnal(): BelongsTo
    {
        return $this->belongsTo(Jurnal::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
