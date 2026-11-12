<?php

namespace App\Http\Controllers;

use App\Support\TourRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TourController extends Controller
{
    public function complete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'route' => ['required', 'string', 'max:255'],
        ]);

        $route = $validated['route'];

        if (! TourRegistry::has($route)) {
            return response()->json(['message' => 'Tour tidak ditemukan.'], 422);
        }

        $request->user()->markTourCompleted($route);

        return response()->json(['ok' => true]);
    }
}
