<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSekolah;
use App\Models\Concerns\LogsScopedActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rkas extends Model
{
    use BelongsToSekolah, LogsScopedActivity;

    protected $table = 'rkas';

    protected $fillable = [
        'sekolah_id',
        'tahun_ajaran',
        'semester',
        'tanggal_mulai',
        'tanggal_akhir',
        'status',
        'synced_at',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_akhir' => 'date',
        'synced_at' => 'datetime',
    ];

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(RkasLine::class);
    }

    public function isFinal(): bool
    {
        return $this->status === 'final';
    }

    public function getLabelAttribute(): string
    {
        $sem = $this->semester === 1 ? 'Sem 1' : 'Sem 2';

        return "RKAS TA {$this->tahun_ajaran} {$sem}";
    }
}
