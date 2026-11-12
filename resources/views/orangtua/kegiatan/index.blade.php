<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background:#1A6B6B;"><svg
                    class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg></div>
            <h2 class="font-bold text-xl" style="color:#2C2C2C;">Agenda Belajar</h2>
        </div>
    </x-slot>

    <div class="app-page-shell" x-data="{ showDetailModal:false, showImageModal:false, activeImage:null, detailData:{},
            onCalClick(detail){
                const p = detail.extendedProps || {};
                if (p.mode !== 'readonly' || !p.detail) return;
                this.detailData = p.detail;
                this.showDetailModal = true;
            }
         }" @kegiatan-cal-click.window="onCalClick($event.detail)">

        @if($anaks->count() > 1)
            <div class="card mb-6">
                <div class="card-pad border-b" style="border-color: rgba(0,0,0,0.06); background: #FAF9F6;">
                    <form method="GET" class="filter-toolbar-inline">
                        <input type="hidden" name="year" value="{{ $year }}">
                        <input type="hidden" name="month" value="{{ $month }}">
                        <input type="hidden" name="day" value="{{ request('day') }}">
                        <div class="filter-toolbar-field">
                            <label class="input-label">Anak</label>
                            <select name="anak_id" class="input-field w-full h-9 md:h-11">
                                <option value="">Semua Anak</option>
                                @foreach($anaks as $anak)
                                    <option value="{{ $anak->id }}" @selected((int) $anakId === (int) $anak->id)>{{ $anak->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-toolbar-field">
                            <label class="input-label">Tampilan Agenda</label>
                            <select name="semua_sekolah" class="input-field w-full h-9 md:h-11">
                                <option value="0" @selected(! $semuaSekolah)>Kelas Anak Saja</option>
                                <option value="1" @selected($semuaSekolah)>Semua Agenda Sekolah</option>
                            </select>
                        </div>
                        <div class="filter-toolbar-actions">
                            <button type="submit" class="btn-primary h-9 w-9 md:h-11 md:w-11 p-0 shrink-0" title="Terapkan" aria-label="Terapkan filter">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            </button>
                            <x-filter-reset :href="route('orangtua.kegiatan.index', ['year' => $year, 'month' => $month, 'day' => request('day')])" compact />
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <div class="card overflow-hidden" data-tour="ortu-kegiatan-calendar">
            <div class="px-4 py-3 md:px-6 md:py-4 border-b" style="border-color:rgba(0,0,0,0.06);">
                <h3 class="section-title text-sm md:text-base">Kalender Jurnal Kegiatan</h3>
            </div>
            <div class="p-2 sm:p-3 md:p-6">
                <x-jurnal-kalender :events="$calendarEvents" :year="$year" :month="$month" />
            </div>
        </div>

        {{-- DETAIL MODAL --}}
        <template x-teleport="body">
        <div x-show="showDetailModal" class="modal-overlay modal-overlay--blur" style="display:none;">
            <div x-show="showDetailModal" x-transition class="modal-box w-11/12 !max-w-none md:max-w-xl" @click.away="showDetailModal=false">
                <div class="modal-header">
                    <h3 class="section-title">Detail: <span x-text="detailData.title"></span></h3>
                </div>
                <div class="modal-body space-y-5">
                    <div class="bg-gray-50 rounded-xl p-4 border" style="border-color:rgba(0,0,0,0.06);">
                        <p class="text-sm" style="color:#5A5A5A;"><strong class="text-gray-800">Tanggal:</strong> <span
                                x-text="detailData.date ? new Date(detailData.date + 'T12:00:00').toLocaleDateString('id-ID') : '-'"></span>
                        </p>
                        <div class="flex items-center gap-2 mt-2 text-sm" style="color:#5A5A5A;">
                            <strong class="text-gray-800 shrink-0">Pengajar:</strong>
                            <img x-show="detailData.pengajar_photo_url" :src="detailData.pengajar_photo_url" alt=""
                                class="h-8 w-8 rounded-xl object-cover shrink-0 border border-black/5">
                            <div x-show="!detailData.pengajar_photo_url"
                                class="h-8 w-8 rounded-xl shrink-0 flex items-center justify-center text-xs font-bold text-white bg-[#1A6B6B]"
                                x-text="(detailData.pengajar_name || '?').charAt(0).toUpperCase()"></div>
                            <span x-text="detailData.pengajar_name || '-'"></span>
                        </div>
                        <p class="text-sm mt-2" style="color:#5A5A5A;"><strong class="text-gray-800">Kelas:</strong>
                            <span x-text="detailData.kelas_name || '-'"></span>
                        </p>
                        <p class="text-sm mt-2" style="color:#5A5A5A;" x-show="detailData.description"><strong
                                class="text-gray-800">Deskripsi:</strong> <br><span
                                x-text="detailData.description"></span></p>
                    </div>

                    <div x-show="detailData.photo_urls && detailData.photo_urls.length > 0">
                        <h4 class="font-bold mb-3 text-sm text-gray-800">Dokumentasi</h4>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <template x-for="url in detailData.photo_urls" :key="url">
                                <div
                                    class="relative aspect-square rounded-xl overflow-hidden border bg-gray-100 shadow-sm flex flex-col group transition-all hover:shadow-md">
                                    <div class="h-full w-full overflow-hidden">
                                        <img :src="url" class="w-full h-full object-cover cursor-pointer"
                                            @click.stop="activeImage = url; showImageModal = true">
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Daftar Pencapaian Siswa Section --}}
                    <div>
                        <h4 class="font-bold mb-3 text-sm text-gray-800">Pencapaian Terkait</h4>

                        {{-- Mobile: kartu bertumpuk --}}
                        <div class="md:hidden space-y-3">
                            <template x-for="pc in (detailData.pencapaians || [])" :key="pc.id">
                                <div class="rounded-xl border p-4 space-y-3" style="border-color:rgba(0,0,0,0.06); background:#FAFAF8;">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <img x-show="pc.anak_photo_url" :src="pc.anak_photo_url" alt=""
                                            class="h-10 w-10 rounded-xl object-cover shrink-0 border border-black/5">
                                        <div x-show="!pc.anak_photo_url"
                                            class="h-10 w-10 rounded-xl shrink-0 flex items-center justify-center text-xs font-bold text-white bg-[#1A6B6B]"
                                            x-text="(pc.anak_name || '?').charAt(0).toUpperCase()"></div>
                                        <span class="font-semibold text-sm min-w-0 break-words leading-snug" style="color:#2C2C2C;"
                                            x-text="pc.anak_name || '-'"></span>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold uppercase tracking-wider mb-1" style="color:#9E9790;">Aspek / Indikator</p>
                                        <span class="font-semibold text-sm text-[#1A6B6B] block" x-text="pc.aspek || ''"></span>
                                        <span x-show="pc.indicator" class="block mt-1 text-xs leading-relaxed break-words" style="color:#5A5A5A;"
                                            x-text="pc.indicator"></span>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold uppercase tracking-wider mb-1" style="color:#9E9790;">Nilai</p>
                                        <span class="inline-block text-xs font-bold px-2.5 py-1 rounded whitespace-nowrap"
                                            x-bind:style="'background:' + (pc.score_color || '#eee')"
                                            x-text="pc.score_label || pc.score"></span>
                                    </div>
                                    <div x-show="pc.feedback">
                                        <p class="text-[10px] font-bold uppercase tracking-wider mb-1" style="color:#9E9790;">Catatan</p>
                                        <p class="text-xs leading-relaxed break-words" style="color:#5A5A5A;" x-text="pc.feedback"></p>
                                    </div>
                                </div>
                            </template>
                            <div x-show="!detailData.pencapaians || detailData.pencapaians.length === 0"
                                class="rounded-xl border px-4 py-6 text-center text-xs" style="border-color:rgba(0,0,0,0.06); color:#9E9790;">
                                Belum ada data pencapaian pada kegiatan ini.
                            </div>
                        </div>

                        {{-- Desktop: tabel --}}
                        <div class="hidden md:block border rounded-xl overflow-hidden"
                            style="border-color:rgba(0,0,0,0.06);">
                            <table class="w-full text-sm">
                                <thead style="background:#F5F5F3;">
                                    <tr>
                                        <th class="text-left px-4 py-3 font-semibold text-xs" style="color:#5A5A5A;">
                                            Anak</th>
                                        <th class="text-left px-4 py-3 font-semibold text-xs" style="color:#5A5A5A;">
                                            Aspek / Indikator</th>
                                        <th class="text-left px-4 py-3 font-semibold text-xs whitespace-nowrap w-px" style="color:#5A5A5A;">
                                            Nilai</th>
                                        <th class="text-left px-4 py-3 font-semibold text-xs" style="color:#5A5A5A;">
                                            Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="pc in (detailData.pencapaians || [])" :key="pc.id">
                                        <tr class="border-t" style="border-color:rgba(0,0,0,0.04);">
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-2">
                                                    <img x-show="pc.anak_photo_url" :src="pc.anak_photo_url" alt=""
                                                        class="h-8 w-8 rounded-xl object-cover shrink-0 border border-black/5">
                                                    <div x-show="!pc.anak_photo_url"
                                                        class="h-8 w-8 rounded-xl shrink-0 flex items-center justify-center text-xs font-bold text-white bg-[#1A6B6B]"
                                                        x-text="(pc.anak_name || '?').charAt(0).toUpperCase()"></div>
                                                    <span class="font-medium text-sm" style="color:#2C2C2C;"
                                                        x-text="pc.anak_name || '-'"></span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-xs" style="color:#5A5A5A;">
                                                <span class="font-semibold text-[#1A6B6B]"
                                                    x-text="pc.aspek || ''"></span>
                                                <span x-show="pc.indicator" class="block mt-0.5"
                                                    x-text="pc.indicator"></span>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap w-px align-top">
                                                <span
                                                    class="inline-block text-xs font-bold px-2.5 py-1 rounded whitespace-nowrap"
                                                    x-bind:style="'background:' + (pc.score_color || '#eee')"
                                                    x-text="pc.score_label || pc.score"></span>
                                            </td>
                                            <td class="px-4 py-3 text-xs" style="color:#5A5A5A;" x-text="pc.feedback">
                                            </td>
                                        </tr>
                                    </template>
                                    <tr x-show="!detailData.pencapaians || detailData.pencapaians.length === 0">
                                        <td colspan="4" class="px-4 py-6 text-center text-xs" style="color:#9E9790;">
                                            Belum ada data pencapaian pada kegiatan ini.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer flex gap-2">
                    <button type="button" @click="showDetailModal=false" class="btn-primary ml-auto">Tutup</button>
                </div>
            </div>
        </div>
        </template>

        {{-- Modal Preview Gambar --}}
        <template x-teleport="body">
        <div x-show="showImageModal"
            class="modal-overlay modal-overlay--elevated modal-overlay--dark"
            style="display: none;" x-transition @keydown.escape.window="showImageModal = false">
            <div class="relative max-w-4xl w-full" @click.away="showImageModal = false">
                <button
                    class="absolute -top-12 right-0 text-white hover:text-gray-300 transition flex items-center gap-2"
                    @click="showImageModal = false">
                    <span class="text-xs font-bold uppercase tracking-widest text-white/50">Klik di mana saja untuk tutup</span>
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <img :src="activeImage"
                    class="w-full h-auto max-h-[85vh] object-contain rounded-2xl shadow-2xl bg-white shadow-black/20">
            </div>
        </div>
        </template>
    </div>
</x-app-layout>
