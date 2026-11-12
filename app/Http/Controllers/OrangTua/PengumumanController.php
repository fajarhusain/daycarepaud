<?php

namespace App\Http\Controllers\OrangTua;

use App\Support\TenantContext;

use App\Http\Controllers\Controller;
use App\Models\Anak;
use App\Models\Pengumuman;
use App\Models\PengumumanRead;
use Illuminate\Http\JsonResponse;

class PengumumanController extends Controller
{
    public function markAsRead(Pengumuman $pengumuman): JsonResponse
    {
        abort_if($pengumuman->sekolah_id !== TenantContext::requireSekolahId(), 403);
        $this->assertPengumumanVisible($pengumuman);

        PengumumanRead::firstOrCreate(
            [
                'pengumuman_id' => $pengumuman->id,
                'user_id' => auth()->id(),
            ],
            ['read_at' => now()]
        );

        return response()->json(['ok' => true]);
    }

    private function assertPengumumanVisible(Pengumuman $pengumuman): void
    {
        if ($pengumuman->kelas_id === null) {
            return;
        }

        $kelasIds = Anak::query()
            ->where('user_id', auth()->id())
            ->where('status', 'approved')
            ->pluck('kelas_id')
            ->filter()
            ->unique()
            ->all();

        abort_if(! in_array((int) $pengumuman->kelas_id, array_map('intval', $kelasIds), true), 403);
    }
}
