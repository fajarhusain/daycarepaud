@php
    $isEdit = $evaluasi !== null;
    $action = $isEdit ? route('admin.monev-guru.update', $evaluasi) : route('admin.monev-guru.store');
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background: #1A6B6B;"><svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg></div>
            <h2 class="font-bold text-xl" style="color: #2C2C2C;">{{ $isEdit ? 'Edit Evaluasi Guru' : 'Buat Evaluasi Guru' }}</h2>
        </div>
    </x-slot>
    <div class="app-page-shell"
        x-data="{
            aiLoading: {},
            aiSuggestions: {},
            presensiInfo: {},
            ringkasanLoading: false,
            catatanRingkasanSuggestions: [],
            rekomendasiRingkasanSuggestions: [],
            kriteriaCount: @js($kriterias->count()),
            hasTokens: @js($hasTokens),
            aiReady: @js($aiReady),
            tokenFallbackMonev: @js($tokenFallbackMonev),
            formatRekap(rekap) {
                if (!rekap) return '';
                const parts = [`Hadir ${rekap.hadir ?? 0}/${rekap.hari_kerja ?? 0} hari (${rekap.persentase ?? 0}%)`];
                if ((rekap.terlambat ?? 0) > 0) parts.push(`Telat masuk ${rekap.terlambat}x`);
                if ((rekap.telat_pulang ?? 0) > 0) parts.push(`Pulang lebih awal ${rekap.telat_pulang}x`);
                if ((rekap.tanpa_check_out ?? 0) > 0) parts.push(`Tanpa checkout ${rekap.tanpa_check_out}x`);
                return parts.join(' · ');
            },
            async fetchAiSuggestions(kriteriaId, index) {
                if (!this.aiReady) { alert('Pengaturan AI belum dikonfigurasi.'); return; }
                if (!this.hasTokens) { alert(this.tokenFallbackMonev); return; }
                const pengajarId = this.$refs.pengajarSelect?.value;
                if (!pengajarId) { alert('Pilih guru terlebih dahulu.'); return; }
                const skor = document.querySelector(`input[name='items[${index}][skor]']`)?.value;
                if (!skor || skor < 1 || skor > 100) { alert('Isi skor (1–100) terlebih dahulu sebelum meminta saran AI.'); return; }
                const key = String(kriteriaId);
                this.aiSuggestions = {};
                this.presensiInfo = { ...this.presensiInfo, [key]: '' };
                this.aiLoading = { ...this.aiLoading, [key]: true };
                try {
                    const res = await fetch(@js(route('admin.ai.monev-guru-suggestions')), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            pengajar_id: parseInt(pengajarId, 10),
                            kriteria_id: kriteriaId,
                            skor: parseInt(skor, 10),
                            periode_mulai: document.querySelector(`input[name='periode_mulai']`)?.value || null,
                            periode_selesai: document.querySelector(`input[name='periode_selesai']`)?.value || null,
                        }),
                        credentials: 'same-origin',
                    });
                    const data = await res.json();
                    if (!res.ok) {
                        if (data.token_exhausted) this.hasTokens = false;
                        alert(data.error || 'Gagal mendapatkan saran AI.');
                        return;
                    }
                    if (typeof data.token_balance === 'number' && data.token_balance < 1) {
                        this.hasTokens = false;
                    }
                    this.aiSuggestions = { [key]: data.suggestions || [] };
                    if (data.rekap) {
                        this.presensiInfo = { ...this.presensiInfo, [key]: this.formatRekap(data.rekap) };
                    }
                } catch (e) {
                    alert('Terjadi kesalahan: ' + e.message);
                } finally {
                    this.aiLoading = { ...this.aiLoading, [key]: false };
                }
            },
            applySuggestion(kriteriaId, index, text) {
                const ta = document.querySelector(`textarea[name='items[${index}][catatan]']`);
                if (ta) ta.value = text;
                this.aiSuggestions = { ...this.aiSuggestions, [String(kriteriaId)]: [] };
            },
            collectPenilaianItems() {
                const items = [];
                for (let i = 0; i < this.kriteriaCount; i++) {
                    const kriteriaId = document.querySelector(`input[name='items[${i}][kriteria_id]']`)?.value;
                    const skor = document.querySelector(`input[name='items[${i}][skor]']`)?.value;
                    const catatan = document.querySelector(`textarea[name='items[${i}][catatan]']`)?.value || '';
                    if (kriteriaId && skor && skor >= 1 && skor <= 100) {
                        items.push({
                            kriteria_id: parseInt(kriteriaId, 10),
                            skor: parseInt(skor, 10),
                            catatan,
                        });
                    }
                }
                return items;
            },
            async fetchRingkasanSuggestions() {
                if (!this.aiReady) { alert('Pengaturan AI belum dikonfigurasi.'); return; }
                if (!this.hasTokens) { alert(this.tokenFallbackMonev); return; }
                const pengajarId = this.$refs.pengajarSelect?.value;
                if (!pengajarId) { alert('Pilih guru terlebih dahulu.'); return; }
                const items = this.collectPenilaianItems();
                if (items.length === 0) { alert('Isi minimal satu skor kriteria (1–100) terlebih dahulu.'); return; }
                this.ringkasanLoading = true;
                this.catatanRingkasanSuggestions = [];
                this.rekomendasiRingkasanSuggestions = [];
                try {
                    const res = await fetch(@js(route('admin.ai.monev-guru-ringkasan-suggestions')), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            pengajar_id: parseInt(pengajarId, 10),
                            judul: document.querySelector(`input[name='judul']`)?.value || null,
                            periode_mulai: document.querySelector(`input[name='periode_mulai']`)?.value || null,
                            periode_selesai: document.querySelector(`input[name='periode_selesai']`)?.value || null,
                            items,
                        }),
                        credentials: 'same-origin',
                    });
                    const data = await res.json();
                    if (!res.ok) {
                        if (data.token_exhausted) this.hasTokens = false;
                        alert(data.error || 'Gagal mendapatkan saran AI.');
                        return;
                    }
                    if (typeof data.token_balance === 'number' && data.token_balance < 1) {
                        this.hasTokens = false;
                    }
                    this.catatanRingkasanSuggestions = data.catatan_suggestions || [];
                    this.rekomendasiRingkasanSuggestions = data.rekomendasi_suggestions || [];
                } catch (e) {
                    alert('Terjadi kesalahan: ' + e.message);
                } finally {
                    this.ringkasanLoading = false;
                }
            },
            applyRingkasan(field, text) {
                const ta = document.querySelector(`textarea[name='${field}']`);
                if (ta) ta.value = text;
                if (field === 'catatan_umum') {
                    this.catatanRingkasanSuggestions = [];
                } else {
                    this.rekomendasiRingkasanSuggestions = [];
                }
            }
        }">
        @if($errors->any())<div class="alert-danger mb-5"><ul class="list-disc pl-5 text-sm">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul></div>@endif

        <form action="{{ $action }}" method="POST" class="space-y-6">
            <div class="card overflow-hidden">
                <div class="card-pad border-b flex flex-wrap items-center justify-between gap-3" style="border-color:rgba(0,0,0,0.06);">
                    <div>
                        <h3 class="section-title mb-0">{{ $isEdit ? 'Edit evaluasi' : 'Buat evaluasi baru' }}</h3>
                        <p class="section-subtitle mt-1">Lengkapi data guru, periode, dan penilaian per kriteria.</p>
                    </div>
                    <a href="{{ route('admin.monev-guru.index') }}"
                        class="btn-secondary toolbar-icon shrink-0 inline-flex items-center justify-center !p-0 h-9 w-9 md:h-11 md:w-11"
                        title="Kembali" aria-label="Kembali">
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    </a>
                </div>
            </div>
            @csrf
            @if($isEdit) @method('PUT') @endif
            <div class="card p-6 space-y-4">
                <h3 class="section-title">Informasi evaluasi</h3>
                <div>
                    <label class="input-label">Guru <span class="text-red-600">*</span></label>
                    <select name="pengajar_id" required class="input-field w-full" x-ref="pengajarSelect">
                        <option value="">Pilih guru</option>
                        @foreach($pengajars as $p)
                            <option value="{{ $p->id }}" @selected(old('pengajar_id', $evaluasi?->pengajar_id) == $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="input-label">Judul evaluasi</label>
                    <input type="text" name="judul" class="input-field w-full" placeholder="Contoh: Monev Semester 1 2026" value="{{ old('judul', $evaluasi?->judul) }}">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="input-label">Periode mulai <span class="text-red-600">*</span></label>
                        <input type="date" name="periode_mulai" required class="input-field w-full" value="{{ old('periode_mulai', $evaluasi?->periode_mulai?->format('Y-m-d')) }}">
                    </div>
                    <div>
                        <label class="input-label">Periode selesai <span class="text-red-600">*</span></label>
                        <input type="date" name="periode_selesai" required class="input-field w-full" value="{{ old('periode_selesai', $evaluasi?->periode_selesai?->format('Y-m-d')) }}">
                    </div>
                </div>
            </div>

            <div class="card overflow-hidden">
                <div class="px-6 py-4 border-b" style="border-color:rgba(0,0,0,0.06);">
                    <h3 class="section-title">Penilaian per kriteria</h3>
                    <p class="section-subtitle">Skor 1–100. Wajib lengkap saat finalisasi. Gunakan saran AI untuk membantu menulis catatan.</p>
                </div>
                <div class="divide-y" style="border-color:rgba(0,0,0,0.06);">
                    @foreach($kriterias as $i => $k)
                        @php
                            $item = $itemsByKriteria->get($k->id);
                            $oldSkor = old("items.{$i}.skor", $item?->skor);
                            $oldCatatan = old("items.{$i}.catatan", $item?->catatan);
                        @endphp
                        <div class="p-6 space-y-3">
                            <input type="hidden" name="items[{{ $i }}][kriteria_id]" value="{{ $k->id }}">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <div>
                                    <div class="font-semibold" style="color:#2C2C2C;">{{ $k->nama }}</div>
                                    @if($k->deskripsi)<p class="text-sm mt-1" style="color:#9E9790;">{{ $k->deskripsi }}</p>@endif
                                </div>
                                <span class="text-xs font-semibold px-2 py-1 rounded" style="background:#E8F5F5;color:#1A6B6B;">Bobot {{ $k->bobot }}%</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                                <div class="sm:col-span-1">
                                    <label class="input-label">Skor (1–100)</label>
                                    <input type="number" name="items[{{ $i }}][skor]" min="1" max="100" class="input-field w-full" value="{{ $oldSkor }}">
                                </div>
                                <div class="sm:col-span-3 space-y-2">
                                    <label class="input-label">Catatan</label>
                                    <textarea name="items[{{ $i }}][catatan]" rows="4" class="input-field w-full min-h-[6.5rem]" placeholder="Catatan penilaian untuk kriteria ini…">{{ $oldCatatan }}</textarea>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <button type="button"
                                            @click="fetchAiSuggestions({{ $k->id }}, {{ $i }})"
                                            :disabled="aiLoading['{{ $k->id }}'] || !aiReady || !hasTokens"
                                            class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-2.5 py-1 rounded-lg border transition-all"
                                            style="color:#1A6B6B; background:#D0E8E8; border-color:#1A6B6B33;"
                                            :class="{ 'opacity-40 cursor-not-allowed': !aiReady || !hasTokens }">
                                            <span x-show="!aiLoading['{{ $k->id }}']">Saran AI</span>
                                            <span x-show="aiLoading['{{ $k->id }}']" class="flex items-center gap-1">
                                                <svg class="animate-spin h-3 w-3" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                Memuat...
                                            </span>
                                        </button>
                                        <span x-show="!aiReady" class="text-[10px] italic" style="color:#9E9790;">AI belum dikonfigurasi</span>
                                        <span x-show="aiReady && !hasTokens" class="text-[10px] italic" style="color:#9E9790;">Token AI habis</span>
                                    </div>
                                    <p class="text-xs mt-1" style="color:#9A9188;" x-text="presensiInfo['{{ $k->id }}'] || ''" x-show="presensiInfo['{{ $k->id }}']"></p>
                                    <template x-if="(aiSuggestions['{{ $k->id }}'] || []).length > 0">
                                        <div class="space-y-1.5 pt-1">
                                            <div class="text-[10px] font-semibold" style="color:#6B6560;">Pilih salah satu saran:</div>
                                            <template x-for="(saran, idx) in aiSuggestions['{{ $k->id }}']" :key="idx">
                                                <button type="button"
                                                    @click="applySuggestion({{ $k->id }}, {{ $i }}, saran)"
                                                    class="block w-full text-left text-[11px] px-3 py-2 rounded-lg border hover:border-teal-400 hover:bg-teal-50 transition-all"
                                                    style="background:#FAF6F0; border-color:rgba(0,0,0,0.08); color:#2C2C2C;"
                                                    x-text="(idx + 1) + '. ' + saran">
                                                </button>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card p-6 space-y-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h3 class="section-title">Catatan & rekomendasi</h3>
                        <p class="section-subtitle">Ringkasan holistik berdasarkan penilaian per kriteria di atas.</p>
                    </div>
                    <button type="button"
                        @click="fetchRingkasanSuggestions()"
                        :disabled="ringkasanLoading || !aiReady || !hasTokens"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg border transition-all shrink-0"
                        style="color:#1A6B6B; background:#D0E8E8; border-color:#1A6B6B33;"
                        :class="{ 'opacity-40 cursor-not-allowed': !aiReady || !hasTokens }">
                        <span x-show="!ringkasanLoading">Saran AI dari penilaian</span>
                        <span x-show="ringkasanLoading" class="flex items-center gap-1">
                            <svg class="animate-spin h-3.5 w-3.5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Memuat...
                        </span>
                    </button>
                </div>
                <div class="space-y-2">
                    <label class="input-label">Catatan umum</label>
                    <textarea name="catatan_umum" rows="4" class="input-field w-full min-h-[6.5rem]">{{ old('catatan_umum', $evaluasi?->catatan_umum) }}</textarea>
                    <template x-if="catatanRingkasanSuggestions.length > 0">
                        <div class="space-y-1.5">
                            <div class="text-[10px] font-semibold" style="color:#6B6560;">Pilih saran catatan umum:</div>
                            <template x-for="(saran, idx) in catatanRingkasanSuggestions" :key="'c'+idx">
                                <button type="button"
                                    @click="applyRingkasan('catatan_umum', saran)"
                                    class="block w-full text-left text-[11px] px-3 py-2 rounded-lg border hover:border-teal-400 hover:bg-teal-50 transition-all"
                                    style="background:#FAF6F0; border-color:rgba(0,0,0,0.08); color:#2C2C2C;"
                                    x-text="(idx + 1) + '. ' + saran">
                                </button>
                            </template>
                        </div>
                    </template>
                </div>
                <div class="space-y-2">
                    <label class="input-label">Rekomendasi tindak lanjut</label>
                    <textarea name="rekomendasi" rows="4" class="input-field w-full min-h-[6.5rem]">{{ old('rekomendasi', $evaluasi?->rekomendasi) }}</textarea>
                    <template x-if="rekomendasiRingkasanSuggestions.length > 0">
                        <div class="space-y-1.5">
                            <div class="text-[10px] font-semibold" style="color:#6B6560;">Pilih saran rekomendasi:</div>
                            <template x-for="(saran, idx) in rekomendasiRingkasanSuggestions" :key="'r'+idx">
                                <button type="button"
                                    @click="applyRingkasan('rekomendasi', saran)"
                                    class="block w-full text-left text-[11px] px-3 py-2 rounded-lg border hover:border-teal-400 hover:bg-teal-50 transition-all"
                                    style="background:#FAF6F0; border-color:rgba(0,0,0,0.08); color:#2C2C2C;"
                                    x-text="(idx + 1) + '. ' + saran">
                                </button>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="submit" name="finalize" value="0" class="btn-secondary">Simpan draft</button>
                <button type="submit" name="finalize" value="1" class="btn-primary" onclick="return confirm('Finalisasi evaluasi? Guru akan dapat melihat hasil ini.')">Simpan & finalisasi</button>
            </div>
        </form>
    </div>
</x-app-layout>
