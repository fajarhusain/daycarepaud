<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\InsufficientAiTokensException;
use App\Http\Controllers\Controller;
use App\Models\AiSetting;
use App\Models\SekolahAiTokenTransaction;
use App\Services\AiFeedbackScopeValidator;
use App\Services\AiPersonaService;
use App\Services\AiTokenService;
use App\Support\AiPersonaScope;
use App\Support\LabelSkorPencapaian;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AiFeedbackController extends Controller
{
    public function __construct(
        protected AiPersonaService $personaService,
        protected AiTokenService $tokenService,
        protected AiFeedbackScopeValidator $scopeValidator
    ) {}

    public function suggest(Request $request): JsonResponse
    {
        $user = auth()->user();

        try {
            $this->scopeValidator->assertAllowedRole($user);
        } catch (AuthorizationException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }

        $sekolahId = (int) $user->sekolah_id;
        $scoreCodes = $sekolahId > 0
            ? LabelSkorPencapaian::codesForSekolah($sekolahId)
            : LabelSkorPencapaian::CODES;

        $validated = $request->validate([
            'anak_id' => ['required', 'integer', 'exists:anaks,id'],
            'kegiatan_id' => ['required', 'integer', 'exists:kegiatans,id'],
            'matrikulasi_id' => ['required', 'integer', 'exists:matrikulasis,id'],
            'score' => ['required', 'string', Rule::in($scoreCodes)],
        ]);

        try {
            $context = $this->scopeValidator->resolve(
                $user,
                (int) $validated['anak_id'],
                (int) $validated['kegiatan_id'],
                (int) $validated['matrikulasi_id']
            );
        } catch (ValidationException $e) {
            return response()->json(['error' => collect($e->errors())->flatten()->first()], 422);
        }

        $anak = $context['anak'];
        $kegiatan = $context['kegiatan'];
        $matrikulasi = $context['matrikulasi'];
        $sekolahId = $context['sekolah_id'];

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

        $matrikulasiLabel = ($matrikulasi->aspek ? $matrikulasi->aspek.': ' : '').$matrikulasi->indicator;
        $scoreLabel = LabelSkorPencapaian::scoreLabelForAi($validated['score'], $sekolahId);
        $sekolahName = $anak->sekolah?->name ?? 'PAUD';
        $personaPrompt = $this->personaService->resolveActivePrompt(
            $sekolahId,
            AiPersonaScope::FEEDBACK_PENCAPAIAN,
            $sekolahName
        );

        try {
            $suggestions = $this->tokenService->runWithToken(
                $sekolahId,
                SekolahAiTokenTransaction::TYPE_PENCAPAIAN,
                $user,
                [
                    'anak_id' => $anak->id,
                    'kegiatan_id' => $kegiatan->id,
                    'matrikulasi_id' => $matrikulasi->id,
                ],
                'Saran feedback pencapaian: '.$anak->displayName(),
                fn () => $aiSetting->resolveAiService()->generateFeedbackSuggestions(
                    $anak->displayName(),
                    $kegiatan->title,
                    $matrikulasiLabel,
                    $scoreLabel,
                    $personaPrompt
                )
            );

            return response()->json([
                'suggestions' => $suggestions,
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
}
