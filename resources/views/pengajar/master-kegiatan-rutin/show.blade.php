<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background:#1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
            </div>
            <h2 class="font-bold text-xl" style="color:#2C2C2C;">Detail Kegiatan Rutin</h2>
        </div>
    </x-slot>

    <div class="app-page-shell" x-data="{ 
        openModal: false, 
        openDetailModal: false,
        showImageModal: false,
        activeImage: null,
        activeDownloadUrl: null,
        activeMediaType: 'image',
        docUploadActive: false, docUploadProgress: 0, docUploadLabel: '',
        selectedAnak: null, 
        statusValue: '',
        keteranganValue: '',
        detailData: [],
        filterMulai: '{{ date('Y-m-01') }}',
        filterSampai: '{{ date('Y-m-t') }}',
        isLoadingDetail: false,
        openMedia(url, downloadUrl) {
            this.activeImage = url;
            this.activeDownloadUrl = downloadUrl ?? null;
            this.activeMediaType = (typeof window.isVideoPath === 'function' && window.isVideoPath(url)) ? 'video' : 'image';
            this.showImageModal = true;
        },
        async onRutinPhotoChange(e) {
            const file = e.target.files?.[0];
            if (!file) return;
            try {
                const prepared = await window.documentationUploadProcess(file, this);
                const dt = new DataTransfer();
                dt.items.add(prepared);
                e.target.files = dt.files;
            } catch (err) {
                alert(err?.message || 'Gagal memproses file.');
                e.target.value = '';
            }
        },
        submitRutinForm(event) {
            if (this.docUploadActive) {
                event.preventDefault();
                return;
            }
            event.target.submit();
        },
        initAnak(id, name, status, keterangan) {
            this.selectedAnak = { id, name };
            this.statusValue = status || '';
            this.keteranganValue = keterangan || '';
            this.openModal = true;
        },
        async loadDetail(id, name) {
            this.selectedAnak = { id, name };
            this.openDetailModal = true;
            this.isLoadingDetail = true;
            
            try {
                const is_admin = {{ auth()->user()->usesAdminKegiatanRutinRoutes() ? 'true' : 'false' }};
                const prefix = is_admin ? '/admin' : '/pengajar';
                let res = await fetch(`${prefix}/master-kegiatan-rutin/detail/{{ $masterKegiatanRutin->id }}/${id}?mulai=${this.filterMulai}&sampai=${this.filterSampai}`);
                let json = await res.json();
                this.detailData = json;
            } catch (e) {
                console.error(e);
                this.detailData = [];
            }
            this.isLoadingDetail = false;
        },
        async reloadDetail() {
            if(this.selectedAnak) {
                this.loadDetail(this.selectedAnak.id, this.selectedAnak.name);
            }
        }
    }">
        @if(session('success'))
            <div class="alert-success mb-5">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-4">
            <a href="{{ route((auth()->user()->kegiatanRutinRoutePrefix()).'master-kegiatan-rutin.index') }}" class="btn-secondary text-sm">← Kegiatan Rutin</a>
        </div>

        <div class="card overflow-hidden" data-tour="kegiatan-rutin-records">
            <div class="card-pad border-b" style="border-color:rgba(0,0,0,0.06);">
                <div class="min-w-0 mb-3 md:mb-4" data-tour="page-header">
                    <h3 class="section-title mb-0">{{ $masterKegiatanRutin->nama_kegiatan }}</h3>
                    <p class="section-subtitle mt-1">Aspek: {{ $masterKegiatanRutin->aspek }} · Isi pencapaian per tanggal</p>
                </div>
                <form method="GET" action="{{ route((auth()->user()->kegiatanRutinRoutePrefix()).'master-kegiatan-rutin.show', $masterKegiatanRutin) }}" class="toolbar-form w-full" data-tour="kegiatan-rutin-summary">
                    <div class="toolbar-field md:min-w-[10rem] md:max-w-[12rem]">
                        <label class="input-label">Tanggal</label>
                        <input type="date" name="tanggal" value="{{ $tanggal }}" class="input-field w-full">
                    </div>
                    <div class="toolbar-field md:min-w-[10rem] md:max-w-[14rem]">
                        <label class="input-label">Kelas</label>
                        <select name="kelas_id" class="input-field w-full">
                            <option value="">— Pilih kelas —</option>
                            @foreach($classList as $c)
                                <option value="{{ $c->id }}" @selected($kelasId == $c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="toolbar-actions">
                        <x-toolbar.filter-icon label="Terapkan" />
                        <x-filter-reset :href="route((auth()->user()->kegiatanRutinRoutePrefix()).'master-kegiatan-rutin.show', $masterKegiatanRutin)" compact />
                    </div>
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nama Siswa</th>
                            <th>Status Pencapaian</th>
                            <th>Keterangan</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($anaks as $anak)
                            @php
                                $r = $rutins->get($anak->id);
                            @endphp
                            <tr>
                                <td class="font-semibold" style="color:#2C2C2C;">{{ $anak->name }}</td>
                                <td>
                                    @if($r && $r->status_pencapaian)
                                        <span class="badge badge-teal">{{ $r->status_pencapaian }}</span>
                                    @else
                                        <span class="text-xs italic" style="color:#9E9790;">Belum diisi</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex items-start gap-3">
                                        @if($r?->photo)
                                            <div class="h-10 w-10 shrink-0 rounded-lg overflow-hidden border border-gray-100 shadow-sm cursor-pointer" @click="activeImage = '{{ Storage::url($r->photo) }}'; activeDownloadUrl = '{{ route((auth()->user()->kegiatanRutinRoutePrefix()).'kegiatan-rutin.photo.download', $r) }}'; showImageModal = true">
                                                <img src="{{ Storage::url($r->photo) }}" class="h-full w-full object-cover">
                                            </div>
                                        @endif
                                        <span class="text-xs text-gray-500 line-clamp-2 max-w-xs">{{ $r?->keterangan ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button"
                                            @click="initAnak('{{ $anak->id }}', '{{ addslashes($anak->name) }}', '{{ $r?->status_pencapaian }}', '{{ addslashes($r?->keterangan ?? '') }}')"
                                            class="row-action-icon" style="color:#1A6B6B;background:#D0E8E8;" title="Isi Capaian" aria-label="Isi Capaian">
                                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <button type="button"
                                            @click="loadDetail('{{ $anak->id }}', '{{ addslashes($anak->name) }}')"
                                            class="row-action-icon" style="color:#1A6B6B;background:#E8F5F5;" title="Detail Riwayat" aria-label="Detail Riwayat">
                                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-12 text-center" style="color:#9E9790;">
                                    Pilih kelas untuk melihat daftar siswa.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Modal Update --}}
        <div x-show="openModal" class="modal-overlay modal-overlay--blur" style="display:none;" x-transition>
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform" @click.away="openModal = false">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="font-bold text-gray-900" x-text="'Update Pencapaian: ' + (selectedAnak?.name || '')"></h3>
                    <button @click="openModal = false" class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <form action="{{ route((auth()->user()->kegiatanRutinRoutePrefix()).'master-kegiatan-rutin.store-rutin', $masterKegiatanRutin) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4 relative" @submit.prevent="submitRutinForm($event)">
                    @csrf
                    <input type="hidden" name="tanggal" value="{{ $tanggal }}">
                    <input type="hidden" name="kelas_id" value="{{ $kelasId }}">
                    <input type="hidden" name="anak_id" :value="selectedAnak?.id">

                    <div>
                        <div class="text-xs font-bold text-indigo-500 uppercase tracking-widest mb-1">{{ $masterKegiatanRutin->aspek }}</div>
                        <div class="font-bold text-gray-900 mb-4">{{ $masterKegiatanRutin->nama_kegiatan }}</div>
                        
                        <div class="mb-4">
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Status Pencapaian</label>
                            <select name="status_pencapaian" x-model="statusValue" class="input-field w-full" required>
                                <option value="">- Pilih Status -</option>
                                <option value="Belum Mulai">Belum Mulai</option>
                                <option value="Belum Lancar">Belum Lancar</option>
                                <option value="Lancar">Lancar</option>
                                <option value="Sangat Lancar">Sangat Lancar</option>
                                <option value="Tidak Hadir">Tidak Hadir</option>
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Keterangan (Opsional)</label>
                            <textarea name="keterangan" x-model="keteranganValue" class="input-field w-full" rows="3" placeholder="Tambahkan catatan jika perlu..."></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Foto / Video Dokumentasi (Opsional)</label>
                            <x-documentation-upload-progress />
                            <input type="file" name="photo" class="input-field w-full text-xs" accept="image/*,video/*" :disabled="docUploadActive" @change="onRutinPhotoChange($event)">
                            <p class="text-[10px] text-gray-400 mt-1">Foto & video dikompres di perangkat Anda sebelum diunggah.</p>
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="btn-primary w-full py-3 rounded-xl font-bold shadow-lg shadow-[#1A6B6B]/20" :disabled="docUploadActive">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal Detail --}}
        <div x-show="openDetailModal" class="modal-overlay modal-overlay--blur" style="display:none;" x-transition>
            <div class="modal-box max-w-2xl w-full" @click.away="openDetailModal = false">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex justify-between items-center shrink-0">
                    <div>
                        <h3 class="font-bold text-gray-900" x-text="'Riwayat Kegiatan: ' + (selectedAnak?.name || '')"></h3>
                        <p class="text-xs text-gray-500 mt-1">Lihat capaian kegiatan ini</p>
                    </div>
                    <button @click="openDetailModal = false" class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                 </div>
                 <div class="p-4 border-b border-gray-100 bg-white flex flex-wrap gap-4 items-end shrink-0">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Dari Tanggal</label>
                        <input type="date" x-model="filterMulai" @change="reloadDetail" class="input-field py-1.5 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Sampai Tanggal</label>
                        <input type="date" x-model="filterSampai" @change="reloadDetail" class="input-field py-1.5 text-sm">
                    </div>
                </div>
                 <div class="p-6 overflow-y-auto flex-1 bg-gray-50/30">
                    <div x-show="isLoadingDetail" class="flex justify-center items-center py-12">
                        <svg class="animate-spin h-8 w-8 text-[#1A6B6B]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </div>
                    
                    <div x-show="!isLoadingDetail && detailData.length === 0" class="text-center py-12 text-gray-400 italic">
                        Tidak ada catatan pencapaian pada rentang tanggal ini.
                    </div>

                    <div x-show="!isLoadingDetail && detailData.length > 0" class="space-y-3">
                        <template x-for="item in detailData" :key="item.id">
                            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col gap-3">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="font-bold text-gray-900" x-text="item.tanggal_formatted"></div>
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex px-3 py-1.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100" x-text="item.status_pencapaian"></span>
                                        <form :action="( {{ auth()->user()->usesAdminKegiatanRutinRoutes() ? 'true' : 'false' }} ? '/admin' : '/pengajar' ) + '/master-kegiatan-rutin/rutin/' + item.id" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-400 hover:text-red-600 transition p-1">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                
                                <div class="flex flex-col sm:flex-row gap-4">
                                    <template x-if="item.photo_url">
                                        <div class="w-24 h-24 shrink-0 rounded-lg overflow-hidden border border-gray-100 shadow-sm cursor-pointer relative" @click="openMedia(item.photo_url, item.photo_download_url)">
                                            <template x-if="item.photo_url && typeof window.isVideoPath === 'function' && window.isVideoPath(item.photo_url)">
                                                <video :src="item.photo_url" class="w-full h-full object-cover" muted playsinline></video>
                                                <span class="absolute inset-0 flex items-center justify-center text-white text-lg bg-black/30">▶</span>
                                            </template>
                                            <template x-if="!(item.photo_url && typeof window.isVideoPath === 'function' && window.isVideoPath(item.photo_url))">
                                                <img :src="item.photo_url" class="w-full h-full object-cover">
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="item.keterangan">
                                        <div class="flex-1 rounded-lg px-3 py-2.5 border border-gray-100 bg-gray-50 overflow-hidden">
                                            <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400 block mb-1">Keterangan</span>
                                            <p class="text-sm text-gray-700 leading-relaxed break-words" x-text="item.keterangan"></p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <x-image-lightbox />
    </div>
</x-app-layout>
