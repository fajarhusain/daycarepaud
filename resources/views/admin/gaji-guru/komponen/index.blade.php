<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <h2 class="font-bold text-xl" style="color:#2C2C2C;">Master Komponen Gaji</h2>
        </div>
    </x-slot>
    <div class="app-page-shell" x-data="{ showCreate:false, showEdit:false, showDelete:false, editData:{}, deleteRoute:'' }">
        @if(session('success'))<div class="alert-success mb-5">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert-danger mb-5"><ul class="list-disc pl-5 text-sm">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
        <div class="mb-4"><a href="{{ route('admin.gaji-guru.index') }}" class="btn-secondary text-sm">← Gaji Guru</a></div>
        <div class="card overflow-hidden">
            <div class="px-6 py-4 flex justify-between border-b" style="border-color:rgba(0,0,0,0.06);">
                <h3 class="section-title">Komponen default per sekolah</h3>
                <x-toolbar.add-icon label="Tambah" @click="showCreate=true" />
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead><tr><th>Nama</th><th>Jenis</th><th>Default</th><th>Urutan</th><th>Status</th><th class="text-right">Aksi</th></tr></thead>
                    <tbody>
                        @forelse($komponens as $k)
                        <tr>
                            <td class="font-semibold">{{ $k->nama }}</td>
                            <td>{{ $k->jenis === 'potong' ? 'Potong' : 'Tambah' }}</td>
                            <td>Rp {{ number_format((float)$k->nominal_default, 0, ',', '.') }}</td>
                            <td>{{ $k->urutan }}</td>
                            <td>@if($k->is_aktif)<span class="badge badge-teal">Aktif</span>@else<span class="badge" style="background:#eee;">Nonaktif</span>@endif</td>
                            <td class="text-right space-x-2">
                                @php $payload = ['id'=>$k->id,'nama'=>$k->nama,'jenis'=>$k->jenis,'nominal_default'=>$k->nominal_default,'urutan'=>$k->urutan,'is_aktif'=>$k->is_aktif]; @endphp
                                <button type="button" @click="editData=@js($payload);showEdit=true" class="row-action-icon" style="color:#1A6B6B;background:#D0E8E8;" title="Edit" aria-label="Edit"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                                <button type="button" @click="deleteRoute='{{ route('admin.gaji-guru.komponen.destroy', $k) }}';showDelete=true" class="row-action-icon" style="color:#C0392B;background:#FAD7D2;" title="Hapus" aria-label="Hapus"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="py-12 text-center" style="color:#9E9790;">Belum ada komponen.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4">{{ $komponens->links() }}</div>
        </div>

        <div x-show="showCreate" class="modal-overlay" style="display:none;">
            <div class="modal-box" @click.away="showCreate=false">
                <form method="POST" action="{{ route('admin.gaji-guru.komponen.store') }}">@csrf
                    <div class="modal-header"><h3 class="section-title">Tambah komponen</h3></div>
                    <div class="modal-body space-y-3">
                        <div><label class="input-label">Nama</label><input name="nama" required class="input-field"></div>
                        <div><label class="input-label">Jenis</label><select name="jenis" class="input-field"><option value="tambah">Tambah</option><option value="potong">Potong</option></select></div>
                        <div><label class="input-label">Nominal default</label><input type="number" name="nominal_default" min="0" step="1" class="input-field" value="0"></div>
                        <div><label class="input-label">Urutan</label><input type="number" name="urutan" min="0" class="input-field" value="0"></div>
                        <label class="flex gap-2 text-sm"><input type="checkbox" name="is_aktif" value="1" checked> Aktif</label>
                    </div>
                    <div class="modal-footer"><button type="button" @click="showCreate=false" class="btn-secondary">Batal</button><button class="btn-primary">Simpan</button></div>
                </form>
            </div>
        </div>

        <div x-show="showEdit" class="modal-overlay" style="display:none;">
            <div class="modal-box" @click.away="showEdit=false">
                <form :action="`/admin/gaji-guru/komponen/${editData.id}`" method="POST">@csrf @method('PUT')
                    <div class="modal-body space-y-3">
                        <div><label class="input-label">Nama</label><input name="nama" x-model="editData.nama" required class="input-field"></div>
                        <div><label class="input-label">Jenis</label><select name="jenis" x-model="editData.jenis" class="input-field"><option value="tambah">Tambah</option><option value="potong">Potong</option></select></div>
                        <div><label class="input-label">Nominal default</label><input type="number" name="nominal_default" x-model="editData.nominal_default" min="0" class="input-field"></div>
                        <div><label class="input-label">Urutan</label><input type="number" name="urutan" x-model="editData.urutan" class="input-field"></div>
                        <label class="flex gap-2 text-sm"><input type="checkbox" name="is_aktif" value="1" :checked="editData.is_aktif"> Aktif</label>
                    </div>
                    <div class="modal-footer"><button type="button" @click="showEdit=false" class="btn-secondary">Batal</button><button class="btn-primary">Simpan</button></div>
                </form>
            </div>
        </div>

        <div x-show="showDelete" class="modal-overlay" style="display:none;">
            <div class="modal-box max-w-md">
                <p class="text-sm p-4">Hapus komponen ini?</p>
                <div class="modal-footer">
                    <button type="button" @click="showDelete=false" class="btn-secondary">Batal</button>
                    <form :action="deleteRoute" method="POST">@csrf @method('DELETE')<button class="btn-danger">Hapus</button></form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
