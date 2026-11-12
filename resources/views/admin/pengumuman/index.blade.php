<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background: #1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                </svg>
            </div>
            <h2 class="font-bold text-xl" style="color: #2C2C2C;">Kelola Pengumuman</h2>
        </div>
    </x-slot>

    <div class="app-page-shell"
         x-data="{
            showCreateModal: false,
            showEditModal: false,
            showDetailModal: false,
            showDeleteModal: false,
            showImageModal: false,
            activeImage: '',
            editData: {},
            detailData: {},
            deleteRoute: '',
            openEdit(d) { this.editData = d; this.showEditModal = true; },
            openDetail(d) { this.detailData = d; this.showDetailModal = true; },
            openDelete(r) { this.deleteRoute = r; this.showDeleteModal = true; }
         }"
         @tour-close-modals.window="showCreateModal=false; showEditModal=false; showDetailModal=false; showDeleteModal=false; showImageModal=false">
        @if(session('success'))
            <div class="alert-success mb-5">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="alert-danger mb-5">
                <ul class="list-disc pl-5 text-sm">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="card overflow-hidden">
            <div class="px-4 md:px-6 py-4 border-b" style="border-color:rgba(0,0,0,0.06);">
                <h3 class="section-title">Pengumuman untuk Orang Tua</h3>
                <p class="section-subtitle">{{ ($waliKelasIds ?? null) !== null ? 'Informasi untuk orang tua kelas wali Anda' : 'Broadcast informasi ke wali murid via dashboard' }}</p>
            </div>

            <form method="get" data-tour="admin-pengumuman-filter" class="card-pad toolbar-form border-b w-full" style="border-color:rgba(0,0,0,0.06);">
                <div class="toolbar-field md:min-w-[12rem] md:flex-[2]">
                    <label class="input-label">Cari</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="input-field w-full" placeholder="Judul, kategori, atau isi…">
                </div>
                @if($kelasList->isNotEmpty())
                    <div class="toolbar-field md:min-w-[9rem] md:max-w-[11rem]">
                        <label class="input-label">Kelas</label>
                        <select name="kelas_id" class="input-field w-full">
                            <option value="">Semua</option>
                            @foreach($kelasList as $kelas)
                                <option value="{{ $kelas->id }}" @selected((string) request('kelas_id') === (string) $kelas->id)>{{ $kelas->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="toolbar-field md:min-w-[9rem] md:max-w-[11rem]">
                    <label class="input-label">Status tayang</label>
                    <select name="status" class="input-field w-full">
                        <option value="" @selected(!request('status'))>Semua</option>
                        <option value="aktif" @selected(request('status') === 'aktif')>Sedang tayang</option>
                        <option value="belum_tayang" @selected(request('status') === 'belum_tayang')>Belum tayang</option>
                        <option value="berakhir" @selected(request('status') === 'berakhir')>Berakhir</option>
                        <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option>
                    </select>
                </div>
                <div class="toolbar-actions">
                    <x-toolbar.filter-icon label="Filter" variant="secondary" />
                    <x-filter-reset :href="route('admin.pengumuman.index')" compact />
                    <x-toolbar.add-icon label="Tambah Pengumuman" type="button" @click="showCreateModal=true" />
                </div>
            </form>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Kategori</th>
                            <th>Kelas</th>
                            <th>Periode Tayang</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pengumumans as $p)
                            @php
                                $isActiveNow = $p->is_active
                                    && $p->mulai_tayang->lte(today())
                                    && $p->selesai_tayang->gte(today());
                                $detailPayload = [
                                    'id' => $p->id,
                                    'judul' => $p->judul,
                                    'kategori' => $p->kategori,
                                    'isi' => $p->isi,
                                    'mulai_tayang' => $p->mulai_tayang->translatedFormat('d M Y'),
                                    'selesai_tayang' => $p->selesai_tayang->translatedFormat('d M Y'),
                                    'is_active' => $p->is_active,
                                    'is_active_now' => $isActiveNow,
                                    'gambar_url' => $p->gambar ? Storage::url($p->gambar) : null,
                                    'kelas_id' => $p->kelas_id,
                                    'kelas_name' => $p->kelas?->name,
                                ];
                                $editPayload = [
                                    'id' => $p->id,
                                    'judul' => $p->judul,
                                    'kategori' => $p->kategori,
                                    'isi' => $p->isi,
                                    'mulai_tayang' => $p->mulai_tayang->format('Y-m-d'),
                                    'selesai_tayang' => $p->selesai_tayang->format('Y-m-d'),
                                    'is_active' => $p->is_active,
                                    'gambar_url' => $p->gambar ? Storage::url($p->gambar) : null,
                                    'kelas_id' => $p->kelas_id,
                                    'kelas_name' => $p->kelas?->name,
                                ];
                            @endphp
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        @if($p->gambar)
                                            <img src="{{ Storage::url($p->gambar) }}" class="h-8 w-8 rounded-xl object-cover shrink-0" alt="">
                                        @else
                                            <div class="h-8 w-8 rounded-xl flex items-center justify-center font-bold text-xs text-white shrink-0" style="background:#1A6B6B;">{{ mb_substr($p->judul, 0, 1) }}</div>
                                        @endif
                                        <span class="font-semibold" style="color:#2C2C2C;">{{ $p->judul }}</span>
                                    </div>
                                </td>
                                <td><span class="badge badge-teal">{{ $p->kategori }}</span></td>
                                <td class="text-sm" style="color:#6B6560;">
                                    @if($p->kelas?->name)
                                        <span class="badge badge-teal">{{ $p->kelas->name }}</span>
                                    @else
                                        Semua kelas
                                    @endif
                                </td>
                                <td class="text-sm" style="color:#6B6560;">{{ $p->mulai_tayang->translatedFormat('d M Y') }} – {{ $p->selesai_tayang->translatedFormat('d M Y') }}</td>
                                <td>
                                    @if($isActiveNow)
                                        <span class="badge badge-green">Aktif</span>
                                    @elseif(!$p->is_active)
                                        <span class="badge badge-rose">Nonaktif</span>
                                    @elseif($p->mulai_tayang->gt(today()))
                                        <span class="badge badge-amber">Belum Tayang</span>
                                    @else
                                        <span class="badge badge-amber">Berakhir</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <div class="flex flex-wrap items-center justify-end gap-1.5">
                                        <x-table.action-icon type="button" label="Detail" variant="detail" @click="openDetail(@js($detailPayload))" />
                                        <x-table.action-icon type="button" label="Edit" variant="edit" @click="openEdit(@js($editPayload))" />
                                        <x-table.action-icon type="button" label="Hapus" variant="delete" @click="openDelete('{{ route('admin.pengumuman.destroy', $p) }}')" />
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-6 md:py-12 text-center" style="color:#9E9790;">Belum ada pengumuman.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-4 md:px-6 py-4 border-t" style="border-color:rgba(0,0,0,0.06);">
                <x-per-page-selector :paginator="$pengumumans" />
                {{ $pengumumans->links() }}
            </div>
        </div>

        {{-- DETAIL MODAL --}}
        <div x-show="showDetailModal" class="modal-overlay" style="display:none;">
            <div x-show="showDetailModal" x-transition class="modal-box max-w-2xl" @click.away="showDetailModal=false">
                <div class="modal-header flex items-center justify-between">
                    <h3 class="section-title">Detail Pengumuman</h3>
                    <button type="button" @click="showDetailModal=false" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>
                <div class="modal-body space-y-4">
                    <div x-show="detailData.gambar_url" class="rounded-2xl overflow-hidden border border-gray-100 cursor-pointer" @click="activeImage = detailData.gambar_url; showImageModal = true">
                        <img :src="detailData.gambar_url" class="w-full max-h-72 object-cover" alt="Gambar pengumuman">
                    </div>
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wide mb-1 block" style="color:#9E9790;">Kategori</span>
                        <span class="text-sm border px-2 py-0.5 rounded text-gray-600 bg-gray-50" x-text="detailData.kategori || ''"></span>
                    </div>
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wide mb-1 block" style="color:#9E9790;">Judul</span>
                        <p class="text-lg font-bold" style="color:#2C2C2C;" x-text="detailData.judul || ''"></p>
                    </div>
                    <div>
                        <span class="text-xs font-semibold uppercase tracking-wide mb-1 block" style="color:#9E9790;">Isi</span>
                        <p class="text-sm whitespace-pre-wrap" style="color:#4A4540;" x-text="detailData.isi || ''"></p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-xs font-semibold uppercase tracking-wide mb-1 block" style="color:#9E9790;">Mulai Tayang</span>
                            <p class="text-sm" x-text="detailData.mulai_tayang || ''"></p>
                        </div>
                        <div>
                            <span class="text-xs font-semibold uppercase tracking-wide mb-1 block" style="color:#9E9790;">Selesai Tayang</span>
                            <p class="text-sm" x-text="detailData.selesai_tayang || ''"></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" @click="showDetailModal=false" class="btn-secondary">Tutup</button></div>
            </div>
        </div>

        {{-- CREATE MODAL --}}
        <div x-show="showCreateModal" class="modal-overlay" style="display:none;">
            <div x-show="showCreateModal" x-transition class="modal-box max-w-2xl" @click.away="showCreateModal=false">
                <form action="{{ route('admin.pengumuman.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header"><h3 class="section-title">Tambah Pengumuman</h3></div>
                    <div class="modal-body space-y-4">
                        <div><label class="input-label">Judul</label><input type="text" name="judul" value="{{ old('judul') }}" required class="input-field" placeholder="Contoh: Libur Nasional"></div>
                        <div><label class="input-label">Kategori</label><input type="text" name="kategori" value="{{ old('kategori') }}" required class="input-field" placeholder="Contoh: Umum, Keuangan, Kegiatan"></div>
                        @if(($waliKelasIds ?? null) === null)
                            <div>
                                <label class="input-label">Target Kelas</label>
                                <select name="kelas_id" class="input-field">
                                    <option value="">Semua kelas</option>
                                    @foreach($kelasList as $kelas)
                                        <option value="{{ $kelas->id }}" @selected((int) old('kelas_id') === $kelas->id)>{{ $kelas->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <div>
                                <label class="input-label">Kelas</label>
                                <select name="kelas_id" required class="input-field">
                                    @foreach($kelasList as $kelas)
                                        <option value="{{ $kelas->id }}" @selected((int) old('kelas_id', $kelasList->first()?->id) === $kelas->id)>{{ $kelas->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div><label class="input-label">Isi / Info Lainnya</label><textarea name="isi" rows="5" required class="input-field" placeholder="Tulis detail pengumuman...">{{ old('isi') }}</textarea></div>
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="input-label">Mulai Tayang</label><input type="date" name="mulai_tayang" value="{{ old('mulai_tayang', now()->format('Y-m-d')) }}" required class="input-field"></div>
                            <div><label class="input-label">Selesai Tayang</label><input type="date" name="selesai_tayang" value="{{ old('selesai_tayang') }}" required class="input-field"></div>
                        </div>
                        <div><label class="input-label">Gambar (opsional)</label><input type="file" name="gambar" accept="image/*" class="input-field py-1.5"></div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-teal-700 focus:ring-teal-600">
                            <span class="text-sm" style="color:#4A4540;">Aktifkan pengumuman</span>
                        </label>
                    </div>
                    <div class="modal-footer flex gap-2 justify-end">
                        <button type="button" @click="showCreateModal=false" class="btn-secondary">Batal</button>
                        <button type="submit" class="btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- EDIT MODAL --}}
        <div x-show="showEditModal" class="modal-overlay" style="display:none;">
            <div x-show="showEditModal" x-transition class="modal-box max-w-2xl" @click.away="showEditModal=false">
                <form :action="'/admin/pengumuman/' + editData.id" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-header"><h3 class="section-title">Edit Pengumuman</h3></div>
                    <div class="modal-body space-y-4">
                        <div><label class="input-label">Judul</label><input type="text" name="judul" x-model="editData.judul" required class="input-field"></div>
                        <div><label class="input-label">Kategori</label><input type="text" name="kategori" x-model="editData.kategori" required class="input-field"></div>
                        @if(($waliKelasIds ?? null) === null)
                            <div>
                                <label class="input-label">Target Kelas</label>
                                <select name="kelas_id" x-model="editData.kelas_id" class="input-field">
                                    <option value="">Semua kelas</option>
                                    @foreach($kelasList as $kelas)
                                        <option value="{{ $kelas->id }}">{{ $kelas->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <input type="hidden" name="kelas_id" x-model="editData.kelas_id">
                            <div>
                                <label class="input-label">Kelas</label>
                                <p class="text-sm font-semibold" style="color:#2C2C2C;" x-text="editData.kelas_name || ''"></p>
                            </div>
                        @endif
                        <div><label class="input-label">Isi / Info Lainnya</label><textarea name="isi" rows="5" x-model="editData.isi" required class="input-field"></textarea></div>
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="input-label">Mulai Tayang</label><input type="date" name="mulai_tayang" x-model="editData.mulai_tayang" required class="input-field"></div>
                            <div><label class="input-label">Selesai Tayang</label><input type="date" name="selesai_tayang" x-model="editData.selesai_tayang" required class="input-field"></div>
                        </div>
                        <div>
                            <label class="input-label">Gambar (opsional)</label>
                            <div x-show="editData.gambar_url" class="mb-2">
                                <img :src="editData.gambar_url" class="h-20 w-32 object-cover rounded-xl border border-gray-100" alt="">
                            </div>
                            <input type="file" name="gambar" accept="image/*" class="input-field py-1.5">
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" :checked="editData.is_active" class="rounded border-gray-300 text-teal-700 focus:ring-teal-600">
                            <span class="text-sm" style="color:#4A4540;">Aktifkan pengumuman</span>
                        </label>
                    </div>
                    <div class="modal-footer flex gap-2 justify-end">
                        <button type="button" @click="showEditModal=false" class="btn-secondary">Batal</button>
                        <button type="submit" class="btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>

        <div x-show="showDeleteModal" class="modal-overlay" style="display:none;">
            <div x-show="showDeleteModal" x-transition class="modal-box max-w-sm" @click.away="showDeleteModal=false">
                <form :action="deleteRoute" method="POST">
                    @csrf @method('DELETE')
                    <div class="modal-body text-center py-6">
                        <div class="h-14 w-14 rounded-2xl mx-auto mb-4 flex items-center justify-center" style="background:#FAD7D2;">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:#C0392B;"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </div>
                        <h3 class="section-title">Hapus Pengumuman?</h3>
                        <p class="section-subtitle mt-1">Pengumuman yang dihapus tidak dapat dikembalikan.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" @click="showDeleteModal=false" class="btn-secondary">Batal</button>
                        <button type="submit" class="btn-danger">Ya, Hapus</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- IMAGE LIGHTBOX --}}
        <div x-show="showImageModal" class="modal-overlay modal-overlay--elevated modal-overlay--dark" style="display:none;" x-transition @keydown.escape.window="showImageModal=false">
            <div class="relative max-w-4xl w-full" @click.away="showImageModal=false">
                <img :src="activeImage" class="w-full h-auto max-h-[85vh] object-contain rounded-2xl shadow-2xl bg-white shadow-black/20" alt="Gambar pengumuman">
            </div>
        </div>
    </div>
</x-app-layout>
