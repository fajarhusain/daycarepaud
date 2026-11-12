<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background:#1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
            </div>
            <h2 class="font-bold text-xl" style="color:#2C2C2C;">Pengaturan AI</h2>
        </div>
    </x-slot>

  @php
      $providersJson = json_encode($providers, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
      $slotsByNumber = $slotsByNumber ?? collect();
      $configuredSlots = $aiSetting?->configuredSlotCount() ?? 0;
  @endphp

    <div class="py-4 md:py-8 px-3 md:px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto"
        x-data="{
            activeTab: @js($activeTab),
            testLoading: false,
            testResult: null,
            providers: {{ $providersJson }},
            setTab(tab) {
                this.activeTab = tab;
                const url = new URL(window.location);
                url.searchParams.set('tab', tab);
                window.history.replaceState({}, '', url);
            },
            async testConnection() {
                this.testLoading = true;
                this.testResult = null;
                try {
                    const res = await fetch('{{ route('superadmin.ai-setting.test') }}?lembaga_id={{ $lembaga_id ?? 0 }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin'
                    });
                    const data = await res.json();
                    this.testResult = data;
                } catch(e) {
                    this.testResult = { ok: false, error: 'Gagal terhubung: ' + e.message };
                } finally {
                    this.testLoading = false;
                }
            }
        }">

        @if($lembagas->isEmpty())
            <div class="card p-8 text-center text-sm" style="color:#9E9790;">Belum ada lembaga terdaftar. Tambahkan lembaga terlebih dahulu.</div>
        @else
        <form method="GET" action="{{ route('superadmin.ai-setting.index') }}" class="mb-6 flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[200px]">
                <label class="input-label">Lembaga / Yayasan</label>
                <select name="lembaga_id" class="input-field" onchange="this.form.submit()">
                    @foreach($lembagas as $l)
                        <option value="{{ $l->id }}" @selected($lembaga_id == $l->id)>{{ $l->name }}</option>
                    @endforeach
                </select>
            </div>
            @if(request('tab'))
                <input type="hidden" name="tab" value="{{ request('tab') }}">
            @endif
            <x-filter-reset :href="route('superadmin.ai-setting.index', request('tab') ? ['tab' => request('tab')] : [])" />
        </form>

        @if(session('success'))
            <div class="alert-success mb-5">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="alert-danger mb-5">
                <ul class="list-disc pl-5 text-sm">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
            </div>
        @endif
        @if($aiSetting?->apiKeyNeedsReentry())
            <div class="alert-danger mb-5">
                API Key tersimpan tidak dapat dibaca (biasanya karena <code>APP_KEY</code> berubah). Masukkan API Key lagi lalu simpan.
            </div>
        @endif

        <div data-tour="superadmin-ai-tabs" class="flex flex-wrap gap-2 mb-6">
            <button type="button" @click="setTab('provider')"
                class="px-4 py-2 rounded-xl text-sm font-semibold transition-all"
                :style="activeTab === 'provider' ? 'background:#1A6B6B; color:#fff;' : 'background:#F5F0E8; color:#6B6560;'">
                Provider & API
            </button>
            <button type="button" @click="setTab('tokens')"
                class="px-4 py-2 rounded-xl text-sm font-semibold transition-all"
                :style="activeTab === 'tokens' ? 'background:#1A6B6B; color:#fff;' : 'background:#F5F0E8; color:#6B6560;'">
                Token Sekolah
            </button>
        </div>

        <div x-show="activeTab === 'provider'" x-cloak>
        {{-- Status Banner --}}
        <div data-tour="superadmin-ai-status" class="mb-6 rounded-2xl border p-5 flex items-start gap-4"
            style="background:{{ $aiSetting?->hasValidApiKey() ? '#D0E8E8' : '#FEF9EC' }}; border-color: {{ $aiSetting?->hasValidApiKey() ? '#1A6B6B33' : '#F0B84233' }};">
            <div class="h-10 w-10 rounded-xl flex items-center justify-center shrink-0"
                style="background:{{ $aiSetting?->hasValidApiKey() ? '#1A6B6B' : '#F0B842' }};">
                @if($aiSetting?->hasValidApiKey())
                    <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                @else
                    <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                @endif
            </div>
            <div>
                <div class="font-bold text-sm" style="color:#2C2C2C;">
                    {{ $aiSetting?->hasValidApiKey() ? 'AI Aktif  '.$configuredSlots.' slot siap dipakai' : 'AI Belum Dikonfigurasi' }}
                </div>
                <div class="text-xs mt-1" style="color:#6B6560;">
                    @if($aiSetting?->hasValidApiKey())
                        Utama: <strong>{{ $aiSetting->providerLabel() }}</strong> · Model: <strong>{{ $aiSetting->primaryModel() ?? '-' }}</strong>
                        @if($configuredSlots > 1)
                            · Fallback otomatis ke slot berikutnya jika gagal
                        @endif
                    @else
                        Isi Slot 1 (Utama). Slot 2–3 opsional sebagai backup.
                    @endif
                </div>
            </div>
        </div>

        {{-- Test Result Banner --}}
        <div x-show="testResult !== null" x-transition class="mb-6 rounded-2xl border p-4" style="display:none;"
            :style="testResult?.ok ? 'background:#D0E8E8; border-color:#1A6B6B33' : 'background:#FEE2E2; border-color:#EF444433'">
            <div class="flex items-start gap-3">
                <div class="h-8 w-8 rounded-lg flex items-center justify-center shrink-0 mt-0.5"
                    :style="testResult?.ok ? 'background:#1A6B6B' : 'background:#EF4444'">
                    <template x-if="testResult?.ok">
                        <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </template>
                    <template x-if="!testResult?.ok">
                        <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </template>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-bold text-sm" style="color:#2C2C2C;"
                        x-text="testResult?.ok ? testResult.message : 'Koneksi Gagal'"></div>
                    <template x-if="testResult?.ok && testResult?.sample">
                        <div class="text-xs mt-2 p-2.5 rounded-lg" style="background:rgba(255,255,255,0.6); color:#2C2C2C; line-height:1.5;">
                            <span class="font-semibold block mb-1" style="color:#1A6B6B;">Contoh saran AI yang dihasilkan:</span>
                            <span x-text="testResult?.sample"></span>
                        </div>
                    </template>
                    <template x-if="!testResult?.ok">
                        <div class="text-xs mt-1 font-mono break-all" style="color:#DC2626;" x-text="testResult?.error"></div>
                    </template>
                </div>
            </div>
        </div>

        {{-- Settings Form --}}
        <div class="card overflow-hidden">
            <div class="px-6 py-4 border-b" style="border-color:rgba(0,0,0,0.06);">
                <h3 class="section-title">Konfigurasi AI Provider</h3>
                <p class="section-subtitle mt-1">
                    Atur hingga 3 slot OpenAI-compatible. Sistem mencoba Slot 1 dulu, lalu Slot 2, lalu Slot 3 jika gagal
                    (HTTP error, timeout, respons kosong, atau format tidak dikenali).
                </p>
            </div>
            <form data-tour="superadmin-ai-form" action="{{ route('superadmin.ai-setting.update') }}" method="POST">
                @csrf
                <input type="hidden" name="lembaga_id" value="{{ $lembaga_id }}">
                <div class="px-6 py-6 space-y-5">
                    @include('superadmin.ai_setting._slot_fields', [
                        'slotNumber' => 1,
                        'slot' => $slotsByNumber->get(1),
                        'title' => 'Slot 1  Utama',
                        'subtitle' => 'Provider utama yang selalu dicoba terlebih dahulu.',
                        'required' => true,
                        'providers' => $providers,
                    ])

                    @include('superadmin.ai_setting._slot_fields', [
                        'slotNumber' => 2,
                        'slot' => $slotsByNumber->get(2),
                        'title' => 'Slot 2  Backup 1',
                        'subtitle' => 'Dipakai otomatis jika Slot 1 gagal.',
                        'required' => false,
                        'providers' => $providers,
                    ])

                    @include('superadmin.ai_setting._slot_fields', [
                        'slotNumber' => 3,
                        'slot' => $slotsByNumber->get(3),
                        'title' => 'Slot 3  Backup 2',
                        'subtitle' => 'Cadangan terakhir jika Slot 1 dan 2 gagal.',
                        'required' => false,
                        'providers' => $providers,
                    ])

                    <div class="rounded-xl border p-4 text-xs space-y-2" style="background:#FAF6F0; border-color:rgba(0,0,0,0.06); color:#6B6560;">
                        <div class="font-semibold" style="color:#2C2C2C;">Cara konfigurasi</div>
                        <ol class="list-decimal pl-4 space-y-1">
                            <li>Isi Slot 1 dengan provider utama (wajib).</li>
                            <li>Opsional: aktifkan Slot 2/3 dengan provider atau model berbeda sebagai backup.</li>
                            <li>Simpan, lalu gunakan <strong>Test Koneksi AI</strong> untuk menguji rantai fallback.</li>
                        </ol>
                    </div>
                </div>

                <div class="px-6 pb-6 flex flex-wrap justify-between items-center gap-3 border-t pt-5" style="border-color:rgba(0,0,0,0.06);">
                    <button type="button" @click="testConnection()"
                        :disabled="testLoading"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold border transition-all"
                        style="color:#1A6B6B; background:#D0E8E8; border-color:#1A6B6B33;"
                        :class="{ 'opacity-60 cursor-not-allowed': testLoading }">
                        <template x-if="!testLoading">
                            <span class="flex items-center gap-2">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                Test Koneksi AI
                            </span>
                        </template>
                        <template x-if="testLoading">
                            <span class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Menguji koneksi...
                            </span>
                        </template>
                    </button>

                    <button type="submit" class="btn-primary shrink-0 h-9 w-9 md:h-11 md:w-11 p-0" title="Simpan Pengaturan" aria-label="Simpan Pengaturan">
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg></button>
                </div>
            </form>
        </div>
        </div>

        <div x-show="activeTab === 'tokens'" x-cloak>
            <div class="card overflow-hidden mb-6" data-tour="superadmin-ai-tokens">
                <div class="px-6 py-4 border-b" style="border-color:rgba(0,0,0,0.06);">
                    <h3 class="section-title">Saldo Token per Sekolah</h3>
                    <p class="section-subtitle mt-1">Tambahkan token AI untuk setiap sekolah. Satu token = satu kali generate monev per anak, saran pencapaian, chat, atau generate persona.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b text-left text-xs uppercase tracking-wide" style="border-color:rgba(0,0,0,0.06); color:#9E9790;">
                                <th class="px-6 py-3 font-semibold">Sekolah</th>
                                <th class="px-4 py-3 font-semibold">Saldo</th>
                                <th class="px-6 py-3 font-semibold">Top-up</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($schoolsWithBalances as $row)
                                <tr class="border-b" style="border-color:rgba(0,0,0,0.04);">
                                    <td class="px-6 py-4 font-medium" style="color:#2C2C2C;">{{ $row['sekolah']->name }}</td>
                                    <td class="px-4 py-4">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold"
                                            style="background:#D0E8E8; color:#1A6B6B;">
                                            {{ number_format($row['balance']) }} token
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <form action="{{ route('superadmin.ai-setting.tokens.store') }}" method="POST" class="flex flex-wrap items-center gap-2">
                                            @csrf
                                            <input type="hidden" name="lembaga_id" value="{{ $lembaga_id }}">
                                            <input type="hidden" name="sekolah_id" value="{{ $row['sekolah']->id }}">
                                            <input type="number" name="amount" min="1" max="100000" required
                                                class="input-field w-28 text-sm py-2" placeholder="Jumlah">
                                            <button type="submit" class="btn-primary text-xs py-2 px-3">Tambah</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-8 text-center text-sm" style="color:#9E9790;">
                                        Belum ada sekolah terdaftar di bawah lembaga ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card overflow-hidden">
                <div class="px-6 py-4 border-b flex flex-wrap items-center justify-between gap-3" style="border-color:rgba(0,0,0,0.06);">
                    <div>
                        <h3 class="section-title">Riwayat Transaksi Token</h3>
                        <p class="section-subtitle mt-1">Top-up dan pemakaian token terbaru.</p>
                    </div>
                    <form method="GET" action="{{ route('superadmin.ai-setting.index') }}" class="flex items-center gap-2">
                        <input type="hidden" name="tab" value="tokens">
                        <input type="hidden" name="lembaga_id" value="{{ $lembaga_id }}">
                        <select name="sekolah_id" class="input-field text-sm py-2" onchange="this.form.submit()">
                            <option value="">Semua sekolah</option>
                            @foreach($schoolsWithBalances as $row)
                                <option value="{{ $row['sekolah']->id }}" @selected(request('sekolah_id') == $row['sekolah']->id)>
                                    {{ $row['sekolah']->name }}
                                </option>
                            @endforeach
                        </select>
                        <x-filter-reset :href="route('superadmin.ai-setting.index', ['tab' => 'tokens', 'lembaga_id' => $lembaga_id])" />
                    </form>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b text-left text-xs uppercase tracking-wide" style="border-color:rgba(0,0,0,0.06); color:#9E9790;">
                                <th class="px-6 py-3 font-semibold">Waktu</th>
                                <th class="px-4 py-3 font-semibold">Sekolah</th>
                                <th class="px-4 py-3 font-semibold">Jenis</th>
                                <th class="px-4 py-3 font-semibold">Jumlah</th>
                                <th class="px-6 py-3 font-semibold">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $tx)
                                <tr class="border-b" style="border-color:rgba(0,0,0,0.04);">
                                    <td class="px-6 py-3 text-xs whitespace-nowrap" style="color:#6B6560;">{{ $tx->created_at->format('d M Y H:i') }}</td>
                                    <td class="px-4 py-3">{{ $tx->sekolah?->name ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $tx->typeLabel() }}</td>
                                    <td class="px-4 py-3 font-semibold" style="color:{{ $tx->amount >= 0 ? '#1A6B6B' : '#DC2626' }};">
                                        {{ $tx->amount >= 0 ? '+' : '' }}{{ number_format($tx->amount) }}
                                    </td>
                                    <td class="px-6 py-3 text-xs" style="color:#6B6560;">{{ $tx->description ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-sm" style="color:#9E9790;">
                                        Belum ada transaksi token.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t" style="border-color:rgba(0,0,0,0.06);">
                    <x-per-page-selector :paginator="$transactions" />
                    {{ $transactions->appends(['tab' => 'tokens'])->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>
</x-app-layout>
