<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background:#1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <h2 class="font-bold text-xl" style="color:#2C2C2C;">Sumber Dana</h2>
        </div>
    </x-slot>

    <div class="app-page-shell"
         x-data="{
            showCreateModal:false, showEditModal:false, showDeleteModal:false, editData:{}, deleteRoute:'',
            openEditSd(sd) {
                this.editData = sd;
                this.showEditModal = true;
                this.$nextTick(() => {
                    const akun = sd.akun;
                    this.$dispatch('akun-search-sync', {
                        field: 'akun_id',
                        id: sd.akun_id ?? '',
                        label: akun ? `${akun.kode}  ${akun.nama}` : '',
                    });
                });
            },
         }">

        @if(session('success'))<div class="alert-success mb-5">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert-danger mb-5"><ul class="list-disc pl-5 text-sm">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

        <div class="card overflow-hidden">
            <div class="px-4 md:px-6 py-4 border-b" style="border-color:rgba(0,0,0,0.06);">
                <h3 class="section-title">Daftar Sumber Dana</h3>
                <p class="section-subtitle">BOS, Komite, SPP, dll. per sekolah</p>
            </div>

            <form method="get" data-tour="admin-sumber-dana-filter" class="card-pad toolbar-form border-b w-full" style="border-color:rgba(0,0,0,0.06);">
                <div class="toolbar-field md:min-w-[12rem] md:flex-[2]">
                    <label class="input-label">Cari</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="input-field w-full" placeholder="Kode atau nama…">
                </div>
                <div class="toolbar-field md:min-w-[9rem] md:max-w-[11rem]">
                    <label class="input-label">Status</label>
                    <select name="status" class="input-field w-full">
                        <option value="" @selected(!request('status'))>Semua</option>
                        <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
                        <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option>
                    </select>
                </div>
                <div class="toolbar-actions">
                    <x-toolbar.filter-icon label="Filter" variant="secondary" />
                    <x-filter-reset :href="route('admin.sumber-dana.index')" compact />
                    <x-export-excel route="admin.sumber-dana.export" :icon-only="true" />
                    <x-toolbar.add-icon label="Tambah Sumber Dana" type="button" @click="showCreateModal=true" />
                </div>
            </form>

            <div class="table-responsive">
            <table class="data-table" data-tour="admin-sumber-dana-table">
                <thead><tr><th>Kode</th><th>Nama</th><th>Akun</th><th class="text-center">Urutan</th><th class="text-center">Status</th><th class="text-right">Aksi</th></tr></thead>
                <tbody>
                    @forelse($sumberDanas as $sd)
                        <tr>
                            <td class="font-mono font-semibold">{{ $sd->kode }}</td>
                            <td>{{ $sd->nama }}</td>
                            <td class="text-xs">{{ $sd->akun ? $sd->akun->kode.'  '.$sd->akun->nama : '' }}</td>
                            <td class="text-center">{{ $sd->urutan }}</td>
                            <td class="text-center">
                                @if($sd->is_aktif)
                                    <span class="badge badge-green">Aktif</span>
                                @else
                                    <span class="badge" style="background:#eee;color:#666;">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="flex flex-wrap justify-end gap-1.5">
                                    <x-table.action-icon type="button" label="Edit" variant="edit" @click="openEditSd({{ Js::from($sd) }})" />
                                    <x-table.action-icon type="button" label="Hapus" variant="delete" @click="deleteRoute='{{ route('admin.sumber-dana.destroy', $sd) }}'; showDeleteModal=true" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-12 text-center" style="color:#9E9790;">Belum ada sumber dana.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>

        <div x-show="showCreateModal" class="modal-overlay" style="display:none;" @click.self="showCreateModal=false">
            <div x-show="showCreateModal" x-transition class="modal-box">
                <form action="{{ route('admin.sumber-dana.store') }}" method="POST">
                    @csrf
                    <div class="modal-header"><h3 class="section-title">Tambah Sumber Dana</h3></div>
                    <div class="modal-body grid grid-cols-2 gap-4">
                        <div><label class="input-label">Kode</label><input type="text" name="kode" required class="input-field" placeholder="BOS"></div>
                        <div><label class="input-label">Urutan</label><input type="number" name="urutan" min="0" class="input-field" value="99"></div>
                        <div class="col-span-2"><label class="input-label">Nama</label><input type="text" name="nama" required class="input-field"></div>
                        <div class="col-span-2">
                            <label class="input-label">Akun</label>
                            <x-akun-search-select name="akun_id" :options="$akunOptions" placeholder="Pilih akun sumber dana…" />
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" @click="showCreateModal=false" class="btn-secondary">Batal</button><button type="submit" class="btn-primary">Simpan</button></div>
                </form>
            </div>
        </div>

        <div x-show="showEditModal" class="modal-overlay" style="display:none;" @click.self="showEditModal=false">
            <div x-show="showEditModal" x-transition class="modal-box">
                <form :action="`{{ url('admin/sumber-dana') }}/${editData.id}`" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-header"><h3 class="section-title">Edit Sumber Dana</h3></div>
                    <div class="modal-body grid grid-cols-2 gap-4">
                        <div><label class="input-label">Kode</label><input type="text" name="kode" x-model="editData.kode" required class="input-field"></div>
                        <div><label class="input-label">Urutan</label><input type="number" name="urutan" x-model="editData.urutan" min="0" class="input-field"></div>
                        <div class="col-span-2"><label class="input-label">Nama</label><input type="text" name="nama" x-model="editData.nama" required class="input-field"></div>
                        <div class="col-span-2">
                            <label class="input-label">Akun</label>
                            <x-akun-search-select name="akun_id" :options="$akunOptions" sync-field="akun_id" placeholder="Pilih akun sumber dana…" />
                        </div>
                        <div class="col-span-2"><label class="flex items-center gap-2"><input type="checkbox" name="is_aktif" value="1" :checked="editData.is_aktif" class="rounded"><span class="input-label mb-0">Aktif</span></label></div>
                    </div>
                    <div class="modal-footer"><button type="button" @click="showEditModal=false" class="btn-secondary">Batal</button><button type="submit" class="btn-primary">Simpan</button></div>
                </form>
            </div>
        </div>

        <x-confirm-modal
            show="showDeleteModal"
            action-binding="deleteRoute"
            method="DELETE"
            title="Hapus Sumber Dana?"
            message="Data yang sudah dipakai di RKAS mungkin terpengaruh."
        />
    </div>
</x-app-layout>
