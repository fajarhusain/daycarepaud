<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\AiProviderSlot;
use App\Models\AiSetting;
use App\Models\Lembaga;
use App\Services\AiTokenService;
use App\Support\ActivityLogger;
use App\Support\AiProvider;
use App\Support\PaginationPerPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AiSettingController extends Controller
{
    public function __construct(
        protected AiTokenService $tokenService
    ) {}

    protected function resolveLembagaId(Request $request): int
    {
        $lembagaId = $request->integer('lembaga_id');

        abort_if($lembagaId < 1, 404, 'Pilih lembaga terlebih dahulu.');

        return $lembagaId;
    }

    public function index(Request $request)
    {
        $lembagas = Lembaga::orderBy('name')->get();
        $lembaga_id = $request->integer('lembaga_id') ?: $lembagas->first()?->id;

        if (! $lembaga_id) {
            return view('superadmin.ai_setting.index', [
                'lembagas' => $lembagas,
                'lembaga_id' => null,
                'aiSetting' => null,
                'slotsByNumber' => collect(),
                'providers' => AiProvider::all(),
                'schoolsWithBalances' => collect(),
                'transactions' => null,
                'activeTab' => $request->query('tab', 'provider'),
            ]);
        }

        $aiSetting = AiSetting::where('lembaga_id', $lembaga_id)->first();
        $slotsByNumber = AiProviderSlot::query()
            ->where('lembaga_id', $lembaga_id)
            ->get()
            ->keyBy('slot');
        $providers = AiProvider::all();
        $schoolsWithBalances = $this->tokenService->schoolsWithBalances((int) $lembaga_id);
        $transactions = $this->tokenService->paginateTransactions(
            (int) $lembaga_id,
            $request->integer('sekolah_id') ?: null,
            PaginationPerPage::resolve($request)
        );
        $activeTab = $request->query('tab', 'provider');

        return view('superadmin.ai_setting.index', compact(
            'lembagas',
            'lembaga_id',
            'aiSetting',
            'slotsByNumber',
            'providers',
            'schoolsWithBalances',
            'transactions',
            'activeTab'
        ));
    }

    public function testConnection(Request $request)
    {
        $lembaga_id = $this->resolveLembagaId($request);
        $aiSetting = AiSetting::where('lembaga_id', $lembaga_id)->first();
        $service = $aiSetting?->resolveAiService();

        if (! $service) {
            return response()->json([
                'ok' => false,
                'error' => $aiSetting?->apiKeyNeedsReentry()
                    ? 'API Key perlu disimpan ulang (enkripsi tidak valid).'
                    : 'API Key belum dikonfigurasi. Simpan pengaturan lebih dulu.',
            ], 422);
        }

        try {
            [$suggestions, $meta] = $service->runWithMeta(
                fn ($ai) => $ai->generateFeedbackSuggestions(
                    'Anisa',
                    'Mengenal Warna',
                    'Kognitif: Mampu menyebutkan minimal 3 warna',
                    'Berkembang Sesuai Harapan (BSH)'
                )
            );

            return response()->json([
                'ok' => true,
                'message' => 'Koneksi berhasil via '.$meta['label']
                    .' · Provider: '.$meta['provider']
                    .' · Model: '.$meta['model'],
                'sample' => $suggestions[0] ?? '',
                'slot' => $meta['slot'],
            ]);
        } catch (\Throwable $e) {
            Log::warning('AI test connection failed', [
                'lembaga_id' => $lembaga_id,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'error' => $e->getMessage() ?: 'Koneksi AI gagal. Periksa provider, API Key, dan model.',
            ], 500);
        }
    }

    public function update(Request $request)
    {
        $lembaga_id = $this->resolveLembagaId($request);

        $request->validate([
            'slots' => ['required', 'array'],
            'slots.1' => ['required', 'array'],
            'slots.1.ai_provider' => ['required', 'string', Rule::in(AiProvider::keys())],
            'slots.1.ai_model' => ['required', 'string', 'max:255'],
            'slots.1.ai_api_key' => ['nullable', 'string', 'max:1000'],
            'slots.1.ai_base_url' => ['nullable', 'string', 'max:500'],
            'slots.2' => ['nullable', 'array'],
            'slots.2.is_enabled' => ['nullable', 'boolean'],
            'slots.2.ai_provider' => ['nullable', 'string', Rule::in(AiProvider::keys())],
            'slots.2.ai_model' => ['nullable', 'string', 'max:255'],
            'slots.2.ai_api_key' => ['nullable', 'string', 'max:1000'],
            'slots.2.ai_base_url' => ['nullable', 'string', 'max:500'],
            'slots.3' => ['nullable', 'array'],
            'slots.3.is_enabled' => ['nullable', 'boolean'],
            'slots.3.ai_provider' => ['nullable', 'string', Rule::in(AiProvider::keys())],
            'slots.3.ai_model' => ['nullable', 'string', 'max:255'],
            'slots.3.ai_api_key' => ['nullable', 'string', 'max:1000'],
            'slots.3.ai_base_url' => ['nullable', 'string', 'max:500'],
        ]);

        $slotsInput = $request->input('slots', []);
        $existing = AiProviderSlot::query()
            ->where('lembaga_id', $lembaga_id)
            ->get()
            ->keyBy('slot');

        $this->assertSlotPayload(1, $slotsInput[1] ?? [], $existing->get(1), required: true);
        foreach ([2, 3] as $n) {
            if (! empty($slotsInput[$n]) && $this->slotLooksConfigured($slotsInput[$n], $existing->get($n))) {
                $this->assertSlotPayload($n, $slotsInput[$n], $existing->get($n), required: false);
            }
        }

        $aiSetting = DB::transaction(function () use ($lembaga_id, $slotsInput, $existing) {
            $aiSetting = AiSetting::firstOrCreate(
                ['lembaga_id' => $lembaga_id],
                [
                    'ai_provider' => 'sumopod',
                    'ai_model' => 'gpt-4o-mini',
                ]
            );

            foreach ([1, 2, 3] as $n) {
                $payload = $slotsInput[$n] ?? [];
                $current = $existing->get($n);

                if ($n === 1) {
                    $this->upsertSlot($lembaga_id, $n, $payload, $current, enabled: true);
                    continue;
                }

                $enabled = filter_var($payload['is_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $configured = $this->slotLooksConfigured($payload, $current);

                if (! $configured && ! $current) {
                    continue;
                }

                if (! $configured && $current) {
                    $current->update(['is_enabled' => false]);

                    continue;
                }

                $this->upsertSlot($lembaga_id, $n, $payload, $current, enabled: $enabled);
            }

            $slot1 = AiProviderSlot::query()
                ->where('lembaga_id', $lembaga_id)
                ->where('slot', 1)
                ->first();

            if ($slot1) {
                $aiSetting->syncLegacyFromSlot($slot1);
            }

            return $aiSetting->fresh();
        });

        return redirect()->route('superadmin.ai-setting.index', [
            'lembaga_id' => $lembaga_id,
            'tab' => 'provider',
        ])->with('success', 'Pengaturan AI berhasil disimpan.');
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function slotLooksConfigured(array $payload, ?AiProviderSlot $existing): bool
    {
        if (filled($payload['ai_provider'] ?? null) && filled($payload['ai_model'] ?? null)) {
            return true;
        }

        if ($existing && ($existing->hasStoredApiKey() || filled($existing->ai_model))) {
            // Still "configured" if user only toggles enable / leaves fields as-is
            return filled($payload['ai_provider'] ?? $existing->ai_provider)
                && filled($payload['ai_model'] ?? $existing->ai_model);
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function assertSlotPayload(int $slot, array $payload, ?AiProviderSlot $existing, bool $required): void
    {
        $provider = $payload['ai_provider'] ?? $existing?->ai_provider;
        $model = $payload['ai_model'] ?? $existing?->ai_model;
        $baseUrl = $payload['ai_base_url'] ?? $existing?->ai_base_url;
        $hasKey = filled($payload['ai_api_key'] ?? null) || ($existing?->hasStoredApiKey() ?? false);

        if ($required) {
            if (! filled($provider) || ! filled($model)) {
                throw ValidationException::withMessages([
                    "slots.{$slot}.ai_provider" => 'Slot 1 (Utama) wajib diisi provider dan model.',
                ]);
            }

            if (! $hasKey) {
                throw ValidationException::withMessages([
                    "slots.{$slot}.ai_api_key" => 'API Key Slot 1 wajib diisi.',
                ]);
            }
        }

        if ($provider === AiProvider::CUSTOM) {
            if (! filled($baseUrl)) {
                throw ValidationException::withMessages([
                    "slots.{$slot}.ai_base_url" => 'Base URL wajib untuk provider custom.',
                ]);
            }

            try {
                AiProvider::assertSafeCustomBaseUrl(rtrim((string) $baseUrl, '/'));
            } catch (\InvalidArgumentException $e) {
                throw ValidationException::withMessages([
                    "slots.{$slot}.ai_base_url" => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function upsertSlot(
        int $lembagaId,
        int $slot,
        array $payload,
        ?AiProviderSlot $existing,
        bool $enabled
    ): AiProviderSlot {
        $provider = $payload['ai_provider'] ?? $existing?->ai_provider ?? 'sumopod';
        $model = $payload['ai_model'] ?? $existing?->ai_model;
        $baseUrl = $provider === AiProvider::CUSTOM
            ? rtrim((string) ($payload['ai_base_url'] ?? $existing?->ai_base_url ?? ''), '/')
            : null;

        $data = [
            'lembaga_id' => $lembagaId,
            'slot' => $slot,
            'ai_provider' => $provider,
            'ai_model' => $model,
            'ai_base_url' => $baseUrl !== '' ? $baseUrl : null,
            'is_enabled' => $enabled,
        ];

        if ($existing) {
            $existing->fill($data);
            if (filled($payload['ai_api_key'] ?? null)) {
                $existing->ai_api_key = $payload['ai_api_key'];
            }
            $existing->save();

            return $existing;
        }

        if (filled($payload['ai_api_key'] ?? null)) {
            $data['ai_api_key'] = $payload['ai_api_key'];
        }

        return AiProviderSlot::query()->create($data);
    }

    public function storeTokens(Request $request)
    {
        $lembaga_id = $this->resolveLembagaId($request);

        $validated = $request->validate([
            'sekolah_id' => ['required', 'integer', 'exists:sekolahs,id'],
            'amount' => ['required', 'integer', 'min:1', 'max:100000'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $this->tokenService->assertSekolahBelongsToLembaga((int) $validated['sekolah_id'], $lembaga_id);

        $this->tokenService->topUp(
            (int) $validated['sekolah_id'],
            (int) $validated['amount'],
            auth()->user(),
            $validated['description'] ?? null
        );

        ActivityLogger::log('Token AI ditambahkan', null, [
            'lembaga_id' => $lembaga_id,
            'sekolah_id' => (int) $validated['sekolah_id'],
            'amount' => (int) $validated['amount'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()
            ->route('superadmin.ai-setting.index', [
                'lembaga_id' => $lembaga_id,
                'tab' => 'tokens',
            ])
            ->with('success', 'Token berhasil ditambahkan.');
    }
}
