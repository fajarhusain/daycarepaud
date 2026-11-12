<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background: #1A6B6B;"><svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg></div>
            <h2 class="font-bold text-xl" style="color: #2C2C2C;">Kelola Akun Admin Sekolah</h2>
        </div>
    </x-slot>
    <div class="app-page-shell" x-data="{ 
        showCreateModal:@js($errors->any() && old('_method') !== 'PUT'), 
        showEditModal:@js($errors->any() && old('_method') === 'PUT'),
        showDeleteModal:false, 
        deleteRoute:'', 
        editRoute:'',
        editData: {id: '', name: '', email: '', sekolah_id: ''},
        openDelete(r){this.deleteRoute=r;this.showDeleteModal=true;},
        openEdit(a, route){
            this.editData = {id: a.id, name: a.name, email: a.email, sekolah_id: a.sekolah_id};
            this.editRoute = route;
            this.showEditModal = true;
        }
    }" @tour-close-modals.window="showCreateModal=false; showEditModal=false; showDeleteModal=false">
        @if(session('success'))<div class="alert-success mb-5"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert-danger mb-5"><ul class="list-disc pl-5 text-sm">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul></div>@endif
        <div class="card overflow-hidden">
            <div class="px-6 py-4 flex items-center justify-between border-b" style="border-color:rgba(0,0,0,0.06);">
                <div><h3 class="section-title">Daftar Admin Sekolah</h3><p class="section-subtitle">Berikan akses manajemen ke staf terpercaya setiap cabang</p></div>
                <button data-tour="lembaga-admin-add-btn" data-tour-open-modal="create" @click="showCreateModal=true" class="btn-primary shrink-0 h-9 w-9 md:h-11 md:w-11 p-0" title="Tambah Admin" aria-label="Tambah Admin"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg></button>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead><tr><th>Nama</th><th>Email Login</th><th>Cabang Sekolah</th><th class="text-right">Aksi</th></tr></thead>
                    <tbody>
                        @forelse($admins as $a)
                        <tr>
                            <td><div class="flex items-center gap-3"><div class="h-8 w-8 rounded-xl flex items-center justify-center font-bold text-sm text-white shrink-0" style="background:#1A6B6B;">{{ substr($a->name, 0, 1) }}</div><span class="font-semibold" style="color:#2C2C2C;">{{ $a->name }}</span></div></td>
                            <td><span class="badge badge-teal">{{ $a->email }}</span></td>
                            <td>{{ $a->sekolah->name ?? '-' }}</td>
                            <td class="text-right flex items-center justify-end gap-2">
                                <button @if($loop->first) data-tour="lembaga-admin-action-edit" data-tour-open-modal="edit" @endif @click="openEdit({{ json_encode($a) }}, '{{ route('lembaga.admin-sekolah.update', $a) }}')" class="row-action-icon" style="color:#1A6B6B;background:#E8F2F2;" title="Edit" aria-label="Edit"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                                <button @if($loop->first) data-tour="lembaga-admin-action-delete" data-tour-demo-action="delete" @endif @click="openDelete('{{ route('lembaga.admin-sekolah.destroy', $a) }}')" class="row-action-icon" style="color:#C0392B;background:#FAD7D2;" title="Hapus" aria-label="Hapus"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="py-6 md:py-12 text-center" style="color:#9E9790;">Belum ada akun admin sekolah.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t" style="border-color:rgba(0,0,0,0.06);">
                <x-per-page-selector :paginator="$admins" />
                {{ $admins->links() }}
            </div>
        </div>
        <!-- CREATE MODAL -->
        <div x-show="showCreateModal" class="modal-overlay" style="display:none;">
            <div x-show="showCreateModal" x-transition class="modal-box" @click.away="showCreateModal=false">
                <form action="{{ route('lembaga.admin-sekolah.store') }}" method="POST">
                    @csrf
                    <div class="modal-header"><h3 class="section-title">Registrasi Admin Sekolah Baru</h3><p class="section-subtitle">Password awal: <code>password123</code></p></div>
                    <div class="modal-body space-y-4">
                        <div class="text-[13px] font-bold text-[#1A6B6B] mt-1 border-b pb-1" data-tour="modal-create-section-form">Data Admin</div>
                        <div>
                            <label class="input-label">Nama Lengkap</label>
                            <input type="text" name="name" required class="input-field @error('name') border-red-500 @enderror" placeholder="Nama admin" value="{{ old('name') }}">
                            @error('name')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="input-label">Alamat Email Valid</label>
                            <input type="email" name="email" required class="input-field @error('email') border-red-500 @enderror" placeholder="admin@sekolah.com" value="{{ old('email') }}">
                            @error('email')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div data-tour="modal-create-section-penugasan">
                            <label class="input-label">Cabang Sekolah</label>
                            <select name="sekolah_id" required class="input-field @error('sekolah_id') border-red-500 @enderror">
                                <option value="">-- Pilih Cabang --</option>
                                @foreach($sekolahs as $s)<option value="{{ $s->id }}" @selected(old('sekolah_id') == $s->id)>{{ $s->name }}</option>@endforeach
                            </select>
                            @error('sekolah_id')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" @click="showCreateModal=false" class="btn-secondary">Batal</button><button type="submit" data-tour="modal-create-submit" class="btn-primary">Simpan</button></div>
                </form>
            </div>
        </div>
        <!-- EDIT MODAL -->
        <div x-show="showEditModal" class="modal-overlay" style="display:none;">
            <div x-show="showEditModal" x-transition class="modal-box" @click.away="showEditModal=false">
                <form :action="editRoute" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="modal-header"><h3 class="section-title">Edit Admin Sekolah</h3><p class="section-subtitle">Perbarui kredensial atau penempatan admin</p></div>
                    <div class="modal-body space-y-4">
                        <div class="text-[13px] font-bold text-[#1A6B6B] mt-1 border-b pb-1" data-tour="modal-edit-section-form">Data Admin</div>
                        <div>
                            <label class="input-label">Nama Lengkap</label>
                            <input type="text" name="name" x-model="editData.name" required class="input-field @error('name') border-red-500 @enderror">
                            @error('name')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="input-label">Alamat Email Login</label>
                            <input type="email" name="email" x-model="editData.email" required class="input-field @error('email') border-red-500 @enderror">
                            @error('email')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div data-tour="modal-edit-section-penugasan">
                            <div class="text-[13px] font-bold text-[#1A6B6B] mt-2 border-b pb-1">Penugasan Sekolah</div>
                        <div>
                            <label class="input-label">Cabang Sekolah</label>
                            <select name="sekolah_id" x-model="editData.sekolah_id" required class="input-field @error('sekolah_id') border-red-500 @enderror">
                                <option value="">-- Pilih Cabang --</option>
                                @foreach($sekolahs as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                            </select>
                            @error('sekolah_id')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="input-label">Password Baru (opsional)</label>
                            <input type="password" name="password" class="input-field @error('password') border-red-500 @enderror" placeholder="Kosongkan jika tidak mau ganti">
                            @error('password')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" @click="showEditModal=false" class="btn-secondary">Batal</button><button type="submit" data-tour="modal-edit-submit" class="btn-primary">Update</button></div>
                </form>
            </div>
        </div>
        <!-- DELETE MODAL -->
        <div x-show="showDeleteModal" data-tour="modal-delete" class="modal-overlay" style="display:none;">
            <div x-show="showDeleteModal" x-transition class="modal-box max-w-sm" @click.away="showDeleteModal=false">
                <form :action="deleteRoute" method="POST">
                    @csrf @method('DELETE')
                    <div class="modal-body text-center py-6"><div class="h-14 w-14 rounded-2xl mx-auto mb-4 flex items-center justify-center" style="background:#FAD7D2;"><svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:#C0392B;"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></div><h3 class="section-title">Hapus Admin?</h3><p class="section-subtitle mt-1">Akses login admin sekolah ini akan dicabut.</p></div>
                    <div class="modal-footer"><button type="button" @click="showDeleteModal=false" class="btn-secondary">Batal</button><button type="submit" class="btn-danger">Ya, Hapus</button></div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
