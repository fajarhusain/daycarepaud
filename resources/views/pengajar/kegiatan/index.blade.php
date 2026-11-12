<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background:#1A6B6B;"><svg
                    class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg></div>
            <h2 class="font-bold text-xl" style="color:#2C2C2C;">Agenda Kegiatan Siswa</h2>
        </div>
    </x-slot>

    @php
        $kegiatanPhotoDownloadRoute = auth()->user()->hasRole('Wali Kelas')
            ? 'adminkelas.kegiatan.photos.download'
            : 'pengajar.kegiatan.photos.download';
        $kegiatanPhotoSingleBase = auth()->user()->hasRole('Wali Kelas')
            ? url('adminkelas/kegiatan')
            : url('pengajar/kegiatan');
    @endphp

    <div class="app-page-shell" x-data="{ showCreateModal:false, showEditModal:false, showDeleteModal:false, showDetailModal:false, showDocModal:false, showPhotoDeleteModal:false, showImageModal:false, activeImage:null, activeDownloadUrl:null, activeMediaType:'image', editData:{}, deleteRoute:'', detailData:{}, detailEditPayload:{},
            tempNewPhotos: [], tempDeletedPhotos: [], isUploading: false,
            docUploadActive: false, docUploadProgress: 0, docUploadLabel: '',
            photoToDelete: {id:null, path:''},
            openEdit(d){ 
                this.editData = JSON.parse(JSON.stringify(d)); 
                if (!this.editData.matrikulasi_ids) this.editData.matrikulasi_ids = [];
                // Ensure IDs are integers for consistency
                this.editData.matrikulasi_ids = this.editData.matrikulasi_ids.map(id => parseInt(id));
                this.showEditModal = true; 
            },
            openDelete(r){ this.deleteRoute=r; this.showDeleteModal=true },
            openDoc(d){ 
                this.editData = JSON.parse(JSON.stringify(d)); 
                this.tempNewPhotos = [];
                this.tempDeletedPhotos = [];
                this.showDocModal = true;
            },
            confirmDeletePhoto(id, path) {
                this.photoToDelete = {id: id, path: path};
                this.showPhotoDeleteModal = true;
            },
            executeDeletePhoto() {
                this.showPhotoDeleteModal = false;
                this.isUploading = true;
                // Pre-fill required fields from the activity detail to satisfy backend validation
                this.editData = JSON.parse(JSON.stringify(this.detailEditPayload));
                this.tempDeletedPhotos = [this.photoToDelete.path];
                this.tempNewPhotos = [];
                this.$nextTick(() => { this.submitDoc(); });
            },
            immediateDelete(id, path) {
                this.confirmDeletePhoto(id, path);
            },
            isVideoUrl(url) {
                return typeof window.isVideoPath === 'function' && window.isVideoPath(url);
            },
            openMedia(url, downloadUrl) {
                this.activeImage = url;
                this.activeDownloadUrl = downloadUrl ?? null;
                this.activeMediaType = this.isVideoUrl(url) ? 'video' : 'image';
                this.showImageModal = true;
            },
            async addPhotos(e) {
                const files = Array.from(e.target.files);
                if (files.length === 0) return;
                try {
                    for (let i = 0; i < files.length; i++) {
                        const prepared = await window.documentationUploadProcess(files[i], this, {
                            fileIndex: i,
                            fileTotal: files.length,
                        });
                        const kind = (window.isVideoFile && window.isVideoFile(prepared)) ? 'video' : 'image';
                        this.tempNewPhotos.push({
                            file: prepared,
                            preview: URL.createObjectURL(prepared),
                            kind,
                        });
                    }
                } catch (err) {
                    alert(err?.message || 'Gagal memproses file.');
                }
                e.target.value = '';
            },
            removeNewPhoto(index) {
                URL.revokeObjectURL(this.tempNewPhotos[index].preview);
                this.tempNewPhotos.splice(index, 1);
            },
            removeExistingPhoto(path) {
                if (!this.tempDeletedPhotos.includes(path)) {
                    this.tempDeletedPhotos.push(path);
                }
            },
            submitDoc() {
                this.isUploading = true;
                const dt = new DataTransfer();
                this.tempNewPhotos.forEach(p => dt.items.add(p.file));
                this.$refs.finalPhotosInput.files = dt.files;
                this.$nextTick(() => {
                    this.$refs.docForm.submit();
                });
            },
            openDetailFromCal(detail, edit){
                this.detailData = detail;
                this.detailEditPayload = edit || {};
                this.showDetailModal = true;
            },
            onCalClick(detail){
                const p = detail.extendedProps || {};
                if (p.mode !== 'pengajar') return;
                this.openDetailFromCal(p.detail, p.edit);
                this.deleteRoute = p.delete_url || '';
            }
         }" @kegiatan-cal-click.window="onCalClick($event.detail)" @tour-close-modals.window="showCreateModal=false; showEditModal=false; showDeleteModal=false; showDetailModal=false; showDocModal=false; showPhotoDeleteModal=false; showImageModal=false">

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

        <div class="card overflow-hidden mb-6" data-tour="pg-kegiatan-calendar">
            <div class="card-pad page-toolbar border-b"
                style="border-color:rgba(0,0,0,0.06);">
                <div>
                    <h3 class="section-title">Kalender Agenda Saya</h3>
                    <p class="section-subtitle">Klik entri untuk detail, edit, atau hapus.</p>
                </div>
                <x-toolbar.add-icon label="Buat Entri Jurnal" data-tour="pg-kegiatan-add-btn" data-tour-open-modal="create"
                    type="button" @click="showCreateModal=true" />
            </div>
            <form method="get" class="card-pad toolbar-form border-b"
                style="border-color:rgba(0,0,0,0.06);">
                <input type="hidden" name="year" value="{{ $year }}">
                <input type="hidden" name="month" value="{{ $month }}">
                <input type="hidden" name="day" value="{{ request('day') }}">
                <div class="toolbar-field">
                    <label class="input-label">Filter Kelas</label>
                    <select name="kelas_id" class="input-field" onchange="this.form.submit()">
                        <option value="">Semua Kelas</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id }}" @selected(request('kelas_id') == $k->id)>{{ $k->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="toolbar-field">
                    <label class="input-label">Filter matrikulasi</label>
                    <select name="matrikulasi_id" class="input-field" onchange="this.form.submit()">
                        <option value="">Semua</option>
                        @foreach($matrikulasis as $m)
                            <option value="{{ $m->id }}" @selected(request('matrikulasi_id') == $m->id)>
                                {{ $m->aspek ? $m->aspek . ': ' : '' }}{{ Str::limit($m->indicator, 48) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="toolbar-actions">
                    <x-toolbar.filter-icon label="Tampilkan" variant="secondary" />
                    <x-filter-reset :href="route('pengajar.kegiatan.index')" compact />
                    <x-download-photos :route="$kegiatanPhotoDownloadRoute" :icon-only="true" />
                </div>
            </form>
            <div class="p-4 md:p-6">
                <x-jurnal-kalender :events="$calendarEvents" :year="$year" :month="$month" />
            </div>
        </div>

        {{-- DETAIL MODAL --}}
        <div x-show="showDetailModal" class="modal-overlay" style="display:none;">
            <div x-show="showDetailModal" x-transition class="modal-box max-w-2xl" @click.away="showDetailModal=false">
                <div class="modal-header">
                    <h3 class="section-title">Detail Kegiatan: <span x-text="detailData.title"></span></h3>
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
                        <h4 class="font-bold mb-3 text-sm text-gray-800">Dokumentasi Kegiatan</h4>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <template x-for="(url, idx) in detailData.photo_urls" :key="url">
                                <div
                                    class="relative aspect-square rounded-xl overflow-hidden border bg-gray-100 shadow-sm transition-all hover:shadow-md flex flex-col group">
                                    <div class="relative-grow h-full w-full overflow-hidden">
                                        <template x-if="isVideoUrl(url)">
                                            <div class="w-full h-full bg-gray-900 flex items-center justify-center cursor-pointer relative"
                                                @click.stop="openMedia(url, `{{ $kegiatanPhotoSingleBase }}/${detailData.id}/photos/download?index=${idx}`)">
                                                <video :src="url" class="w-full h-full object-cover opacity-80" muted playsinline></video>
                                                <span class="absolute inset-0 flex items-center justify-center text-white text-3xl drop-shadow">▶</span>
                                            </div>
                                        </template>
                                        <template x-if="!isVideoUrl(url)">
                                            <img :src="url" class="w-full h-full object-cover cursor-pointer"
                                                @click.stop="openMedia(url, `{{ $kegiatanPhotoSingleBase }}/${detailData.id}/photos/download?index=${idx}`)">
                                        </template>
                                    </div>
                                    <button type="button"
                                        @click="immediateDelete(detailData.id, detailData.photo_urls_raw[idx])"
                                        class="absolute -top-1 -right-1 p-2 bg-red-600 rounded-bl-xl text-white shadow-lg hover:bg-red-700 transition-colors z-10 scale-90 group-hover:scale-100 opacity-0 group-hover:opacity-100"
                                        title="Hapus dokumentasi ini secara permanen">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                            stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div>
                        <h4 class="font-bold mb-3 text-sm text-gray-800">Daftar Pencapaian Siswa</h4>
                        <div class="table-responsive border rounded-xl overflow-hidden"
                            style="border-color:rgba(0,0,0,0.06);">
                            <table class="w-full text-sm">
                                <thead style="background:#F5F5F3;">
                                    <tr>
                                        <th class="text-left px-4 py-3 font-semibold text-xs" style="color:#5A5A5A;">
                                            Siswa</th>
                                        <th class="text-left px-4 py-3 font-semibold text-xs" style="color:#5A5A5A;">
                                            Aspek / Indikator</th>
                                        <th class="text-left px-4 py-3 font-semibold text-xs" style="color:#5A5A5A;">
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
                                                    <img x-show="pc.anak && pc.anak.photo_url" :src="pc.anak.photo_url"
                                                        alt=""
                                                        class="h-8 w-8 rounded-xl object-cover shrink-0 border border-black/5">
                                                    <div x-show="!(pc.anak && pc.anak.photo_url)"
                                                        class="h-8 w-8 rounded-xl shrink-0 flex items-center justify-center text-xs font-bold text-white bg-[#1A6B6B]"
                                                        x-text="((pc.anak && pc.anak.name) || '?').charAt(0).toUpperCase()">
                                                    </div>
                                                    <span class="font-medium text-sm" style="color:#2C2C2C;"
                                                        x-text="pc.anak?.name || '-'"></span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-xs" style="color:#5A5A5A;">
                                                <span class="font-semibold text-[#1A6B6B]"
                                                    x-text="pc.aspek || ''"></span>
                                                <span x-show="pc.indicator" class="block mt-0.5"
                                                    x-text="pc.indicator"></span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <span
                                                    class="text-xs font-bold px-2 py-1 rounded max-w-[12rem] leading-snug"
                                                    x-bind:style="'background:' + (pc.score_color || '#eee')"
                                                    x-text="pc.score_label || pc.score"></span>
                                            </td>
                                            <td class="px-4 py-3 text-xs" style="color:#5A5A5A;" x-text="pc.feedback">
                                            </td>
                                        </tr>
                                    </template>
                                    <tr x-show="!detailData.pencapaians || detailData.pencapaians.length === 0">
                                        <td colspan="4" class="px-4 py-6 text-center text-xs" style="color:#9E9790;">
                                            Belum ada pencapaian siswa pada kegiatan ini.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer flex flex-wrap gap-2">
                    <button type="button" @click="showDetailModal=false; openDoc(detailEditPayload)" class="btn-primary shrink-0 h-9 w-9 md:h-11 md:w-11 p-0"
                        x-show="detailEditPayload.id" style="background:#10B981; border-color:#10B981;" title="Dokumentasi" aria-label="Dokumentasi">
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg></button>
                    <button type="button" @click="showDetailModal=false; openEdit(detailEditPayload)"
                        data-tour-open-modal="edit" class="btn-secondary">Edit</button>
                    <button type="button" @click="openDelete(deleteRoute); showDetailModal=false" data-tour-open-modal="delete" class="btn-danger"
                        x-show="deleteRoute">Hapus</button>
                    <button type="button" @click="showDetailModal=false" class="btn-primary ml-auto">Tutup</button>
                </div>
            </div>
        </div>

        {{-- CREATE MODAL --}}
        <div x-show="showCreateModal" class="modal-overlay" style="display:none;">
            <div x-show="showCreateModal" x-transition class="modal-box" @click.away="showCreateModal=false">
                <form action="{{ route('pengajar.kegiatan.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h3 class="section-title">Buat Entri Jurnal Baru</h3>
                    </div>
                    <div class="modal-body space-y-4">
                        <div class="text-[13px] font-bold text-[#1A6B6B] mt-1 border-b pb-1" data-tour="modal-create-section-form">Data Jurnal</div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="input-label">Tanggal <span class="text-red-500">*</span></label>
                                <input type="date" name="date" required value="{{ old('date', date('Y-m-d')) }}"
                                    class="input-field @error('date') border-red-500 @enderror">
                                @error('date')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="input-label">Kelas <span class="text-red-500">*</span></label>
                                <select name="kelas_id" required
                                    class="input-field @error('kelas_id') border-red-500 @enderror">
                                    <option value="">-- Pilih Kelas --</option>
                                    @foreach($kelas as $k)
                                        <option value="{{ $k->id }}" @selected(old('kelas_id') == $k->id)>{{ $k->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('kelas_id')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div>
                            <label class="input-label">Judul Kegiatan <span class="text-red-500">*</span></label>
                            <input type="text" name="title" required
                                class="input-field @error('title') border-red-500 @enderror"
                                placeholder="Contoh: Bermain Sensori" value="{{ old('title') }}">
                            @error('title')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="input-label">Deskripsi Kegiatan <span class="text-red-500">*</span></label>
                            <textarea name="description" required rows="3"
                                class="input-field @error('description') border-red-500 @enderror"
                                placeholder="Ceritakan apa yang terjadi...">{{ old('description') }}</textarea>
                            @error('description')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="input-label">Kaitkan dengan Indikator Matrikulasi <span
                                    class="text-red-500">*</span></label>
                            <div class="mt-2 rounded-xl border overflow-hidden max-h-48 overflow-y-auto"
                                style="border-color:rgba(0,0,0,0.1);">
                                @forelse($matrikulasis as $m)
                                    <label
                                        class="flex items-start gap-3 px-4 py-2.5 hover:bg-teal-50 cursor-pointer border-b last:border-b-0"
                                        style="border-color:rgba(0,0,0,0.04);">
                                        <input type="checkbox" name="matrikulasi_ids[]" value="{{ $m->id }}"
                                            class="mt-0.5 accent-teal-600">
                                        <div>
                                            @if($m->aspek)<span class="text-xs font-bold"
                                            style="color:#1A6B6B;">{{ $m->aspek }}</span><br>@endif
                                            <span class="text-sm" style="color:#2C2C2C;">{{ $m->indicator }}</span>
                                        </div>
                                    </label>
                                @empty
                                    <p class="px-4 py-3 text-xs text-center" style="color:#9E9790;">Belum ada indikator
                                        matrikulasi. Buat dulu di menu Matrikulasi.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" @click="showCreateModal=false"
                            class="btn-secondary">Batal</button><button type="submit" data-tour="modal-create-submit" class="btn-primary">Simpan
                            Jurnal</button></div>
                </form>
            </div>
        </div>

        {{-- EDIT MODAL --}}
        <div x-show="showEditModal" class="modal-overlay" style="display:none;">
            <div x-show="showEditModal" x-transition class="modal-box" @click.away="showEditModal=false">
                <form :action="`/pengajar/kegiatan/${editData.id}`" method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="modal-header">
                        <h3 class="section-title">Edit Jurnal Kegiatan</h3>
                    </div>
                    <div class="modal-body space-y-4">
                        <div class="text-[13px] font-bold text-[#1A6B6B] mt-1 border-b pb-1" data-tour="modal-edit-section-form">Data Jurnal</div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="input-label">Tanggal <span class="text-red-500">*</span></label>
                                <input type="date" name="date"
                                    :value="editData.date ? editData.date.split('T')[0] : editData.date" required
                                    class="input-field @error('date') border-red-500 @enderror">
                                @error('date')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="input-label">Kelas <span class="text-red-500">*</span></label>
                                <select name="kelas_id" required
                                    class="input-field @error('kelas_id') border-red-500 @enderror"
                                    :value="editData.kelas_id || ''">
                                    <option value="">-- Pilih Kelas --</option>
                                    @foreach($kelas as $k)
                                        <option value="{{ $k->id }}">{{ $k->name }}</option>
                                    @endforeach
                                </select>
                                @error('kelas_id')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div>
                            <label class="input-label">Judul <span class="text-red-500">*</span></label>
                            <input type="text" name="title" x-model="editData.title" required
                                class="input-field @error('title') border-red-500 @enderror">
                            @error('title')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="input-label">Deskripsi <span class="text-red-500">*</span></label>
                            <textarea name="description" required x-model="editData.description" rows="3"
                                class="input-field @error('description') border-red-500 @enderror"></textarea>
                            @error('description')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="input-label">Indikator Matrikulasi <span class="text-red-500">*</span></label>
                            <div class="mt-2 rounded-xl border overflow-hidden max-h-48 overflow-y-auto"
                                style="border-color:rgba(0,0,0,0.1);">
                                @forelse($matrikulasis as $m)
                                    <label
                                        class="flex items-start gap-3 px-4 py-2.5 hover:bg-teal-50 cursor-pointer border-b last:border-b-0"
                                        style="border-color:rgba(0,0,0,0.04);">
                                        <input type="checkbox" name="matrikulasi_ids[]" :value="{{ $m->id }}"
                                            x-model="editData.matrikulasi_ids" class="mt-0.5 accent-teal-600">
                                        <div>
                                            @if($m->aspek)<span class="text-xs font-bold"
                                            style="color:#1A6B6B;">{{ $m->aspek }}</span><br>@endif
                                            <span class="text-sm" style="color:#2C2C2C;">{{ $m->indicator }}</span>
                                        </div>
                                    </label>
                                @empty
                                    <p class="px-4 py-3 text-xs text-center" style="color:#9E9790;">Belum ada indikator
                                        matrikulasi.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" @click="showEditModal=false"
                            class="btn-secondary">Batal</button><button type="submit" data-tour="modal-edit-submit" class="btn-primary">Simpan
                            Perubahan</button></div>
                </form>
            </div>
        </div>

        {{-- DELETE MODAL --}}
        <div x-show="showDeleteModal" data-tour="modal-delete" class="modal-overlay" style="display:none;">
            <div x-show="showDeleteModal" x-transition class="modal-box max-w-sm" @click.away="showDeleteModal=false">
                <form :action="deleteRoute" method="POST">
                    @csrf @method('DELETE')
                    <div class="modal-body text-center py-6">
                        <div class="h-14 w-14 rounded-2xl mx-auto mb-4 flex items-center justify-center"
                            style="background:#FAD7D2;"><svg class="h-7 w-7" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2" style="color:#C0392B;">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg></div>
                        <h3 class="section-title">Hapus Jurnal?</h3>
                        <p class="section-subtitle mt-1">Semua pencapaian yang terkait juga akan terhapus.</p>
                    </div>
                    <div class="modal-footer"><button type="button" @click="showDeleteModal=false"
                            class="btn-secondary">Batal</button><button type="submit" class="btn-danger">Ya,
                            Hapus</button></div>
                </form>
            </div>
        </div>

        {{-- DELETE PHOTO MODAL (Uniform Alert) --}}
        <div x-show="showPhotoDeleteModal" class="modal-overlay modal-overlay--front" style="display:none;">
            <div x-show="showPhotoDeleteModal" x-transition class="modal-box max-w-sm border-0 shadow-2xl"
                @click.away="showPhotoDeleteModal=false">
                <div class="modal-body text-center py-6 pt-8">
                    <div class="h-16 w-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Hapus Foto?</h3>
                    <p class="text-sm text-gray-500 mt-2 px-6">Foto ini akan dihapus secara permanen dari dokumentasi
                        kegiatan.</p>
                </div>
                <div class="modal-footer grid grid-cols-2 gap-3 p-6 !pt-0">
                    <button type="button" @click="showPhotoDeleteModal=false"
                        class="btn-secondary w-full py-3">Batal</button>
                    <button type="button" @click="executeDeletePhoto()"
                        class="btn-danger w-full py-3 shadow-lg shadow-red-200">Ya, Hapus</button>
                </div>
            </div>
        </div>

        {{-- DOCUMENTATION MODAL --}}
        <div x-show="showDocModal || isUploading" class="modal-overlay" style="display:none;">
            <div x-show="showDocModal || isUploading" x-transition class="modal-box relative overflow-hidden max-w-2xl"
                @click.away="!isUploading && (showDocModal=false)">
                {{-- Loading Overlay --}}
                <div x-show="isUploading"
                    class="absolute inset-0 z-[60] bg-white/80 backdrop-blur-[2px] flex flex-col items-center justify-center">
                    <div class="h-12 w-12 border-4 border-teal-600/30 border-t-teal-600 rounded-full animate-spin">
                    </div>
                    <p class="mt-4 text-sm font-bold text-teal-800"
                        x-text="tempNewPhotos.length > 0 || tempDeletedPhotos.length > 1 ? 'Menyimpan Perubahan...' : 'Menghapus Foto...'">
                    </p>
                </div>

                <form :action="`/pengajar/kegiatan/${editData.id}`" method="POST" enctype="multipart/form-data"
                    x-ref="docForm">
                    @csrf @method('PUT')
                    <input type="hidden" name="date" :value="editData.date">
                    <input type="hidden" name="title" :value="editData.title">
                    <input type="hidden" name="kelas_id" :value="editData.kelas_id">
                    <input type="hidden" name="description" :value="editData.description">

                    {{-- Hidden inputs for matrikulasi_ids to prevent data loss --}}
                    <template x-for="mid in (editData.matrikulasi_ids || [])" :key="mid">
                        <input type="hidden" name="matrikulasi_ids[]" :value="mid">
                    </template>

                    {{-- Hidden file input to be populated on submit --}}
                    <input type="file" name="photos[]" multiple class="hidden" x-ref="finalPhotosInput">

                    {{-- Hidden inputs for deleted photos --}}
                    <template x-for="path in tempDeletedPhotos" :key="path">
                        <input type="hidden" name="delete_photos[]" :value="path">
                    </template>

                    <div class="modal-header">
                        <h3 class="section-title">Kelola Dokumentasi: <span x-text="editData.title"></span></h3>
                    </div>
                    <div class="modal-body space-y-6 pt-2">
                        <div
                            class="p-6 rounded-2xl border-2 border-dashed bg-teal-50/30 flex flex-col items-center justify-center border-teal-200/50">
                            <div class="w-full max-w-md mb-4 px-2">
                                <x-documentation-upload-progress />
                            </div>
                            <label
                                class="btn-primary cursor-pointer px-8 py-3 shadow-md hover:shadow-lg transition-all scale-105 active:scale-95"
                                :class="docUploadActive ? 'opacity-60 pointer-events-none' : ''"
                                style="background:#10B981; border-color:#10B981;">
                                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                                Tambah Foto / Video
                                <input type="file" accept="image/*,video/*" multiple class="hidden" :disabled="docUploadActive" @change="addPhotos($event)">
                            </label>
                            <p class="text-[12px] font-bold text-teal-800 mt-4 text-center">Bisa pilih lebih dari satu
                                foto atau video sekaligus</p>
                        </div>

                        <div class="space-y-6">
                            {{-- New Photos Previews --}}
                            <div x-show="tempNewPhotos.length > 0">
                                <h4 class="text-xs font-bold text-teal-600 uppercase tracking-wider mb-3">Foto Baru
                                    (<span x-text="tempNewPhotos.length"></span>)</h4>
                                <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                                    <template x-for="(p, index) in tempNewPhotos" :key="index">
                                        <div
                                            class="relative aspect-square rounded-xl overflow-hidden border-2 border-teal-100 group shadow-sm">
                                            <video x-show="p.kind === 'video'" :src="p.preview" class="w-full h-full object-cover" muted playsinline></video>
                                            <img x-show="p.kind !== 'video'" :src="p.preview" class="w-full h-full object-cover">
                                            <div
                                                class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-all flex items-center justify-center">
                                                <button type="button" @click="removeNewPhoto(index)"
                                                    class="p-2 bg-red-600 rounded-full text-white hover:bg-red-700 shadow-lg transform scale-90 group-hover:scale-100 transition-transform">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                            <div
                                                class="absolute top-1 left-1 bg-teal-500 text-white text-[8px] font-bold px-1.5 py-0.5 rounded shadow-sm">
                                                BARU</div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- Existing Photos --}}
                            <div x-show="editData.photo_urls && editData.photo_urls.length > 0">
                                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Foto Tersimpan
                                </h4>
                                <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                                    <template x-for="(url, index) in editData.photo_urls" :key="index">
                                        <div class="relative aspect-square rounded-xl overflow-hidden border group bg-white shadow-sm ring-1 ring-black/5"
                                            x-show="!tempDeletedPhotos.includes(editData.photo_urls_raw[index])">
                                            <img :src="url" class="w-full h-full object-cover">
                                            <div
                                                class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-all flex items-center justify-center">
                                                <button type="button"
                                                    @click="removeExistingPhoto(editData.photo_urls_raw[index])"
                                                    class="p-2 bg-red-600 rounded-full text-white hover:bg-red-700 shadow-lg transform scale-90 group-hover:scale-100 transition-transform"
                                                    title="Hapus Foto">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div x-show="tempNewPhotos.length === 0 && (!editData.photo_urls || editData.photo_urls.filter((url, idx) => !tempDeletedPhotos.includes(editData.photo_urls_raw[idx])).length === 0)"
                            class="text-center py-8">
                            <div
                                class="h-16 w-16 bg-gray-100 rounded-full mx-auto mb-3 flex items-center justify-center text-gray-300">
                                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <p class="text-sm text-gray-400">Belum ada dokumentasi yang dipilih.</p>
                        </div>
                    </div>
                    <div class="modal-footer flex items-center justify-between">
                        <button type="button" @click="showDocModal=false; showDetailModal=true"
                            class="btn-secondary">Batal</button>
                        <button type="button" @click="submitDoc()" class="btn-primary"
                            style="background:#1A6B6B; border-color:#1A6B6B;" :disabled="isUploading">
                            <svg x-show="!isUploading" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <x-image-lightbox />

    </div>
</x-app-layout>