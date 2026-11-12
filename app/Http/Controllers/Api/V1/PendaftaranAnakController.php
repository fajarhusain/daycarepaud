<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\ApiErrorCode;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PendaftaranAnakResource;
use App\Models\Anak;
use App\Models\Sekolah;
use App\Services\AnakRegistrationService;
use App\Support\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PendaftaranAnakController extends Controller
{
    public function __construct(private readonly AnakRegistrationService $registration) {}

    public function index(Request $request): JsonResponse
    {
        $items = Anak::withoutSekolahScope()
            ->with(['sekolah', 'kelas'])
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return ApiResponse::success(
            PendaftaranAnakResource::collection($items)->resolve(),
            ['total' => $items->count()],
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sekolah_id' => ['required', 'integer', Rule::exists('sekolahs', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'dob' => ['required', 'date', 'before:today'],
            'catatan_ortu' => ['nullable', 'string', 'max:1000'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:2048'],
        ], [
            'dob.before' => 'Tanggal lahir harus sebelum hari ini.',
        ]);

        $sekolah = Sekolah::query()->find((int) $validated['sekolah_id']);

        if (! $sekolah) {
            throw ApiException::make(ApiErrorCode::SEKOLAH_TIDAK_DITEMUKAN);
        }

        if (! $sekolah->isOperational()) {
            throw ApiException::make(ApiErrorCode::PENDAFTARAN_DITUTUP);
        }

        $name = trim((string) $validated['name']);
        $dob = Carbon::parse($validated['dob'])->toDateString();

        $duplicate = Anak::withoutSekolahScope()
            ->where('user_id', $request->user()->id)
            ->where('sekolah_id', $sekolah->id)
            ->where('name', $name)
            ->whereDate('dob', $dob)
            ->exists();

        if ($duplicate) {
            throw ApiException::make(ApiErrorCode::PENDAFTARAN_DUPLIKAT);
        }

        $anak = $this->registration->createPendingForParent(
            $request->user(),
            [
                'name' => $name,
                'dob' => $dob,
                'catatan_ortu' => $validated['catatan_ortu'] ?? null,
            ],
            (int) $sekolah->id,
            $request->file('photo'),
        );

        $anak->load('sekolah');

        return ApiResponse::success(
            [
                'id' => $anak->id,
                'status' => $anak->status,
                'sekolah' => [
                    'id' => $sekolah->id,
                    'name' => $sekolah->name,
                ],
            ],
            [],
            'Pendaftaran anak berhasil dikirim. Menunggu persetujuan admin sekolah.',
            201,
        );
    }
}
