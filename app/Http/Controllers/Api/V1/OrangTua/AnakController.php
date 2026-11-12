<?php

namespace App\Http\Controllers\Api\V1\OrangTua;

use App\Http\Controllers\Controller;
use App\Models\Anak;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnakController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $sekolahId = $request->user()->sekolah_id;
        $anaks = Anak::query()
            ->where('user_id', $request->user()->id)
            ->where('sekolah_id', $sekolahId)
            ->with(['kelas:id,name'])
            ->orderBy('name')
            ->get()
            ->map(fn (Anak $a) => [
                'id' => $a->id,
                'name' => $a->name,
                'nickname' => $a->nickname,
                'dob' => $a->dob ? Carbon::parse($a->dob)->format('Y-m-d') : null,
                'status' => $a->status,
                'kelas' => $a->kelas ? ['id' => $a->kelas->id, 'name' => $a->kelas->name] : null,
            ]);

        return response()->json(['data' => $anaks]);
    }

    public function updateNickname(Request $request, Anak $anak): JsonResponse
    {
        if ($anak->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $request->validate([
            'nickname' => 'nullable|string|max:50',
        ]);

        $nickname = filled(trim($request->input('nickname', '')))
            ? trim($request->input('nickname'))
            : null;

        $anak->update(['nickname' => $nickname]);

        return response()->json([
            'message' => 'Nickname berhasil diperbarui.',
            'data' => [
                'id' => $anak->id,
                'nickname' => $anak->nickname,
            ],
        ]);
    }
}
