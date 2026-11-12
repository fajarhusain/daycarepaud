<?php

namespace App\Services;

use App\Models\SekolahAiChatDataAccess;
use App\Support\AiChatDataSource;

class AiChatDataAccessService
{
    public function resolveForSekolah(int $sekolahId): SekolahAiChatDataAccess
    {
        return SekolahAiChatDataAccess::firstOrCreate(
            ['sekolah_id' => $sekolahId],
            SekolahAiChatDataAccess::defaults()
        );
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function updateForSekolah(int $sekolahId, array $validated): SekolahAiChatDataAccess
    {
        $settings = $this->resolveForSekolah($sekolahId);

        $settings->update([
            'access_monev' => (bool) ($validated['access_monev'] ?? false),
            'access_pencapaian' => (bool) ($validated['access_pencapaian'] ?? false),
            'access_presensi' => (bool) ($validated['access_presensi'] ?? false),
            'access_kesehatan' => (bool) ($validated['access_kesehatan'] ?? false),
            'access_agenda' => (bool) ($validated['access_agenda'] ?? false),
            'access_kegiatan_rutin' => (bool) ($validated['access_kegiatan_rutin'] ?? false),
            'access_menu_makanan' => (bool) ($validated['access_menu_makanan'] ?? false),
            'include_tanggal' => (bool) ($validated['include_tanggal'] ?? false),
            'agenda_days_back' => (int) $validated['agenda_days_back'],
            'agenda_days_forward' => (int) $validated['agenda_days_forward'],
            'kegiatan_rutin_days_back' => (int) $validated['kegiatan_rutin_days_back'],
            'kegiatan_rutin_days_forward' => (int) $validated['kegiatan_rutin_days_forward'],
        ]);

        return $settings->fresh();
    }

    public function buildAccessSummary(SekolahAiChatDataAccess $access): string
    {
        $lines = ['Pengaturan akses data chat (oleh admin sekolah):'];

        foreach (AiChatDataSource::toggleKeys() as $key) {
            $status = $access->{$key} ? 'aktif' : 'nonaktif';
            $line = '- '.AiChatDataSource::label($key).': '.$status;

            if ($key === AiChatDataSource::AGENDA && $access->access_agenda) {
                $line .= " ({$access->agenda_days_back} hari ke belakang, {$access->agenda_days_forward} hari ke depan)";
            }

            if ($key === AiChatDataSource::KEGIATAN_RUTIN && $access->access_kegiatan_rutin) {
                $line .= " ({$access->kegiatan_rutin_days_back} hari ke belakang, {$access->kegiatan_rutin_days_forward} hari ke depan)";
            }

            $lines[] = $line;
        }

        return implode("\n", $lines);
    }
}
