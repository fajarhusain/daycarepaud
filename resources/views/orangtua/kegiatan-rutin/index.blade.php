<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background: #1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
            </div>
            <h2 class="font-bold text-xl" style="color: #2C2C2C;">Kegiatan Rutin Anak Saya</h2>
        </div>
    </x-slot>

    <div class="app-page-shell" x-data="{ 
            showImageModal: false, 
            activeImage: '',
            showDetailModal: false,
            selectedKeg : null
         }">
        <div class="card overflow-hidden">
            <div class="card-pad border-b" style="border-color: rgba(0,0,0,0.06); background: #FAF9F6;">
                <form data-tour="ortu-rutin-filters" method="GET" class="toolbar-form w-full">
                    @if($anaks->count() > 1)
                        <div class="toolbar-field md:min-w-[10rem] md:max-w-[14rem]">
                            <label class="input-label">Pilih Anak</label>
                            <select name="anak_id" class="input-field w-full" onchange="this.form.submit()">
                                <option value="">Semua Anak</option>
                                @foreach($anaks as $anak)
                                    <option value="{{ $anak->id }}" {{ request('anak_id') == $anak->id ? 'selected' : '' }}>
                                        {{ $anak->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="toolbar-field md:min-w-[10rem] md:flex-[1.2]">
                        <label class="input-label">Kegiatan</label>
                        <select name="master_id" class="input-field w-full" onchange="this.form.submit()">
                            <option value="">Semua Kegiatan</option>
                            @foreach($masters as $m)
                                <option value="{{ $m->master_kegiatan_rutin_id }}"
                                    @selected(request('master_id') == $m->master_kegiatan_rutin_id)>
                                    {{ $m->kegiatan }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="toolbar-field md:min-w-[9rem] md:max-w-[11rem]">
                        <label class="input-label">Dari</label>
                        <input type="date" name="mulai" value="{{ $mulai }}" class="input-field w-full">
                    </div>
                    <div class="toolbar-field md:min-w-[9rem] md:max-w-[11rem]">
                        <label class="input-label">Sampai</label>
                        <input type="date" name="sampai" value="{{ $sampai }}" class="input-field w-full">
                    </div>
                    <div class="toolbar-actions">
                        <x-toolbar.filter-icon label="Filter" />
                        <x-filter-reset :href="route('orangtua.kegiatan-rutin.index')" compact />
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="data-table" data-tour="ortu-rutin-results">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            @if($anaks->count() > 1)
                                <th>Anak</th>
                            @endif
                            <th>Kegiatan</th>
                            <th>Aspek</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kegiatans as $keg)
                                                <tr>
                                                    <td class="whitespace-nowrap font-medium text-[#1A6B6B]">
                                                        {{ \Carbon\Carbon::parse($keg->tanggal)->format('d/m/Y') }}
                                                    </td>
                                @if($anaks->count() > 1)
                                    <td class="font-medium text-[#2C2C2C]">{{ $keg->anak->name ?? '-' }}</td>
                                @endif
                                                    <td class="font-semibold text-[#2C2C2C]">{{ $keg->kegiatan }}</td>
                                                    <td><span
                                                            class="text-xs text-gray-500 font-medium px-2 py-1 bg-gray-100 rounded-md">{{ $keg->aspek }}</span>
                                                    </td>
                                                    <td class="text-right">
                                                        <button type="button" @click="selectedKeg = {{ json_encode([
                                'tanggal' => \Carbon\Carbon::parse($keg->tanggal)->format('d M Y'),
                                'kegiatan' => $keg->kegiatan,
                                'aspek' => $keg->aspek,
                                'anak' => $keg->anak->name ?? '-',
                                'status' => $keg->status_pencapaian,
                                'keterangan' => $keg->keterangan,
                                'photo' => $keg->photo ? Storage::url($keg->photo) : null
                            ]) }}; showDetailModal = true"
                                                            class="row-action-icon"
                                                            style="color:#1A6B6B;background:#D0E8E8;"
                                                            title="Detail" aria-label="Detail">
                                                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                        </button>
                                                    </td>
                                                </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $anaks->count() > 1 ? 5 : 4 }}" class="py-12 text-center text-gray-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="h-12 w-12 mb-3 text-gray-300" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                        </svg>
                                        <p>Belum ada data kegiatan rutin pada periode ini.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Modal Detail --}}
        <template x-teleport="body">
        <div x-show="showDetailModal"
            class="modal-overlay modal-overlay--blur"
            style="display:none;" x-transition>
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform"
                @click.away="showDetailModal = false">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="font-bold text-gray-900">Detail Kegiatan Rutin</h3>
                    <button @click="showDetailModal = false" class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="p-6 space-y-5">
                    <template x-if="selectedKeg?.photo">
                        <div class="rounded-xl overflow-hidden border border-gray-100 shadow-sm cursor-pointer"
                            @click="activeImage = selectedKeg.photo; showImageModal = true">
                            <img :src="selectedKeg.photo" class="w-full h-48 object-cover">
                        </div>
                    </template>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span
                                class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Anak</span>
                            <p class="text-sm font-bold text-gray-900" x-text="selectedKeg?.anak || '-'"></p>
                        </div>
                        <div>
                            <span
                                class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Tanggal</span>
                            <p class="text-sm font-bold text-gray-900" x-text="selectedKeg?.tanggal"></p>
                        </div>
                        <div>
                            <span
                                class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Status</span>
                            <span
                                class="inline-flex px-2 py-1 rounded-md text-[10px] font-bold uppercase tracking-tighter"
                                :class="selectedKeg?.status ? 'bg-green-50 text-green-700 border border-green-100' : 'bg-gray-50 text-gray-500'"
                                x-text="selectedKeg?.status || '-'"></span>
                        </div>
                    </div>

                    <div>
                        <span class="text-[10px] font-bold text-[#1A6B6B] uppercase tracking-widest block mb-1"
                            x-text="selectedKeg?.aspek"></span>
                        <h4 class="text-lg font-bold text-gray-900 leading-tight" x-text="selectedKeg?.kegiatan"></h4>
                    </div>

                    <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4">
                        <span
                            class="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-2">Keterangan
                            / Catatan Guru</span>
                        <p class="text-sm text-gray-700 leading-relaxed italic"
                            x-text="selectedKeg?.keterangan || 'Tidak ada keterangan tambahan.'"></p>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                    <button @click="showDetailModal = false"
                        class="btn-secondary w-full py-2.5 rounded-xl font-bold">Tutup</button>
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
                    <span class="text-xs font-bold uppercase tracking-widest text-white/50">Klik di mana saja untuk
                        tutup</span>
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <img :src="activeImage"
                    class="w-full h-auto max-h-[85vh] object-contain rounded-2xl shadow-2xl bg-white shadow-black/20">
            </div>
        </div>
        </template>
    </div>
</x-app-layout>
