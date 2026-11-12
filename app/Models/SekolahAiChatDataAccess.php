<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSekolah;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SekolahAiChatDataAccess extends Model
{
    use BelongsToSekolah;

    protected $table = 'sekolah_ai_chat_data_access';

    protected $fillable = [
        'sekolah_id',
        'access_monev',
        'access_pencapaian',
        'access_presensi',
        'access_kesehatan',
        'access_agenda',
        'access_kegiatan_rutin',
        'access_menu_makanan',
        'include_tanggal',
        'agenda_days_back',
        'agenda_days_forward',
        'kegiatan_rutin_days_back',
        'kegiatan_rutin_days_forward',
    ];

    /**
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'access_monev' => true,
            'access_pencapaian' => true,
            'access_presensi' => true,
            'access_kesehatan' => true,
            'access_agenda' => true,
            'access_kegiatan_rutin' => true,
            'access_menu_makanan' => true,
            'include_tanggal' => true,
            'agenda_days_back' => 7,
            'agenda_days_forward' => 7,
            'kegiatan_rutin_days_back' => 7,
            'kegiatan_rutin_days_forward' => 7,
        ];
    }

    protected function casts(): array
    {
        return [
            'access_monev' => 'boolean',
            'access_pencapaian' => 'boolean',
            'access_presensi' => 'boolean',
            'access_kesehatan' => 'boolean',
            'access_agenda' => 'boolean',
            'access_kegiatan_rutin' => 'boolean',
            'access_menu_makanan' => 'boolean',
            'include_tanggal' => 'boolean',
            'agenda_days_back' => 'integer',
            'agenda_days_forward' => 'integer',
            'kegiatan_rutin_days_back' => 'integer',
            'kegiatan_rutin_days_forward' => 'integer',
        ];
    }

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }
}
