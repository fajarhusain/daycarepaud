<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Sekolah;
use App\Services\Api\ApiSchoolService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SekolahController extends Controller
{
    public function __construct(private readonly ApiSchoolService $schools) {}

    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'akses' => ['nullable', 'in:terdaftar,pendaftaran'],
        ]);

        $access = $data['akses'] ?? 'terdaftar';

        if ($access === 'pendaftaran') {
            $schools = Sekolah::query()
                ->where('status', Sekolah::STATUS_ACTIVE)
                ->orderBy('name')
                ->get();
        } else {
            $schools = $this->schools->accessible($request->user());
        }

        return ApiResponse::success(
            $this->schools->payloads($request->user(), $schools),
        );
    }

    public function mine(Request $request): JsonResponse
    {
        $schools = $this->schools->accessible($request->user());

        return ApiResponse::success(
            $this->schools->payloads($request->user(), $schools),
        );
    }
}
