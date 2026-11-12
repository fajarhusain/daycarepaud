<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background: #1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h2 class="font-bold text-xl" style="color: #2C2C2C;">Manajemen Role</h2>
        </div>
    </x-slot>

    <div class="app-page-shell"
         x-data="{
            showCreateModal: false,
            showEditModal: false,
            showDeleteModal: false,
            showPermissionModal: false,
            editData: { id: '', name: '' },
            deleteData: { id: '', name: '' },
            permissionData: { id: '', name: '' },
            permissionChecks: {},
            openEdit(role) { this.editData = { id: role.id, name: role.name }; this.showEditModal = true; },
            openDelete(role) { this.deleteData = { id: role.id, name: role.name }; this.showDeleteModal = true; },
            openPermission(role, perms) {
                this.permissionData = { id: role.id, name: role.name };
                this.permissionChecks = {};
                (perms || []).forEach(p => { this.permissionChecks[p] = true; });
                this.showPermissionModal = true;
            },
            toggleGroup(groupPerms, checked) {
                groupPerms.forEach(p => { this.permissionChecks[p] = checked; });
            },
            groupAllChecked(groupPerms) {
                return groupPerms.every(p => this.permissionChecks[p]);
            },
            groupSomeChecked(groupPerms) {
                return groupPerms.some(p => this.permissionChecks[p]) && !this.groupAllChecked(groupPerms);
            }
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
                    <h3 class="section-title">Daftar Role</h3>
                    <p class="section-subtitle">Role digunakan untuk mengelompokkan hak akses pengguna</p>
                </div>
                <button @click="showCreateModal = true" class="btn-primary shrink-0 h-9 w-9 md:h-11 md:w-11 p-0" title="Tambah Role" aria-label="Tambah Role">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg></button>
            </div>

            <div class="overflow-x-auto">
                <table class="data-table w-full" data-tour="admin-role-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Role</th>
                            <th>Status</th>
                            <th>Akses Menu</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($roles as $index => $role)
                            @php
                                $isDefault = in_array($role->name, ['Lembaga','Admin Sekolah','Wali Kelas','Pengajar','Orang Tua']);
                                $rolePerms = $role->permissions->pluck('name')->toArray();
                            @endphp
                            <tr>
                                <td class="text-sm font-mono" style="color:#9E9790;">{{ $loop->iteration }}</td>
                                <td class="font-medium" style="color: #2C2C2C;">
                                    <div class="flex items-center gap-2">
                                        <span>{{ $role->name }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($isDefault)
                                        <span class="badge text-xs" style="background:#D0E8E8; color:#1A6B6B;">Default</span>
                                    @else
                                        <span class="badge text-xs" style="background:#E8E0D0; color:#8A7A6A;">Custom</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-xs font-medium" style="color:#5A5A5A;">{{ count($rolePerms) }} menu</span>
                                </td>
                                <td class="text-right">
                                    <div class="inline-flex items-center justify-end gap-1 whitespace-nowrap">
                                        <button @click="openPermission({{ json_encode(['id' => $role->id, 'name' => $role->name]) }}, {{ json_encode($rolePerms) }})"
                                            class="row-action-icon" style="color:#8A6E3A;background:#F0E6D0;" title="Akses Menu" aria-label="Akses Menu"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg></button>
                                        <button @click="openEdit({{ json_encode(['id' => $role->id, 'name' => $role->name]) }})"
                                            class="row-action-icon" style="color:#1A6B6B;background:#D0E8E8;" title="Edit" aria-label="Edit"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                                        @if($isDefault)
                                            <button type="button" disabled
                                                class="row-action-icon opacity-50 cursor-not-allowed"
                                                style="color:#9E9790;background:#EFECE8;"
                                                title="Role default tidak dapat dihapus"
                                                aria-label="Hapus (role default)">
                                                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        @else
                                            <button @click="openDelete({{ json_encode(['id' => $role->id, 'name' => $role->name]) }})"
                                                class="row-action-icon" style="color:#C0392B;background:#FAD7D2;" title="Hapus" aria-label="Hapus"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- CREATE MODAL -->
        <div x-show="showCreateModal" class="modal-overlay" style="display:none;">
            <div x-show="showCreateModal" x-transition class="modal-box" @click.away="showCreateModal = false">
                <form action="{{ route('admin.role.store') }}" method="POST">
                    @csrf
                    <div class="modal-header"><h3 class="section-title">Tambah Role Baru</h3></div>
                    <div class="modal-body space-y-4">
                        <div>
                            <label class="input-label">Nama Role</label>
                            <input type="text" name="name" required placeholder="contoh: Kepala Sekolah" class="input-field">
                            <p class="text-xs mt-1" style="color:#9E9790;">Gunakan nama yang mudah dikenali, misal: Kepala Sekolah, Bendahara</p>
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
                <form :action="`/admin/role/${editData.id}`" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-header"><h3 class="section-title">Edit Role</h3></div>
                    <div class="modal-body space-y-4">
                        <div>
                            <label class="input-label">Nama Role</label>
                            <input type="text" name="name" x-model="editData.name" required class="input-field">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" @click="showEditModal = false" class="btn-secondary">Batal</button>
                        <button type="submit" class="btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- PERMISSION MODAL -->
        <div x-show="showPermissionModal" class="modal-overlay" style="display:none;">
            <div x-show="showPermissionModal" x-transition class="modal-box max-w-2xl" @click.away="showPermissionModal = false">
                <form :action="`/admin/role/${permissionData.id}/update-permissions`" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h3 class="section-title">Akses Menu  <span x-text="permissionData.name"></span></h3>
                    </div>
                    <div class="modal-body max-h-[60vh] overflow-y-auto space-y-5">
                        <p class="text-xs" style="color:#9E9790;">Centang menu yang boleh diakses oleh role ini.</p>

                        @foreach($permissionGroups as $group => $perms)
                            @php $permNames = array_column($perms, 'name'); @endphp
                            <div class="border rounded-xl p-4" style="border-color:rgba(0,0,0,0.06);">
                                <div class="flex items-center justify-between mb-3">
                                    <label class="text-sm font-bold" style="color:#2C2C2C;">{{ $group }}</label>
                                    <div class="flex items-center gap-3">
                                        <button type="button" @click="toggleGroup({{ json_encode($permNames) }}, true)"
                                            class="text-xs font-semibold px-2 py-1 rounded" style="color:#1A6B6B;background:#D0E8E8;">Pilih Semua</button>
                                        <button type="button" @click="toggleGroup({{ json_encode($permNames) }}, false)"
                                            class="text-xs font-semibold px-2 py-1 rounded" style="color:#9E9790;background:#EFECE8;">Hapus Semua</button>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    @foreach($perms as $perm)
                                        <label class="flex items-center gap-3 cursor-pointer px-2 py-1.5 rounded-lg hover:bg-black/5 transition-colors">
                                            <input type="checkbox"
                                                   name="permissions[]"
                                                   value="{{ $perm['name'] }}"
                                                   x-model="permissionChecks['{{ $perm['name'] }}']"
                                                   class="h-4 w-4 rounded border-gray-300"
                                                   style="accent-color:#1A6B6B;">
                                            <span class="text-sm font-medium" style="color:#2C2C2C;">{{ $perm['label'] }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="modal-footer">
                        <button type="button" @click="showPermissionModal = false" class="btn-secondary">Batal</button>
                        <button type="submit" class="btn-primary">Simpan Akses Menu</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- DELETE MODAL -->
        <div x-show="showDeleteModal" class="modal-overlay" style="display:none;">
            <div x-show="showDeleteModal" x-transition class="modal-box max-w-sm" @click.away="showDeleteModal = false">
                <form :action="`/admin/role/${deleteData.id}`" method="POST">
                    @csrf @method('DELETE')
                    <div class="modal-body text-center py-6">
                        <div class="h-14 w-14 rounded-2xl mx-auto mb-4 flex items-center justify-center" style="background:#FAD7D2;">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:#C0392B;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </div>
                        <h3 class="section-title">Hapus Role?</h3>
                        <p class="section-subtitle mt-1">Role <strong x-text="deleteData.name"></strong> akan dihapus permanen. Pengguna dengan role ini perlu diassign ulang.</p>
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
