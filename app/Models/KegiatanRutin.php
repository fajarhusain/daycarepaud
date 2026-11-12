<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSekolah;
use App\Models\Concerns\LogsScopedActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KegiatanRutin extends Model
{
    use BelongsToSekolah, LogsScopedActivity;

    protected $fillable = [
        'sekolah_id',
        'kelas_id',
        'anak_id',
        'pengajar_id',
        'master_kegiatan_rutin_id',
        'tanggal',
        'aspek',
        'kegiatan',
        'status_pencapaian',
        'keterangan',
        'photo',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    /** @return list<string> */
    public static function statusOptions(): array
    {
        return [
            'Belum Mulai',
            'Belum Lancar',
            'Lancar',
            'Sangat Lancar',
            'Tidak Hadir',
        ];
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function gridMapForKelas(?int $kelasId, string $tanggal): array
    {
        if (! $kelasId) {
            return [];
        }

        $grid = [];
        static::query()
            ->where('kelas_id', $kelasId)
            ->where('tanggal', $tanggal)
            ->get(['anak_id', 'master_kegiatan_rutin_id', 'status_pencapaian'])
            ->each(function (self $rutin) use (&$grid) {
                if ($rutin->master_kegiatan_rutin_id) {
                    $grid[$rutin->anak_id][$rutin->master_kegiatan_rutin_id] = $rutin->status_pencapaian;
                }
            });

        return $grid;
    }

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function anak(): BelongsTo
    {
        return $this->belongsTo(Anak::class);
    }

    public function pengajar(): BelongsTo
    {
        return $this->belongsTo(Pengajar::class);
    }

    public function masterKegiatanRutin(): BelongsTo
    {
        return $this->belongsTo(MasterKegiatanRutin::class);
    }
}
