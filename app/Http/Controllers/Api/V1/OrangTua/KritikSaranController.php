<?php

namespace App\Http\Controllers\Api\V1\OrangTua;

use App\Http\Controllers\Controller;
use App\Models\KritikSaran;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KritikSaranController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = KritikSaran::query()
            ->where('user_id', $request->user()->id)
            ->with(['sekolah:id,name'])
            ->latest()
            ->paginate((int) $request->input('per_page', 15));

        return response()->json($items);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $kritik = KritikSaran::query()
            ->where('user_id', $request->user()->id)
            ->with(['sekolah:id,name'])
            ->findOrFail($id);

        return response()->json([
            'id' => $kritik->id,
            'message' => $kritik->message,
            'status' => $kritik->status,
            'created_at' => $kritik->created_at?->toIso8601String(),
            'updated_at' => $kritik->updated_at?->toIso8601String(),
            'sekolah' => $kritik->sekolah ? [
                'id' => $kritik->sekolah->id,
                'name' => $kritik->sekolah->name,
            ] : null,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'min:10'],
        ]);

        $kritik = KritikSaran::create([
            'sekolah_id' => $request->user()->sekolah_id,
            'user_id' => $request->user()->id,
            'message' => $data['message'],
            'status' => 'Terkirim',
        ]);

        return response()->json([
            'info' => 'Kritik atau saran berhasil dikirim.',
            'data' => ['id' => $kritik->id],
        ], 201);
    }
}
