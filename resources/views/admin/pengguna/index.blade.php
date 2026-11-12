<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background: #1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            <h2 class="font-bold text-xl" style="color: #2C2C2C;">Manajemen Pengguna</h2>
        </div>
    </x-slot>

    <div class="app-page-shell"
         x-data="{
            showCreateModal: @js($errors->any() && old('_method') !== 'PUT'),
            showEditModal: @js($errors->any() && old('_method') === 'PUT'),
            showDeleteModal: false,
            createData: { role: '', kelas_id: '' },
            editData: { id: '', name: '', email: '', role: '', kelas_id: '' },
            deleteData: { id: '', name: '' },
            openEdit(user) { this.editData = { id: user.id, name: user.name, email: user.email, role: user.role, kelas_id: user.kelas_id || '' }; this.showEditModal = true; },
            openDelete(user) { this.deleteData = { id: user.id, name: user.name }; this.showDeleteModal = true; },
         }">
        <x-settings-tabs />

        @if(session('success'))
            <div class="alert-success mb-5">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="alert-danger mb-5">
                <ul class="list-disc pl-5 text-sm">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="card overflow-hidden">
            <div class="px-6 py-4 flex items-center justify-between border-b" style="border-color:rgba(0,0,0,0.06);">
                <div>
                    <h3 class="section-title">Daftar Pengguna</h3>
                    <p class="section-subtitle">Kelola pengguna yang memiliki akses ke sistem</p>
                </div>
                <div class="flex items-center gap-2">
                    <x-export-excel route="admin.pengguna.export" />
                    <button @click="showCreateModal = true" class="btn-primary shrink-0 h-9 w-9 md:h-11 md:w-11 p-0" title="Tambah Pengguna" aria-label="Tambah Pengguna">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg></button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="data-table w-full" data-tour="admin-pengguna-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Bergabung</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($penggunas as $index => $pengguna)
                            <tr>
                                <td class="text-sm font-mono" style="color:#9E9790;">{{ $penggunas->firstItem() + $index }}</td>
                                <td class="font-medium" style="color: #2C2C2C;">
                                    <div class="flex items-center gap-2">
                                        <div class="h-8 w-8 rounded-full flex items-center justify-center text-xs font-bold shrink-0" style="background:#D0E8E8; color:#1A6B6B;">
                                            {{ strtoupper(substr($pengguna->name, 0, 1)) }}
                                        </div>
                                        <span>{{ $pengguna->name }}</span>
                                    </div>
                                </td>
                                <td class="text-sm" style="color:#5A5A5A;">{{ $pengguna->email }}</td>
                                <td>
                                    @foreach($pengguna->roles as $role)
                                        <span class="badge text-xs" style="background:#D0E8E8; color:#1A6B6B;">{{ $role->name }}</span>
                                    @endforeach
                                </td>
                                <td class="text-sm" style="color:#9E9790;">{{ $pengguna->created_at->format('d M Y') }}</td>
                                <td class="text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="openEdit({{ json_encode([
                                            'id' => $pengguna->id,
                                            'name' => $pengguna->name,
                                            'email' => $pengguna->email,
                                            'role' => $pengguna->hasRole('Wali Kelas') ? 'Wali Kelas' : ($pengguna->roles->first()?->name ?? ''),
                                            'kelas_id' => $pengguna->kelas_id ?? $pengguna->pengajar?->waliKelas->first()?->id,
                                        ]) }})"
                                            class="row-action-icon" style="color:#1A6B6B;background:#D0E8E8;" title="Edit" aria-label="Edit"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                                        @if($pengguna->id !== auth()->id())
                                        <button @click="openDelete({{ json_encode(['id' => $pengguna->id, 'name' => $pengguna->name]) }})"
                                            class="row-action-icon" style="color:#C0392B;background:#FAD7D2;" title="Hapus" aria-label="Hapus"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                        @else
                                        <span class="text-xs px-3 py-1.5 rounded-lg" style="color:#9E9790;background:#EFECE8;cursor:not-allowed;" title="Tidak dapat menghapus akun sendiri">Hapus</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-10" style="color:#9E9790;">
                                    <svg class="h-10 w-10 mx-auto mb-3 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                    </svg>
                                    <p>Belum ada pengguna. Klik "Tambah Pengguna" untuk menambahkan.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t" style="border-color:rgba(0,0,0,0.06);">
                <x-per-page-selector :paginator="$penggunas" />
                {{ $penggunas->links() }}
            </div>
        </div>

        <!-- CREATE MODAL -->
        <div x-show="showCreateModal" class="modal-overlay" style="display:none;">
            <div x-show="showCreateModal" x-transition class="modal-box" @click.away="showCreateModal = false">
                <form action="{{ route('admin.pengguna.store') }}" method="POST">
                    @csrf
                    <div class="modal-header"><h3 class="section-title">Tambah Pengguna Baru</h3></div>
                    <div class="modal-body space-y-4">
                        <div>
                            <label class="input-label">Nama Lengkap</label>
                            <input type="text" name="name" required placeholder="Nama pengguna" class="input-field" value="{{ old('name') }}">
                        </div>
                        <div>
                            <label class="input-label">Email</label>
                            <input type="email" name="email" required placeholder="email@example.com" class="input-field @error('email') border-red-500 @enderror" value="{{ old('email') }}">
                            @error('email')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="input-label">Password</label>
                            <input type="password" name="password" required placeholder="Minimal 8 karakter" class="input-field">
                        </div>
                        <div>
                            <label class="input-label">Role</label>
                            <select name="role" required class="input-field" x-model="createData.role">
                                <option value="">-- Pilih Role --</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                            <p class="text-[11px] mt-1" style="color:#9E9790;">Untuk guru PAUD, gunakan menu Data Guru. Role Pengajar tidak perlu dibuat di sini.</p>
                        </div>
                        <div x-show="createData.role === 'Wali Kelas'" x-cloak>
                            <label class="input-label">Kelas Wali</label>
                            <select name="kelas_id" class="input-field" :required="createData.role === 'Wali Kelas'" x-model="createData.kelas_id">
                                <option value="">-- Pilih Kelas --</option>
                                @foreach($kelas as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                            <p class="text-[11px] mt-1" style="color:#9E9790;">Saat role `Wali Kelas` dipilih, kelas akan otomatis terhubung ke akun ini.</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" @click="showCreateModal = false" class="btn-secondary">Batal</button>
                        <button type="submit" class="btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- EDIT MODAL -->
        <div x-show="showEditModal" class="modal-overlay" style="display:none;">
            <div x-show="showEditModal" x-transition class="modal-box" @click.away="showEditModal = false">
                <form :action="`/admin/pengguna/${editData.id}`" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-header"><h3 class="section-title">Edit Pengguna</h3></div>
                    <div class="modal-body space-y-4">
                        <div>
                            <label class="input-label">Nama Lengkap</label>
                            <input type="text" name="name" x-model="editData.name" required class="input-field">
                        </div>
                        <div>
                            <label class="input-label">Email</label>
                            <input type="email" name="email" x-model="editData.email" required class="input-field @error('email') border-red-500 @enderror" value="{{ old('email') }}">
                            @error('email')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="input-label">Password <span class="text-xs" style="color:#9E9790;">(kosongkan jika tidak diubah)</span></label>
                            <input type="password" name="password" placeholder="Kosongkan jika tidak diubah" class="input-field">
                        </div>
                        <div>
                            <label class="input-label">Role</label>
                            <select name="role" required class="input-field" x-model="editData.role">
                                <option value="">-- Pilih Role --</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}" :selected="editData.role === '{{ $role->name }}'">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div x-show="editData.role === 'Wali Kelas'" x-cloak>
                            <label class="input-label">Kelas Wali</label>
                            <select name="kelas_id" class="input-field" :required="editData.role === 'Wali Kelas'" x-model="editData.kelas_id">
                                <option value="">-- Pilih Kelas --</option>
                                @foreach($kelas as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                            <p class="text-[11px] mt-1" style="color:#9E9790;">Kelas akan otomatis terisi sesuai penugasan wali kelas pengguna ini.</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" @click="showEditModal = false" class="btn-secondary">Batal</button>
                        <button type="submit" class="btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- DELETE MODAL -->
        <div x-show="showDeleteModal" class="modal-overlay" style="display:none;">
            <div x-show="showDeleteModal" x-transition class="modal-box max-w-sm" @click.away="showDeleteModal = false">
                <form :action="`/admin/pengguna/${deleteData.id}`" method="POST">
                    @csrf @method('DELETE')
                    <div class="modal-body text-center py-6">
                        <div class="h-14 w-14 rounded-2xl mx-auto mb-4 flex items-center justify-center" style="background:#FAD7D2;">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:#C0392B;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </div>
                        <h3 class="section-title">Hapus Pengguna?</h3>
                        <p class="section-subtitle mt-1">Pengguna <strong x-text="deleteData.name"></strong> akan dihapus permanen. Data terkait juga akan terhapus.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" @click="showDeleteModal = false" class="btn-secondary">Batal</button>
                        <button type="submit" class="btn-danger">Ya, Hapus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
