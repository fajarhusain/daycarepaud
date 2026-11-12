<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <h2 class="font-bold text-xl" style="color:#2C2C2C;">Kelola Sekolah</h2>
        </div>
    </x-slot>
    <div class="app-page-shell" x-data="{ showCreateModal:false, showEditModal:false, showDeleteModal:false, showRejectModal:false, editData:{}, deleteRoute:'', rejectRoute:'', openEdit(d){this.editData=d;this.showEditModal=true}, openDelete(r){this.deleteRoute=r;this.showDeleteModal=true}, openReject(r){this.rejectRoute=r;this.showRejectModal=true} }">
        @if(session('success'))<div class="alert-success mb-5">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert-danger mb-5"><ul class="list-disc pl-5 text-sm">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul></div>@endif

        <div class="flex flex-wrap gap-2 mb-4">
            @foreach(['all' => 'Semua', 'pending' => 'Pending ('.$pendingCount.')', 'active' => 'Aktif', 'suspended' => 'Ditangguhkan', 'rejected' => 'Ditolak'] as $key => $label)
                <a href="{{ route('superadmin.sekolah.index', ['status' => $key]) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ ($status ?? 'all') === $key ? 'bg-[#1A6B6B] text-white' : 'bg-white border' }}"
                   style="{{ ($status ?? 'all') === $key ? '' : 'border-color:rgba(0,0,0,0.1);color:#6B6560;' }}">{{ $label }}</a>
            @endforeach
        </div>

        <div class="card overflow-hidden">
            <div class="px-6 py-4 flex items-center justify-between border-b" style="border-color:rgba(0,0,0,0.06);">
                <div><h3 class="section-title">Daftar Sekolah</h3><p class="section-subtitle">Kelola sekolah yang terdaftar di platform</p></div>
                <x-toolbar.add-icon label="Tambah Sekolah" @click="showCreateModal=true" />
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead><tr><th>Sekolah</th><th>Status</th><th>Admin</th><th>Alamat</th><th class="text-right">Aksi</th></tr></thead>
                    <tbody>
                        @forelse($sekolahs as $s)
                        @php $admin = $adminEmails->get($s->id); $st = $s->status ?? 'active'; @endphp
                        <tr>
                            <td class="font-semibold">{{ $s->name }}</td>
                            <td>
                                <span class="text-xs font-semibold px-2 py-1 rounded-full
                                    @if($st === 'pending') bg-amber-100 text-amber-800
                                    @elseif($st === 'rejected') bg-red-100 text-red-800
                                    @elseif($st === 'suspended') bg-gray-200 text-gray-700
                                    @else bg-emerald-100 text-emerald-800 @endif">{{ ucfirst($st) }}</span>
                            </td>
                            <td class="text-sm">{{ $admin?->email ?? '-' }}</td>
                            <td class="text-sm">{{ $s->address ?? '-' }}</td>
                            <td class="text-right space-x-1">
                                @if($st === 'pending')
                                    <form action="{{ route('superadmin.sekolah.approve', $s) }}" method="POST" class="inline">@csrf
                                        <button type="submit" class="row-action-icon" style="color:#1A6B6B;background:#D0E8E8;" title="Setujui" aria-label="Setujui"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></button>
                                    </form>
                                    <button type="button" @click="openReject('{{ route('superadmin.sekolah.reject', $s) }}')" class="row-action-icon" style="color:#C0392B;background:#FAD7D2;" title="Tolak" aria-label="Tolak"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
                                @endif
                                <button @click="openEdit({{ json_encode($s) }})" class="row-action-icon" style="color:#1A6B6B;background:#D0E8E8;" title="Edit" aria-label="Edit"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                                <button @click="openDelete('{{ route('superadmin.sekolah.destroy', $s) }}')" class="row-action-icon" style="color:#C0392B;background:#FAD7D2;" title="Hapus" aria-label="Hapus"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="py-8 text-center" style="color:#9E9790;">Belum ada sekolah.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t">
                <x-per-page-selector :paginator="$sekolahs" />
                {{ $sekolahs->links() }}
            </div>
        </div>

        <div x-show="showCreateModal" class="modal-overlay" style="display:none;"><div x-show="showCreateModal" x-transition class="modal-box max-w-lg" @click.away="showCreateModal=false">
            <form action="{{ route('superadmin.sekolah.store') }}" method="POST">@csrf
                <div class="modal-header"><h3 class="section-title">Tambah Sekolah</h3></div>
                <div class="modal-body space-y-4">
                    <div><label class="input-label">Nama sekolah</label><input type="text" name="sekolah_name" required class="input-field"></div>
                    <div><label class="input-label">Alamat</label><textarea name="sekolah_address" rows="2" class="input-field"></textarea></div>
                    <div><label class="input-label">Telepon</label><input type="text" name="sekolah_phone" class="input-field"></div>
                    <hr style="border-color:rgba(0,0,0,0.08);">
                    <div><label class="input-label">Nama admin sekolah</label><input type="text" name="admin_name" required class="input-field"></div>
                    <div><label class="input-label">Email admin</label><input type="email" name="admin_email" required class="input-field"></div>
                    <div><label class="input-label">Password (opsional)</label><input type="password" name="password" class="input-field" placeholder="Default: password123"></div>
                </div>
                <div class="modal-footer"><button type="button" @click="showCreateModal=false" class="btn-secondary">Batal</button><button type="submit" class="btn-primary">Simpan</button></div>
            </form>
        </div></div>

        <div x-show="showEditModal" class="modal-overlay" style="display:none;"><div x-show="showEditModal" x-transition class="modal-box" @click.away="showEditModal=false">
            <form :action="`/superadmin/sekolah/${editData.id}`" method="POST">@csrf @method('PUT')
                <div class="modal-header"><h3 class="section-title">Edit Sekolah</h3></div>
                <div class="modal-body space-y-4">
                    <div><label class="input-label">Nama</label><input type="text" name="name" x-model="editData.name" required class="input-field"></div>
                    <div><label class="input-label">Alamat</label><textarea name="address" x-model="editData.address" rows="2" class="input-field"></textarea></div>
                    <div><label class="input-label">Telepon</label><input type="text" name="phone" x-model="editData.phone" class="input-field"></div>
                </div>
                <div class="modal-footer"><button type="button" @click="showEditModal=false" class="btn-secondary">Batal</button><button type="submit" class="btn-primary">Simpan</button></div>
            </form>
        </div></div>

        <div x-show="showDeleteModal" class="modal-overlay" style="display:none;"><div x-show="showDeleteModal" x-transition class="modal-box" @click.away="showDeleteModal=false">
            <div class="modal-header"><h3 class="section-title">Hapus Sekolah?</h3></div>
            <div class="modal-body"><p class="text-sm" style="color:#6B6560;">Data sekolah akan dihapus permanen.</p></div>
            <div class="modal-footer">
                <button type="button" @click="showDeleteModal=false" class="btn-secondary">Batal</button>
                <form :action="deleteRoute" method="POST">@csrf @method('DELETE')<button type="submit" class="btn-danger">Hapus</button></form>
            </div>
        </div></div>

        <div x-show="showRejectModal" class="modal-overlay" style="display:none;"><div x-show="showRejectModal" x-transition class="modal-box" @click.away="showRejectModal=false">
            <form :action="rejectRoute" method="POST">@csrf
                <div class="modal-header"><h3 class="section-title">Tolak Pendaftaran</h3></div>
                <div class="modal-body"><label class="input-label">Alasan penolakan</label><textarea name="rejection_reason" rows="3" required class="input-field"></textarea></div>
                <div class="modal-footer"><button type="button" @click="showRejectModal=false" class="btn-secondary">Batal</button><button type="submit" class="btn-danger">Tolak</button></div>
            </form>
        </div></div>
    </div>
</x-app-layout>
