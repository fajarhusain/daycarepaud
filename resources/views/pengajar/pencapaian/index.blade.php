<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background:#1A6B6B;"><svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
            <h2 class="font-bold text-xl" style="color:#2C2C2C;">Evaluasi Pencapaian Siswa</h2>
        </div>
    </x-slot>

    @php
        $photoDownloadRoute = auth()->user()->hasRole('Wali Kelas')
            ? 'adminkelas.pencapaian.photos.download'
            : 'pengajar.pencapaian.photos.download';
        $bundlePhotoDownloadRoute = auth()->user()->hasRole('Wali Kelas')
            ? 'adminkelas.pencapaian.photos.download-bundle'
            : 'pengajar.pencapaian.photos.download-bundle';
        $sekolahId = (int) ($skalas->first()?->sekolah_id ?? auth()->user()->sekolah_id ?? 0);
        $skalaOptions = $skalas->map(fn ($s) => ['code' => $s->code, 'label' => $s->label])->values()->all();
        $filterAspek = $filterAspek ?? null;
        // Kumpulkan anak_id yang sudah punya pencapaian per kegiatan
        $pencapaianAnak = \App\Models\Pencapaian::query()
            ->whereIn('kegiatan_id', $kegiatans->pluck('id'))
            ->select('kegiatan_id', 'anak_id')
            ->distinct()
            ->get()
            ->groupBy('kegiatan_id')
            ->map(fn ($rows) => $rows->pluck('anak_id')->values()->all());

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
                'id'                  => $kg->id,
                'kelas_id'            => $kg->kelas_id,
                'title'               => $kg->title,
                'date_label'          => \Carbon\Carbon::parse($kg->date)->format('d M Y'),
                'is_executed'         => !empty($kg->photos),
                'pencapaian_anak_ids' => $pencapaianAnak[$kg->id] ?? [],
                'present_anak_ids'     => $kehadiran[\Carbon\Carbon::parse($kg->date)->toDateString()] ?? [],
                'matrikulasis'        => $kg->matrikulasis->map(fn ($m) => [
                    'id'        => $m->id,
                    'aspek'     => $m->aspek,
                    'indicator' => $m->indicator,
                    'label'     => ($m->aspek ? $m->aspek.': ' : '').$m->indicator,
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
        $kelasOptions = $availableKelas
            ->map(fn ($kelas) => ['id' => $kelas->id, 'name' => $kelas->name])
            ->values()
            ->all();
        $flags = JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
        $payloadJson = json_encode(['kegiatanData' => $kegiatanData, 'anakMap' => $anakMap, 'editBundles' => $editBundles, 'kelasOptions' => $kelasOptions], $flags);
        if ($payloadJson === false) { $payloadJson = '{}'; }
    @endphp

    <div class="app-page-shell"
         x-data="{
            hasTokens: @js($hasTokens ?? false),
            tokenFallbackPencapaian: @js($tokenFallbackPencapaian ?? 'Maaf, fitur ini sedang terbatas.'),
            skalaOptions: @js($skalaOptions),
            showCreateModal: false, showEditModal: false, showDeleteBundleModal: false,
            showImageModal: false, activeImage: null, activeDownloadUrl: null, activeMediaType: 'image',
            deleteBundleAnak: '', deleteBundleKeg: '', payload: {},
            selectedKelasId: '', selectedAnakId: '', selectedKegiatanId: '', selectedKegiatanIdEdit: '',
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
                const nilaiMap = mode === 'create' ? this.createNilai : this.editNilai;
                const resolvedScore = score || nilaiMap[String(optId)] || '';
                if (!resolvedScore) { alert('Pilih skala capaian terlebih dahulu sebelum meminta saran AI.'); return; }
                const key = mode + '_' + optId;
                this.aiSuggestions = {};
                this.aiLoading = { ...this.aiLoading, [key]: true };
                try {
                    const res = await fetch(@js(route('pengajar.ai.feedback-suggestions')), {
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
                this.aiSuggestions = { ...this.aiSuggestions, [mode + '_' + optId]: [] };
            },
            get kegiatanData() { return this.payload.kegiatanData || {}; },
            get anakMap() { return this.payload.anakMap || {}; },
            get editBundles() { return this.payload.editBundles || {}; },
            get kelasOptions() { return this.payload.kelasOptions || []; },
            get filteredAnaks() {
                const items = Object.values(this.anakMap);
                if (this.kelasOptions.length <= 1) return items;
                if (!this.selectedKelasId) return [];
                return items.filter(anak => String(anak.kelas_id) === String(this.selectedKelasId));
            },
            get filteredKegiatans() {
                if (!this.selectedAnakId) return [];
                const anakKelas = this.anakMap[this.selectedAnakId]?.kelas_id;
                const anakId = parseInt(this.selectedAnakId);
                return Object.values(this.kegiatanData).filter(k =>
                    k.kelas_id == anakKelas &&
                    k.is_executed === true &&
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
                this.selectedKelasId = this.kelasOptions.length === 1 ? String(this.kelasOptions[0].id) : '';
                this.selectedAnakId = ''; this.selectedKegiatanId = '';
                this.createNilai = {}; this.createCatatan = {}; this.aiSuggestions = {};
                this.showCreateModal = true;
            },
            openEditBundle(key) {
                const b = this.editBundles[key]; if (!b) return;
                this.editBundleKey = key;
                this.selectedKegiatanIdEdit = String(b.kegiatan_id);
                this.editNilai = {}; this.editCatatan = {}; this.aiSuggestions = {};
                this.showEditModal = true;
                this.$nextTick(() => {
                    const tempNilai = {}; const tempCatatan = {};
                    const opts = (this.kegiatanData[this.selectedKegiatanIdEdit] || {}).matrikulasis || [];
                    opts.forEach(opt => {
                        const id = String(opt.id);
                        tempNilai[id] = b.nilai[id] ?? b.nilai[opt.id] ?? '';
                        tempCatatan[id] = b.catatan[id] ?? b.catatan[opt.id] ?? '';
                    });
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

        @if(session('success'))<div class="alert-success mb-5"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>{{ session('success') }}</div>@endif
        @if(session('warning'))<div class="alert-danger mb-5">{{ session('warning') }}</div>@endif
        @if($errors->any())
            <div class="alert-danger mb-5">
                <ul class="list-disc pl-5 text-sm">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        @if(!($hasTokens ?? false))
            <div class="mb-5 px-5 py-3 text-sm rounded-xl border" style="background:#FEE2E2; color:#DC2626; border-color: rgba(0,0,0,0.06);">
                {{ $tokenFallbackPencapaian ?? 'Maaf, fitur ini sedang terbatas.' }}
            </div>
        @endif

        {{-- Filter Card --}}
        <div class="card mb-6">
            <div class="px-6 py-6 border-b" style="background:#FAF6F0; border-color: rgba(0,0,0,0.06);">
                <div class="space-y-6">
                    <div class="space-y-1">
                        <h3 class="text-xl font-bold" style="color:#2C2C2C;">Filter Evaluasi</h3>
                        <p class="text-sm font-medium" style="color:#9E9790;">Cari data berdasarkan rentang tanggal, kelas, dan indikator aspek</p>
                    </div>
                    <form data-tour="pg-pencapaian-filter" method="get" action="{{ route('pengajar.pencapaian.index') }}" class="filter-toolbar-inline">
                        <div class="filter-toolbar-field">
                            <label class="text-[11px] font-bold uppercase tracking-wider mb-1.5 block" style="color:#1A6B6B;">Dari</label>
                            <input type="date" name="tanggal_dari" value="{{ $tanggalDari }}" class="input-field w-full h-9 md:h-11 text-xs font-bold border-black/10" style="background:white;">
                        </div>
                        <div class="filter-toolbar-field">
                            <label class="text-[11px] font-bold uppercase tracking-wider mb-1.5 block" style="color:#1A6B6B;">Sampai</label>
                            <input type="date" name="tanggal_sampai" value="{{ $tanggalSampai }}" class="input-field w-full h-9 md:h-11 text-xs font-bold border-black/10" style="background:white;">
                        </div>
                        <div class="filter-toolbar-field">
                            <label class="text-[11px] font-bold uppercase tracking-wider mb-1.5 block" style="color:#1A6B6B;">Kelas</label>
                            <select name="filter_kelas_id" class="input-field w-full h-9 md:h-11 text-xs font-bold border-black/10" style="background:white;">
                                <option value="">Semua Kelas</option>
                                @foreach($availableKelas as $k)<option value="{{ $k->id }}" @selected($filterKelasId === (int) $k->id)>{{ $k->name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="filter-toolbar-field">
                            <label class="text-[11px] font-bold uppercase tracking-wider mb-1.5 block" style="color:#1A6B6B;">Anak</label>
                            <select name="filter_anak_id" class="input-field w-full h-9 md:h-11 text-xs font-bold border-black/10" style="background:white;">
                                <option value="">Semua Anak</option>
                                @foreach($anaks as $a)<option value="{{ $a->id }}" @selected($filterAnakId === (int) $a->id)>{{ $a->name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="filter-toolbar-field">
                            <label class="text-[11px] font-bold uppercase tracking-wider mb-1.5 block" style="color:#1A6B6B;">Aspek</label>
                            <select name="aspek" class="input-field w-full h-9 md:h-11 text-xs font-bold border-black/10" style="background:white;">
                                <option value="">Semua Aspek</option>
                                <option value="{{ \App\Support\FilterAspekPencapaian::UMUM }}" @selected($filterAspekRaw === \App\Support\FilterAspekPencapaian::UMUM)>Umum / Tanpa Aspek</option>
                                @foreach($aspekPilihan as $asp)<option value="{{ $asp }}" @selected($filterAspekRaw === $asp)>{{ $asp }}</option>@endforeach
                            </select>
                        </div>
                        <div class="filter-toolbar-actions">
                            <button type="submit" class="btn-primary h-9 w-9 md:h-11 md:w-11 p-0 shrink-0" title="Cari Data" aria-label="Cari Data">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            </button>
                            <x-filter-reset :href="route('pengajar.pencapaian.index')" compact />
                            <x-download-photos :route="$photoDownloadRoute" :icon-only="true" />
                        </div>
                    </form>
                </div>
            </div>
            <div class="px-5 sm:px-6 py-3 border-t text-sm flex flex-wrap items-center gap-x-2 gap-y-1" style="background:#FAF6F0; border-color:rgba(0,0,0,0.04); color:#6B6560;">
                <span class="inline-flex items-center gap-1.5 shrink-0"><svg class="h-4 w-4 shrink-0 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>Ringkasan:</span></span>
                <span style="color:#2C2C2C;">
                    @if(!$tanggalDari && !$tanggalSampai)
                        Seluruh Periode
                    @elseif($tanggalDari === $tanggalSampai)
                        {{ \Carbon\Carbon::parse($tanggalDari)->translatedFormat('d M Y') }}
                    @else
                        {{ \Carbon\Carbon::parse($tanggalDari)->translatedFormat('d M Y') }} – {{ \Carbon\Carbon::parse($tanggalSampai)->translatedFormat('d M Y') }}
                    @endif
                </span>
                @if($filterKelasId)<span class="text-black/25 hidden sm:inline">·</span><span>Kelas: {{ $availableKelas->firstWhere('id', $filterKelasId)?->name ?? 'Terpilih' }}</span>@endif
                @if($filterAnakId)<span class="text-black/25 hidden sm:inline">·</span><span>Anak: {{ $anaks->firstWhere('id', $filterAnakId)?->name ?? 'Terpilih' }}</span>@endif
                @if($filterAspekRaw !== '')<span class="text-black/25 hidden sm:inline">·</span><span>{{ $filterAspekRaw === \App\Support\FilterAspekPencapaian::UMUM ? 'Umum / tanpa aspek' : $filterAspekRaw }}</span>@endif
            </div>
        </div>

        {{-- Data Table --}}
        <div class="card overflow-hidden">
            <div class="px-6 py-4 flex items-center justify-between border-b" style="border-color:rgba(0,0,0,0.06);">
                <div><h3 class="section-title">Laporan per Kegiatan & Aspek</h3><p class="section-subtitle">Satu baris = satu anak + satu kegiatan; nilai per indikator matrikulasi.</p></div>
                <button type="button" data-tour="pg-pencapaian-add-btn" data-tour-open-modal="create" @click="openCreateModal()" class="btn-primary shrink-0 h-9 w-9 md:h-11 md:w-11 p-0" title="Buat Evaluasi" aria-label="Buat Evaluasi"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg></button>
            </div>

            {{-- Mobile Card View --}}
            <div class="block lg:hidden pb-4">
                <div class="grid grid-cols-1 gap-4 px-4 pt-4">
                    @forelse($groupedPencapaian as $bundleKey => $rows)
                        @php $first = $rows->first(); @endphp
                        <div class="relative rounded-2xl bg-white border border-black/5 shadow-sm p-4 hover:shadow-md transition">
                            <div class="flex items-start gap-3 mb-4">
                                @if($first->photo)
                                    @php $mediaUrl = \App\Support\DocumentationMedia::publicAssetPath($first->photo); @endphp
                                    <x-documentation-thumbnail
                                        :path="$first->photo"
                                        class="h-16 w-16 rounded-xl shadow-sm cursor-pointer"
                                        @click="openMedia('{{ $mediaUrl }}', '{{ route($bundlePhotoDownloadRoute, ['anak_id' => $first->anak_id, 'kegiatan_id' => $first->kegiatan_id]) }}')"
                                    />
                                @else
                                    <div class="h-16 w-16 bg-gray-50 rounded-xl flex items-center justify-center shrink-0 border border-black/5"><svg class="h-6 w-6 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <x-foto-profil :path="$first->anak->photo ?? null" :name="$first->anak->name ?? '?'" size="xs" />
                                        <h4 class="font-bold text-[#2C2C2C] truncate text-sm">{{ $first->anak->name ?? '-' }}</h4>
                                    </div>
                                    <p class="text-[11px] font-bold text-teal-600 mb-1 leading-tight">{{ $first->kegiatan->title ?? '-' }}</p>
                                    <p class="text-[10px] text-gray-400 font-medium">{{ \Carbon\Carbon::parse($first->created_at)->translatedFormat('d M Y') }}</p>
                                </div>
                            </div>
                            <div class="space-y-2 mb-4 bg-gray-50/50 rounded-xl p-3 border border-black/[0.03]">
                                @foreach($rows->filter(fn ($p) => \App\Support\FilterAspekPencapaian::rowMatches($filterAspek, $p))->sortBy(fn ($p) => ($p->matrikulasi->aspek ?? '').($p->matrikulasi->indicator ?? '')) as $p)
                                    <div class="pb-2 border-b border-black/[0.05] last:border-0 last:pb-0">
                                        <div class="flex justify-between items-start gap-2 mb-1">
                                            <span class="text-[10px] font-extrabold text-teal-800 uppercase tracking-tight">{{ $p->matrikulasi->aspek ?: 'Umum' }}</span>
                                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded" style="background:{{ \App\Support\LabelSkorPencapaian::color($p->score, $sekolahId) }};">{{ \App\Support\LabelSkorPencapaian::label($p->score, $sekolahId) }}</span>
                                        </div>
                                        <p class="text-[11px] text-gray-600 leading-tight mb-1">{{ $p->matrikulasi->indicator ?? 'Data indikator lama' }}</p>
                                        @if($p->feedback)<p class="text-[10px] text-gray-400 italic bg-white/50 px-2 py-1 rounded border border-black/5 mt-1">"{{ $p->feedback }}"</p>@endif
                                    </div>
                                @endforeach
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="openEditBundle('{{ $bundleKey }}')" class="flex-1 py-2.5 rounded-xl bg-teal-50 text-teal-700 text-xs font-bold text-center hover:bg-teal-100 transition">Edit Evaluasi</button>
                                <button type="button" @click="openDeleteBundle('{{ $bundleKey }}')" class="h-10 w-10 flex items-center justify-center rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 transition border border-rose-100/50"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center text-gray-400 text-sm">Belum ada evaluasi pada rentang tanggal ini.</div>
                    @endforelse
                </div>
            </div>

            {{-- Desktop Table View --}}
            <div class="hidden lg:block overflow-x-auto">
                <table class="data-table">
                    <thead><tr><th class="w-28 min-w-[7rem]">Bukti</th><th>Anak</th><th>Kegiatan</th><th>Aspek &amp; Nilai</th><th>Tanggal</th><th class="text-right">Aksi</th></tr></thead>
                    <tbody>
                        @forelse($groupedPencapaian as $bundleKey => $rows)
                            @php $first = $rows->first(); @endphp
                            <tr>
                                <td class="w-28 min-w-[7rem] align-middle">
                                    @if($first->photo)
                                        @php $mediaUrl = \App\Support\DocumentationMedia::publicAssetPath($first->photo); @endphp
                                        <div class="flex items-center gap-2">
                                            <div class="relative group cursor-pointer shrink-0" @click="openMedia('{{ $mediaUrl }}', '{{ route($bundlePhotoDownloadRoute, ['anak_id' => $first->anak_id, 'kegiatan_id' => $first->kegiatan_id]) }}')">
                                                <x-documentation-thumbnail
                                                    :path="$first->photo"
                                                    class="h-14 w-14 rounded-lg"
                                                />
                                                <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center rounded pointer-events-none"><svg class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg></div>
                                            </div>
                                            <a href="{{ route($bundlePhotoDownloadRoute, ['anak_id' => $first->anak_id, 'kegiatan_id' => $first->kegiatan_id]) }}"
                                                class="text-[10px] font-bold uppercase tracking-wide text-teal-700 hover:underline"
                                                title="Unduh foto">Unduh</a>
                                        </div>
                                    @else
                                        <div class="h-10 w-10 bg-gray-100 rounded flex items-center justify-center"><svg class="h-5 w-5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <x-foto-profil :path="$first->anak->photo ?? null" :name="$first->anak->name ?? '?'" size="sm" />
                                        <div>
                                            <span class="font-semibold block" style="color:#2C2C2C;">{{ $first->anak->name ?? '-' }}</span>
                                            @if($first->anak && $first->anak->dob)<span class="text-[10px] font-bold text-[#1A6B6B]">{{ $first->anak->age }}</span>@endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($first->kegiatan)<div class="font-medium text-sm" style="color:#2C2C2C;">{{ $first->kegiatan->title }}</div><div class="text-xs" style="color:#9E9790;">{{ \Carbon\Carbon::parse($first->kegiatan->date)->format('d M Y') }}</div>
                                    @else@endif
                                </td>
                                <td class="min-w-[240px]">
                                    <div class="space-y-2">
                                        @foreach($rows->filter(fn ($p) => \App\Support\FilterAspekPencapaian::rowMatches($filterAspek, $p))->sortBy(fn ($p) => ($p->matrikulasi->aspek ?? '').($p->matrikulasi->indicator ?? '')) as $p)
                                            <div class="text-xs rounded-lg px-2 py-1.5 border" style="border-color:rgba(0,0,0,0.06);background:#FAF6F0;">
                                                @if($p->matrikulasi)
                                                    <div class="font-semibold" style="color:#1A6B6B;">{{ $p->matrikulasi->aspek ?: 'Aspek' }}</div>
                                                    <div class="text-[11px] mt-0.5" style="color:#5A5A5A;">{{ $p->matrikulasi->indicator }}</div>
                                                @else<div class="font-semibold" style="color:#9E9790;">Data lama (tanpa aspek)</div>@endif
                                                <div class="mt-1 flex flex-wrap items-center gap-2">
                                                    <span class="text-xs font-bold px-2 py-0.5 rounded leading-snug" style="background:{{ \App\Support\LabelSkorPencapaian::color($p->score, $sekolahId) }};">{{ \App\Support\LabelSkorPencapaian::label($p->score, $sekolahId) }}</span>
                                                    @if($p->feedback)<span class="text-[11px] italic truncate max-w-[200px]" style="color:#6B6560;" title="{{ $p->feedback }}">"{{ Str::limit($p->feedback, 40) }}"</span>@endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="whitespace-nowrap text-sm">{{ \Carbon\Carbon::parse($first->created_at)->format('d M Y') }}</td>
                                <td class="text-right">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        <button type="button" @if($loop->first) data-tour="pg-pencapaian-action-edit" data-tour-open-modal="edit" @endif @click="openEditBundle('{{ $bundleKey }}')" class="row-action-icon" style="color:#1A6B6B;background:#D0E8E8;" title="Edit" aria-label="Edit"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                                        <button type="button" @if($loop->first) data-tour="pg-pencapaian-action-delete" data-tour-demo-action="delete" @endif @click="openDeleteBundle('{{ $bundleKey }}')" class="row-action-icon" style="color:#C0392B;background:#FAD7D2;" title="Hapus" aria-label="Hapus"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-12 text-center" style="color:#9E9790;">Belum ada evaluasi pada rentang tanggal ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t" style="border-color:rgba(0,0,0,0.06);">
                <x-per-page-selector :paginator="$groupedPencapaian" />
                {{ $groupedPencapaian->links() }}
            </div>
        </div>

        {{-- CREATE MODAL --}}
        <div x-show="showCreateModal" class="modal-overlay" style="display:none;"
            @click.self="!docUploadActive && (showCreateModal=false)">
            <div x-show="showCreateModal" x-transition class="modal-box max-w-lg w-full relative" @click.stop>
                <form action="{{ route('pengajar.pencapaian.sync') }}" method="POST" enctype="multipart/form-data" x-ref="createForm" @submit.prevent="submitWithCompression('createForm')">
                    @csrf
                    <input type="hidden" name="tanggal_dari" value="{{ $tanggalDari }}">
                    <input type="hidden" name="tanggal_sampai" value="{{ $tanggalSampai }}">
                    @if($filterAnakId)<input type="hidden" name="filter_anak_id" value="{{ $filterAnakId }}">@endif
                    @if($filterAspekRaw !== '')<input type="hidden" name="aspek" value="{{ $filterAspekRaw }}">@endif
                    <div class="modal-header"><h3 class="section-title">Rekam Pencapaian</h3></div>
                    <div class="modal-body space-y-4">
                        <div x-show="kelasOptions.length > 1">
                            <label class="input-label">Kelas <span class="text-red-500">*</span></label>
                            <select class="input-field" x-model="selectedKelasId" @change="selectedAnakId = ''; selectedKegiatanId = ''; resetCreateMatrices()">
                                <option value=""> Pilih Kelas </option>
                                <template x-for="kelas in kelasOptions" :key="kelas.id">
                                    <option :value="kelas.id" x-text="kelas.name"></option>
                                </template>
                            </select>
                            <p class="text-xs mt-1.5" style="color:#6B6560;">Pilih kelas dulu untuk mempermudah penyaringan siswa saat guru mengampu lebih dari satu kelas.</p>
                        </div>
                        <div data-tour="modal-create-section-siswa">
                            <label class="input-label">Target Siswa <span class="text-red-500">*</span></label>
                            <select name="anak_id" required class="input-field" x-model="selectedAnakId" @change="selectedKegiatanId = ''" :disabled="kelasOptions.length > 1 && !selectedKelasId">
                                <option value=""> Pilih Siswa </option>
                                <template x-for="anak in filteredAnaks" :key="anak.id">
                                    <option :value="anak.id" x-text="anak.display_label"></option>
                                </template>
                            </select>
                            <p class="text-xs mt-1.5" style="color:#9E9790;" x-show="kelasOptions.length > 1 && !selectedKelasId">Pilih kelas terlebih dahulu.</p>
                            <p class="text-xs mt-1.5" style="color:#6B6560;" x-show="selectedAnakId && anakMap[selectedAnakId]?.nickname">
                                <span class="font-semibold">Nama panggilan:</span>
                                <span x-text="anakMap[selectedAnakId]?.nickname"></span>
                                <span class="text-[10px] italic" style="color:#9E9790;">  dipakai saran AI</span>
                            </p>
                        </div>
                        <div data-tour="modal-create-section-kegiatan" x-show="selectedAnakId">
                            <label class="input-label">Jurnal Kegiatan <span class="text-red-500">*</span></label>
                            <select name="kegiatan_id" required class="input-field" x-model="selectedKegiatanId" @change="resetCreateMatrices()">
                                <option value=""> Pilih </option>
                                <template x-for="k in filteredKegiatans" :key="k.id">
                                    <option :value="k.id" x-text="k.date_label + '  ' + k.title"></option>
                                </template>
                            </select>
                            <p class="text-xs mt-1" style="color:#9E9790;">Hanya menampilkan kegiatan yang sudah memiliki dokumentasi foto (sudah dilaksanakan), belum ada pencapaiannya, dan siswa <strong>hadir</strong> (sudah dipresensi) pada kegiatan tersebut.</p>
                            <template x-if="selectedAnakId && filteredKegiatans.length === 0">
                                <p class="text-xs mt-1 font-semibold" style="color:#C0392B;">Tidak ada kegiatan tersedia. Pastikan kegiatan sudah diupload foto dokumentasinya, belum diisi pencapaiannya, dan siswa <strong>hadir</strong> (sudah dipresensi) di tanggal kegiatan tersebut.</p>
                            </template>
                        </div>
                        <p class="text-sm" x-show="selectedKegiatanId && matrikulasiOptions.length === 0" style="display:none;color:#C0392B;">Kegiatan ini belum punya matrikulasi. Ubah di Jurnal Kegiatan.</p>
                        <template x-for="(opt, index) in matrikulasiOptions" :key="opt.id">
                            <div class="p-3 bg-gray-50 rounded-xl border border-black/5 space-y-2">
                                <div class="text-xs font-black text-teal-800" x-text="opt.label"></div>
                                <div :data-tour="index === 0 ? 'modal-create-section-skala' : null">
                                    <select class="input-field bg-white" required :name="'nilai[' + opt.id + ']'" x-model="createNilai[String(opt.id)]">
                                        <option value=""> Skala Capaian </option>
                                        <template x-for="sk in skalaOptions" :key="sk.code">
                                            <option :value="sk.code" x-text="sk.code + '  ' + sk.label"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="space-y-1.5" :data-tour="index === 0 ? 'modal-create-section-feedback' : null">
                                    <textarea class="input-field bg-white text-xs" rows="3" :name="'catatan[' + opt.id + ']'"
                                        x-model="createCatatan[String(opt.id)]"
                                        placeholder="Berikan umpan balik positif…"></textarea>
                                    {{-- AI Button --}}
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <button type="button"
                                            @click="fetchAiSuggestions('create', opt.id, selectedAnakId, selectedKegiatanId, createNilai[String(opt.id)])"
                                            :disabled="aiLoading['create_' + opt.id] || !createNilai[String(opt.id)] || !hasTokens"
                                            class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-2.5 py-1 rounded-lg border transition-all"
                                            style="color:#1A6B6B; background:#D0E8E8; border-color:#1A6B6B33;"
                                            :class="{ 'opacity-40 cursor-not-allowed': !createNilai[String(opt.id)] || !hasTokens }">
                                            <span x-show="!aiLoading['create_' + opt.id]">💡 Saran AI</span>
                                            <span x-show="aiLoading['create_' + opt.id]" class="flex items-center gap-1">
                                                <svg class="animate-spin h-3 w-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                Memuat...
                                            </span>
                                        </button>
                                        <span x-show="!createNilai[String(opt.id)]" class="text-[10px] italic" style="color:#9E9790;">Pilih capaian dulu</span>
                                    </div>
                                    {{-- AI Chips --}}
                                    <template x-if="(aiSuggestions['create_' + opt.id] || []).length > 0">
                                        <div class="space-y-1.5 pt-1">
                                            <div class="text-[10px] font-semibold" style="color:#6B6560;">Pilih salah satu saran:</div>
                                            <template x-for="(saran, idx) in aiSuggestions['create_' + opt.id]" :key="idx">
                                                <button type="button" @click="applySuggestion('create', opt.id, saran)"
                                                    class="block w-full text-left text-[11px] px-3 py-2 rounded-lg border hover:border-teal-400 hover:bg-teal-50 transition-all"
                                                    style="background:#FAF6F0; border-color:rgba(0,0,0,0.08); color:#2C2C2C;"
                                                    x-text="(idx + 1) + '. ' + saran"></button>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                        <div data-tour="modal-create-section-evidence">
                            <label class="input-label">Foto / video bukti (opsional)</label>
                            <x-documentation-upload-progress />
                            <input type="file" name="photo" accept="image/*,video/*" class="input-field py-1.5 text-xs @error('photo') border-red-500 @enderror" :disabled="docUploadActive" @change="handleFile($event)">
                            @error('photo')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" @click="showCreateModal=false" class="btn-secondary" :disabled="docUploadActive">Batal</button><button type="submit" data-tour="modal-create-submit" class="btn-primary" :disabled="!selectedKegiatanId || matrikulasiOptions.length === 0 || docUploadActive">Simpan Evaluasi</button></div>
                </form>
            </div>
        </div>

        {{-- EDIT MODAL --}}
        <div x-show="showEditModal" class="modal-overlay" style="display:none;"
            @click.self="!docUploadActive && (showEditModal=false)">
            <div x-show="showEditModal" x-transition class="modal-box max-w-lg w-full relative" @click.stop>
                <form action="{{ route('pengajar.pencapaian.sync') }}" method="POST" enctype="multipart/form-data" x-ref="editForm" @submit.prevent="submitWithCompression('editForm')">
                    <input type="hidden" name="_is_edit" value="1">
                    @csrf
                    <input type="hidden" name="tanggal_dari" value="{{ $tanggalDari }}">
                    <input type="hidden" name="tanggal_sampai" value="{{ $tanggalSampai }}">
                    @if($filterAnakId)<input type="hidden" name="filter_anak_id" value="{{ $filterAnakId }}">@endif
                    @if($filterAspekRaw !== '')<input type="hidden" name="aspek" value="{{ $filterAspekRaw }}">@endif
                    <input type="hidden" name="anak_id" :value="editBundles[editBundleKey]?.anak_id">
                    <input type="hidden" name="kegiatan_id" :value="editBundles[editBundleKey]?.kegiatan_id">
                    <div class="modal-header"><h3 class="section-title">Ubah Hasil Evaluasi</h3></div>
                    <div class="modal-body space-y-3">
                        <div class="text-[13px] font-bold text-[#1A6B6B] mt-1 border-b pb-1" data-tour="modal-edit-section-form">Data Evaluasi</div>
                        <div class="rounded-xl border p-3 flex items-start gap-3" style="background:#F0F9F9; border-color:#1A6B6B22;">
                            <div class="h-8 w-8 rounded-lg flex items-center justify-center shrink-0" style="background:#1A6B6B;">
                                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            </div>
                            <div class="min-w-0">
                                <div class="font-bold text-sm" style="color:#1A6B6B;" x-text="anakMap[editBundles[editBundleKey]?.anak_id]?.name || 'Siswa'"></div>
                                <div class="text-xs mt-0.5" style="color:#1A6B6B;"
                                    x-show="anakMap[editBundles[editBundleKey]?.anak_id]?.nickname">
                                    Panggilan: <span x-text="anakMap[editBundles[editBundleKey]?.anak_id]?.nickname"></span>
                                </div>
                                <div class="text-xs mt-0.5 truncate" style="color:#6B6560;" x-text="(kegiatanData[selectedKegiatanIdEdit]?.date_label || '') + (kegiatanData[selectedKegiatanIdEdit]?.title ? ' · ' + kegiatanData[selectedKegiatanIdEdit]?.title : '')"></div>
                            </div>
                        </div>
                        <template x-if="matrikulasiOptionsEdit.length === 0">
                            <div class="text-center py-8 text-sm" style="color:#9E9790;">Memuat data matrikulasi...</div>
                        </template>
                        <template x-for="opt in matrikulasiOptionsEdit" :key="opt.id">
                            <div class="p-3 bg-gray-50 rounded-xl border border-black/5 space-y-2">
                                <div class="text-xs font-black text-teal-800" x-text="opt.label"></div>
                                <select class="input-field bg-white" required :name="'nilai[' + opt.id + ']'" x-model="editNilai[String(opt.id)]">
                                    <option value="" disabled> Pilih Capaian </option>
                                    <template x-for="sk in skalaOptions" :key="'edit-' + sk.code">
                                        <option :value="sk.code" x-text="sk.code + '  ' + sk.label"></option>
                                    </template>
                                </select>
                                <div class="space-y-1.5">
                                    <textarea class="input-field bg-white text-xs" rows="3"
                                        :name="'catatan[' + opt.id + ']'"
                                        x-model="editCatatan[String(opt.id)]"
                                        placeholder="Berikan umpan balik atau evaluasi untuk aspek ini..."></textarea>
                                    {{-- AI Button --}}
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <button type="button"
                                            @click="fetchAiSuggestions('edit', opt.id, editBundles[editBundleKey]?.anak_id, editBundles[editBundleKey]?.kegiatan_id, editNilai[String(opt.id)])"
                                            :disabled="aiLoading['edit_' + opt.id] || !editNilai[String(opt.id)] || !hasTokens"
                                            class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-2.5 py-1 rounded-lg border transition-all"
                                            style="color:#1A6B6B; background:#D0E8E8; border-color:#1A6B6B33;"
                                            :class="{ 'opacity-40 cursor-not-allowed': !editNilai[String(opt.id)] || !hasTokens }">
                                            <span x-show="!aiLoading['edit_' + opt.id]">💡 Saran AI</span>
                                            <span x-show="aiLoading['edit_' + opt.id]" class="flex items-center gap-1">
                                                <svg class="animate-spin h-3 w-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                Memuat...
                                            </span>
                                        </button>
                                        <span x-show="!editNilai[String(opt.id)]" class="text-[10px] italic" style="color:#9E9790;">Pilih capaian dulu</span>
                                    </div>
                                    {{-- AI Chips --}}
                                    <template x-if="(aiSuggestions['edit_' + opt.id] || []).length > 0">
                                        <div class="space-y-1.5 pt-1">
                                            <div class="text-[10px] font-semibold" style="color:#6B6560;">Pilih salah satu saran:</div>
                                            <template x-for="(saran, idx) in aiSuggestions['edit_' + opt.id]" :key="idx">
                                                <button type="button" @click="applySuggestion('edit', opt.id, saran)"
                                                    class="block w-full text-left text-[11px] px-3 py-2 rounded-lg border hover:border-teal-400 hover:bg-teal-50 transition-all"
                                                    style="background:#FAF6F0; border-color:rgba(0,0,0,0.08); color:#2C2C2C;"
                                                    x-text="(idx + 1) + '. ' + saran"></button>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                        <div class="pt-2">
                            <label class="input-label">Dokumentasi (Evidence)</label>
                            <template x-if="editBundles[editBundleKey]?.has_photo">
                                <div class="mb-3">
                                    <div class="text-[11px] mb-2 flex items-center justify-between gap-2 font-bold" style="color:#1A6B6B;">
                                        <div class="flex items-center gap-1.5">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                            Foto Sebelumnya:
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
                                    <p class="text-[10px] mt-2 italic" style="color:#9E9790;">Upload file baru untuk mengganti foto.</p>
                                </div>
                            </template>
                            <x-documentation-upload-progress />
                            <input type="file" name="photo" accept="image/*,video/*" class="input-field py-1.5 text-xs" :disabled="docUploadActive" @change="handleFile($event)">
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" @click="showEditModal=false" class="btn-secondary" :disabled="docUploadActive">Batal</button><button type="submit" data-tour="modal-edit-submit" class="btn-primary" :disabled="docUploadActive">Update Evaluasi</button></div>
                </form>
            </div>
        </div>

        {{-- DELETE BUNDLE --}}
        <div x-show="showDeleteBundleModal" data-tour="modal-delete" class="modal-overlay" style="display:none;">
            <div x-show="showDeleteBundleModal" x-transition class="modal-box max-w-sm" @click.away="showDeleteBundleModal=false">
                <form method="POST" action="{{ route('pengajar.pencapaian.destroy-bundle') }}">
                    @csrf @method('DELETE')
                    <input type="hidden" name="tanggal_dari" value="{{ $tanggalDari }}">
                    <input type="hidden" name="tanggal_sampai" value="{{ $tanggalSampai }}">
                    @if($filterAnakId)<input type="hidden" name="filter_anak_id" value="{{ $filterAnakId }}">@endif
                    @if($filterAspekRaw !== '')<input type="hidden" name="aspek" value="{{ $filterAspekRaw }}">@endif
                    <input type="hidden" name="anak_id" :value="deleteBundleAnak">
                    <input type="hidden" name="kegiatan_id" :value="deleteBundleKeg">
                    <div class="modal-body text-center py-6">
                        <h3 class="section-title">Hapus seluruh evaluasi?</h3>
                        <p class="section-subtitle mt-1">Semua aspek untuk anak &amp; kegiatan ini akan dihapus.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" @click="showDeleteBundleModal=false" class="btn-secondary">Batal</button>
                        <button type="submit" class="btn-danger">Ya, hapus</button>
                    </div>
                </form>
            </div>
        </div>

        <x-image-lightbox />
    </div>
</x-app-layout>
