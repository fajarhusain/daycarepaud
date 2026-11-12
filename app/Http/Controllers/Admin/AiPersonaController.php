<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InsufficientAiTokensException;
use App\Http\Controllers\Controller;
use App\Models\Sekolah;
use App\Models\SekolahAiPersona;
use App\Models\SekolahAiTokenTransaction;
use App\Services\AiChatDataAccessService;
use App\Services\AiPersonaService;
use App\Services\AiTokenService;
use App\Support\AiChatDataSource;
use App\Support\AiPersonaScope;
use App\Support\PaginationPerPage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AiPersonaController extends Controller
{
    public function __construct(
        protected AiPersonaService $personaService,
        protected AiChatDataAccessService $dataAccessService,
        protected AiTokenService $tokenService
    ) {}

    private function sekolahId(): ?int
    {
        $id = auth()->user()->sekolah_id;

        return $id !== null ? (int) $id : null;
    }

    public function index(Request $request): View
    {
        $sekolahId = $this->sekolahId();
        abort_if($sekolahId === null, 403, 'Akun tidak terikat sekolah.');

        $sekolah = Sekolah::findOrFail($sekolahId);
        $personas = $this->personaService->allForSekolah($sekolahId);
        $aiConfigured = $this->personaService->isAiConfiguredForSekolah($sekolahId);
        $dataAccess = $this->dataAccessService->resolveForSekolah($sekolahId);
        $aiSettings = $this->tokenService->resolveSettings($sekolahId);
        $tokenBalance = $this->tokenService->getBalance($sekolahId);
        $activeTab = $request->query('tab', AiPersonaScope::CHAT_ORANGTUA);

        if (! in_array($activeTab, AiPersonaScope::adminIndexTabs(), true)) {
            $activeTab = AiPersonaScope::CHAT_ORANGTUA;
        }

        $tokenTransactions = $activeTab === AiPersonaScope::TAB_LOG_AI
            ? $this->tokenService->paginateTransactionsForSekolah($sekolahId, PaginationPerPage::resolve($request))
            : null;

        return view('admin.ai_persona.index', compact(
            'sekolah',
            'personas',
            'aiConfigured',
            'dataAccess',
            'aiSettings',
            'tokenBalance',
            'tokenTransactions',
            'activeTab'
        ));
    }

    public function update(Request $request): RedirectResponse
    {
        $sekolahId = $this->sekolahId();
        abort_if($sekolahId === null, 403, 'Akun tidak terikat sekolah.');

        $validated = $request->validate([
            'scope' => ['required', Rule::in(AiPersonaScope::all())],
            'name' => ['required', 'string', 'max:120'],
            'role_title' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'gender' => ['nullable', Rule::in([
                SekolahAiPersona::GENDER_PEREMPUAN,
                SekolahAiPersona::GENDER_LAKI_LAKI,
                SekolahAiPersona::GENDER_NETRAL,
            ])],
            'age' => ['nullable', 'integer', 'min:18', 'max:80'],
            'dialog_language' => ['required', 'string', 'max:60'],
            'personality_traits' => ['nullable', 'string', 'max:2000'],
            'communication_style' => ['nullable', 'string', 'max:2000'],
            'behavior_guidelines' => ['nullable', 'string', 'max:2000'],
            'background' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $scope = $validated['scope'];

        $this->personaService->getOrCreate($sekolahId, $scope)->update([
            'name' => $validated['name'],
            'role_title' => $validated['role_title'] ?? null,
            'description' => $validated['description'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'age' => $validated['age'] ?? null,
            'dialog_language' => $validated['dialog_language'],
            'personality_traits' => $validated['personality_traits'] ?? null,
            'communication_style' => $validated['communication_style'] ?? null,
            'behavior_guidelines' => $validated['behavior_guidelines'] ?? null,
            'background' => $validated['background'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.ai-persona.index', ['tab' => $scope])
            ->with('success', 'Persona '.AiPersonaScope::label($scope).' berhasil disimpan.');
    }

    public function updateDataAccess(Request $request): RedirectResponse
    {
        $sekolahId = $this->sekolahId();
        abort_if($sekolahId === null, 403, 'Akun tidak terikat sekolah.');

        $validated = $request->validate([
            'agenda_days_back' => ['required', 'integer', 'min:0', 'max:30'],
            'agenda_days_forward' => ['required', 'integer', 'min:0', 'max:30'],
            'kegiatan_rutin_days_back' => ['required', 'integer', 'min:0', 'max:30'],
            'kegiatan_rutin_days_forward' => ['required', 'integer', 'min:0', 'max:30'],
        ]);

        $payload = $validated;
        foreach (AiChatDataSource::toggleKeys() as $key) {
            $payload[$key] = $request->boolean($key);
        }

        $this->dataAccessService->updateForSekolah($sekolahId, $payload);

        return redirect()
            ->route('admin.ai-persona.index', ['tab' => AiPersonaScope::CHAT_ORANGTUA])
            ->with('success', 'Pengaturan akses data chat berhasil disimpan.');
    }

    public function generate(Request $request): JsonResponse
    {
        $sekolahId = $this->sekolahId();
        abort_if($sekolahId === null, 403, 'Akun tidak terikat sekolah.');

        $request->validate([
            'scope' => ['required', Rule::in(AiPersonaScope::all())],
            'brief' => ['nullable', 'string', 'max:500'],
        ]);

        $sekolah = Sekolah::findOrFail($sekolahId);
        $scope = $request->string('scope')->toString();

        try {
            $fields = $this->tokenService->runWithToken(
                $sekolahId,
                SekolahAiTokenTransaction::TYPE_PERSONA,
                auth()->user(),
                ['scope' => $scope],
                'Generate persona: '.AiPersonaScope::label($scope),
                fn () => $this->personaService->generate($sekolah, $scope, $request->input('brief'))
            );
        } catch (InsufficientAiTokensException $e) {
            return response()->json([
                'ok' => false,
                'error' => $e->fallbackMessage,
                'token_exhausted' => true,
                'token_balance' => $this->tokenService->getBalance($sekolahId),
            ], 422);
        } catch (\RuntimeException $e) {
            return response()->json([
                'ok' => false,
                'error' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'ok' => false,
                'error' => 'Generate gagal. Coba lagi atau periksa pengaturan AI lembaga.',
            ], 500);
        }

        $this->personaService->getOrCreate($sekolahId, $scope)->update([
            'ai_generated_at' => now(),
        ]);

        return response()->json([
            'ok' => true,
            'fields' => $fields,
            'token_balance' => $this->tokenService->getBalance($sekolahId),
        ]);
    }

    public function updateFallbacks(Request $request): RedirectResponse
    {
        $sekolahId = $this->sekolahId();
        abort_if($sekolahId === null, 403, 'Akun tidak terikat sekolah.');

        $validated = $request->validate([
            'fallback_monev' => ['nullable', 'string', 'max:1000'],
            'fallback_pencapaian' => ['nullable', 'string', 'max:1000'],
            'fallback_chat' => ['nullable', 'string', 'max:1000'],
            'fallback_persona' => ['nullable', 'string', 'max:1000'],
            'tab' => ['nullable', Rule::in(AiPersonaScope::all())],
        ]);

        $this->tokenService->updateFallbacks($sekolahId, $validated);

        $tab = $validated['tab'] ?? AiPersonaScope::CHAT_ORANGTUA;

        return redirect()
            ->route('admin.ai-persona.index', ['tab' => $tab])
            ->with('success', 'Pesan fallback berhasil disimpan.');
    }

    public function updateChatEnabled(Request $request): RedirectResponse
    {
        $sekolahId = $this->sekolahId();
        abort_if($sekolahId === null, 403, 'Akun tidak terikat sekolah.');

        $request->validate([
            'chat_orangtua_enabled' => ['sometimes', 'boolean'],
        ]);

        $this->tokenService->updateChatOrangTuaEnabled($sekolahId, $request->boolean('chat_orangtua_enabled'));

        return redirect()
            ->route('admin.ai-persona.index', ['tab' => AiPersonaScope::CHAT_ORANGTUA])
            ->with('success', 'Pengaturan chat orang tua berhasil disimpan.');
    }
}
