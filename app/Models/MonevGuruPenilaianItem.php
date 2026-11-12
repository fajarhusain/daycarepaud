<?php

namespace App\Models;

use App\Models\Concerns\LogsScopedActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonevGuruPenilaianItem extends Model
{
    use LogsScopedActivity;

    protected $fillable = [
        'monev_guru_evaluasi_id',
        'monev_guru_kriteria_id',
        'skor',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'skor' => 'integer',
        ];
    }

    public function evaluasi(): BelongsTo
    {
        return $this->belongsTo(MonevGuruEvaluasi::class, 'monev_guru_evaluasi_id');
    }

    public function kriteria(): BelongsTo
    {
        return $this->belongsTo(MonevGuruKriteria::class, 'monev_guru_kriteria_id');
    }
}
