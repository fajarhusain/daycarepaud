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

    <div class="app-page-shell" x-data="{ showCreateModal:false, showEditModal:false, showDeleteModal:false, showDetailModal:false, showDocModal:false, showPhotoDeleteModal:false, showImageModal:false, activeImage:null, activeDownloadUrl:null, activeMediaType:'image', compressLabel:'Memproses media...', editData:{}, deleteRoute:'', detailData:{}, detailEditPayload:{},
            tempNewPhotos: [], tempDeletedPhotos: [], isUploading: false,
            docUploadActive: false, docUploadProgress: 0, docUploadLabel: '',
            photoToDelete: {id:null, path:''},
            openEdit(d){ 
                this.editData = JSON.parse(JSON.stringify(d)); 
                if (!this.editData.matrikulasi_ids) this.editData.matrikulasi_ids = [];
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
                this.editData = JSON.parse(JSON.stringify(this.detailEditPayload));
                this.tempDeletedPhotos = [this.photoToDelete.path];
                this.tempNewPhotos = [];
                this.$nextTick(() => { this.submitDoc(); });
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
                        this.tempNewPhotos.push({ file: prepared, preview: URL.createObjectURL(prepared), kind });
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
                if (!this.tempDeletedPhotos.includes(path)) this.tempDeletedPhotos.push(path);
            },
            submitDoc() {
                this.isUploading = true;
                const dt = new DataTransfer();
                this.tempNewPhotos.forEach(p => dt.items.add(p.file));
                this.$refs.finalPhotosInput.files = dt.files;
                this.$nextTick(() => { this.$refs.docForm.submit(); });
            },
            openDetailFromCal(detail, edit){
                this.detailData = detail;
                this.detailEditPayload = edit || {};
                this.showDetailModal = true;
            },
            onCalClick(detail){
                const p = detail.extendedProps || {};
                if (p.mode !== 'admin') return;
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

        <div class="card overflow-hidden mb-6" data-tour="admin-kegiatan-calendar">
            <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b"
                style="border-color:rgba(0,0,0,0.06);">
                <div>
                    <h3 class="section-title">Kalender Agenda Belajar</h3>
                    <p class="section-subtitle">Pilih tanggal dan kelas untuk melihat atau menambah agenda.</p>
                </div>
                <div class="toolbar-actions">
                    <x-export-excel route="admin.kegiatan.export" />
                    <x-download-photos route="admin.kegiatan.photos.download" :icon-only="true" />
                    <x-toolbar.add-icon label="Buat Jurnal Baru" data-tour="admin-kegiatan-add-btn" data-tour-open-modal="create"
                        type="button" @click="showCreateModal=true" />
                </div>
            </div>
            <form data-tour="admin-kegiatan-filter" method="get" class="card-pad toolbar-form border-b"
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
                    <label class="input-label">Filter Pengajar</label>
                    <select name="pengajar_id" class="input-field" onchange="this.form.submit()">
                        <option value="">Semua Pengajar</option>
                        @foreach($pengajars as $p)
                            <option value="{{ $p->id }}" @selected(request('pengajar_id') == $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="toolbar-actions">
                    <x-toolbar.filter-icon label="Tampilkan" variant="secondary" />
                    <x-filter-reset :href="route('admin.kegiatan.index')" compact />
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
                        <div class="flex flex-wrap items-center gap-2 mt-2 text-sm" style="color:#5A5A5A;">
                            <span class="font-semibold text-gray-800 shrink-0">Kelas</span>
                            <span x-show="detailData.kelas_name && detailData.kelas_name !== '-'" class="badge badge-teal" x-text="detailData.kelas_name"></span>
                            <span x-show="!detailData.kelas_name || detailData.kelas_name === '-'" class="text-sm">-</span>
                        </div>
                        <p class="text-sm mt-2" style="color:#5A5A5A;" x-show="detailData.description"><strong
                                class="text-gray-800">Deskripsi:</strong> <br><span
                                x-text="detailData.description"></span></p>
                    </div>
                    <div x-show="detailData.photo_urls && detailData.photo_urls.length > 0">
                        <h4 class="font-bold mb-3 text-sm text-gray-800">Dokumentasi</h4>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <template x-for="(url, idx) in detailData.photo_urls" :key="url">
                                <div class="relative aspect-square rounded-xl overflow-hidden border bg-gray-100 shadow-sm flex flex-col group transition-all hover:shadow-md">
                                    <div class="h-full w-full overflow-hidden">
                                        <template x-if="isVideoUrl(url)">
                                            <div class="w-full h-full bg-gray-900 flex items-center justify-center cursor-pointer relative"
                                                @click.stop="openMedia(url, `{{ url('admin/kegiatan') }}/${detailData.id}/photos/download?index=${idx}`)">
                                                <video :src="url" class="w-full h-full object-cover opacity-80" muted playsinline></video>
                                                <span class="absolute inset-0 flex items-center justify-center text-white text-3xl drop-shadow">▶</span>
                                            </div>
                                        </template>
                                        <template x-if="!isVideoUrl(url)">
                                            <img :src="url" class="w-full h-full object-cover cursor-pointer" @click.stop="openMedia(url, `{{ url('admin/kegiatan') }}/${detailData.id}/photos/download?index=${idx}`)">
                                        </template>
                                    </div>
                                    <button type="button" @click="confirmDeletePhoto(detailData.id, detailData.photo_urls_raw[idx])"
                                        class="absolute -top-1 -right-1 p-2 bg-red-600 rounded-bl-xl text-white opacity-0 group-hover:opacity-100 transition-opacity">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Daftar Pencapaian Siswa Section --}}
                    <div>
                        <h4 class="font-bold mb-3 text-sm text-gray-800">Daftar Pencapaian Siswa</h4>
                        <div class="table-responsive border rounded-xl overflow-hidden" style="border-color:rgba(0,0,0,0.06);">
                            <table class="w-full text-sm">
                                <thead style="background:#F5F5F3;">
                                    <tr>
                                        <th class="text-left px-4 py-3 font-semibold text-xs" style="color:#5A5A5A;">Siswa</th>
                                        <th class="text-left px-4 py-3 font-semibold text-xs" style="color:#5A5A5A;">Aspek / Indikator</th>
                                        <th class="text-left px-4 py-3 font-semibold text-xs" style="color:#5A5A5A;">Nilai</th>
                                        <th class="text-left px-4 py-3 font-semibold text-xs" style="color:#5A5A5A;">Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="pc in (detailData.pencapaians || [])" :key="pc.id">
                                        <tr class="border-t" style="border-color:rgba(0,0,0,0.04);">
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-2">
                                                    <img x-show="pc.anak && pc.anak.photo_url" :src="pc.anak.photo_url" alt="" class="h-8 w-8 rounded-xl object-cover shrink-0 border border-black/5">
                                                    <div x-show="!(pc.anak && pc.anak.photo_url)" class="h-8 w-8 rounded-xl shrink-0 flex items-center justify-center text-xs font-bold text-white bg-[#1A6B6B]" x-text="((pc.anak && pc.anak.name) || '?').charAt(0).toUpperCase()"></div>
                                                    <span class="font-medium text-sm" style="color:#2C2C2C;" x-text="pc.anak?.name || '-'"></span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-xs" style="color:#5A5A5A;">
                                                <span class="font-semibold text-[#1A6B6B]" x-text="pc.aspek || ''"></span>
                                                <span x-show="pc.indicator" class="block mt-0.5" x-text="pc.indicator"></span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="text-xs font-bold px-2 py-1 rounded max-w-[12rem] leading-snug" x-bind:style="'background:' + (pc.score_color || '#eee')" x-text="pc.score_label || pc.score"></span>
                                            </td>
                                            <td class="px-4 py-3 text-xs" style="color:#5A5A5A;" x-text="pc.feedback"></td>
                                        </tr>
                                    </template>
                                    <tr x-show="!detailData.pencapaians || detailData.pencapaians.length === 0">
                                        <td colspan="4" class="px-4 py-6 text-center text-xs" style="color:#9E9790;">Belum ada pencapaian siswa pada kegiatan ini.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer flex gap-2">
                    <button type="button" @click="showDetailModal=false; openDoc(detailEditPayload)" class="btn-primary" style="background:#10B981; border-color:#10B981;">Dokumentasi</button>
                    <button type="button" @click="showDetailModal=false; openEdit(detailEditPayload)" data-tour-open-modal="edit" class="btn-secondary">Edit</button>
                    <button type="button" @click="openDelete(deleteRoute); showDetailModal=false" data-tour-open-modal="delete" class="btn-danger">Hapus</button>
                    <button type="button" @click="showDetailModal=false" class="btn-primary ml-auto">Tutup</button>
                </div>
            </div>
        </div>

        {{-- CREATE MODAL --}}
        <div x-show="showCreateModal" class="modal-overlay" style="display:none;">
            <div x-show="showCreateModal" x-transition class="modal-box" @click.away="showCreateModal=false">
                <form action="{{ route('admin.kegiatan.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header"><h3 class="section-title">Buat Jurnal Baru</h3></div>
                    <div class="modal-body space-y-4">
                        <div class="text-[13px] font-bold text-[#1A6B6B] mt-1 border-b pb-1" data-tour="modal-create-section-form">Data Jurnal</div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="input-label">Tanggal</label>
                                <input type="date" name="date" required value="{{ date('Y-m-d') }}" class="input-field">
                            </div>
                            <div>
                                <label class="input-label">Kelas</label>
                                <select name="kelas_id" required class="input-field">
                                    <option value="">-- Pilih Kelas --</option>
                                    @foreach($kelas as $k)
                                        <option value="{{ $k->id }}">{{ $k->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="input-label">Pengajar Terkait</label>
                            <select name="pengajar_id" required class="input-field">
                                <option value="">-- Pilih Pengajar --</option>
                                @foreach($pengajars as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="input-label">Judul Kegiatan</label>
                            <input type="text" name="title" required class="input-field" placeholder="Contoh: Belajar Mewarnai">
                        </div>
                        <div>
                            <label class="input-label">Deskripsi</label>
                            <textarea name="description" required rows="3" class="input-field"></textarea>
                        </div>
                        <div>
                            <label class="input-label">Indikator Matrikulasi</label>
                            <div class="mt-2 rounded-xl border max-h-48 overflow-y-auto p-2 space-y-1">
                                @foreach($matrikulasis as $m)
                                    <label class="flex items-start gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                        <input type="checkbox" name="matrikulasi_ids[]" value="{{ $m->id }}" class="mt-1 accent-teal-600">
                                        <span class="text-sm">[{{ $m->aspek }}] {{ $m->indicator }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" @click="showCreateModal=false" class="btn-secondary">Batal</button><button type="submit" data-tour="modal-create-submit" class="btn-primary">Simpan</button></div>
                </form>
            </div>
        </div>

        {{-- EDIT MODAL --}}
        <div x-show="showEditModal" class="modal-overlay" style="display:none;">
            <div x-show="showEditModal" x-transition class="modal-box" @click.away="showEditModal=false">
                <form :action="`/admin/kegiatan/${editData.id}`" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-header"><h3 class="section-title">Edit Jurnal</h3></div>
                    <div class="modal-body space-y-4">
                        <div class="text-[13px] font-bold text-[#1A6B6B] mt-1 border-b pb-1" data-tour="modal-edit-section-form">Data Jurnal</div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="input-label">Tanggal</label>
                                <input type="date" name="date" :value="editData.date" required class="input-field">
                            </div>
                            <div>
                                <label class="input-label">Kelas</label>
                                <select name="kelas_id" required class="input-field" x-model="editData.kelas_id">
                                    @foreach($kelas as $k)
                                        <option value="{{ $k->id }}">{{ $k->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="input-label">Pengajar</label>
                            <select name="pengajar_id" required class="input-field" x-model="editData.pengajar_id">
                                @foreach($pengajars as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="input-label">Judul</label>
                            <input type="text" name="title" x-model="editData.title" required class="input-field">
                        </div>
                        <div>
                            <label class="input-label">Deskripsi</label>
                            <textarea name="description" x-model="editData.description" required rows="3" class="input-field"></textarea>
                        </div>
                        <div>
                            <label class="input-label">Indikator</label>
                            <div class="mt-2 rounded-xl border max-h-48 overflow-y-auto p-2">
                                @foreach($matrikulasis as $m)
                                    <label class="flex items-start gap-2 p-1.5 hover:bg-gray-50 rounded">
                                        <input type="checkbox" name="matrikulasi_ids[]" value="{{ $m->id }}" x-model="editData.matrikulasi_ids" class="mt-1 accent-teal-600">
                                        <span class="text-sm">[{{ $m->aspek }}] {{ $m->indicator }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" @click="showEditModal=false" class="btn-secondary">Batal</button><button type="submit" data-tour="modal-edit-submit" class="btn-primary">Simpan Perubahan</button></div>
                </form>
            </div>
        </div>

        {{-- DELETE MODAL --}}
        <div x-show="showDeleteModal" data-tour="modal-delete" class="modal-overlay" style="display:none;">
            <div x-show="showDeleteModal" x-transition class="modal-box max-w-sm" @click.away="showDeleteModal=false">
                <form :action="deleteRoute" method="POST">
                    @csrf @method('DELETE')
                    <div class="modal-body text-center py-6">
                        <div class="h-14 w-14 rounded-2xl mx-auto mb-4 flex items-center justify-center bg-red-100 text-red-600">
                             <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </div>
                        <h3 class="section-title">Hapus Jurnal?</h3>
                    </div>
                    <div class="modal-footer"><button type="button" @click="showDeleteModal=false" class="btn-secondary">Batal</button><button type="submit" class="btn-danger">Ya, Hapus</button></div>
                </form>
            </div>
        </div>

        {{-- DOC MODAL (Documentation) --}}
        <div x-show="showDocModal || isUploading" class="modal-overlay" style="display:none;">
            <div x-show="showDocModal || isUploading" x-transition class="modal-box max-w-2xl" @click.away="!isUploading && (showDocModal=false)">
                <div x-show="isUploading" class="absolute inset-0 z-[60] bg-white/80 flex flex-col items-center justify-center">
                    <div class="h-10 w-10 border-4 border-teal-600/30 border-t-teal-600 rounded-full animate-spin"></div>
                    <p class="mt-3 text-sm font-bold text-teal-800">Memproses...</p>
                </div>
                <form :action="`/admin/kegiatan/${editData.id}`" method="POST" enctype="multipart/form-data" x-ref="docForm">
                    @csrf @method('PUT')
                    <input type="hidden" name="date" :value="editData.date">
                    <input type="hidden" name="title" :value="editData.title">
                    <input type="hidden" name="kelas_id" :value="editData.kelas_id">
                    <input type="hidden" name="pengajar_id" :value="editData.pengajar_id">
                    <input type="hidden" name="description" :value="editData.description">
                    <template x-for="mid in (editData.matrikulasi_ids || [])" :key="mid">
                        <input type="hidden" name="matrikulasi_ids[]" :value="mid">
                    </template>
                    <input type="file" name="photos[]" multiple class="hidden" x-ref="finalPhotosInput">
                    <template x-for="path in tempDeletedPhotos" :key="path">
                        <input type="hidden" name="delete_photos[]" :value="path">
                    </template>

                    <div class="modal-header"><h3 class="section-title">Kelola Dokumentasi</h3></div>
                    <div class="modal-body space-y-6">
                        <div class="p-6 border-2 border-dashed border-teal-200 bg-teal-50/30 rounded-2xl flex flex-col items-center w-full">
                            <div class="w-full max-w-md mb-4">
                                <x-documentation-upload-progress />
                            </div>
                            <label class="btn-primary cursor-pointer" :class="docUploadActive ? 'opacity-60 pointer-events-none' : ''">
                                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                Tambah Foto / Video
                                <input type="file" multiple accept="image/*,video/*" class="hidden" :disabled="docUploadActive" @change="addPhotos($event)">
                            </label>
                        </div>
                        <div class="grid grid-cols-4 gap-2">
                            <template x-for="(p, i) in tempNewPhotos" :key="i">
                                <div class="relative aspect-square border-2 border-teal-400 rounded-lg overflow-hidden group">
                                    <video x-show="p.kind === 'video'" :src="p.preview" class="w-full h-full object-cover" muted playsinline></video>
                                    <img x-show="p.kind !== 'video'" :src="p.preview" class="w-full h-full object-cover">
                                    <button type="button" @click="removeNewPhoto(i)" class="absolute top-0 right-0 p-1 bg-red-600 text-white opacity-0 group-hover:opacity-100"><svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg></button>
                                </div>
                            </template>
                            <template x-for="(url, i) in editData.photo_urls" :key="i">
                                <div class="relative aspect-square border rounded-lg overflow-hidden group" x-show="!tempDeletedPhotos.includes(editData.photo_urls_raw[i])">
                                    <img :src="url" class="w-full h-full object-cover">
                                    <button type="button" @click="removeExistingPhoto(editData.photo_urls_raw[i])" class="absolute top-0 right-0 p-1 bg-red-600 text-white opacity-0 group-hover:opacity-100"><svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div class="modal-footer flex justify-between">
                        <button type="button" @click="showDocModal=false" class="btn-secondary">Batal</button>
                        <button type="button" @click="submitDoc()" class="btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- PHOTO DELETE CONFIRM MODAL (Simple) --}}
        <div x-show="showPhotoDeleteModal" class="modal-overlay modal-overlay--front" style="display:none;">
             <div class="modal-box max-w-sm text-center py-8">
                 <div class="h-16 w-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
                     <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                 </div>
                 <h3 class="text-lg font-bold">Hapus Foto?</h3>
                 <div class="flex gap-2 mt-6">
                     <button @click="showPhotoDeleteModal=false" class="btn-secondary flex-1">Batal</button>
                     <button @click="executeDeletePhoto()" class="btn-danger flex-1">Ya, Hapus</button>
                 </div>
             </div>
        </div>

        <x-image-lightbox />

    </div>
</x-app-layout>
