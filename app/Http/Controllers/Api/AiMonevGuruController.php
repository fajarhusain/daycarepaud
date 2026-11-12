<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\InsufficientAiTokensException;
use App\Http\Controllers\Controller;
use App\Models\AiSetting;
use App\Models\MonevGuruKriteria;
use App\Models\Pengajar;
use App\Models\SekolahAiTokenTransaction;
use App\Services\AiPersonaService;
use App\Services\AiTokenService;
use App\Services\PresensiPengajarService;
use App\Support\AiPersonaScope;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AiMonevGuruController extends Controller
{
    public function __construct(
        protected AiPersonaService $personaService,
        protected AiTokenService $tokenService,
        protected PresensiPengajarService $presensiService
    ) {}

    public function suggest(Request $request): JsonResponse
    {
        $user = auth()->user();
        abort_unless($user?->hasAnyRole(['Admin Sekolah', 'Lembaga']), 403);

        $sekolahId = (int) $user->sekolah_id;
        abort_if($sekolahId < 1, 403, 'Akun tidak terikat sekolah.');

        $validated = $request->validate([
            'pengajar_id' => ['required', 'integer', 'exists:pengajars,id'],
            'kriteria_id' => ['required', 'integer', 'exists:monev_guru_kriterias,id'],
            'skor' => ['required', 'integer', 'min:1', 'max:100'],
            'periode_mulai' => ['nullable', 'date'],
            'periode_selesai' => ['nullable', 'date', 'after_or_equal:periode_mulai'],
        ]);

        $pengajar = Pengajar::query()
            ->where('id', (int) $validated['pengajar_id'])
            ->where('sekolah_id', $sekolahId)
            ->first();

        if ($pengajar === null) {
            throw ValidationException::withMessages([
                'pengajar_id' => 'Guru tidak ditemukan atau tidak termasuk sekolah Anda.',
            ]);
        }

        $kriteria = MonevGuruKriteria::query()
            ->where('id', (int) $validated['kriteria_id'])
            ->where('sekolah_id', $sekolahId)
            ->where('is_active', true)
            ->first();

        if ($kriteria === null) {
            throw ValidationException::withMessages([
                'kriteria_id' => 'Kriteria tidak ditemukan atau tidak aktif.',
            ]);
        }

        $lembagaId = $user->lembaga_id ?? $user->sekolah?->lembaga_id;
        if (! $lembagaId) {
            return response()->json(['error' => 'Tidak dapat menemukan lembaga pengguna.'], 403);
        }

        $aiSetting = AiSetting::where('lembaga_id', $lembagaId)->first();
        if (! $aiSetting || ! $aiSetting->hasValidApiKey()) {
            return response()->json([
                'error' => 'Pengaturan AI belum dikonfigurasi. Minta admin lembaga untuk mengisi API Key di menu Pengaturan AI.',
            ], 422);
        }

        $periodeLabel = $this->buildPeriodeLabel(
            $validated['periode_mulai'] ?? null,
            $validated['periode_selesai'] ?? null
        );

        $rekap = null;
        if (
            $kriteria->usesPresensiContext()
            && ! empty($validated['periode_mulai'])
            && ! empty($validated['periode_selesai'])
        ) {
            $rekap = $this->presensiService->rekapKehadiran(
                $pengajar,
                Carbon::parse($validated['periode_mulai'])->startOfDay(),
                Carbon::parse($validated['periode_selesai'])->endOfDay()
            );
            if ((int) ($rekap['hari_kerja'] ?? 0) === 0) {
                $rekap = null;
            }
        }

        $sekolahName = $user->sekolah?->name ?? 'PAUD';
        $personaPrompt = $this->personaService->resolveActivePrompt(
            $sekolahId,
            AiPersonaScope::MONEV,
            $sekolahName
        );

        try {
            $suggestions = $this->tokenService->runWithToken(
                $sekolahId,
                SekolahAiTokenTransaction::TYPE_MONEV,
                $user,
                [
                    'pengajar_id' => $pengajar->id,
                    'kriteria_id' => $kriteria->id,
                    'skor' => (int) $validated['skor'],
                ],
                'Saran catatan monev guru: '.$pengajar->name.'  '.$kriteria->nama,
                fn () => $aiSetting->resolveAiService()->generateMonevGuruCatatanSuggestions(
                    $pengajar->name,
                    $kriteria->nama,
                    (string) ($kriteria->deskripsi ?? ''),
                    (int) $validated['skor'],
                    $periodeLabel,
                    $personaPrompt,
                    $rekap
                )
            );

            $payload = [
                'suggestions' => $suggestions,
                'token_balance' => $this->tokenService->getBalance($sekolahId),
            ];
            if ($rekap !== null) {
                $payload['rekap'] = $rekap;
            }

            return response()->json($payload);
        } catch (InsufficientAiTokensException $e) {
            return response()->json([
                'error' => $e->fallbackMessage,
                'token_exhausted' => true,
                'token_balance' => $this->tokenService->getBalance($sekolahId),
            ], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'error' => 'Gagal menghubungi layanan AI. Coba lagi nanti.',
            ], 500);
        }
    }

    public function suggestRingkasan(Request $request): JsonResponse
    {
        $user = auth()->user();
        abort_unless($user?->hasAnyRole(['Admin Sekolah', 'Lembaga']), 403);

        $sekolahId = (int) $user->sekolah_id;
        abort_if($sekolahId < 1, 403, 'Akun tidak terikat sekolah.');

        $validated = $request->validate([
            'pengajar_id' => ['required', 'integer', 'exists:pengajars,id'],
            'judul' => ['nullable', 'string', 'max:255'],
            'periode_mulai' => ['nullable', 'date'],
            'periode_selesai' => ['nullable', 'date', 'after_or_equal:periode_mulai'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.kriteria_id' => ['required', 'integer'],
            'items.*.skor' => ['required', 'integer', 'min:1', 'max:100'],
            'items.*.catatan' => ['nullable', 'string', 'max:2000'],
        ]);

        $pengajar = Pengajar::query()
            ->where('id', (int) $validated['pengajar_id'])
            ->where('sekolah_id', $sekolahId)
            ->first();

        if ($pengajar === null) {
            throw ValidationException::withMessages([
                'pengajar_id' => 'Guru tidak ditemukan atau tidak termasuk sekolah Anda.',
            ]);
        }

        $kriteriaMap = MonevGuruKriteria::query()
            ->where('sekolah_id', $sekolahId)
            ->whereIn('id', collect($validated['items'])->pluck('kriteria_id'))
            ->get()
            ->keyBy('id');

        $penilaianItems = [];
        foreach ($validated['items'] as $item) {
            $kriteria = $kriteriaMap->get((int) $item['kriteria_id']);
            if ($kriteria === null) {
                continue;
            }

            $penilaianItems[] = [
                'nama' => $kriteria->nama,
                'bobot' => (int) $kriteria->bobot,
                'skor' => (int) $item['skor'],
                'catatan' => (string) ($item['catatan'] ?? ''),
            ];
        }

        if ($penilaianItems === []) {
            throw ValidationException::withMessages([
                'items' => 'Minimal satu kriteria dengan skor valid diperlukan.',
            ]);
        }

        $lembagaId = $user->lembaga_id ?? $user->sekolah?->lembaga_id;
        if (! $lembagaId) {
            return response()->json(['error' => 'Tidak dapat menemukan lembaga pengguna.'], 403);
        }

        $aiSetting = AiSetting::where('lembaga_id', $lembagaId)->first();
        if (! $aiSetting || ! $aiSetting->hasValidApiKey()) {
            return response()->json([
                'error' => 'Pengaturan AI belum dikonfigurasi. Minta admin lembaga untuk mengisi API Key di menu Pengaturan AI.',
            ], 422);
        }

        $periodeLabel = $this->buildPeriodeLabel(
            $validated['periode_mulai'] ?? null,
            $validated['periode_selesai'] ?? null
        );

        $sekolahName = $user->sekolah?->name ?? 'PAUD';
        $personaPrompt = $this->personaService->resolveActivePrompt(
            $sekolahId,
            AiPersonaScope::MONEV,
            $sekolahName
        );

        try {
            $result = $this->tokenService->runWithToken(
                $sekolahId,
                SekolahAiTokenTransaction::TYPE_MONEV,
                $user,
                [
                    'pengajar_id' => $pengajar->id,
                    'type' => 'ringkasan',
                ],
                'Saran ringkasan monev guru: '.$pengajar->name,
                fn () => $aiSetting->resolveAiService()->generateMonevGuruRingkasanSuggestions(
                    $pengajar->name,
                    $periodeLabel,
                    $penilaianItems,
                    $validated['judul'] ?? null,
                    $personaPrompt
                )
            );

            return response()->json([
                'catatan_suggestions' => $result['catatan'] ?? [],
                'rekomendasi_suggestions' => $result['rekomendasi'] ?? [],
                'token_balance' => $this->tokenService->getBalance($sekolahId),
            ]);
        } catch (InsufficientAiTokensException $e) {
            return response()->json([
                'error' => $e->fallbackMessage,
                'token_exhausted' => true,
                'token_balance' => $this->tokenService->getBalance($sekolahId),
            ], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'error' => 'Gagal menghubungi layanan AI. Coba lagi nanti.',
            ], 500);
        }
    }

    private function buildPeriodeLabel(?string $mulai, ?string $selesai): string
    {
        if ($mulai && $selesai) {
            return $mulai === $selesai ? $mulai : "{$mulai} – {$selesai}";
        }

        return 'Periode evaluasi belum ditentukan';
    }
}
