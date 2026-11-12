<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <h2 class="font-bold text-xl" style="color:#2C2C2C;">Kelola Admin Lembaga</h2>
        </div>
    </x-slot>
    <div class="app-page-shell" x-data="{ showCreateModal:false, showEditModal:false, showDeleteModal:false, deleteRoute:'', editRoute:'', editData:{name:'',email:'',lembaga_id:''}, openDelete(r){this.deleteRoute=r;this.showDeleteModal=true}, openEdit(a,r){this.editData={name:a.name,email:a.email,lembaga_id:a.lembaga_id};this.editRoute=r;this.showEditModal=true} }">
        @if(session('success'))<div class="alert-success mb-5">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert-danger mb-5"><ul class="list-disc pl-5 text-sm">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul></div>@endif
        <div class="card overflow-hidden">
            <div class="px-6 py-4 flex justify-between border-b" style="border-color:rgba(0,0,0,0.06);">
                <h3 class="section-title">Daftar Admin Lembaga</h3>
                <x-toolbar.add-icon label="Tambah Admin" @click="showCreateModal=true" />
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead><tr><th>Nama</th><th>Email</th><th>Lembaga</th><th class="text-right">Aksi</th></tr></thead>
                    <tbody>
                        @forelse($admins as $a)
                        <tr>
                            <td>{{ $a->name }}</td>
                            <td>{{ $a->email }}</td>
                            <td>{{ $a->lembaga?->name ?? '-' }}</td>
                            <td class="text-right">
                                <button @click="openEdit({{ json_encode($a) }}, '{{ route('superadmin.admin-lembaga.update', $a) }}')" class="row-action-icon" style="color:#1A6B6B;background:#E8F2F2;" title="Edit" aria-label="Edit"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                                <button @click="openDelete('{{ route('superadmin.admin-lembaga.destroy', $a) }}')" class="row-action-icon" style="color:#C0392B;background:#FAD7D2;" title="Hapus" aria-label="Hapus"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="py-8 text-center" style="color:#9E9790;">Belum ada admin lembaga.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t">
                <x-per-page-selector :paginator="$admins" />
                {{ $admins->links() }}
            </div>
        </div>
        <div x-show="showCreateModal" class="modal-overlay" style="display:none;"><div class="modal-box" @click.away="showCreateModal=false">
            <form action="{{ route('superadmin.admin-lembaga.store') }}" method="POST">@csrf
                <div class="modal-header"><h3 class="section-title">Tambah Admin Lembaga</h3><p class="section-subtitle">Password awal: password123</p></div>
                <div class="modal-body space-y-4">
                    <div><label class="input-label">Nama</label><input type="text" name="name" required class="input-field"></div>
                    <div><label class="input-label">Email</label><input type="email" name="email" required class="input-field"></div>
                    <div><label class="input-label">Lembaga</label><select name="lembaga_id" required class="input-field"><option value="">-- Pilih --</option>@foreach($lembagas as $l)<option value="{{ $l->id }}">{{ $l->name }}</option>@endforeach</select></div>
                </div>
                <div class="modal-footer"><button type="button" @click="showCreateModal=false" class="btn-secondary">Batal</button><button type="submit" class="btn-primary">Simpan</button></div>
            </form>
        </div></div>
        <div x-show="showEditModal" class="modal-overlay" style="display:none;"><div class="modal-box" @click.away="showEditModal=false">
            <form :action="editRoute" method="POST">@csrf @method('PUT')
                <div class="modal-header"><h3 class="section-title">Edit Admin Lembaga</h3></div>
                <div class="modal-body space-y-4">
                    <div><label class="input-label">Nama</label><input type="text" name="name" x-model="editData.name" required class="input-field"></div>
                    <div><label class="input-label">Email</label><input type="email" name="email" x-model="editData.email" required class="input-field"></div>
                    <div><label class="input-label">Lembaga</label><select name="lembaga_id" x-model="editData.lembaga_id" required class="input-field">@foreach($lembagas as $l)<option value="{{ $l->id }}">{{ $l->name }}</option>@endforeach</select></div>
                    <div><label class="input-label">Password baru (opsional)</label><input type="password" name="password" class="input-field"></div>
                </div>
                <div class="modal-footer"><button type="button" @click="showEditModal=false" class="btn-secondary">Batal</button><button type="submit" class="btn-primary">Simpan</button></div>
            </form>
        </div></div>
        <div x-show="showDeleteModal" class="modal-overlay" style="display:none;"><div class="modal-box" @click.away="showDeleteModal=false">
            <form :action="deleteRoute" method="POST">@csrf @method('DELETE')
                <div class="modal-header"><h3 class="section-title">Hapus admin ini?</h3></div>
                <div class="modal-footer"><button type="button" @click="showDeleteModal=false" class="btn-secondary">Batal</button><button type="submit" class="btn-primary" style="background:#C0392B;">Hapus</button></div>
            </form>
        </div></div>
    </div>
</x-app-layout>
