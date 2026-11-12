<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background: #1A6B6B;"><svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></div>
            <h2 class="font-bold text-xl" style="color: #2C2C2C;">Skala Capaian</h2>
        </div>
    </x-slot>
    <div class="app-page-shell" x-data="{
        showCreateModal:false,
        showEditModal:false,
        showDeleteModal:false,
        editData:{},
        deleteRoute:'',
        openEdit(d){this.editData=d;this.showEditModal=true},
        openDelete(r){this.deleteRoute=r;this.showDeleteModal=true}
    }" @tour-close-modals.window="showCreateModal=false; showEditModal=false; showDeleteModal=false">
        @if(session('success'))<div class="alert-success mb-5"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert-danger mb-5"><ul class="list-disc pl-5 text-sm">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul></div>@endif
        <div class="card overflow-hidden">
            <div class="px-4 md:px-6 py-4 border-b" style="border-color:rgba(0,0,0,0.06);">
                <h3 class="section-title">Master skala capaian</h3>
                <p class="section-subtitle">Opsi penilaian pencapaian siswa (mis. BB, MB, BSH, BSB). Dipakai di form pencapaian dan saran AI.</p>
            </div>

            <form method="get" data-tour="admin-skala-filter" class="card-pad toolbar-form border-b w-full" style="border-color:rgba(0,0,0,0.06);">
                <div class="toolbar-field md:min-w-[12rem] md:flex-[2]">
                    <label class="input-label">Cari</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="input-field w-full" placeholder="Kode atau label…">
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
                    <x-filter-reset :href="route('admin.skala-pencapaian.index')" compact />
                    <x-export-excel route="admin.skala-pencapaian.export" :icon-only="true" />
                    <x-toolbar.add-icon label="Tambah Skala" data-tour="admin-skala-add-btn" data-tour-open-modal="create" type="button" @click="showCreateModal=true" />
                </div>
            </form>

            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr><th>Kode</th><th>Label</th><th>Warna</th><th>Urutan</th><th>Status</th><th class="text-right">Aksi</th></tr></thead>
                    <tbody>
                        @forelse($skalas as $s)
                        <tr>
                            <td><span class="font-mono font-bold" style="color:#2C2C2C;">{{ $s->code }}</span></td>
                            <td>{{ $s->label }}</td>
                            <td>
                                <span class="inline-block w-8 h-5 rounded border" style="background:{{ $s->color }}; border-color:rgba(0,0,0,0.1);" title="{{ $s->color }}"></span>
                            </td>
                            <td>{{ $s->sort_order }}</td>
                            <td>
                                @if($s->is_active)
                                    <span class="badge badge-teal">Aktif</span>
                                @else
                                    <span class="badge" style="background:#eee;color:#666;">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @php
                                    $payload = [
                                        'id' => $s->id,
                                        'code' => $s->code,
                                        'label' => $s->label,
                                        'color' => $s->color,
                                        'sort_order' => $s->sort_order,
                                        'is_active' => $s->is_active,
                                    ];
                                @endphp
                                <div class="flex flex-wrap items-center justify-end gap-1.5">
                                    <x-table.action-icon type="button" label="Edit" variant="edit"
                                        @if($loop->first) data-tour="admin-skala-action-edit" data-tour-open-modal="edit" @endif
                                        @click="openEdit(@js($payload))" />
                                    <x-table.action-icon type="button" label="Hapus" variant="delete"
                                        @if($loop->first) data-tour="admin-skala-action-delete" data-tour-demo-action="delete" @endif
                                        @click="openDelete('{{ route('admin.skala-pencapaian.destroy', $s) }}')" />
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="py-6 md:py-12 text-center" style="color:#9E9790;">Belum ada skala capaian.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 md:px-6 py-4 border-t" style="border-color:rgba(0,0,0,0.06);">
                <x-per-page-selector :paginator="$skalas" />
                {{ $skalas->links() }}
            </div>
        </div>

        <div x-show="showCreateModal" class="modal-overlay" style="display:none;">
            <div x-show="showCreateModal" x-transition class="modal-box" @click.away="showCreateModal=false">
                <form action="{{ route('admin.skala-pencapaian.store') }}" method="POST">
                    @csrf
                    <div class="modal-header"><h3 class="section-title">Tambah skala capaian</h3></div>
                    <div class="modal-body space-y-4">
                        <div class="text-[13px] font-bold text-[#1A6B6B] mt-1 border-b pb-1" data-tour="modal-create-section-form">Data Skala</div>
                        <div>
                            <label class="input-label">Kode <span class="text-red-600">*</span></label>
                            <input type="text" name="code" required maxlength="20" class="input-field uppercase @error('code') border-red-500 @enderror" placeholder="Contoh: MB, K1" value="{{ old('code') }}">
                            <p class="text-[10px] text-gray-500 mt-1">Huruf, angka, dan underscore. Disimpan huruf besar.</p>
                            @error('code')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="input-label">Label <span class="text-red-600">*</span></label>
                            <input type="text" name="label" required class="input-field @error('label') border-red-500 @enderror" placeholder="Contoh: Mulai Berkembang" value="{{ old('label') }}">
                            @error('label')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="input-label">Warna badge <span class="text-red-600">*</span></label>
                            <input type="color" name="color" required class="h-10 w-20 rounded border" value="{{ old('color', '#FDE9BC') }}">
                            @error('color')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="input-label">Urutan tampil</label>
                            <input type="number" name="sort_order" min="0" class="input-field" value="{{ old('sort_order', 0) }}">
                        </div>
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="is_active" value="1" checked class="rounded">
                            Aktif (tampil di form penilaian)
                        </label>
                    </div>
                    <div class="modal-footer"><button type="button" @click="showCreateModal=false" class="btn-secondary">Batal</button><button type="submit" data-tour="modal-create-submit" class="btn-primary">Simpan</button></div>
                </form>
            </div>
        </div>

        <div x-show="showEditModal" class="modal-overlay" style="display:none;">
            <div x-show="showEditModal" x-transition class="modal-box" @click.away="showEditModal=false">
                <form :action="`/admin/skala-pencapaian/${editData.id}`" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-header"><h3 class="section-title">Edit skala capaian</h3></div>
                    <div class="modal-body space-y-4">
                        <div class="text-[13px] font-bold text-[#1A6B6B] mt-1 border-b pb-1" data-tour="modal-edit-section-form">Data Skala</div>
                        <div>
                            <label class="input-label">Kode</label>
                            <input type="text" name="code" x-model="editData.code" required maxlength="20" class="input-field uppercase">
                        </div>
                        <div>
                            <label class="input-label">Label</label>
                            <input type="text" name="label" x-model="editData.label" required class="input-field">
                        </div>
                        <div>
                            <label class="input-label">Warna badge</label>
                            <input type="color" name="color" x-model="editData.color" required class="h-10 w-20 rounded border">
                        </div>
                        <div>
                            <label class="input-label">Urutan tampil</label>
                            <input type="number" name="sort_order" x-model="editData.sort_order" min="0" class="input-field">
                        </div>
                        <label class="flex items-center gap-2 text-sm">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" :checked="editData.is_active" class="rounded">
                            Aktif
                        </label>
                    </div>
                    <div class="modal-footer"><button type="button" @click="showEditModal=false" class="btn-secondary">Batal</button><button type="submit" data-tour="modal-edit-submit" class="btn-primary">Simpan</button></div>
                </form>
            </div>
        </div>

        <div x-show="showDeleteModal" data-tour="modal-delete" class="modal-overlay" style="display:none;">
            <div x-show="showDeleteModal" x-transition class="modal-box max-w-sm" @click.away="showDeleteModal=false">
                <form :action="deleteRoute" method="POST">
                    @csrf @method('DELETE')
                    <div class="modal-body text-center py-6">
                        <h3 class="section-title">Hapus skala capaian?</h3>
                        <p class="section-subtitle mt-1">Tidak dapat dihapus jika masih dipakai di data pencapaian.</p>
                    </div>
                    <div class="modal-footer"><button type="button" @click="showDeleteModal=false" class="btn-secondary">Batal</button><button type="submit" class="btn-danger">Ya, hapus</button></div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
