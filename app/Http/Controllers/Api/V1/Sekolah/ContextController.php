<?php

namespace App\Http\Controllers\Api\V1\Sekolah;

use App\Exceptions\ApiErrorCode;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\AnakResource;
use App\Models\Anak;
use App\Models\PembayaranBulanan;
use App\Models\Pengumuman;
use App\Models\Sekolah;
use App\Services\AiTokenService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContextController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $sekolah = $request->attributes->get('sekolah');

        if (! $sekolah instanceof Sekolah) {
            throw ApiException::make(ApiErrorCode::KESALAHAN_SERVER);
        }

        $user = $request->user();

        $anaks = Anak::withoutSekolahScope()
            ->with('kelas')
            ->where('user_id', $user->id)
            ->where('sekolah_id', $sekolah->id)
            ->where('status', 'approved')
            ->orderBy('name')
            ->get();

        $kelasIds = $anaks
            ->pluck('kelas_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $unreadAnnouncements = Pengumuman::query()
            ->where('sekolah_id', $sekolah->id)
            ->active()
            ->forKelasIds($kelasIds)
            ->whereDoesntHave('reads', fn ($query) => $query->where('user_id', $user->id))
            ->count();

        $unpaidBills = PembayaranBulanan::query()
            ->where('sekolah_id', $sekolah->id)
            ->whereIn('anak_id', $anaks->pluck('id')->all())
            ->whereIn('status', ['pending', 'rejected'])
            ->count();

        $chatEnabled = app(AiTokenService::class)->isChatOrangTuaEnabled((int) $sekolah->id);

        return ApiResponse::success([
            'sekolah' => [
                'id' => $sekolah->id,
                'name' => $sekolah->name,
                'alamat' => $sekolah->address,
                'telepon' => $sekolah->phone,
            ],
            'anak' => AnakResource::collection($anaks)->resolve(),
            'fitur' => [
                'chat' => $chatEnabled,
                'pembayaran' => true,
                'vote_menu' => true,
                'pengumuman' => true,
            ],
            'pengumuman_belum_dibaca' => $unreadAnnouncements,
            'tagihan_belum_dibayar' => $unpaidBills,
        ]);
    }
}
