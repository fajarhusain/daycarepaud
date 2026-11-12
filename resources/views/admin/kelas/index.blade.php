<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background: #1A6B6B;"><svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg></div>
            <h2 class="font-bold text-xl" style="color: #2C2C2C;">Kelola Kelas & Ruang Lingkup</h2>
        </div>
    </x-slot>
    <div class="app-page-shell" x-data="{
        showCreateModal:false,
        showEditModal:false,
        showDeleteModal:false,
        showDetailModal:false,
        detailTitle:'',
        detailHtml:'',
        detailLoading:false,
        detailError:'',
        editData:{},
        deleteRoute:'',
        openEdit(d){this.editData=d;this.showEditModal=true},
        openDelete(r){this.deleteRoute=r;this.showDeleteModal=true},
        async openDetail(url,title){
            this.detailTitle=title;
            this.showDetailModal=true;
            this.detailLoading=true;
            this.detailError='';
            this.detailHtml='';
            try{
                const r=await fetch(url,{headers:{'X-Requested-With':'XMLHttpRequest','Accept':'text/html'},credentials:'same-origin'});
                if(!r.ok) throw new Error(r.status===403?'Tidak memiliki akses.':(r.status===404?'Kelas tidak ditemukan.':'Gagal memuat data.'));
                this.detailHtml=await r.text();
            }catch(e){this.detailError=e.message||'Gagal memuat data.';}
            finally{
                this.detailLoading=false;
                requestAnimationFrame(()=>requestAnimationFrame(()=>{
                    if(this.$refs.detailScroll) this.$refs.detailScroll.scrollTop=0;
                }));
            }
        },
        closeDetail(){this.showDetailModal=false;this.detailHtml='';this.detailError='';this.detailTitle='';}
    }" @tour-close-modals.window="showCreateModal=false; showEditModal=false; showDeleteModal=false; showDetailModal=false">
        @if(session('success'))<div class="alert-success mb-5"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert-danger mb-5"><ul class="list-disc pl-5 text-sm">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul></div>@endif
        <div class="card overflow-hidden">
            <div class="card-pad page-toolbar border-b" style="border-color:rgba(0,0,0,0.06);">
                <div><h3 class="section-title">Daftar Kelas</h3><p class="section-subtitle">Struktur pembagian kelas beserta admin pengelolanya</p></div>
                <div class="toolbar-actions">
                    <x-export-excel route="admin.kelas.export" />
                    <button data-tour="admin-kelas-add-btn" data-tour-open-modal="create" @click="showCreateModal=true" class="btn-primary toolbar-primary shrink-0 h-9 w-9 md:h-11 md:w-11 p-0" title="Buat Kelas Baru" aria-label="Buat Kelas Baru"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg></button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead><tr><th>Nama Kelas</th><th>Wali Kelas</th><th>Deskripsi</th><th>Jumlah Siswa</th><th class="text-right">Aksi</th></tr></thead>
                    <tbody>
                        @forelse($kelasList as $k)
                        <tr>
                            <td>
                                <span class="font-semibold" style="color:#2C2C2C;">{{ $k->name }}</span>
                            </td>
                            <td>
                                @if($k->waliKelas)
                                    <div class="flex items-center gap-2">
                                        <x-foto-profil :path="$k->waliKelas->photo" :name="$k->waliKelas->name" size="xs" />
                                        <span class="text-sm font-medium" style="color:#2C2C2C;">{{ $k->waliKelas->name }}</span>
                                    </div>
                                @else
                                    <span class="text-xs italic" style="color:#9E9790;">Belum ditentukan</span>
                                @endif
                            </td>
                            <td><span class="text-sm" style="color:#5A5A5A;">{{ $k->description ?: '-' }}</span></td>
                            <td><span class="badge badge-teal">{{ $k->anaks_count }} Siswa</span></td>
                            <td class="text-right"><div class="flex items-center justify-end gap-2 flex-wrap">
                                @php
                                    $kelasEditPayload = [
                                        'id' => $k->id,
                                        'name' => $k->name,
                                        'description' => $k->description,
                                        'wali_id' => $k->wali_kelas_id,
                                        'pengajar_ids' => $k->pengajars->pluck('id')->toArray(),
                                    ];
                                @endphp
                                <button type="button" @if($loop->first) data-tour="admin-kelas-action-detail" data-tour-open-modal="detail" @endif @click="openDetail(@js(route('admin.kelas.siswa-modal', $k)), @js($k->name))" class="row-action-icon" style="color:#1A6B6B;background:#E8F5F5;" title="Detail" aria-label="Detail"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button>
                                <button type="button" @if($loop->first) data-tour="admin-kelas-action-edit" data-tour-open-modal="edit" @endif @click="openEdit(@js($kelasEditPayload))" class="row-action-icon" style="color:#1A6B6B;background:#D0E8E8;" title="Edit" aria-label="Edit"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                                <button @if($loop->first) data-tour="admin-kelas-action-delete" data-tour-demo-action="delete" @endif @click="openDelete('{{ route('admin.kelas.destroy', $k->id) }}')" class="row-action-icon" style="color:#C0392B;background:#FAD7D2;" title="Hapus" aria-label="Hapus"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            </div></td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="py-6 md:py-12 text-center" style="color:#9E9790;">Belum ada kelas yang dibuat.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t" style="border-color:rgba(0,0,0,0.06);">
                <x-per-page-selector :paginator="$kelasList" />
                {{ $kelasList->links() }}
            </div>
        </div>

        <!-- CREATE MODAL -->
        <div x-show="showCreateModal" class="modal-overlay" style="display:none;">
            <div x-show="showCreateModal" x-transition class="modal-box" @click.away="showCreateModal=false">
                <form action="{{ route('admin.kelas.store') }}" method="POST">
                    @csrf
                    <div class="modal-header"><h3 class="section-title">Buat Kelas Baru</h3></div>
                    <div class="modal-body space-y-4">
                        <div class="text-[13px] font-bold text-[#1A6B6B] mt-1 border-b pb-1" data-tour="modal-create-section-form">Data Kelas</div>
                        <div>
                            <label class="input-label">Nama Kelas</label>
                            <input type="text" name="name" required class="input-field @error('name') border-red-500 @enderror" placeholder="Contoh: Kelas TK A - Mawar" value="{{ old('name') }}">
                            @error('name')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="input-label">Deskripsi (Opsional)</label>
                            <textarea name="description" class="input-field @error('description') border-red-500 @enderror" rows="2">{{ old('description') }}</textarea>
                            @error('description')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="p-3 rounded-xl bg-orange-50 border border-orange-100">
                           <p class="text-[11px] text-orange-700 leading-relaxed font-medium">
                               <svg class="h-3.5 w-3.5 inline mr-1 -mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                               Wali Kelas ditambahkan setelah kelas dibuat melalui tombol <b>Edit</b>, dan hanya bisa memilih pengajar yang sudah ditempatkan di kelas ini.
                           </p>
                        </div>
                        

                    </div>
                    <div class="modal-footer"><button type="button" @click="showCreateModal=false" class="btn-secondary">Batal</button><button type="submit" data-tour="modal-create-submit" class="btn-primary">Buat Kelas</button></div>
                </form>
            </div>
        </div>

        <!-- EDIT MODAL -->
        <div x-show="showEditModal" class="modal-overlay" style="display:none;">
            <div x-show="showEditModal" x-transition class="modal-box" @click.away="showEditModal=false">
                <form :action="`/admin/kelas/${editData.id}`" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-header"><h3 class="section-title">Edit Data Kelas</h3></div>
                    <div class="modal-body space-y-4">
                        <div class="text-[13px] font-bold text-[#1A6B6B] mt-1 border-b pb-1" data-tour="modal-edit-section-form">Data Kelas</div>
                        <div>
                            <label class="input-label">Nama Kelas</label>
                            <input type="text" name="name" x-model="editData.name" required class="input-field @error('name') border-red-500 @enderror">
                            @error('name')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="input-label">Deskripsi</label>
                            <textarea name="description" x-model="editData.description" rows="2" class="input-field @error('description') border-red-500 @enderror"></textarea>
                            @error('description')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="input-label">Tentukan Wali Kelas</label>
                            <select name="wali_kelas_id" x-model="editData.wali_id" class="input-field @error('wali_kelas_id') border-red-500 @enderror">
                                <option value=""> Pilih Guru </option>
                                @foreach($pengajars as $p)
                                    <template x-if="editData.pengajar_ids && editData.pengajar_ids.includes({{ $p->id }})">
                                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                                    </template>
                                @endforeach
                            </select>
                            @error('wali_kelas_id')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                            <div class="mt-1.5 p-2 rounded-lg bg-teal-50 border border-teal-100" x-show="editData.pengajar_ids && editData.pengajar_ids.length > 0">
                                <p class="text-[10px] text-teal-800">Hanya menampilkan guru yang terdaftar di kelas ini.</p>
                            </div>
                            <div class="mt-1.5 p-2 rounded-lg bg-red-50 border border-red-100" x-show="!editData.pengajar_ids || editData.pengajar_ids.length === 0">
                                <p class="text-[10px] text-red-700">Belum ada guru di kelas ini. Tambahkan guru di menu <b>Data Guru</b> dulu.</p>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer"><button type="button" @click="showEditModal=false" class="btn-secondary">Batal</button><button type="submit" data-tour="modal-edit-submit" class="btn-primary">Simpan Perubahan</button></div>
                </form>
            </div>
        </div>

        <!-- DETAIL SISWA (MODAL) -->
        <div x-show="showDetailModal" data-tour="modal-detail" class="modal-overlay modal-overlay--elevated" style="display:none;">
            <div x-show="showDetailModal" x-transition class="modal-box max-w-4xl w-full" @click.away="closeDetail()">
                <div class="modal-header shrink-0 flex items-start justify-between gap-3">
                    <div>
                        <h3 class="section-title">Detail kelas</h3>
                        <p class="text-sm font-semibold mt-0.5" style="color:#1A6B6B;" x-text="detailTitle"></p>
                    </div>
                    <button type="button" @click="closeDetail()" class="shrink-0 p-1.5 rounded-lg hover:bg-black/5 transition" aria-label="Tutup">
                        <svg class="h-5 w-5" style="color:#5A5A5A;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div x-ref="detailScroll" class="modal-body overflow-x-auto scroll-smooth pt-2 [scrollbar-gutter:stable]">
                    <div x-show="detailLoading" class="flex flex-col items-center justify-center py-16 gap-3" style="display:none; color:#5A5A5A;">
                        <svg class="h-8 w-8 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span class="text-sm">Memuat daftar siswa…</span>
                    </div>
                    <div x-show="!detailLoading && detailError" class="rounded-xl p-4 text-sm text-center" style="display:none; background:#FAD7D2;color:#C0392B;" x-text="detailError"></div>
                    <div x-show="!detailLoading && !detailError && detailHtml" x-html="detailHtml" style="display:none;"></div>
                </div>
                <div class="modal-footer shrink-0">
                    <button type="button" @click="closeDetail()" class="btn-secondary">Tutup</button>
                </div>
            </div>
        </div>

        <!-- DELETE MODAL -->
        <div x-show="showDeleteModal" data-tour="modal-delete" class="modal-overlay" style="display:none;">
            <div x-show="showDeleteModal" x-transition class="modal-box max-w-sm" @click.away="showDeleteModal=false">
                <form :action="deleteRoute" method="POST">
                    @csrf @method('DELETE')
                    <div class="modal-body text-center py-6"><div class="h-14 w-14 rounded-2xl mx-auto mb-4 flex items-center justify-center" style="background:#FAD7D2;"><svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:#C0392B;"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></div>
                    <h3 class="section-title">Hapus Kelas?</h3><p class="section-subtitle mt-1">Kelas ini akan dihapus permanen. Siswa di kelas ini tetap ada namun tanpa kelas (Null).</p></div>
                    <div class="modal-footer"><button type="button" @click="showDeleteModal=false" class="btn-secondary">Batal</button><button type="submit" class="btn-danger">Ya, Hapus</button></div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
