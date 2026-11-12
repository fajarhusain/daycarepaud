<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background: #1A6B6B;"><svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg></div>
            <h2 class="font-bold text-xl" style="color: #2C2C2C;">Kelola Cabang Sekolah</h2>
        </div>
    </x-slot>
    <div class="app-page-shell" x-data="{ showCreateModal:false, showEditModal:false, showDeleteModal:false, editData:{}, deleteRoute:'', openEdit(d){this.editData=d;this.showEditModal=true}, openDelete(r){this.deleteRoute=r;this.showDeleteModal=true} }" @tour-close-modals.window="showCreateModal=false; showEditModal=false; showDeleteModal=false">
        @if(session('success'))<div class="alert-success mb-5"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert-danger mb-5"><ul class="list-disc pl-5 text-sm">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul></div>@endif
        <div class="card overflow-hidden">
            <div class="card-pad page-toolbar border-b" style="border-color:rgba(0,0,0,0.06);">
                <div><h3 class="section-title">Daftar Sekolah / Cabang</h3><p class="section-subtitle">Kelola semua cabang daycare & PAUD di bawah yayasan Anda</p></div>
                <button data-tour="lembaga-sekolah-add-btn" data-tour-open-modal="create" @click="showCreateModal=true" class="btn-primary shrink-0 h-9 w-9 md:h-11 md:w-11 p-0" title="Tambah Sekolah" aria-label="Tambah Sekolah"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg></button>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead><tr><th>Nama Sekolah</th><th>Alamat</th><th>No. Telepon</th><th class="text-right">Aksi</th></tr></thead>
                    <tbody>
                        @forelse($sekolahs as $s)
                        <tr>
                            <td><div class="flex items-center gap-3"><div class="h-8 w-8 rounded-xl flex items-center justify-center font-bold text-sm text-white shrink-0" style="background:#1A6B6B;">{{ substr($s->name, 0, 1) }}</div><span class="font-semibold" style="color:#2C2C2C;">{{ $s->name }}</span></div></td>
                            <td>{{ $s->address ?? '-' }}</td>
                            <td>{{ $s->phone ?? '-' }}</td>
                            <td class="text-right"><div class="flex items-center justify-end gap-2">
                                <button @if($loop->first) data-tour="lembaga-sekolah-action-edit" data-tour-open-modal="edit" @endif @click="openEdit({{ json_encode($s) }})" class="row-action-icon" style="color:#1A6B6B;background:#D0E8E8;" title="Edit" aria-label="Edit"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                                <button @if($loop->first) data-tour="lembaga-sekolah-action-delete" data-tour-demo-action="delete" @endif @click="openDelete('{{ route('lembaga.sekolah.destroy', $s) }}')" class="row-action-icon" style="color:#C0392B;background:#FAD7D2;" title="Hapus" aria-label="Hapus"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            </div></td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="py-6 md:py-12 text-center" style="color:#9E9790;">Belum ada cabang sekolah terdaftar.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t" style="border-color:rgba(0,0,0,0.06);">
                <x-per-page-selector :paginator="$sekolahs" />
                {{ $sekolahs->links() }}
            </div>
        </div>
        <!-- CREATE MODAL -->
        <div x-show="showCreateModal" class="modal-overlay" style="display:none;">
            <div x-show="showCreateModal" x-transition class="modal-box" @click.away="showCreateModal=false">
                <form action="{{ route('lembaga.sekolah.store') }}" method="POST">
                    @csrf
                    <div class="modal-header"><h3 class="section-title">Tambah Sekolah / Cabang Baru</h3></div>
                    <div class="modal-body space-y-4">
                        <div class="text-[13px] font-bold text-[#1A6B6B] mt-1 border-b pb-1" data-tour="modal-create-section-form">Data Sekolah</div>
                        <div>
                            <label class="input-label">Nama Sekolah</label>
                            <input type="text" name="name" required class="input-field @error('name') border-red-500 @enderror" placeholder="PAUD Pelita Kasih Cabang A" value="{{ old('name') }}">
                            @error('name')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="input-label">Alamat Lengkap</label>
                            <textarea name="address" rows="2" class="input-field @error('address') border-red-500 @enderror" placeholder="Jl. ...">{{ old('address') }}</textarea>
                            @error('address')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="input-label">Nomor Telepon</label>
                            <input type="text" name="phone" class="input-field @error('phone') border-red-500 @enderror" placeholder="021-..." value="{{ old('phone') }}">
                            @error('phone')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" @click="showCreateModal=false" class="btn-secondary">Batal</button><button type="submit" data-tour="modal-create-submit" class="btn-primary">Simpan</button></div>
                </form>
            </div>
        </div>
        <!-- EDIT MODAL -->
        <div x-show="showEditModal" class="modal-overlay" style="display:none;">
            <div x-show="showEditModal" x-transition class="modal-box" @click.away="showEditModal=false">
                <form :action="`/lembaga/sekolah/${editData.id}`" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-header"><h3 class="section-title">Edit Data Sekolah</h3></div>
                    <div class="modal-body space-y-4">
                        <div class="text-[13px] font-bold text-[#1A6B6B] mt-1 border-b pb-1" data-tour="modal-edit-section-form">Data Sekolah</div>
                        <div>
                            <label class="input-label">Nama Sekolah</label>
                            <input type="text" name="name" x-model="editData.name" required class="input-field @error('name') border-red-500 @enderror">
                            @error('name')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="input-label">Alamat Lengkap</label>
                            <textarea name="address" x-model="editData.address" rows="2" class="input-field @error('address') border-red-500 @enderror"></textarea>
                            @error('address')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="input-label">Nomor Telepon</label>
                            <input type="text" name="phone" x-model="editData.phone" class="input-field @error('phone') border-red-500 @enderror">
                            @error('phone')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" @click="showEditModal=false" class="btn-secondary">Batal</button><button type="submit" data-tour="modal-edit-submit" class="btn-primary">Simpan Perubahan</button></div>
                </form>
            </div>
        </div>
        <!-- DELETE MODAL -->
        <div x-show="showDeleteModal" data-tour="modal-delete" class="modal-overlay" style="display:none;">
            <div x-show="showDeleteModal" x-transition class="modal-box max-w-sm" @click.away="showDeleteModal=false">
                <form :action="deleteRoute" method="POST">
                    @csrf @method('DELETE')
                    <div class="modal-body text-center py-6"><div class="h-14 w-14 rounded-2xl mx-auto mb-4 flex items-center justify-center" style="background:#FAD7D2;"><svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:#C0392B;"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></div><h3 class="section-title">Hapus Sekolah?</h3><p class="section-subtitle mt-1">Semua data yang berkaitan dengan sekolah ini dapat terpengaruh.</p></div>
                    <div class="modal-footer"><button type="button" @click="showDeleteModal=false" class="btn-secondary">Batal</button><button type="submit" class="btn-danger">Ya, Hapus</button></div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
