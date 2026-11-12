<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background: #1A6B6B;"><svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></div>
            <h2 class="font-bold text-xl" style="color: #2C2C2C;">Kelola Data Guru</h2>
        </div>
    </x-slot>
    <div class="app-page-shell" x-data="{ showCreateModal:@js($errors->any() && old('_method') !== 'PUT'), showEditModal:@js($errors->any() && old('_method') === 'PUT'), showDeleteModal:false, editData:{ kelas_ids: [] }, deleteRoute:'', openEdit(d){this.editData=d; this.editData.kelas_ids = d.kelas ? d.kelas.map(k => k.id) : []; this.showEditModal=true}, openDelete(r){this.deleteRoute=r;this.showDeleteModal=true} }" @tour-close-modals.window="showCreateModal=false; showEditModal=false; showDeleteModal=false">
        @if(session('success'))<div class="alert-success mb-5"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert-danger mb-5"><ul class="list-disc pl-5 text-sm">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul></div>@endif
        <div class="card overflow-hidden">
            <div class="card-pad page-toolbar border-b" style="border-color:rgba(0,0,0,0.06);">
                <div><h3 class="section-title">Daftar Guru</h3><p class="section-subtitle">Kelola data guru dan akun login yang digunakan untuk akses sistem</p></div>
                <div class="toolbar-actions">
                    <x-export-excel route="admin.pengajar.export" />
                    <button data-tour="admin-pengajar-add-btn" data-tour-open-modal="create" @click="showCreateModal=true" class="btn-primary toolbar-primary shrink-0 h-9 w-9 md:h-11 md:w-11 p-0" title="Registrasi Guru" aria-label="Registrasi Guru"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg></button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead><tr><th>Nama Guru</th><th>Email Login</th><th>Shift Presensi</th><th>Jabatan / Posisi</th><th>Penempatan Kelas</th><th class="text-right">Aksi</th></tr></thead>
                    <tbody>
                        @forelse($pengajars as $p)
                        <tr>
                            <td><div class="flex items-center gap-3">
                                <x-foto-profil :path="$p->photo" :name="$p->name" size="sm" />
                                <span class="font-semibold" style="color:#2C2C2C;">{{ $p->name }}</span></div></td>
                            <td><span class="badge badge-teal">{{ $p->user->email ?? '-' }}</span></td>
                            <td><span class="text-sm" style="color:#6B6560;">{{ $p->presensiShift?->name ?? '-' }}</span></td>
                            <td>{{ $p->jabatan ?? '-' }}</td>
                            <td>
                                @if($p->kelas && $p->kelas->count() > 0)
                                    <div class="flex flex-wrap gap-1">
                                    @foreach($p->kelas as $k)
                                        <span class="badge badge-teal font-medium">{{ $k->name }}</span>
                                    @endforeach
                                    </div>
                                @else
                                    <span class="text-xs italic" style="color:#9E9790;">Tanpa Kelas</span>
                                @endif
                            </td>
                            <td class="text-right"><div class="flex items-center justify-end gap-2">
                                <button @if($loop->first) data-tour="admin-pengajar-action-edit" data-tour-open-modal="edit" @endif @click="openEdit({{ json_encode($p) }})" class="row-action-icon" style="color:#1A6B6B;background:#D0E8E8;" title="Edit" aria-label="Edit"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                                <button @if($loop->first) data-tour="admin-pengajar-action-delete" data-tour-demo-action="delete" @endif @click="openDelete('{{ route('admin.pengajar.destroy', $p) }}')" class="row-action-icon" style="color:#C0392B;background:#FAD7D2;" title="Hapus" aria-label="Hapus"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            </div></td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="py-6 md:py-12 text-center" style="color:#9E9790;">Belum ada data guru.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t" style="border-color:rgba(0,0,0,0.06);">
                <x-per-page-selector :paginator="$pengajars" />
                {{ $pengajars->links() }}
            </div>
        </div>
        <!-- CREATE MODAL -->
        <div x-show="showCreateModal" class="modal-overlay" style="display:none;">
            <div x-show="showCreateModal" x-transition class="modal-box" @click.away="showCreateModal=false">
                <form action="{{ route('admin.pengajar.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header"><h3 class="section-title">Registrasi Guru Baru</h3><p class="section-subtitle">Password login awal: <code>password123</code></p></div>
                    <div class="modal-body space-y-4">
                        <div class="text-[13px] font-bold text-[#1A6B6B] mt-1 border-b pb-1" data-tour="modal-create-section-data">Data Guru</div>
                        <div>
                            <label class="input-label">Nama Lengkap</label>
                            <input type="text" name="name" required class="input-field @error('name') border-red-500 @enderror" placeholder="Nama lengkap guru" value="{{ old('name') }}">
                            @error('name')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="text-[13px] font-bold text-[#1A6B6B] mt-2 border-b pb-1" data-tour="modal-create-section-akun">Akun & Penugasan</div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="input-label">Alamat Email Valid</label>
                                <input type="email" name="email" required class="input-field @error('email') border-red-500 @enderror" placeholder="email@sekolah.com" value="{{ old('email') }}">
                                @error('email')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="input-label">Penempatan Kelas</label>
                                <div class="mt-1 rounded-xl border p-3 max-h-32 overflow-y-auto bg-gray-50 space-y-2" style="border-color:rgba(0,0,0,0.1);">
                                    @forelse($kelas as $k)
                                    <label class="flex items-start gap-2.5 cursor-pointer">
                                        <input type="checkbox" name="kelas_id[]" value="{{ $k->id }}" class="mt-1 accent-teal-600 rounded">
                                        <span class="text-sm font-medium" style="color:#2C2C2C;">{{ $k->name }}</span>
                                    </label>
                                    @empty
                                    <span class="text-xs italic text-gray-400">Belum ada kelas</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="input-label">Shift Presensi</label>
                            <select name="presensi_shift_id" class="input-field @error('presensi_shift_id') border-red-500 @enderror" required>
                                @forelse($presensiShifts as $shift)
                                    <option value="{{ $shift->id }}" @selected(old('presensi_shift_id') == $shift->id || ($shift->is_default && ! old('presensi_shift_id')))>{{ $shift->name }}</option>
                                @empty
                                    <option value="">Buat shift di Pengaturan Presensi dulu</option>
                                @endforelse
                            </select>
                            @error('presensi_shift_id')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="input-label">NIK KTP</label>
                                <input type="text" name="nik" class="input-field @error('nik') border-red-500 @enderror" placeholder="NIK Guru" value="{{ old('nik') }}">
                                @error('nik')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="input-label">Kontak / WA</label>
                                <input type="text" name="phone" class="input-field @error('phone') border-red-500 @enderror" placeholder="08..." value="{{ old('phone') }}">
                                @error('phone')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="input-label">Pendidikan terakhir</label>
                                <x-pendidikan-select name="pendidikan" />
                            </div>
                            <div>
                                <label class="input-label">Jenis Kelamin</label>
                                <select name="jenis_kelamin" class="input-field @error('jenis_kelamin') border-red-500 @enderror">
                                    <option value="">Pilih...</option>
                                    <option value="Pria" @selected(old('jenis_kelamin') == 'Pria')>Pria</option>
                                    <option value="Wanita" @selected(old('jenis_kelamin') == 'Wanita')>Wanita</option>
                                </select>
                                @error('jenis_kelamin')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="input-label">Jabatan / Posisi</label><input type="text" name="jabatan" class="input-field" placeholder="Contoh: Guru Kelas A"></div>
                            <div><label class="input-label">Foto Guru</label><input type="file" name="photo" accept="image/*" class="input-field py-1.5"></div>
                        </div>
                        <div><label class="input-label">Alamat Lengkap</label><textarea name="alamat" rows="2" class="input-field" placeholder="Prov, Kab, Kec, Kel..."></textarea></div>
                    </div>
                    <div class="modal-footer"><button type="button" @click="showCreateModal=false" class="btn-secondary">Batal</button><button type="submit" data-tour="modal-create-submit" class="btn-primary">Registrasikan</button></div>
                </form>
            </div>
        </div>
        <!-- EDIT MODAL -->
        <div x-show="showEditModal" class="modal-overlay" style="display:none;">
            <div x-show="showEditModal" x-transition class="modal-box" @click.away="showEditModal=false">
                <form :action="`/admin/pengajar/${editData.id}`" method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="modal-header"><h3 class="section-title">Edit Data Guru</h3></div>
                    <div class="modal-body space-y-4">
                        <div class="text-[13px] font-bold text-[#1A6B6B] mt-1 border-b pb-1" data-tour="modal-edit-section-data">Data Guru</div>
                        <div>
                            <label class="input-label">Nama Lengkap</label>
                            <input type="text" name="name" x-model="editData.name" required class="input-field @error('name') border-red-500 @enderror">
                            @error('name')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="text-[13px] font-bold text-[#1A6B6B] mt-2 border-b pb-1" data-tour="modal-edit-section-akun">Akun & Penugasan</div>
                        <div>
                            <label class="input-label">Penempatan Kelas</label>
                            <div class="mt-1 rounded-xl border p-3 max-h-40 overflow-y-auto bg-gray-50 grid grid-cols-1 sm:grid-cols-2 gap-2" style="border-color:rgba(0,0,0,0.1);">
                                @forelse($kelas as $k)
                                <label class="flex items-start gap-2.5 cursor-pointer">
                                    <input type="checkbox" name="kelas_id[]" value="{{ $k->id }}" x-model="editData.kelas_ids" class="mt-1 accent-teal-600 rounded">
                                    <span class="text-sm font-medium" style="color:#2C2C2C;">{{ $k->name }}</span>
                                </label>
                                @empty
                                <span class="text-xs italic text-gray-400">Belum ada data kelas</span>
                                @endforelse
                            </div>
                        </div>
                        <div>
                            <label class="input-label">Shift Presensi</label>
                            <select name="presensi_shift_id" x-model="editData.presensi_shift_id" class="input-field" required>
                                @foreach($presensiShifts as $shift)
                                    <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="input-label">NIK KTP</label><input type="text" name="nik" x-model="editData.nik" class="input-field"></div>
                            <div><label class="input-label">Kontak / WA</label><input type="text" name="phone" x-model="editData.phone" class="input-field"></div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="input-label">Pendidikan terakhir</label>
                                <select name="pendidikan" class="input-field" x-model="editData.pendidikan">
                                    <option value=""> Pilih </option>
                                    @foreach($pendidikanOptions as $opt)
                                        <option value="{{ $opt }}">{{ $opt }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div><label class="input-label">Jenis Kelamin</label><select name="jenis_kelamin" x-model="editData.jenis_kelamin" class="input-field"><option value="">Pilih...</option><option value="Pria">Pria</option><option value="Wanita">Wanita</option></select></div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="input-label">Jabatan / Posisi</label><input type="text" name="jabatan" x-model="editData.jabatan" class="input-field"></div>
                            <div><label class="input-label">Ganti Foto</label><input type="file" name="photo" accept="image/*" class="input-field py-1.5"></div>
                        </div>
                        <div><label class="input-label">Alamat Lengkap</label><textarea name="alamat" x-model="editData.alamat" rows="2" class="input-field"></textarea></div>
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
                    <div class="modal-body text-center py-6"><div class="h-14 w-14 rounded-2xl mx-auto mb-4 flex items-center justify-center" style="background:#FAD7D2;"><svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:#C0392B;"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></div><h3 class="section-title">Hapus Guru?</h3><p class="section-subtitle mt-1">Akses login guru juga akan dihapus bersama data ini.</p></div>
                    <div class="modal-footer"><button type="button" @click="showDeleteModal=false" class="btn-secondary">Batal</button><button type="submit" class="btn-danger">Ya, Hapus</button></div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
