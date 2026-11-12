<?php

namespace App\Http\Controllers\Api\V1\OrangTua;

use App\Http\Controllers\Controller;
use App\Models\MenuMakanan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuMakananController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $menus = MenuMakanan::query()
            ->where('sekolah_id', $request->user()->sekolah_id)
            ->orderByDesc('date')
            ->paginate((int) $request->input('per_page', 15));

        return response()->json($menus);
    }
}
