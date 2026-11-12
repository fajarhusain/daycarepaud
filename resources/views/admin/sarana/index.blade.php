<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background: #1A6B6B;"><svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg></div>
            <h2 class="font-bold text-xl" style="color: #2C2C2C;">Kelola Sarana Prasarana</h2>
        </div>
    </x-slot>
    <div class="app-page-shell" x-data="{ showCreateModal:false, showEditModal:false, showDetailModal:false, showDeleteModal:false, showImageModal:false, activeImage:'', activeDownloadUrl:null, editData:{}, detailData:{}, deleteRoute:'', openEdit(d){this.editData=d;this.showEditModal=true}, openDetail(d){this.detailData=d;this.showDetailModal=true}, openDelete(r){this.deleteRoute=r;this.showDeleteModal=true} }" @tour-close-modals.window="showCreateModal=false; showEditModal=false; showDetailModal=false; showDeleteModal=false; showImageModal=false">
        @if(session('success'))<div class="alert-success mb-5"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert-danger mb-5"><ul class="list-disc pl-5 text-sm">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul></div>@endif
        <div class="card overflow-hidden">
            <div class="px-6 py-4 flex items-center justify-between border-b" style="border-color:rgba(0,0,0,0.06);">
                <div><h3 class="section-title">Inventaris Sarana & Prasarana</h3><p class="section-subtitle">Pantau kondisi dan jumlah sarana yang dimiliki sekolah</p></div>
                <div class="flex items-center gap-2">
                    <x-export-excel route="admin.sarana.export" />
                    <button data-tour="admin-sarana-add-btn" data-tour-open-modal="create" @click="showCreateModal=true" class="btn-primary shrink-0 h-9 w-9 md:h-11 md:w-11 p-0" title="Tambah Sarana" aria-label="Tambah Sarana"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg></button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead><tr><th>Nama Sarana</th><th>Lokasi</th><th>Jenis</th><th class="text-center">Jumlah</th><th>Kondisi</th><th class="text-right">Aksi</th></tr></thead>
                    <tbody>
                        @forelse($saranas as $s)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    @if($s->photo)
                                        <img src="{{ Storage::url($s->photo) }}" class="h-8 w-8 rounded-xl object-cover shrink-0">
                                    @else
                                        <div class="h-8 w-8 rounded-xl flex items-center justify-center font-bold text-xs text-white shrink-0" style="background:#1A6B6B;">{{ substr($s->name, 0, 1) }}</div>
                                    @endif
                                    <span class="font-semibold" style="color:#2C2C2C;">{{ $s->name }}</span>
                                </div>
                            </td>
                            <td><span class="text-sm border px-2 py-0.5 rounded text-gray-600 bg-gray-50">{{ $s->lokasi ?? '-' }}</span></td>
                            <td><span class="text-sm border px-2 py-0.5 rounded text-gray-600 bg-gray-50">{{ $s->jenis ?? '-' }}</span></td>
                            <td class="text-center"><span class="badge badge-teal">{{ $s->quantity }}</span></td>
                            <td>
                                @if(strtolower($s->condition)==='baik') <span class="badge badge-green">Baik</span>
                                @elseif(strtolower($s->condition)==='rusak') <span class="badge badge-rose">Rusak</span>
                                @else <span class="badge badge-amber">{{ $s->condition }}</span> @endif
                            </td>
                            <td class="text-right"><div class="flex items-center justify-end gap-2">
                                @php
                                    $detailPayload = [
                                        'id' => $s->id,
                                        'name' => $s->name,
                                        'lokasi' => $s->lokasi,
                                        'jenis' => $s->jenis,
                                        'quantity' => $s->quantity,
                                        'condition' => $s->condition,
                                        'photo_url' => $s->photo ? Storage::url($s->photo) : null,
                                        'photo_download_url' => $s->photo ? route('admin.sarana.photo.download', $s) : null,
                                    ];
                                @endphp
                                <button type="button" @if($loop->first) data-tour="admin-sarana-action-detail" data-tour-open-modal="detail" @endif @click="openDetail(@js($detailPayload))" class="row-action-icon" style="color:#1A6B6B;background:#F0F7F7;border:1px solid #D0E8E8;" title="Detail" aria-label="Detail"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button>
                                <button @if($loop->first) data-tour="admin-sarana-action-edit" data-tour-open-modal="edit" @endif @click="openEdit({{ json_encode($s) }})" class="row-action-icon" style="color:#1A6B6B;background:#D0E8E8;" title="Edit" aria-label="Edit"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                                <button @if($loop->first) data-tour="admin-sarana-action-delete" data-tour-demo-action="delete" @endif @click="openDelete('{{ route('admin.sarana.destroy', $s) }}')" class="row-action-icon" style="color:#C0392B;background:#FAD7D2;" title="Hapus" aria-label="Hapus"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            </div></td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="py-6 md:py-12 text-center" style="color:#9E9790;">Belum ada data sarana.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t" style="border-color:rgba(0,0,0,0.06);">
                <x-per-page-selector :paginator="$saranas" />
                {{ $saranas->links() }}
            </div>
        </div>
        <!-- DETAIL MODAL -->
        <div x-show="showDetailModal" data-tour="modal-detail" class="modal-overlay" style="display:none;">
            <div x-show="showDetailModal" x-transition class="modal-box max-w-2xl" @click.away="showDetailModal=false">
                <div class="modal-header flex items-center justify-between">
                    <h3 class="section-title">Detail Sarana</h3>
                    <button type="button" @click="showDetailModal=false" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>
                <div class="modal-body space-y-5" data-tour="modal-detail-content">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide mb-2" style="color:#9E9790;">Foto Sarana</p>
                        <div x-show="detailData.photo_url"
                             class="rounded-2xl overflow-hidden border border-gray-100 cursor-pointer shadow-sm"
                             @click="activeImage = detailData.photo_url; activeDownloadUrl = detailData.photo_download_url; showImageModal = true">
                            <img :src="detailData.photo_url" class="w-full max-h-72 object-cover hover:scale-[1.02] transition duration-300" alt="Foto sarana">
                        </div>
                        <div x-show="!detailData.photo_url"
                             class="rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50 flex flex-col items-center justify-center py-10 text-center">
                            <svg class="h-10 w-10 mb-2" style="color:#9E9790;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            <p class="text-sm" style="color:#9E9790;">Belum ada foto sarana</p>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide mb-1" style="color:#9E9790;">Nama Sarana</p>
                        <p class="text-lg font-bold" style="color:#2C2C2C;" x-text="detailData.name || ''"></p>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide mb-1" style="color:#9E9790;">Lokasi</p>
                            <span class="text-sm border px-2 py-0.5 rounded text-gray-600 bg-gray-50" x-text="detailData.lokasi || '-'"></span>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide mb-1" style="color:#9E9790;">Jenis</p>
                            <span class="text-sm border px-2 py-0.5 rounded text-gray-600 bg-gray-50" x-text="detailData.jenis || '-'"></span>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide mb-1" style="color:#9E9790;">Jumlah</p>
                            <span class="badge badge-teal" x-text="detailData.quantity ?? ''"></span>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide mb-1" style="color:#9E9790;">Kondisi</p>
                            <span class="badge badge-green" x-text="detailData.condition || ''"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" @click="showDetailModal=false" class="btn-secondary w-full sm:w-auto">Tutup</button></div>
            </div>
        </div>
        <x-image-lightbox />
        <!-- CREATE MODAL -->
        <div x-show="showCreateModal" class="modal-overlay" style="display:none;">
            <div x-show="showCreateModal" x-transition class="modal-box" @click.away="showCreateModal=false">
                <form action="{{ route('admin.sarana.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header"><h3 class="section-title">Tambah Sarana Baru</h3></div>
                    <div class="modal-body space-y-4">
                        <div class="text-[13px] font-bold text-[#1A6B6B] mt-1 border-b pb-1" data-tour="modal-create-section-form">Data Sarana</div>
                        <div><label class="input-label">Nama Sarana / Alat / Ruangan</label><input type="text" name="name" required class="input-field" placeholder="Contoh: Kursi Belajar"></div>
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="input-label">Lokasi Penempatan</label><select name="lokasi" class="input-field"><option value="">Pilih...</option><option value="Indoor">Indoor (Dalam Ruangan)</option><option value="Outdoor">Outdoor (Luar Ruangan)</option></select></div>
                            <div><label class="input-label">Jenis Sarana</label><select name="jenis" class="input-field"><option value="">Pilih...</option><option value="Edukasi">Edukasi / Pembelajaran</option><option value="Permainan">Permainan Bebas</option><option value="Sarpras">Sarpras</option><option value="ATK">ATK</option></select></div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="input-label">Jumlah</label><input type="number" name="quantity" min="1" value="1" required class="input-field"></div>
                            <div><label class="input-label">Kondisi</label><select name="condition" class="input-field"><option value="Baik">Baik</option><option value="Rusak">Rusak</option><option value="Dalam Perbaikan">Dalam Perbaikan</option></select></div>
                        </div>
                        <div><label class="input-label">Foto Sarana</label><input type="file" name="photo" accept="image/*" class="input-field py-1.5"></div>
                    </div>
                    <div class="modal-footer"><button type="button" @click="showCreateModal=false" class="btn-secondary">Batal</button><button type="submit" data-tour="modal-create-submit" class="btn-primary">Simpan</button></div>
                </form>
            </div>
        </div>
        <!-- EDIT MODAL -->
        <div x-show="showEditModal" class="modal-overlay" style="display:none;">
            <div x-show="showEditModal" x-transition class="modal-box" @click.away="showEditModal=false">
                <form :action="`/admin/sarana/${editData.id}`" method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="modal-header"><h3 class="section-title">Edit Data Sarana</h3></div>
                    <div class="modal-body space-y-4">
                        <div class="text-[13px] font-bold text-[#1A6B6B] mt-1 border-b pb-1" data-tour="modal-edit-section-form">Data Sarana</div>
                        <div><label class="input-label">Nama Sarana</label><input type="text" name="name" x-model="editData.name" required class="input-field"></div>
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="input-label">Lokasi Penempatan</label><select name="lokasi" x-model="editData.lokasi" class="input-field"><option value="">Pilih...</option><option value="Indoor">Indoor (Dalam Ruangan)</option><option value="Outdoor">Outdoor (Luar Ruangan)</option></select></div>
                            <div><label class="input-label">Jenis Sarana</label><select name="jenis" x-model="editData.jenis" class="input-field"><option value="">Pilih...</option><option value="Edukasi">Edukasi / Pembelajaran</option><option value="Permainan">Permainan Bebas</option><option value="Sarpras">Sarpras</option><option value="ATK">ATK</option></select></div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="input-label">Jumlah</label><input type="number" name="quantity" x-model="editData.quantity" min="1" required class="input-field"></div>
                            <div><label class="input-label">Kondisi</label><select name="condition" x-model="editData.condition" class="input-field"><option value="Baik">Baik</option><option value="Rusak">Rusak</option><option value="Dalam Perbaikan">Dalam Perbaikan</option></select></div>
                        </div>
                        <div><label class="input-label">Ganti Foto</label><input type="file" name="photo" accept="image/*" class="input-field py-1.5"></div>
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
                    <div class="modal-body text-center py-6"><div class="h-14 w-14 rounded-2xl mx-auto mb-4 flex items-center justify-center" style="background:#FAD7D2;"><svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:#C0392B;"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></div><h3 class="section-title">Hapus Sarana?</h3><p class="section-subtitle mt-1">Tindakan ini tidak dapat dibatalkan.</p></div>
                    <div class="modal-footer"><button type="button" @click="showDeleteModal=false" class="btn-secondary">Batal</button><button type="submit" class="btn-danger">Ya, Hapus</button></div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
