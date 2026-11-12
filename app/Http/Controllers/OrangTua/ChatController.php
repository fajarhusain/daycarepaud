<?php

namespace App\Http\Controllers\OrangTua;

use App\Support\TenantContext;

use App\Exceptions\InsufficientAiTokensException;
use App\Http\Controllers\Controller;
use App\Services\AiTokenService;
use App\Services\OrangTuaChatContextBuilder;
use App\Services\OrangTuaChatService;
use App\Support\AiTokenFeature;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function __construct(
        protected OrangTuaChatService $chatService,
        protected OrangTuaChatContextBuilder $contextBuilder,
        protected AiTokenService $tokenService
    ) {}

    public function index(): View
    {
        $user = auth()->user();
        $sekolahId = (int) TenantContext::requireSekolahId();
        abort_unless($this->tokenService->isChatOrangTuaEnabled($sekolahId), 403, 'Fitur chat sedang dinonaktifkan oleh sekolah.');

        $chat = $this->chatService->getOrCreateChat($user);
        $messages = $this->chatService->messagesForUser($chat);
        $anaks = $this->contextBuilder->approvedAnaks($user);
        $hasTokens = $this->tokenService->getBalance($sekolahId) > 0;
        $tokenFallbackChat = $this->tokenService->resolveFallback($sekolahId, AiTokenFeature::CHAT);

        return view('orangtua.chat.index', compact('chat', 'messages', 'anaks', 'hasTokens', 'tokenFallbackChat'));
    }

    public function store(Request $request): JsonResponse
    {
        $sekolahId = (int) TenantContext::requireSekolahId();
        if (! $this->tokenService->isChatOrangTuaEnabled($sekolahId)) {
            return response()->json(['error' => 'Fitur chat sedang dinonaktifkan oleh sekolah.'], 403);
        }

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:1000'],
        ]);

        try {
            $result = $this->chatService->sendMessage(auth()->user(), $validated['content']);

            return response()->json([
                'messages' => [
                    [
                        'id' => $result['user_message']->id,
                        'role' => $result['user_message']->role,
                        'content' => $result['user_message']->content,
                        'created_at' => $result['user_message']->created_at->toIso8601String(),
                    ],
                    [
                        'id' => $result['assistant_message']->id,
                        'role' => $result['assistant_message']->role,
                        'content' => $result['assistant_message']->content,
                        'created_at' => $result['assistant_message']->created_at->toIso8601String(),
                    ],
                ],
                'token_balance' => $this->tokenService->getBalance((int) TenantContext::requireSekolahId()),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (InsufficientAiTokensException $e) {
            $chat = $this->chatService->getOrCreateChat(auth()->user());
            $lastMessages = $this->chatService->messagesForUser($chat)->take(-2);

            return response()->json([
                'error' => $e->fallbackMessage,
                'token_exhausted' => true,
                'token_balance' => $this->tokenService->getBalance((int) TenantContext::requireSekolahId()),
                'messages' => $lastMessages->map(fn ($msg) => [
                    'id' => $msg->id,
                    'role' => $msg->role,
                    'content' => $msg->content,
                    'created_at' => $msg->created_at->toIso8601String(),
                ])->values()->all(),
            ], 422);
        } catch (\RuntimeException $e) {
            $status = str_contains($e->getMessage(), 'Pengaturan AI') ? 422 : 500;

            return response()->json(['error' => $e->getMessage()], $status);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Gagal menghubungi layanan AI: '.$e->getMessage(),
            ], 500);
        }
    }

    public function destroy(): JsonResponse
    {
        $sekolahId = (int) TenantContext::requireSekolahId();
        if (! $this->tokenService->isChatOrangTuaEnabled($sekolahId)) {
            return response()->json(['error' => 'Fitur chat sedang dinonaktifkan oleh sekolah.'], 403);
        }

        $this->chatService->clearHistory(auth()->user());

        return response()->json(['success' => true]);
    }
}
