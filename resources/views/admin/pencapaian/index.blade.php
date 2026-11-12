<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background:#1A6B6B;"><svg
                    class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg></div>
            <h2 class="font-bold text-xl" style="color:#2C2C2C;">Evaluasi Pencapaian Siswa (Admin)</h2>
        </div>
    </x-slot>

    @php
        $sekolahId = (int) auth()->user()->sekolah_id;
        $skalaOptions = $skalas->map(fn ($s) => ['code' => $s->code, 'label' => $s->label])->values()->all();
        $filterAspek = $filterAspek ?? null;
        // Kumpulkan anak_id yang sudah punya pencapaian per kegiatan
        $pencapaianAnak = \App\Models\Pencapaian::query()
            ->whereIn('kegiatan_id', $kegiatans->pluck('id'))
            ->select('kegiatan_id', 'anak_id')
            ->distinct()
            ->get()
            ->groupBy('kegiatan_id')
            ->map(fn($rows) => $rows->pluck('anak_id')->values()->all());

        $tanggalKegiatans = $kegiatans->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->toDateString())->unique()->toArray();
        $kehadiran = \App\Models\Presensi::query()
            ->whereIn('tanggal', $tanggalKegiatans)
            ->where('hadir', true)
            ->get()
            ->groupBy(function($p) {
                return $p->tanggal instanceof \Carbon\Carbon ? $p->tanggal->toDateString() : \Carbon\Carbon::parse($p->tanggal)->toDateString();
            })
            ->map(fn($rows) => $rows->pluck('anak_id')->values()->all());

        $kegiatanData = [];
        foreach ($kegiatans as $kg) {
            $kegiatanData[$kg->id] = [
                'id' => $kg->id,
                'kelas_id' => $kg->kelas_id,
                'title' => $kg->title,
                'date_label' => \Carbon\Carbon::parse($kg->date)->format('d M Y'),
                'is_executed' => !empty($kg->photos),
                'pencapaian_anak_ids' => $pencapaianAnak[$kg->id] ?? [],
                'present_anak_ids' => $kehadiran[\Carbon\Carbon::parse($kg->date)->toDateString()] ?? [],
                'matrikulasis' => $kg->matrikulasis->map(fn($m) => [
                    'id' => $m->id,
                    'aspek' => $m->aspek,
                    'indicator' => $m->indicator,
                    'label' => ($m->aspek ? $m->aspek . ': ' : '') . $m->indicator,
                ])->values()->all()
            ];
        }
        $anakMap = [];
        foreach ($anaks as $a) {
            $anakMap[$a->id] = [
                'id' => $a->id,
                'name' => $a->name,
                'nickname' => $a->nickname,
                'kelas_id' => $a->kelas_id,
                'display_label' => $a->nickname ? $a->name . ' (' . $a->nickname . ')' : $a->name,
            ];
        }
        $flags = JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
        $payloadJson = json_encode(['kegiatanData' => $kegiatanData, 'anakMap' => $anakMap, 'editBundles' => $editBundles], $flags);
        if ($payloadJson === false) {
            $payloadJson = '{}';
        }
    @endphp

    <div class="app-page-shell" x-data="{
            hasTokens: @js($hasTokens ?? false),
            tokenFallbackPencapaian: @js($tokenFallbackPencapaian ?? 'Maaf, fitur ini sedang terbatas.'),
            skalaOptions: @js($skalaOptions),
            showCreateModal: false, showEditModal: false, showDeleteBundleModal: false,
            showImageModal: false, activeImage: null, activeDownloadUrl: null, activeMediaType: 'image',
            deleteBundleAnak: '', deleteBundleKeg: '', payload: {},
            selectedKelasIdCreate: '', selectedAnakId: '', selectedKegiatanId: '', selectedKegiatanIdEdit: '',
            editBundleKey: null, editNilai: {}, editCatatan: {}, createNilai: {}, createCatatan: {},
            docUploadActive: false, docUploadProgress: 0, docUploadLabel: '',
            compressedFile: null,
            aiLoading: {}, aiSuggestions: {},
            init() {
                const el = document.getElementById('pencapaian-payload-json');
                if (el) { try { this.payload = JSON.parse(el.textContent); } catch (e) { this.payload = { kegiatanData: {}, anakMap: {}, editBundles: {} }; } }
            },
            openMedia(url, downloadUrl) {
                this.activeImage = url;
                this.activeDownloadUrl = downloadUrl ?? null;
                this.activeMediaType = (typeof window.isVideoPath === 'function' && window.isVideoPath(url)) ? 'video' : 'image';
                this.showImageModal = true;
            },
            async handleFile(e) {
                const file = e.target.files[0]; if (!file) return;
                try {
                    this.compressedFile = await window.documentationUploadProcess(file, this);
                } catch (err) {
                    alert(err?.message || 'Gagal memproses file.');
                    e.target.value = '';
                    this.compressedFile = null;
                }
            },
            submitWithCompression(formRef) {
                const form = this.$refs[formRef];
                // Safety net: manually sync Alpine catatan data → DOM textarea values
                // (needed because native form.submit() reads DOM, not Alpine data)
                const catatanMap = formRef === 'editForm' ? this.editCatatan : this.createCatatan;
                Object.entries(catatanMap).forEach(([matId, text]) => {
                    const ta = form.querySelector(`textarea[name='catatan[${matId}]']`);
                    if (ta && ta.value !== text) ta.value = text;
                });
                if (this.compressedFile) {
                    const dt = new DataTransfer(); dt.items.add(this.compressedFile);
                    form.querySelector('input[type=file]').files = dt.files;
                }
                form.submit();
            },
            async fetchAiSuggestions(mode, optId, anakId, kegiatanId, score) {
                if (!this.hasTokens) { alert(this.tokenFallbackPencapaian); return; }
                const nilaiMap  = mode === 'create' ? this.createNilai  : this.editNilai;
                const resolvedScore = score || nilaiMap[String(optId)] || '';
                if (!resolvedScore) { alert('Pilih skala capaian terlebih dahulu sebelum meminta saran AI.'); return; }
                const key = mode + '_' + optId;
                // Tutup semua saran aspek lain  hanya tampilkan untuk aspek yang diklik
                this.aiSuggestions = {};
                this.aiLoading = { ...this.aiLoading, [key]: true };
                try {
                    const res = await fetch(@js(route('admin.ai.feedback-suggestions')), {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                        body: JSON.stringify({ anak_id: anakId, kegiatan_id: kegiatanId, matrikulasi_id: optId, score: resolvedScore }),
                        credentials: 'same-origin'
                    });
                    const data = await res.json();
                    if (!res.ok) {
                        if (data.token_exhausted) {
                            this.hasTokens = false;
                        }
                        alert(data.error || 'Gagal mendapatkan saran AI.');
                        return;
                    }
                    if (typeof data.token_balance === 'number' && data.token_balance < 1) {
                        this.hasTokens = false;
                    }
                    // Hanya set saran untuk aspek ini (aspek lain sudah dikosongkan di atas)
                    this.aiSuggestions = { [key]: data.suggestions || [] };
                } catch(e) {
                    alert('Terjadi kesalahan: ' + e.message);
                } finally {
                    this.aiLoading = { ...this.aiLoading, [key]: false };
                }
            },
            applySuggestion(mode, optId, text) {
                const id = String(optId);
                if (mode === 'create') {
                    this.createCatatan = { ...this.createCatatan, [id]: text };
                } else {
                    this.editCatatan = { ...this.editCatatan, [id]: text };
                }
                // Use spread so Alpine re-evaluates x-if chip template
                this.aiSuggestions = { ...this.aiSuggestions, [mode + '_' + optId]: [] };
            },
            get kegiatanData() { return this.payload.kegiatanData || {}; },
            get anakMap() { return this.payload.anakMap || {}; },
            get editBundles() { return this.payload.editBundles || {}; },
            get filteredAnaks() {
                if (!this.selectedKelasIdCreate) return Object.values(this.anakMap);
                return Object.values(this.anakMap).filter(a => a.kelas_id == this.selectedKelasIdCreate);
            },
            get filteredKegiatans() {
                if (!this.selectedAnakId) return [];
                const anakKelas = this.anakMap[this.selectedAnakId]?.kelas_id;
                const anakId = parseInt(this.selectedAnakId);
                return Object.values(this.kegiatanData).filter(k => 
                    k.kelas_id == anakKelas && 
                    k.is_executed && 
                    !(k.pencapaian_anak_ids || []).includes(anakId) &&
                    (k.present_anak_ids || []).includes(anakId)
                );
            },
            get matrikulasiOptions() { return (this.kegiatanData[this.selectedKegiatanId] || {}).matrikulasis || []; },
            get matrikulasiOptionsEdit() { return (this.kegiatanData[this.selectedKegiatanIdEdit] || {}).matrikulasis || []; },
            resetCreateMatrices() {
                this.createNilai = {}; this.createCatatan = {}; this.aiSuggestions = {};
                (this.matrikulasiOptions || []).forEach(o => { this.createNilai[String(o.id)] = ''; this.createCatatan[String(o.id)] = ''; });
            },
            openCreateModal() { 
                this.selectedKelasIdCreate = ''; 
                this.selectedAnakId = ''; 
                this.selectedKegiatanId = ''; 
                this.createNilai = {}; 
                this.createCatatan = {}; 
                this.aiSuggestions = {};
                this.showCreateModal = true; 
            },
            openEditBundle(key) {
                const b = this.editBundles[key]; if (!b) return;
                this.editBundleKey = key; 
                this.selectedKegiatanIdEdit = String(b.kegiatan_id);
                
                // Clear state first
                this.editNilai = {}; 
                this.editCatatan = {}; 
                this.aiSuggestions = {};
                this.showEditModal = true;

                this.$nextTick(() => {
                    const tempNilai = {}; 
                    const tempCatatan = {};
                    const opts = (this.kegiatanData[this.selectedKegiatanIdEdit] || {}).matrikulasis || [];
                    
                    opts.forEach(opt => {
                        const id = String(opt.id);
                        tempNilai[id] = b.nilai[id] ?? b.nilai[opt.id] ?? '';
                        tempCatatan[id] = b.catatan[id] ?? b.catatan[opt.id] ?? '';
                    });
                    
                    // Assign all at once for reactivity
                    this.editNilai = tempNilai;
                    this.editCatatan = tempCatatan;
                });
            },

            openDeleteBundle(key) {
                const b = this.editBundles[key]; if (!b) return;
                this.deleteBundleAnak = b.anak_id; this.deleteBundleKeg = b.kegiatan_id;
                this.showDeleteBundleModal = true;
            }
         }" @tour-close-modals.window="showCreateModal=false; showEditModal=false; showDeleteBundleModal=false">

        <script type="application/json" id="pencapaian-payload-json">{!! $payloadJson !!}</script>

        @if(session('success'))
            <div class="alert-success mb-5"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>{{ session('success') }}</div>@endif
        @if(session('warning'))<div class="alert-danger mb-5">{{ session('warning') }}</div>@endif
        @if($errors->any())
            <div class="alert-danger mb-5">
                <ul class="list-disc pl-5 text-sm">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
        </div>@endif

        @if(!($hasTokens ?? false))
            <div class="mb-5 px-5 py-3 text-sm rounded-xl border" style="background:#FEE2E2; color:#DC2626; border-color: rgba(0,0,0,0.06);">
                {{ $tokenFallbackPencapaian ?? 'Maaf, fitur ini sedang terbatas.' }}
            </div>
        @endif

        <div class="card mb-6">
            <div class="px-6 py-6 border-b" style="background:#FAF6F0; border-color: rgba(0,0,0,0.06);">
                <div class="space-y-6">
                    <div class="space-y-1">
                        <h3 class="text-xl font-bold" style="color:#2C2C2C;">Filter Evaluasi (Sekolah)</h3>
                    </div>
                    <form data-tour="admin-pencapaian-filter" method="get" action="{{ route('admin.pencapaian.index') }}"
                        class="filter-toolbar-inline">
                        <div class="filter-toolbar-field">
                            <label class="input-label">Dari</label>
                            <input type="date" name="tanggal_dari" value="{{ $tanggalDari }}"
                                class="input-field w-full h-9 md:h-11 text-xs font-bold border-black/10">
                        </div>
                        <div class="filter-toolbar-field">
                            <label class="input-label">Sampai</label>
                            <input type="date" name="tanggal_sampai" value="{{ $tanggalSampai }}"
                                class="input-field w-full h-9 md:h-11 text-xs font-bold border-black/10">
                        </div>
                        <div class="filter-toolbar-field">
                            <label class="input-label">Kelas</label>
                            <select name="filter_kelas_id"
                                class="input-field w-full h-9 md:h-11 text-xs font-bold border-black/10">
                                <option value="">Semua Kelas</option>
                                @foreach($availableKelas as $k)<option value="{{ $k->id }}"
                                @selected($filterKelasId === (int) $k->id)>{{ $k->name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="filter-toolbar-field">
                            <label class="input-label">Nama Siswa</label>
                            <select name="filter_anak_id"
                                class="input-field w-full h-9 md:h-11 text-xs font-bold border-black/10">
                                <option value="">Semua Siswa</option>
                                @foreach($anaks as $a)<option value="{{ $a->id }}" @selected($filterAnakId === (int) $a->id)>{{ $a->name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="filter-toolbar-field">
                            <label class="input-label">Aspek</label>
                            <select name="aspek" class="input-field w-full h-9 md:h-11 text-xs font-bold border-black/10">
                                <option value="">Semua Aspek</option>
                                <option value="{{ \App\Support\FilterAspekPencapaian::UMUM }}"
                                    @selected($filterAspekRaw === \App\Support\FilterAspekPencapaian::UMUM)>Umum</option>
                                @foreach($aspekPilihan as $asp)<option value="{{ $asp }}"
                                @selected($filterAspekRaw === $asp)>{{ $asp }}</option>@endforeach
                            </select>
                        </div>
                        <div class="filter-toolbar-actions">
                            <button type="submit" class="btn-primary h-9 w-9 md:h-11 md:w-11 p-0 shrink-0" title="Cari Data" aria-label="Cari Data">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </button>
                            <x-filter-reset :href="route('admin.pencapaian.index')" compact />
                            <x-export-excel route="admin.pencapaian.export" :icon-only="true" />
                            <x-download-photos route="admin.pencapaian.photos.download" :icon-only="true" />
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="px-5 sm:px-6 py-3 mb-6 bg-[#FAF6F0] border rounded-2xl text-sm flex flex-wrap items-center gap-x-2 gap-y-1"
            style="border-color:rgba(0,0,0,0.04); color:#6B6560;">
            <span class="inline-flex items-center gap-1.5 shrink-0">
                <svg class="h-4 w-4 shrink-0 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Ringkasan:</span>
            </span>
            <span style="color:#2C2C2C;">
                @if(!$tanggalDari && !$tanggalSampai)
                    Seluruh Periode
                @elseif($tanggalDari === $tanggalSampai)
                    {{ \Carbon\Carbon::parse($tanggalDari)->translatedFormat('d M Y') }}
                @else
                    {{ \Carbon\Carbon::parse($tanggalDari)->translatedFormat('d M Y') }} –
                    {{ \Carbon\Carbon::parse($tanggalSampai)->translatedFormat('d M Y') }}
                @endif
            </span>
            @if($filterKelasId)
                <span class="text-black/25 hidden sm:inline">·</span>
                <span>Kelas: {{ $availableKelas->firstWhere('id', $filterKelasId)?->name ?? 'Terpilih' }}</span>
            @endif
            @if($filterAnakId)
                <span class="text-black/25 hidden sm:inline">·</span>
                <span>Siswa: {{ $anaks->firstWhere('id', $filterAnakId)?->name ?? 'Terpilih' }}</span>
            @endif
            @if($filterAspekRaw !== '')
                <span class="text-black/25 hidden sm:inline">·</span>
                <span>{{ $filterAspekRaw === \App\Support\FilterAspekPencapaian::UMUM ? 'Umum' : $filterAspekRaw }}</span>
            @endif
        </div>

        <div class="card overflow-hidden">
            <div class="card-pad page-toolbar border-b" style="border-color:rgba(0,0,0,0.06);">
                <h3 class="section-title mb-0">Laporan Pencapaian Sekolah</h3>
                <x-toolbar.add-icon label="Buat Evaluasi" data-tour="admin-pencapaian-add-btn" data-tour-open-modal="create" @click="openCreateModal()" />
            </div>

            <div class="lg:hidden divide-y" style="border-color:rgba(0,0,0,0.06);">
                @forelse($groupedPencapaian as $bundleKey => $rows)
                    @php $first = $rows->first(); @endphp
                    <article class="p-3 space-y-2.5">
                        <div class="flex items-start gap-2.5 min-w-0">
                            @if($first->photo)
                                @php $mediaUrl = \App\Support\DocumentationMedia::publicAssetPath($first->photo); @endphp
                                <x-documentation-thumbnail
                                    :path="$first->photo"
                                    class="h-14 w-14 rounded-lg cursor-pointer"
                                    @click="openMedia('{{ $mediaUrl }}', '{{ route('admin.pencapaian.photos.download-bundle', ['anak_id' => $first->anak_id, 'kegiatan_id' => $first->kegiatan_id]) }}')"
                                />
                            @else
                                <div class="h-12 w-12 rounded-lg bg-gray-100 flex items-center justify-center shrink-0 text-gray-300">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <x-foto-profil :path="$first->anak->photo ?? null" :name="$first->anak->name ?? '?'" size="xs" />
                                    <p class="font-semibold text-sm truncate" style="color:#2C2C2C;">{{ $first->anak->name ?? '-' }}</p>
                                </div>
                                <p class="text-xs mt-1 leading-snug break-words" style="color:#1A6B6B;">{{ $first->kegiatan->title ?? '-' }}</p>
                                <p class="text-[10px] mt-0.5" style="color:#9E9790;">{{ \Carbon\Carbon::parse($first->created_at)->translatedFormat('d M Y') }}</p>
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            @foreach($rows->filter(fn($p) => \App\Support\FilterAspekPencapaian::rowMatches($filterAspek, $p)) as $p)
                                <div class="text-[11px] rounded-lg bg-gray-50 px-2 py-1.5 border border-black/5">
                                    <p class="font-bold uppercase leading-tight" style="color:#1A6B6B;">{{ $p->matrikulasi->aspek ?: 'Umum' }}</p>
                                    <p class="mt-0.5 leading-snug break-words" style="color:#5A5A5A;">{{ $p->matrikulasi->indicator ?? '' }}</p>
                                    <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                        <span class="font-bold px-1.5 py-0.5 rounded" style="background:{{ \App\Support\LabelSkorPencapaian::color($p->score, $sekolahId) }};">{{ \App\Support\LabelSkorPencapaian::label($p->score, $sekolahId) }}</span>
                                        @if($p->feedback)<span class="italic break-words" style="color:#6B6560;">"{{ $p->feedback }}"</span>@endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" @if($loop->first) data-tour="admin-pencapaian-action-edit" data-tour-open-modal="edit" @endif @click="openEditBundle('{{ $bundleKey }}')" class="btn-primary flex-1">Edit</button>
                            <button type="button" @if($loop->first) data-tour="admin-pencapaian-action-delete" data-tour-demo-action="delete" @endif @click="openDeleteBundle('{{ $bundleKey }}')" class="h-9 w-9 shrink-0 rounded-lg flex items-center justify-center" style="color:#C0392B;background:#FAD7D2;" title="Hapus" aria-label="Hapus">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </article>
                @empty
                    <p class="px-4 py-10 text-center text-sm" style="color:#9E9790;">Belum ada evaluasi.</p>
                @endforelse
            </div>

            <div class="hidden lg:block overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="w-28 min-w-[7rem]">Bukti</th>
                            <th>Siswa</th>
                            <th>Kegiatan</th>
                            <th>Aspek / Nilai</th>
                            <th>Tanggal</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($groupedPencapaian as $bundleKey => $rows)
                            @php $first = $rows->first(); @endphp
                            <tr>
                                <td class="w-28 min-w-[7rem] align-middle">
                                    @if($first->photo)
                                        @php $mediaUrl = \App\Support\DocumentationMedia::publicAssetPath($first->photo); @endphp
                                        <div class="flex items-center gap-2">
                                            <div class="relative group cursor-pointer shrink-0"
                                                @click="openMedia('{{ $mediaUrl }}', '{{ route('admin.pencapaian.photos.download-bundle', ['anak_id' => $first->anak_id, 'kegiatan_id' => $first->kegiatan_id]) }}')">
                                                <x-documentation-thumbnail
                                                    :path="$first->photo"
                                                    class="h-14 w-14 rounded-lg"
                                                />
                                                <div
                                                    class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center rounded pointer-events-none">
                                                    <svg class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                </div>
                                            </div>
                                            <a href="{{ route('admin.pencapaian.photos.download-bundle', ['anak_id' => $first->anak_id, 'kegiatan_id' => $first->kegiatan_id]) }}"
                                                class="text-[10px] font-bold uppercase tracking-wide text-teal-700 hover:underline"
                                                title="Unduh foto">Unduh</a>
                                        </div>
                                    @else<div
                                        class="h-10 w-10 bg-gray-100 rounded flex items-center justify-center opacity-30">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>@endif
                                </td>
                                <td>
                                    <div class="flex items-center gap-2 min-w-[10rem]">
                                        <x-foto-profil :path="$first->anak->photo ?? null" :name="$first->anak->name ?? '?'"
                                            size="sm" />
                                        <span class="font-semibold break-words"
                                            style="color:#2C2C2C;">{{ $first->anak->name ?? '-' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="font-medium break-words min-w-[8rem]" style="color:#2C2C2C;">{{ $first->kegiatan->title ?? '-' }}
                                    </div>
                                </td>
                                <td class="min-w-[240px]">
                                    <div class="space-y-1.5">
                                        @foreach($rows->filter(fn($p) => \App\Support\FilterAspekPencapaian::rowMatches($filterAspek, $p)) as $p)
                                            <div class="text-[11px] rounded bg-gray-50 p-1.5 border border-black/5">
                                                <span class="font-bold text-teal-700 uppercase"
                                                    x-text="'{{ $p->matrikulasi->aspek ?: 'Umum' }}'"></span>:
                                                {{ $p->matrikulasi->indicator ?? '' }}
                                                <div class="mt-1 flex items-center gap-2">
                                                    <span class="font-bold px-1.5 py-0.5 rounded"
                                                        style="background:{{ \App\Support\LabelSkorPencapaian::color($p->score, $sekolahId) }};">{{ \App\Support\LabelSkorPencapaian::label($p->score, $sekolahId) }}</span>
                                                    @if($p->feedback)<span class="italic text-[#6B6560] truncate max-w-[180px]"
                                                    title="{{ $p->feedback }}">"{{ $p->feedback }}"</span>@endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="text-xs">{{ \Carbon\Carbon::parse($first->created_at)->format('d/m/Y') }}</td>
                                <td class="text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button" @if($loop->first) data-tour="admin-pencapaian-action-edit" data-tour-open-modal="edit" @endif @click="openEditBundle('{{ $bundleKey }}')"
                                            class="btn-xs border border-teal-200 text-teal-700 bg-teal-50 px-2 py-1 rounded">Edit</button>
                                        <button type="button" @if($loop->first) data-tour="admin-pencapaian-action-delete" data-tour-demo-action="delete" @endif @click="openDeleteBundle('{{ $bundleKey }}')"
                                            class="btn-xs border border-red-200 text-red-700 bg-red-50 px-2 py-1 rounded">Hapus</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-gray-400">Belum ada evaluasi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t">
                <x-per-page-selector :paginator="$groupedPencapaian" />
                {{ $groupedPencapaian->links() }}
            </div>
        </div>

        {{-- CREATE MODAL --}}
        <div x-show="showCreateModal" class="modal-overlay" style="display:none;"
            @click.self="!docUploadActive && (showCreateModal=false)">
            <div x-show="showCreateModal" x-transition class="modal-box max-w-lg w-full relative" @click.stop>
                <form action="{{ route('admin.pencapaian.sync') }}" method="POST" enctype="multipart/form-data"
                    x-ref="createForm" @submit.prevent="submitWithCompression('createForm')">
                    @csrf
                    <div class="modal-header">
                        <h3 class="section-title">Rekam Pencapaian</h3>
                    </div>
                    <div class="modal-body space-y-4">
                        <div data-tour="modal-create-section-kelas">
                            <label class="input-label">Filter Kelas</label>
                            <select class="input-field" x-model="selectedKelasIdCreate"
                                @change="selectedAnakId = ''; selectedKegiatanId = ''">
                                <option value=""> Semua Kelas </option>
                                @foreach($availableKelas as $k)
                                    <option value="{{ $k->id }}">{{ $k->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div data-tour="modal-create-section-siswa">
                            <label class="input-label">Target Siswa</label>
                            <select name="anak_id" required class="input-field" x-model="selectedAnakId"
                                @change="selectedKegiatanId = ''">
                                <option value=""> Pilih Siswa </option>
                                <template x-for="a in filteredAnaks" :key="a.id">
                                    <option :value="a.id" x-text="a.display_label"></option>
                                </template>
                            </select>
                            <p class="text-xs mt-1.5" style="color:#6B6560;" x-show="selectedAnakId && anakMap[selectedAnakId]?.nickname">
                                <span class="font-semibold">Nama panggilan:</span>
                                <span x-text="anakMap[selectedAnakId]?.nickname"></span>
                                <span class="text-[10px] italic" style="color:#9E9790;">  dipakai saran AI</span>
                            </p>
                        </div>
                        <div data-tour="modal-create-section-kegiatan" x-show="selectedAnakId">
                            <label class="input-label">Jurnal Kegiatan</label>
                            <select name="kegiatan_id" required class="input-field" x-model="selectedKegiatanId"
                                @change="resetCreateMatrices()">
                                <option value=""> Pilih Kegiatan </option>
                                <template x-for="k in filteredKegiatans" :key="k.id">
                                    <option :value="k.id" x-text="k.date_label + ' : ' + k.title"></option>
                                </template>
                            </select>
                            <p class="text-xs mt-1" style="color:#9E9790;">Hanya menampilkan kegiatan yang sudah
                                memiliki dokumentasi foto (sudah dilaksanakan), belum ada pencapaiannya, dan siswa <strong>hadir</strong> (sudah dipresensi) pada kegiatan tersebut.</p>
                            <template x-if="selectedAnakId && filteredKegiatans.length === 0">
                                <p class="text-xs mt-1 font-semibold" style="color:#C0392B;">Tidak ada kegiatan
                                    tersedia. Pastikan kegiatan sudah diupload foto dokumentasinya, belum diisi
                                    pencapaiannya, dan siswa <strong>hadir</strong> (sudah dipresensi) di tanggal tersebut.</p>
                            </template>
                        </div>
                        <template x-for="(opt, index) in matrikulasiOptions" :key="opt.id">
                            <div class="p-3 bg-gray-50 rounded-xl border border-black/5 space-y-2">
                                <div class="text-xs font-black text-teal-800" x-text="opt.label"></div>
                                <div :data-tour="index === 0 ? 'modal-create-section-skala' : null">
                                    <select class="input-field bg-white" required :name="'nilai[' + opt.id + ']'"
                                        x-model="createNilai[String(opt.id)]">
                                        <option value=""> Skala Capaian </option>
                                        <template x-for="sk in skalaOptions" :key="sk.code">
                                            <option :value="sk.code" x-text="sk.code + ' (' + sk.label + ')'"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="space-y-1.5" :data-tour="index === 0 ? 'modal-create-section-feedback' : null">
                                    <textarea class="input-field bg-white text-xs" rows="3"
                                        :name="'catatan[' + opt.id + ']'" x-model="createCatatan[String(opt.id)]"
                                        placeholder="Berikan umpan balik positif…"></textarea>
                                    {{-- AI Suggestion Button --}}
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <button type="button"
                                            @click="fetchAiSuggestions('create', opt.id, selectedAnakId, selectedKegiatanId, createNilai[String(opt.id)])"
                                            :disabled="aiLoading['create_' + opt.id] || !createNilai[String(opt.id)] || !hasTokens"
                                            class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-2.5 py-1 rounded-lg border transition-all"
                                            style="color:#1A6B6B; background:#D0E8E8; border-color:#1A6B6B33;"
                                            :class="{ 'opacity-40 cursor-not-allowed': !createNilai[String(opt.id)] || !hasTokens }">
                                            <span x-show="!aiLoading['create_' + opt.id]">💡 Saran AI</span>
                                            <span x-show="aiLoading['create_' + opt.id]"
                                                class="flex items-center gap-1">
                                                <svg class="animate-spin h-3 w-3" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                                        stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor"
                                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                    </path>
                                                </svg>
                                                Memuat...
                                            </span>
                                        </button>
                                        <span x-show="!createNilai[String(opt.id)]" class="text-[10px] italic"
                                            style="color:#9E9790;">Pilih capaian dulu</span>
                                    </div>
                                    {{-- AI Suggestion Chips --}}
                                    <template x-if="(aiSuggestions['create_' + opt.id] || []).length > 0">
                                        <div class="space-y-1.5 pt-1">
                                            <div class="text-[10px] font-semibold" style="color:#6B6560;">Pilih salah
                                                satu saran:</div>
                                            <template x-for="(saran, idx) in aiSuggestions['create_' + opt.id]"
                                                :key="idx">
                                                <button type="button" @click="applySuggestion('create', opt.id, saran)"
                                                    class="block w-full text-left text-[11px] px-3 py-2 rounded-lg border hover:border-teal-400 hover:bg-teal-50 transition-all"
                                                    style="background:#FAF6F0; border-color:rgba(0,0,0,0.08); color:#2C2C2C;"
                                                    x-text="(idx + 1) + '. ' + saran">
                                                </button>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                        <div data-tour="modal-create-section-evidence">
                            <label class="input-label">Unggah dokumentasi (foto / video)</label>
                            <x-documentation-upload-progress />
                            <input type="file" name="photo" accept="image/*,video/*" class="input-field py-1"
                                :disabled="docUploadActive" @change="handleFile($event)">
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" @click="showCreateModal=false"
                            class="btn-secondary" :disabled="docUploadActive">Batal</button><button type="submit" data-tour="modal-create-submit" class="btn-primary"
                            :disabled="!selectedKegiatanId || matrikulasiOptions.length === 0 || docUploadActive">Simpan Evaluasi</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- EDIT MODAL --}}
        <div x-show="showEditModal" class="modal-overlay" style="display:none;"
            @click.self="!docUploadActive && (showEditModal=false)">
            <div x-show="showEditModal" x-transition class="modal-box max-w-lg w-full relative" @click.stop>
                <form action="{{ route('admin.pencapaian.sync') }}" method="POST" enctype="multipart/form-data"
                    x-ref="editForm" @submit.prevent="submitWithCompression('editForm')">
                    @csrf
                    <input type="hidden" name="_is_edit" value="1">
                    <input type="hidden" name="anak_id" :value="editBundles[editBundleKey]?.anak_id">
                    <input type="hidden" name="kegiatan_id" :value="editBundles[editBundleKey]?.kegiatan_id">
                    <div class="modal-header">
                        <h3 class="section-title">Ubah Hasil Evaluasi</h3>
                    </div>
                    <div class="modal-body space-y-3">
                        <div class="text-[13px] font-bold text-[#1A6B6B] mt-1 border-b pb-1" data-tour="modal-edit-section-form">Data Evaluasi</div>
                        <div class="rounded-xl border p-3 flex items-start gap-3"
                            style="background:#F0F9F9; border-color:#1A6B6B22;">
                            <div class="h-8 w-8 rounded-lg flex items-center justify-center shrink-0"
                                style="background:#1A6B6B;">
                                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <div class="font-bold text-sm" style="color:#1A6B6B;"
                                    x-text="anakMap[editBundles[editBundleKey]?.anak_id]?.name || 'Siswa'"></div>
                                <div class="text-xs mt-0.5" style="color:#1A6B6B;"
                                    x-show="anakMap[editBundles[editBundleKey]?.anak_id]?.nickname">
                                    Panggilan: <span x-text="anakMap[editBundles[editBundleKey]?.anak_id]?.nickname"></span>
                                </div>
                                <div class="text-xs mt-0.5 truncate" style="color:#6B6560;"
                                    x-text="(kegiatanData[selectedKegiatanIdEdit]?.date_label || '') + (kegiatanData[selectedKegiatanIdEdit]?.title ? ' · ' + kegiatanData[selectedKegiatanIdEdit]?.title : '')">
                                </div>
                            </div>
                        </div>
                        <template x-if="matrikulasiOptionsEdit.length === 0">
                            <div class="text-center py-8 text-sm" style="color:#9E9790;">Memuat data matrikulasi...
                            </div>
                        </template>
                        <template x-for="opt in matrikulasiOptionsEdit" :key="opt.id">
                            <div class="p-3 bg-gray-50 rounded-xl border border-black/5 space-y-2">
                                <div class="text-xs font-black text-teal-800" x-text="opt.label"></div>
                                <select class="input-field bg-white" required :name="'nilai[' + opt.id + ']'"
                                    x-model="editNilai[String(opt.id)]">
                                    <option value="" disabled> Pilih Capaian </option>
                                    <template x-for="sk in skalaOptions" :key="'edit-' + sk.code">
                                        <option :value="sk.code" x-text="sk.code + '  ' + sk.label"></option>
                                    </template>
                                </select>
                                <div class="space-y-1.5">
                                    <textarea class="input-field bg-white text-xs" rows="3"
                                        :name="'catatan[' + opt.id + ']'" x-model="editCatatan[String(opt.id)]"
                                        placeholder="Berikan umpan balik atau evaluasi untuk aspek ini..."></textarea>
                                    {{-- AI Suggestion Button --}}
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <button type="button"
                                            @click="fetchAiSuggestions('edit', opt.id, editBundles[editBundleKey]?.anak_id, editBundles[editBundleKey]?.kegiatan_id, editNilai[String(opt.id)])"
                                            :disabled="aiLoading['edit_' + opt.id] || !editNilai[String(opt.id)] || !hasTokens"
                                            class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-2.5 py-1 rounded-lg border transition-all"
                                            style="color:#1A6B6B; background:#D0E8E8; border-color:#1A6B6B33;"
                                            :class="{ 'opacity-40 cursor-not-allowed': !editNilai[String(opt.id)] || !hasTokens }">
                                            <span x-show="!aiLoading['edit_' + opt.id]">💡 Saran AI</span>
                                            <span x-show="aiLoading['edit_' + opt.id]" class="flex items-center gap-1">
                                                <svg class="animate-spin h-3 w-3" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                                        stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor"
                                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                    </path>
                                                </svg>
                                                Memuat...
                                            </span>
                                        </button>
                                        <span x-show="!editNilai[String(opt.id)]" class="text-[10px] italic"
                                            style="color:#9E9790;">Pilih capaian dulu</span>
                                    </div>
                                    {{-- AI Suggestion Chips --}}
                                    <template x-if="(aiSuggestions['edit_' + opt.id] || []).length > 0">
                                        <div class="space-y-1.5 pt-1">
                                            <div class="text-[10px] font-semibold" style="color:#6B6560;">Pilih salah
                                                satu saran:</div>
                                            <template x-for="(saran, idx) in aiSuggestions['edit_' + opt.id]"
                                                :key="idx">
                                                <button type="button" @click="applySuggestion('edit', opt.id, saran)"
                                                    class="block w-full text-left text-[11px] px-3 py-2 rounded-lg border hover:border-teal-400 hover:bg-teal-50 transition-all"
                                                    style="background:#FAF6F0; border-color:rgba(0,0,0,0.08); color:#2C2C2C;"
                                                    x-text="(idx + 1) + '. ' + saran">
                                                </button>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                        <div class="pt-2">
                            <label class="input-label">Dokumentasi (Evidence)</label>
                            <template x-if="editBundles[editBundleKey]?.has_photo">
                                <div class="mb-4">
                                    <div class="text-[11px] mb-2 flex items-center justify-between gap-2" style="color:#1A6B6B;">
                                        <div class="flex items-center gap-1.5">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <span class="font-bold">Foto Hasil Sebelumnya:</span>
                                        </div>
                                        <a :href="editBundles[editBundleKey]?.photo_download_url"
                                            class="text-[10px] font-bold uppercase tracking-wide underline hover:no-underline"
                                            x-show="editBundles[editBundleKey]?.photo_download_url">Unduh</a>
                                    </div>
                                    <div class="relative w-32 h-32 rounded-xl overflow-hidden border-2 shadow-sm group cursor-pointer bg-stone-200"
                                        style="border-color:#1A6B6B22;"
                                        @click="openMedia(editBundles[editBundleKey].photo_url, editBundles[editBundleKey].photo_download_url)">
                                        <template x-if="showEditModal && editBundles[editBundleKey]?.is_video">
                                            <video
                                                :src="(editBundles[editBundleKey].photo_url || '') + '#t=0.1'"
                                                class="w-full h-full object-cover bg-stone-300"
                                                muted playsinline preload="metadata"
                                                @loadedmetadata="window.seekDocumentationVideoPreview($event.target)"></video>
                                        </template>
                                        <template x-if="!editBundles[editBundleKey]?.is_video">
                                            <img
                                                :src="editBundles[editBundleKey].photo_url"
                                                class="w-full h-full object-cover"
                                                alt="">
                                        </template>
                                        <span x-show="editBundles[editBundleKey]?.is_video"
                                            class="absolute bottom-1 right-1 flex h-6 w-6 items-center justify-center rounded bg-black/55 text-white text-[10px] pointer-events-none">▶</span>
                                    </div>
                                    <p class="text-[10px] mt-2 italic" style="color:#9E9790;">Pilih file baru di bawah
                                        ini jika ingin mengganti foto.</p>
                                </div>
                            </template>
                            <x-documentation-upload-progress />
                            <input type="file" name="photo" accept="image/*,video/*" class="input-field"
                                :disabled="docUploadActive" @change="handleFile($event)">
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" @click="showEditModal=false"
                            class="btn-secondary" :disabled="docUploadActive">Batal</button><button type="submit" data-tour="modal-edit-submit"
                            class="btn-primary" :disabled="docUploadActive">Update</button></div>
                </form>
            </div>
        </div>

        {{-- DELETE MODAL --}}
        <div x-show="showDeleteBundleModal" data-tour="modal-delete" class="modal-overlay" style="display:none;">
            <div x-show="showDeleteBundleModal" x-transition class="modal-box max-w-sm"
                @click.away="showDeleteBundleModal=false">
                <form method="POST" action="{{ route('admin.pencapaian.destroy-bundle') }}">
                    @csrf @method('DELETE')
                    <input type="hidden" name="anak_id" :value="deleteBundleAnak">
                    <input type="hidden" name="kegiatan_id" :value="deleteBundleKeg">
                    <div class="modal-body text-center py-6">
                        <div
                            class="h-14 w-14 bg-red-100 text-red-600 rounded-2xl mx-auto flex items-center justify-center mb-4">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold">Hapus Evaluasi Ini?</h3>
                    </div>
                    <div class="modal-footer">
                        <button type="button" @click="showDeleteBundleModal=false" class="btn-secondary">Batal</button>
                        <button type="submit" class="btn-danger">Hapus Permanen</button>
                    </div>
                </form>
            </div>
        </div>

        <x-image-lightbox />
    </div>
</x-app-layout>