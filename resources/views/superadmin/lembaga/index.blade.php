<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background:#1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5" /></svg>
            </div>
            <h2 class="font-bold text-xl" style="color:#2C2C2C;">Kelola Lembaga / Yayasan</h2>
        </div>
    </x-slot>
    <div class="app-page-shell" x-data="{ showCreateModal:false, showEditModal:false, showDeleteModal:false, showRejectModal:false, editData:{}, deleteRoute:'', rejectRoute:'', openEdit(d){this.editData=d;this.showEditModal=true}, openDelete(r){this.deleteRoute=r;this.showDeleteModal=true}, openReject(r){this.rejectRoute=r;this.showRejectModal=true} }">
        @if(session('success'))<div class="alert-success mb-5">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert-danger mb-5"><ul class="list-disc pl-5 text-sm">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul></div>@endif

        <div class="flex flex-wrap gap-2 mb-4">
            @foreach(['all' => 'Semua', 'pending' => 'Pending ('.$pendingCount.')', 'active' => 'Aktif', 'rejected' => 'Ditolak'] as $key => $label)
                <a href="{{ route('superadmin.lembaga.index', ['status' => $key]) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ ($status ?? 'all') === $key ? 'bg-[#1A6B6B] text-white' : 'bg-white border' }}"
                   style="{{ ($status ?? 'all') === $key ? '' : 'border-color:rgba(0,0,0,0.1);color:#6B6560;' }}">{{ $label }}</a>
            @endforeach
        </div>

        <div class="card overflow-hidden">
            <div class="px-6 py-4 flex items-center justify-between border-b" style="border-color:rgba(0,0,0,0.06);">
                <div><h3 class="section-title">Daftar Lembaga</h3><p class="section-subtitle">Kelola yayasan yang terdaftar di platform</p></div>
                <x-toolbar.add-icon label="Tambah Lembaga" @click="showCreateModal=true" />
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead><tr><th>Nama</th><th>Status</th><th>Kontak</th><th>Sekolah</th><th class="text-right">Aksi</th></tr></thead>
                    <tbody>
                        @forelse($lembagas as $l)
                        <tr>
                            <td class="font-semibold">{{ $l->name }}</td>
                            <td>
                                @php $st = $l->status ?? 'active'; @endphp
                                <span class="text-xs font-semibold px-2 py-1 rounded-full
                                    @if($st === 'pending') bg-amber-100 text-amber-800
                                    @elseif($st === 'rejected') bg-red-100 text-red-800
                                    @elseif($st === 'suspended') bg-gray-200 text-gray-700
                                    @else bg-emerald-100 text-emerald-800 @endif">{{ ucfirst($st) }}</span>
                            </td>
                            <td class="text-sm">{{ $l->contact_email ?? '-' }}</td>
                            <td>{{ $l->sekolahs_count }}</td>
                            <td class="text-right space-x-1">
                                @if(($l->status ?? 'active') === 'pending')
                                    <form action="{{ route('superadmin.lembaga.approve', $l) }}" method="POST" class="inline">@csrf
                                        <button type="submit" class="row-action-icon" style="color:#1A6B6B;background:#D0E8E8;" title="Setujui" aria-label="Setujui"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></button>
                                    </form>
                                    <button type="button" @click="openReject('{{ route('superadmin.lembaga.reject', $l) }}')" class="row-action-icon" style="color:#C0392B;background:#FAD7D2;" title="Tolak" aria-label="Tolak"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
                                @endif
                                <button @click="openEdit({{ json_encode($l) }})" class="row-action-icon" style="color:#1A6B6B;background:#D0E8E8;" title="Edit" aria-label="Edit"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                                <button @click="openDelete('{{ route('superadmin.lembaga.destroy', $l) }}')" class="row-action-icon" style="color:#C0392B;background:#FAD7D2;" title="Hapus" aria-label="Hapus"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="py-8 text-center" style="color:#9E9790;">Belum ada lembaga.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t">
                <x-per-page-selector :paginator="$lembagas" />
                {{ $lembagas->links() }}
            </div>
        </div>

        {{-- modals unchanged structure --}}
        <div x-show="showCreateModal" class="modal-overlay" style="display:none;"><div x-show="showCreateModal" x-transition class="modal-box" @click.away="showCreateModal=false">
            <form action="{{ route('superadmin.lembaga.store') }}" method="POST">@csrf
                <div class="modal-header"><h3 class="section-title">Tambah Lembaga</h3></div>
                <div class="modal-body space-y-4">
                    <div><label class="input-label">Nama</label><input type="text" name="name" required class="input-field"></div>
                    <div><label class="input-label">Alamat</label><textarea name="address" rows="2" class="input-field"></textarea></div>
                    <div><label class="input-label">Telepon</label><input type="text" name="phone" class="input-field"></div>
                </div>
                <div class="modal-footer"><button type="button" @click="showCreateModal=false" class="btn-secondary">Batal</button><button type="submit" class="btn-primary">Simpan</button></div>
            </form>
        </div></div>
        <div x-show="showEditModal" class="modal-overlay" style="display:none;"><div x-show="showEditModal" x-transition class="modal-box" @click.away="showEditModal=false">
            <form :action="`/superadmin/lembaga/${editData.id}`" method="POST">@csrf @method('PUT')
                <div class="modal-header"><h3 class="section-title">Edit Lembaga</h3></div>
                <div class="modal-body space-y-4">
                    <div><label class="input-label">Nama</label><input type="text" name="name" x-model="editData.name" required class="input-field"></div>
                    <div><label class="input-label">Alamat</label><textarea name="address" x-model="editData.address" rows="2" class="input-field"></textarea></div>
                    <div><label class="input-label">Telepon</label><input type="text" name="phone" x-model="editData.phone" class="input-field"></div>
                </div>
                <div class="modal-footer"><button type="button" @click="showEditModal=false" class="btn-secondary">Batal</button><button type="submit" class="btn-primary">Simpan</button></div>
            </form>
        </div></div>
        <div x-show="showRejectModal" class="modal-overlay" style="display:none;"><div x-show="showRejectModal" x-transition class="modal-box" @click.away="showRejectModal=false">
            <form :action="rejectRoute" method="POST">@csrf
                <div class="modal-header"><h3 class="section-title">Tolak Pendaftaran?</h3></div>
                <div class="modal-body">
                    <label class="input-label">Alasan penolakan</label>
                    <textarea name="rejection_reason" rows="3" class="input-field" required></textarea>
                </div>
                <div class="modal-footer"><button type="button" @click="showRejectModal=false" class="btn-secondary">Batal</button><button type="submit" class="btn-primary" style="background:#C0392B;">Tolak</button></div>
            </form>
        </div></div>
        <div x-show="showDeleteModal" class="modal-overlay" style="display:none;"><div x-show="showDeleteModal" x-transition class="modal-box" @click.away="showDeleteModal=false">
            <form :action="deleteRoute" method="POST">@csrf @method('DELETE')
                <div class="modal-header"><h3 class="section-title">Hapus Lembaga?</h3></div>
                <div class="modal-footer"><button type="button" @click="showDeleteModal=false" class="btn-secondary">Batal</button><button type="submit" class="btn-primary" style="background:#C0392B;">Hapus</button></div>
            </form>
        </div></div>
    </div>
</x-app-layout>
