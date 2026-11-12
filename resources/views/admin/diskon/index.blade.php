<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background: #1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
            </div>
            <h2 class="font-bold text-xl" style="color: #2C2C2C;">Diskon</h2>
        </div>
    </x-slot>

    <div class="app-page-shell"
         x-data="{ showCreateModal: false, showEditModal: false, showDeleteModal: false, editData: {}, deleteRoute: '' }" @tour-close-modals.window="showCreateModal=false; showEditModal=false; showDeleteModal=false">

        @if(session('success'))<div class="alert-success mb-5">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert-danger mb-5"><ul class="list-disc pl-5 text-sm">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul></div>@endif

        <div class="card overflow-hidden">
            <div class="px-6 py-4 flex items-center justify-between border-b" style="border-color:rgba(0,0,0,0.06);">
                <div>
                    <h3 class="section-title">Daftar Diskon</h3>
                    <p class="section-subtitle">Diskon diterapkan per siswa saat generate tagihan</p>
                </div>
                <x-toolbar.add-icon label="Tambah Diskon" data-tour="admin-diskon-add-btn" data-tour-open-modal="create" @click="showCreateModal=true" />
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nama Diskon</th>
                            <th class="text-center">Tipe</th>
                            <th class="text-right">Nilai</th>
                            <th class="text-center">Status</th>
                            <th>Keterangan</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($diskons as $diskon)
                            <tr>
                                <td class="font-medium">{{ $diskon->nama_diskon }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $diskon->tipe === 'persentase' ? 'badge-blue' : 'badge-green' }}">
                                        {{ $diskon->tipe === 'persentase' ? 'Persentase' : 'Nominal' }}
                                    </span>
                                </td>
                                <td class="text-right font-semibold" style="color:#1A6B6B;">{{ $diskon->getNilaiFormatted() }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $diskon->is_aktif ? 'badge-green' : 'badge-gray' }}">
                                        {{ $diskon->is_aktif ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td>{{ $diskon->keterangan ?? '-' }}</td>
                                <td class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <button @if($loop->first) data-tour="admin-diskon-action-edit" data-tour-open-modal="edit" @endif @click="editData={{ json_encode($diskon) }}; showEditModal=true" class="row-action-icon" style="color:#1A6B6B;background:#D0E8E8;" title="Edit" aria-label="Edit"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                                        <button @if($loop->first) data-tour="admin-diskon-action-delete" data-tour-demo-action="delete" @endif @click="deleteRoute='{{ route('admin.diskon.destroy', $diskon) }}'; showDeleteModal=true" class="row-action-icon" style="color:#C0392B;background:#FAD7D2;" title="Hapus" aria-label="Hapus"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-12 text-center" style="color:#9E9790;">Belum ada diskon.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t" style="border-color:rgba(0,0,0,0.06);">
                <x-per-page-selector :paginator="$diskons" />
                {{ $diskons->withQueryString()->links() }}
            </div>
        </div>

        <!-- CREATE -->
        <div x-show="showCreateModal" class="modal-overlay" style="display:none;">
            <div x-show="showCreateModal" x-transition class="modal-box" @click.away="showCreateModal=false">
                <form action="{{ route('admin.diskon.store') }}" method="POST">
                    @csrf
                    <div class="modal-header"><h3 class="section-title">Tambah Diskon</h3></div>
                    <div class="modal-body grid grid-cols-2 gap-4" data-tour="modal-create-section-form">
                        <div class="col-span-2"><label class="input-label">Nama Diskon</label><input type="text" name="nama_diskon" required class="input-field"></div>
                        <div><label class="input-label">Tipe</label><select name="tipe" required class="input-field"><option value="persentase">Persentase (%)</option><option value="nominal">Nominal (Rp)</option></select></div>
                        <div><label class="input-label">Nilai</label><input type="number" name="nilai" min="0" required class="input-field"></div>
                        <div class="col-span-2"><label class="input-label">Keterangan</label><textarea name="keterangan" rows="2" class="input-field"></textarea></div>
                    </div>
                    <div class="modal-footer"><button type="button" @click="showCreateModal=false" class="btn-secondary">Batal</button><button type="submit" data-tour="modal-create-submit" class="btn-primary">Simpan</button></div>
                </form>
            </div>
        </div>

        <!-- EDIT -->
        <div x-show="showEditModal" class="modal-overlay" style="display:none;">
            <div x-show="showEditModal" x-transition class="modal-box" @click.away="showEditModal=false">
                <form :action="`{{ url('admin/diskon') }}/${editData.id}`" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-header"><h3 class="section-title">Edit Diskon</h3></div>
                    <div class="modal-body grid grid-cols-2 gap-4" data-tour="modal-edit-section-form">
                        <div class="col-span-2"><label class="input-label">Nama Diskon</label><input type="text" name="nama_diskon" x-model="editData.nama_diskon" required class="input-field"></div>
                        <div><label class="input-label">Tipe</label><select name="tipe" x-model="editData.tipe" required class="input-field"><option value="persentase">Persentase (%)</option><option value="nominal">Nominal (Rp)</option></select></div>
                        <div><label class="input-label">Nilai</label><input type="number" name="nilai" x-model="editData.nilai" min="0" required class="input-field"></div>
                        <div class="col-span-2"><label class="input-label">Keterangan</label><textarea name="keterangan" x-model="editData.keterangan" rows="2" class="input-field"></textarea></div>
                        <div class="col-span-2"><label class="flex items-center gap-2"><input type="checkbox" name="is_aktif" value="1" :checked="editData.is_aktif" class="rounded"><span class="input-label mb-0">Aktif</span></label></div>
                    </div>
                    <div class="modal-footer"><button type="button" @click="showEditModal=false" class="btn-secondary">Batal</button><button type="submit" data-tour="modal-edit-submit" class="btn-primary">Simpan</button></div>
                </form>
            </div>
        </div>

        <!-- DELETE -->
        <div x-show="showDeleteModal" data-tour="modal-delete" class="modal-overlay" style="display:none;">
            <div x-show="showDeleteModal" x-transition class="modal-box max-w-sm" @click.away="showDeleteModal=false">
                <form :action="deleteRoute" method="POST">
                    @csrf @method('DELETE')
                    <div class="modal-body text-center py-6">
                        <h3 class="section-title">Hapus Diskon?</h3>
                    </div>
                    <div class="modal-footer"><button type="button" @click="showDeleteModal=false" class="btn-secondary">Batal</button><button type="submit" class="btn-danger">Ya, Hapus</button></div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
