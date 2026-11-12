<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background: #1A6B6B;"><svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.701 2.701 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18zm-3-9v-2a2 2 0 00-2-2H8a2 2 0 00-2 2v2h12z" /></svg></div>
            <h2 class="font-bold text-xl" style="color: #2C2C2C;">Kelola Menu Makanan</h2>
        </div>
    </x-slot>
    <div class="app-page-shell" x-data="{
        showCreateModal: false,
        showEditModal: false,
        showDetailModal: false,
        showDeleteModal: false,
        showImageModal: false,
        activeImage: null,
        activeDownloadUrl: null,
        editData: {},
        detailData: {},
        detailEditPayload: {},
        deleteRoute: '',
        menuLines: [{id: Date.now(), value: ''}],
        editMenuLines: [],
        parseMenuToLines(text) {
            if (!text || !String(text).trim()) return [{id: Date.now(), value: ''}];
            let t = String(text).trim();
            let parts = t.split(/\r?\n/).map(s => s.trim()).filter(Boolean);
            if (parts.length <= 1 && t.includes(' • ')) parts = t.split(' • ').map(s => s.trim()).filter(Boolean);
            if (parts.length <= 1 && t.includes(',')) parts = t.split(',').map(s => s.trim()).filter(Boolean);
            return parts.map(v => ({id: Math.random(), value: v}));
        },
        joinMenuLines(lines) {
            return lines.map(l => String(l.value || '').trim()).filter(Boolean).join('\n');
        },
        openCreateModal() {
            this.menuLines = [{id: Date.now(), value: ''}];
            this.showCreateModal = true;
        },
        openEdit(d) {
            this.editData = d;
            this.editMenuLines = this.parseMenuToLines(d.menu);
            this.showEditModal = true;
        },
        openDetail(d) {
            this.detailData = d;
            this.showDetailModal = true;
        },
        openDetailFromCal(detail, edit) {
            this.detailData = detail;
            this.detailEditPayload = edit || {};
            this.showDetailModal = true;
        },
        onCalClick(payload) {
            const p = payload.extendedProps || {};
            if (p.mode !== 'menu_admin') return;
            this.openDetailFromCal(p.detail, p.edit);
            this.deleteRoute = p.delete_url || '';
        },
        addMenuLine() { this.menuLines.push({id: Date.now(), value: ''}); },
        removeMenuLine(i) { if (this.menuLines.length > 1) this.menuLines.splice(i, 1); },
        addEditMenuLine() { this.editMenuLines.push({id: Date.now(), value: ''}); },
        removeEditMenuLine(i) { if (this.editMenuLines.length > 1) this.editMenuLines.splice(i, 1); },
        openDelete(r) { this.deleteRoute = r; this.showDeleteModal = true; },
        isCompressing: false,
        compressedFiles: {},
        async handleFile(e, key) {
            const file = e.target.files[0];
            if (!file) return;
            this.isCompressing = true;
            try {
                this.compressedFiles[key] = await window.compressImage(file);
            } catch(err) {
                console.error(err);
            } finally {
                this.isCompressing = false;
            }
        },
        submitForm(formRef, lines) {
            if (!this.joinMenuLines(lines)) {
                alert('Daftar menu tidak boleh kosong.');
                return;
            }
            const form = this.$refs[formRef];
            Object.keys(this.compressedFiles).forEach(name => {
                if (this.compressedFiles[name]) {
                    const dt = new DataTransfer();
                    dt.items.add(this.compressedFiles[name]);
                    const input = form.querySelector(`input[name='${name}']`);
                    if (input) input.files = dt.files;
                }
            });
            form.submit();
        }
    }" @kegiatan-cal-click.window="onCalClick($event.detail)" @tour-close-modals.window="showCreateModal=false; showEditModal=false; showDetailModal=false; showDeleteModal=false; showImageModal=false">
        @if(session('success'))<div class="alert-success mb-5"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert-danger mb-5"><ul class="list-disc pl-5 text-sm">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul></div>@endif
        <div class="card overflow-hidden mb-6" data-tour="admin-menu-calendar">
            <div class="px-4 sm:px-6 py-4 border-b" style="border-color:rgba(0,0,0,0.06);">
                <h3 class="section-title">Kalender Menu Makanan</h3>
                <p class="section-subtitle">Navigasi bulan seperti Agenda Belajar — klik entri untuk detail, edit, atau hapus.</p>
            </div>
            <form method="get" data-tour="admin-menu-filter" class="card-pad toolbar-form border-b w-full" style="border-color:rgba(0,0,0,0.06);">
                <input type="hidden" name="year" value="{{ $year }}">
                <input type="hidden" name="month" value="{{ $month }}">
                <input type="hidden" name="view" value="{{ request('view', 'listMonth') }}">
                @if(request('day'))
                    <input type="hidden" name="day" value="{{ request('day') }}">
                @endif
                <div class="toolbar-field md:min-w-[12rem] md:flex-[2]">
                    <label class="input-label">Cari Menu</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="input-field w-full"
                        placeholder="Nama hidangan atau info gizi…">
                </div>
                <div class="toolbar-field md:min-w-[9rem] md:max-w-[11rem]">
                    <label class="input-label">Filter Foto</label>
                    <select name="foto" class="input-field w-full" onchange="this.form.submit()">
                        <option value="" @selected(!request('foto'))>Semua</option>
                        <option value="ada" @selected(request('foto') === 'ada')>Ada foto</option>
                        <option value="tanpa" @selected(request('foto') === 'tanpa')>Tanpa foto</option>
                    </select>
                </div>
                <div class="toolbar-actions">
                    <x-toolbar.filter-icon label="Cari" />
                    <x-filter-reset :href="route('admin.menu-makanan.index', ['view' => 'listMonth', 'year' => $year, 'month' => $month])" compact />
                    @if(auth()->user()->hasRole('Admin Sekolah'))
                        <x-toolbar.add-icon label="Input Menu" data-tour="admin-menu-add-btn" data-tour-open-modal="create" type="button" @click="openCreateModal()" />
                    @endif
                </div>
            </form>
            <div class="p-4 md:p-6">
                <x-jurnal-kalender :events="$calendarEvents" :year="$year" :month="$month" />
            </div>
        </div>
        <!-- DETAIL MODAL -->
        <div x-show="showDetailModal" data-tour="modal-detail" class="modal-overlay" style="display:none;">
            <div x-show="showDetailModal" x-transition class="modal-box max-w-2xl" @click.away="showDetailModal=false">
                <div class="modal-header flex items-center justify-between">
                    <h3 class="section-title">Detail Menu Makanan</h3>
                    <button type="button" @click="showDetailModal=false" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>
                <div class="modal-body space-y-5" data-tour="modal-detail-content">
                    <div class="flex items-center gap-2">
                        <p class="text-lg font-bold" style="color:#2C2C2C;" x-text="detailData.date || ''"></p>
                        <span x-show="detailData.is_today" class="badge badge-green">Hari Ini</span>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide mb-2" style="color:#9E9790;">Daftar Menu</p>
                        <p class="text-sm whitespace-pre-line leading-relaxed" style="color:#2C2C2C;" x-text="detailData.menu || ''"></p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide mb-1" style="color:#9E9790;">Informasi Gizi</p>
                        <p class="text-sm" style="color:#6B6560;" x-text="detailData.nutrition_info || '-'"></p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" x-show="detailData.photo_url || detailData.photo_kegiatan_url">
                        <template x-if="detailData.photo_url">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide mb-2" style="color:#9E9790;">Foto Makanan</p>
                                <img :src="detailData.photo_url" class="w-full rounded-xl object-cover max-h-48 border border-gray-100 cursor-pointer" alt="" @click="activeImage = detailData.photo_url; activeDownloadUrl = detailData.photo_download_url; showImageModal = true">
                            </div>
                        </template>
                        <template x-if="detailData.photo_kegiatan_url">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide mb-2" style="color:#9E9790;">Foto Kegiatan Makan</p>
                                <img :src="detailData.photo_kegiatan_url" class="w-full rounded-xl object-cover max-h-48 border border-gray-100 cursor-pointer" alt="" @click="activeImage = detailData.photo_kegiatan_url; activeDownloadUrl = detailData.photo_kegiatan_download_url; showImageModal = true">
                            </div>
                        </template>
                    </div>
                    <div class="flex items-center gap-4 pt-2">
                        <span class="inline-flex items-center gap-1 text-xs font-bold text-teal-700 bg-teal-50 px-2 py-1 rounded-md">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 10h4.757c1.246 0 2.228 1.053 2.115 2.285l-1.157 12.63c-.105 1.157-1.077 2.085-2.238 2.085H6.115c-1.161 0-2.133-.928-2.238-2.085L2.72 12.285C2.607 11.053 3.589 10 4.835 10H8.5l.5-5a3 3 0 013 3v2h2z" /></svg>
                            <span x-text="detailData.likes_count ?? 0"></span> Suka
                        </span>
                        <span class="inline-flex items-center gap-1 text-xs font-bold text-orange-700 bg-orange-50 px-2 py-1 rounded-md">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14H5.243c-1.246 0-2.228-1.053-2.115-2.285l1.157-12.63C4.39 1.157 5.362.23 6.523.23h11.362c1.161 0 2.133.928 2.238 2.085l1.157 12.63c.113 1.232-.869 2.285-2.115 2.285H15.5l-.5 5a3 3 0 01-3-3v-2h-2z" /></svg>
                            <span x-text="detailData.dislikes_count ?? 0"></span> Tidak Suka
                        </span>
                    </div>
                </div>
                <div class="modal-footer flex flex-wrap gap-2">
                    @if(auth()->user()->hasRole('Admin Sekolah'))
                        <button type="button" @click="showDetailModal=false; openEdit(detailEditPayload)" data-tour-open-modal="edit" class="btn-secondary">Edit</button>
                        <button type="button" @click="openDelete(deleteRoute); showDetailModal=false" data-tour-open-modal="delete" class="btn-danger">Hapus</button>
                    @endif
                    <button type="button" @click="showDetailModal=false" class="btn-primary ml-auto w-full sm:w-auto">Tutup</button>
                </div>
            </div>
        </div>
        <!-- CREATE MODAL -->
        <div x-show="showCreateModal" class="modal-overlay" style="display:none;">
            <div x-show="showCreateModal" x-transition class="modal-box relative overflow-hidden" @click.away="!isCompressing && (showCreateModal=false)">
                {{-- Compressing Overlay --}}
                <div x-show="isCompressing" class="absolute inset-0 z-[60] bg-white/90 backdrop-blur-[4px] flex flex-col items-center justify-center text-center p-6">
                    <div class="h-12 w-12 border-4 border-teal-600/30 border-t-teal-600 rounded-full animate-spin"></div>
                    <p class="mt-4 text-sm font-bold text-teal-800 uppercase tracking-wider">Mengecilkan Ukuran Foto...</p>
                </div>
                <form action="{{ route('admin.menu-makanan.store') }}" method="POST" enctype="multipart/form-data" x-ref="createForm" @submit.prevent="submitForm('createForm', menuLines)">
                    @csrf
                    <input type="hidden" name="year" value="{{ $year }}">
                    <input type="hidden" name="month" value="{{ $month }}">
                    <input type="hidden" name="view" value="{{ request('view', 'listMonth') }}">
                    <input type="hidden" name="menu" :value="joinMenuLines(menuLines)">
                    <div class="modal-header"><h3 class="section-title">Input Jadwal Menu Baru</h3></div>
                    <div class="modal-body space-y-4">
                        <div class="text-[13px] font-bold text-[#1A6B6B] mt-1 border-b pb-1" data-tour="modal-create-section-form">Data Menu</div>
                        <div><label class="input-label">Tanggal</label><input type="date" name="date" required value="{{ date('Y-m-d') }}" class="input-field"></div>
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <label class="input-label mb-0">Daftar hidangan <span class="text-red-500">*</span></label>
                                <button type="button" @click="addMenuLine()" class="text-xs font-semibold px-2.5 py-1 rounded-lg inline-flex items-center gap-1" style="color:#1A6B6B;background:#D0E8E8;">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                                    Tambah
                                </button>
                            </div>
                            <p class="text-xs mb-2" style="color:#9E9790;">Satu baris per hidangan. Klik Tambah untuk menambah baris baru.</p>
                            <div class="space-y-2">
                                <template x-for="(line, index) in menuLines" :key="line.id">
                                    <div class="flex gap-2 items-center">
                                        <input type="text" class="input-field flex-1" x-model="line.value" placeholder="Contoh: Nasi putih, Ayam goreng…">
                                        <button type="button" @click="removeMenuLine(index)" class="shrink-0 text-xs font-semibold px-2 py-2 rounded-lg disabled:opacity-40" style="color:#C0392B;background:#FAD7D2;" :disabled="menuLines.length <= 1" title="Hapus baris">Hapus</button>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <div><label class="input-label">Informasi Gizi & Catatan Alergi</label><textarea name="nutrition_info" rows="2" class="input-field" placeholder="Tanpa kacang, tinggi protein..."></textarea></div>
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="input-label">Foto Makanan (opsional)</label><input type="file" name="photo" accept="image/*" class="input-field py-2" @change="handleFile($event, 'photo')"></div>
                            <div><label class="input-label">Foto Kegiatan Makan</label><input type="file" name="photo_kegiatan" accept="image/*" class="input-field py-2" @change="handleFile($event, 'photo_kegiatan')"></div>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" @click="showCreateModal=false" class="btn-secondary">Batal</button><button type="submit" data-tour="modal-create-submit" class="btn-primary" :disabled="!joinMenuLines(menuLines) || isCompressing">Simpan Menu</button></div>
                </form>
            </div>
        </div>
        <!-- EDIT MODAL -->
        <div x-show="showEditModal" class="modal-overlay" style="display:none;">
            <div x-show="showEditModal" x-transition class="modal-box relative overflow-hidden" @click.away="!isCompressing && (showEditModal=false)">
                {{-- Compressing Overlay --}}
                <div x-show="isCompressing" class="absolute inset-0 z-[60] bg-white/90 backdrop-blur-[4px] flex flex-col items-center justify-center text-center p-6">
                    <div class="h-12 w-12 border-4 border-teal-600/30 border-t-teal-600 rounded-full animate-spin"></div>
                    <p class="mt-4 text-sm font-bold text-teal-800 uppercase tracking-wider">Mengecilkan Ukuran Foto...</p>
                </div>
                <form :action="`/admin/menu-makanan/${editData.id}`" method="POST" enctype="multipart/form-data" x-ref="editForm" @submit.prevent="submitForm('editForm', editMenuLines)">
                    @csrf @method('PUT')
                    <input type="hidden" name="year" value="{{ $year }}">
                    <input type="hidden" name="month" value="{{ $month }}">
                    <input type="hidden" name="view" value="{{ request('view', 'listMonth') }}">
                    <input type="hidden" name="menu" :value="joinMenuLines(editMenuLines)">
                    <div class="modal-header"><h3 class="section-title">Edit Menu Makanan</h3></div>
                    <div class="modal-body space-y-4">
                        <div class="text-[13px] font-bold text-[#1A6B6B] mt-1 border-b pb-1" data-tour="modal-edit-section-form">Data Menu</div>
                        <div><label class="input-label">Tanggal</label><input type="date" name="date" :value="editData.date ? editData.date.split('T')[0] : ''" required class="input-field"></div>
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <label class="input-label mb-0">Daftar hidangan <span class="text-red-500">*</span></label>
                                <button type="button" @click="addEditMenuLine()" class="text-xs font-semibold px-2.5 py-1 rounded-lg inline-flex items-center gap-1" style="color:#1A6B6B;background:#D0E8E8;">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                                    Tambah
                                </button>
                            </div>
                            <div class="space-y-2">
                                <template x-for="(line, index) in editMenuLines" :key="line.id">
                                    <div class="flex gap-2 items-center">
                                        <input type="text" class="input-field flex-1" x-model="line.value" placeholder="Nama hidangan">
                                        <button type="button" @click="removeEditMenuLine(index)" class="shrink-0 text-xs font-semibold px-2 py-2 rounded-lg disabled:opacity-40" style="color:#C0392B;background:#FAD7D2;" :disabled="editMenuLines.length <= 1">Hapus</button>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <div><label class="input-label">Informasi Gizi</label><textarea name="nutrition_info" x-model="editData.nutrition_info" rows="2" class="input-field"></textarea></div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="input-label">Foto Makanan Saat Ini</label>
                                <div class="mb-2 h-24 w-full rounded-lg border-2 border-dashed border-gray-200 overflow-hidden bg-gray-50 flex items-center justify-center">
                                    <template x-if="editData.photo">
                                        <img :src="'/storage/' + editData.photo" class="h-full w-full object-cover">
                                    </template>
                                    <template x-if="!editData.photo">
                                        <span class="text-[10px] text-gray-400">Tidak ada foto</span>
                                    </template>
                                </div>
                                <label class="input-label mt-2">Ganti Foto Makanan</label>
                                <input type="file" name="photo" accept="image/*" class="input-field py-2" @change="handleFile($event, 'photo')">
                            </div>
                            <div>
                                <label class="input-label">Foto Kegiatan Saat Ini</label>
                                <div class="mb-2 h-24 w-full rounded-lg border-2 border-dashed border-gray-200 overflow-hidden bg-gray-50 flex items-center justify-center">
                                    <template x-if="editData.photo_kegiatan">
                                        <img :src="'/storage/' + editData.photo_kegiatan" class="h-full w-full object-cover">
                                    </template>
                                    <template x-if="!editData.photo_kegiatan">
                                        <span class="text-[10px] text-gray-400">Tidak ada foto</span>
                                    </template>
                                </div>
                                <label class="input-label mt-2">Ganti Foto Kegiatan</label>
                                <input type="file" name="photo_kegiatan" accept="image/*" class="input-field py-2" @change="handleFile($event, 'photo_kegiatan')">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" @click="showEditModal=false" class="btn-secondary">Batal</button><button type="submit" data-tour="modal-edit-submit" class="btn-primary" :disabled="!joinMenuLines(editMenuLines) || isCompressing">Simpan Perubahan</button></div>
                </form>
            </div>
        </div>
        <!-- DELETE MODAL -->
        <div x-show="showDeleteModal" data-tour="modal-delete" class="modal-overlay" style="display:none;">
            <div x-show="showDeleteModal" x-transition class="modal-box max-w-sm" @click.away="showDeleteModal=false">
                <form :action="deleteRoute" method="POST">
                    @csrf @method('DELETE')
                    <input type="hidden" name="year" value="{{ $year }}">
                    <input type="hidden" name="month" value="{{ $month }}">
                    <input type="hidden" name="view" value="{{ request('view', 'listMonth') }}">
                    <div class="modal-body text-center py-6"><div class="h-14 w-14 rounded-2xl mx-auto mb-4 flex items-center justify-center" style="background:#FAD7D2;"><svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:#C0392B;"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></div><h3 class="section-title">Hapus Menu?</h3><p class="section-subtitle mt-1">Data jadwal menu ini akan dihapus secara permanen.</p></div>
                    <div class="modal-footer"><button type="button" @click="showDeleteModal=false" class="btn-secondary">Batal</button><button type="submit" class="btn-danger">Ya, Hapus</button></div>
                </form>
            </div>
        </div>
        <x-image-lightbox />
    </div>
</x-app-layout>
