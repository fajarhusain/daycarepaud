<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrangTuaSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $sekolahId = auth()->user()->sekolah_id;
        $term = trim((string) $request->query('q', ''));

        if (strlen($term) < 2) {
            return response()->json(['data' => []]);
        }

        $users = User::query()
            ->role('Orang Tua')
            ->where('sekolah_id', $sekolahId)
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', '%'.$term.'%')
                    ->orWhere('email', 'like', '%'.$term.'%');
            })
            ->withCount('anaks')
            ->orderBy('name')
            ->limit(15)
            ->get(['id', 'name', 'email']);

        return response()->json([
            'data' => $users->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'anaks_count' => $user->anaks_count,
            ]),
        ]);
    }
}
