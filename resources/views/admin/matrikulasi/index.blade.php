<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background: #1A6B6B;"><svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg></div>
            <h2 class="font-bold text-xl" style="color: #2C2C2C;">Matrikulasi Sekolah</h2>
        </div>
    </x-slot>
    <div class="app-page-shell" x-data="{
        showCreateModal: @js($errors->any() && ! $errors->has('file')),
        showImportModal: @js($errors->has('file')),
        showEditModal: false,
        showDetailModal: false,
        showDeleteModal: false,
        editData: {},
        detailData: {},
        deleteRoute: '',
        importTesting: false,
        importTest: null,
        importTestError: null,
        importFileName: null,
        openEdit(d) { this.editData = d; this.showEditModal = true },
        openDetail(d) { this.detailData = d; this.showDetailModal = true },
        openDelete(r) { this.deleteRoute = r; this.showDeleteModal = true },
        openImportModal() {
            this.importTest = null;
            this.importTestError = null;
            this.importTesting = false;
            this.importFileName = null;
            this.showImportModal = true;
            this.$nextTick(() => {
                const input = this.$refs.importForm?.querySelector('input[type=file]');
                if (input) input.value = '';
            });
        },
        onImportFileSelected(event) {
            const file = event.target.files?.[0];
            this.importFileName = file ? file.name : null;
            this.resetImportTest();
        },
        onImportFileDropped(event) {
            const file = event.dataTransfer?.files?.[0];
            if (!file) return;
            const input = this.$refs.importForm?.querySelector('input[type=file]');
            if (!input) return;
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
            this.importFileName = file.name;
            this.resetImportTest();
        },
        resetImportTest() {
            this.importTest = null;
            this.importTestError = null;
        },
        async runImportTest() {
            this.importTestError = null;
            const form = this.$refs.importForm;
            const fileInput = form?.querySelector('input[type=file]');
            if (!fileInput?.files?.length) {
                this.importTestError = 'Pilih file Excel terlebih dahulu.';
                return;
            }
            this.importTesting = true;
            try {
                const res = await fetch('{{ route('admin.matrikulasi.import.test') }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: new FormData(form),
                });
                const json = await res.json();
                if (!res.ok) {
                    this.importTest = null;
                    this.importTestError = json.errors?.file?.[0] ?? json.message ?? 'Gagal mengetes file.';
                    return;
                }
                this.importTest = json;
            } catch (e) {
                this.importTest = null;
                this.importTestError = 'Gagal mengetes file. Coba lagi.';
            } finally {
                this.importTesting = false;
            }
        },
    }" @tour-close-modals.window="showCreateModal=false; showImportModal=false; showEditModal=false; showDeleteModal=false; showDetailModal=false">
        @if(session('success'))<div class="alert-success mb-5"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>{{ session('success') }}</div>@endif
        @if(session('warning'))<div class="alert-error mb-5">{{ session('warning') }}</div>@endif
        @if(session('import_errors') && count(session('import_errors')) > 0)
            <div class="alert-error mb-5">
                <p class="font-semibold text-sm mb-2">Detail baris gagal:</p>
                <ul class="list-disc pl-5 text-sm max-h-48 overflow-y-auto">
                    @foreach(session('import_errors') as $row => $error)
                        <li>Baris {{ $row }}: {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if($errors->any())<div class="alert-danger mb-5"><ul class="list-disc pl-5 text-sm">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul></div>@endif
        <div class="card overflow-hidden">
            <div class="card-pad page-toolbar border-b" style="border-color:rgba(0,0,0,0.06);">
                <div>
                    <h3 class="section-title">Indikator matrikulasi</h3>
                    <p class="section-subtitle">Standar penilaian tingkat sekolah; dipakai di jurnal kegiatan dan pencapaian siswa.</p>
                </div>
                <div class="toolbar-actions">
                    <x-export-excel route="admin.matrikulasi.export" />
                    <button type="button" @click="openImportModal()"
                        class="toolbar-icon shrink-0 rounded-lg flex items-center justify-center transition border"
                        style="color: #1A6B6B; background: #E8F5F5; border-color: #D0E8E8;"
                        title="Import Excel">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </button>
                    <button data-tour="admin-matrikulasi-add-btn" data-tour-open-modal="create" type="button" @click="showCreateModal=true" class="btn-primary toolbar-primary shrink-0 h-9 w-9 md:h-11 md:w-11 p-0" title="Tambah indikator" aria-label="Tambah indikator"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg></button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead><tr><th>Aspek / bidang</th><th>Indikator</th><th>Deskripsi</th><th class="text-right">Aksi</th></tr></thead>
                    <tbody>
                        @forelse($matrikulasis as $m)
                        <tr>
                            <td><span class="badge badge-teal">{{ $m->aspek ?? 'Umum' }}</span></td>
                            <td><span class="font-semibold" style="color:#2C2C2C;">{{ $m->indicator }}</span></td>
                            <td class="max-w-xs truncate">{{ $m->description ?? '-' }}</td>
                            <td class="text-right"><div class="flex items-center justify-end gap-2">
                                @php
                                    $matPayload = [
                                        'id' => $m->id,
                                        'aspek' => $m->aspek,
                                        'indicator' => $m->indicator,
                                        'description' => $m->description,
                                        'tujuan' => $m->tujuan,
                                        'strategi' => $m->strategi,
                                    ];
                                @endphp
                                <button type="button" @if($loop->first) data-tour="admin-matrikulasi-action-detail" data-tour-open-modal="detail" @endif @click="openDetail(@js($matPayload))" class="row-action-icon" style="color:#1A6B6B;background:#F0F7F7;border:1px solid #D0E8E8;" title="Detail" aria-label="Detail"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button>
                                <button type="button" @if($loop->first) data-tour="admin-matrikulasi-action-edit" data-tour-open-modal="edit" @endif @click="openEdit(@js($matPayload))" class="row-action-icon" style="color:#1A6B6B;background:#D0E8E8;" title="Edit" aria-label="Edit"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                                <button type="button" @if($loop->first) data-tour="admin-matrikulasi-action-delete" data-tour-demo-action="delete" @endif @click="openDelete('{{ route('admin.matrikulasi.destroy', $m) }}')" class="row-action-icon" style="color:#C0392B;background:#FAD7D2;" title="Hapus" aria-label="Hapus"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            </div></td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="py-6 md:py-12 text-center" style="color:#9E9790;">Belum ada indikator matrikulasi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t" style="border-color:rgba(0,0,0,0.06);">
                <x-per-page-selector :paginator="$matrikulasis" />
                {{ $matrikulasis->links() }}
            </div>
        </div>

        {{-- IMPORT MODAL --}}
        <div x-show="showImportModal" class="modal-overlay" style="display:none;">
            <div x-show="showImportModal" x-transition class="modal-box max-w-lg" @click.away="showImportModal = false">
                <form x-ref="importForm" action="{{ route('admin.matrikulasi.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header border-b pb-4" style="border-color: rgba(0,0,0,0.06);">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-lg flex items-center justify-center shrink-0" style="background: #E8F5F5;">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color: #1A6B6B;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="section-title">Import Matrikulasi dari Excel</h3>
                                <p class="section-subtitle">Ikuti 3 langkah di bawah ini secara berurutan.</p>
                            </div>
                        </div>
                    </div>

                    <div class="modal-body space-y-3 py-4">
                        <div class="rounded-xl border p-4" style="border-color: rgba(0,0,0,0.08); background: #FAFAF8;">
                            <div class="flex items-start gap-3">
                                <span class="h-7 w-7 shrink-0 rounded-full flex items-center justify-center text-xs font-bold text-white" style="background: #1A6B6B;">1</span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold" style="color: #2C2C2C;">Unduh formulir Excel</p>
                                    <p class="text-xs mt-1 leading-relaxed" style="color: #6B6560;">
                                        Klik tombol di bawah untuk mengunduh file kosong. Isi data matrikulasi di komputer Anda,
                                        lalu simpan file tersebut.
                                    </p>
                                    <a href="{{ route('admin.matrikulasi.import.template') }}"
                                        class="btn-secondary mt-3 inline-flex items-center text-sm w-full sm:w-auto justify-center">
                                        <svg class="h-4 w-4 mr-1.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                        Unduh Formulir Excel
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-xl border p-4" style="border-color: rgba(0,0,0,0.08); background: #FAFAF8;">
                            <div class="flex items-start gap-3">
                                <span class="h-7 w-7 shrink-0 rounded-full flex items-center justify-center text-xs font-bold text-white" style="background: #1A6B6B;">2</span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold" style="color: #2C2C2C;">Pilih file yang sudah diisi</p>
                                    <p class="text-xs mt-1 leading-relaxed" style="color: #6B6560;">
                                        Hapus baris contoh di Excel sebelum upload. File harus berformat
                                        <strong>.xlsx</strong> atau <strong>.xls</strong> (maks. 5 MB).
                                    </p>

                                    <input type="file" name="file" accept=".xlsx,.xls" required
                                        @change="onImportFileSelected($event)"
                                        class="sr-only" id="import-matrikulasi-file">

                                    <label for="import-matrikulasi-file" x-show="!importFileName"
                                        @dragover.prevent
                                        @drop.prevent="onImportFileDropped($event)"
                                        class="mt-3 flex flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed px-4 py-8 cursor-pointer transition hover:bg-white"
                                        style="border-color: #D0E8E8; background: #F5FAFA;">
                                        <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" style="color: #1A6B6B;">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                        </svg>
                                        <span class="text-sm font-semibold" style="color: #1A6B6B;">Klik untuk memilih file Excel</span>
                                        <span class="text-xs" style="color: #9E9790;">atau seret file ke sini</span>
                                    </label>

                                    <div x-show="importFileName" class="mt-3 space-y-3" style="display:none;">
                                        <div class="flex items-center gap-3 rounded-xl border px-4 py-3" style="border-color: #D0E8E8; background: #E8F5F5;">
                                            <svg class="h-8 w-8 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" style="color: #1A6B6B;">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-xs font-medium" style="color: #6B6560;">File terpilih:</p>
                                                <p class="text-sm font-semibold truncate" style="color: #2C2C2C;" x-text="importFileName"></p>
                                            </div>
                                            <label for="import-matrikulasi-file"
                                                class="text-xs font-semibold shrink-0 cursor-pointer px-2 py-1 rounded-lg"
                                                style="color: #1A6B6B; background: white;">Ganti</label>
                                        </div>

                                        <button type="button" @click="runImportTest()" :disabled="importTesting"
                                            class="btn-secondary w-full justify-center">
                                            <svg class="h-4 w-4 mr-1.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span x-text="importTesting ? 'Sedang memeriksa...' : 'Periksa File'"></span>
                                        </button>
                                        <p class="text-xs text-center" style="color: #9E9790;">Periksa dulu agar data tidak salah sebelum disimpan.</p>
                                    </div>

                                    @error('file')<p class="text-xs text-red-500 mt-2">{{ $message }}</p>@enderror
                                    <p x-show="importTestError" x-text="importTestError" class="text-xs text-red-500 mt-2" style="display:none;"></p>
                                </div>
                            </div>
                        </div>

                        <div x-show="importTest" class="rounded-xl border p-4" style="display:none; border-color: rgba(0,0,0,0.08);"
                            :style="{ background: importTest?.can_import ? '#E8F5F5' : '#FAD7D2' }">
                            <div class="flex items-start gap-3">
                                <span class="h-7 w-7 shrink-0 rounded-full flex items-center justify-center text-xs font-bold text-white"
                                    :style="{ background: importTest?.can_import ? '#1A6B6B' : '#C0392B' }">!</span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold" style="color: #2C2C2C;"
                                        x-text="importTest?.can_import ? 'File siap disimpan' : 'Ada data yang perlu diperbaiki'"></p>
                                    <p class="text-xs mt-1 leading-relaxed" style="color: #6B6560;" x-text="importTest?.message"></p>

                                    <template x-if="importTest && importTest.valid_count > 0">
                                        <div class="mt-3">
                                            <p class="text-xs font-semibold" style="color: #1A6B6B;"
                                                x-text="`${importTest.valid_count} indikator siap diimport`"></p>
                                            <ul class="list-none text-xs mt-1 max-h-24 overflow-y-auto space-y-1" style="color: #6B6560;">
                                                <template x-for="[row, label] in Object.entries(importTest.valid_rows || {})" :key="`valid-${row}`">
                                                    <li class="flex gap-1.5">
                                                        <span class="shrink-0 font-medium" x-text="`Baris ${row}:`"></span>
                                                        <span x-text="label"></span>
                                                    </li>
                                                </template>
                                            </ul>
                                        </div>
                                    </template>

                                    <template x-if="importTest && importTest.invalid_count > 0">
                                        <div class="mt-3">
                                            <p class="text-xs font-semibold" style="color: #C0392B;"
                                                x-text="`${importTest.invalid_count} baris bermasalah`"></p>
                                            <ul class="list-none text-xs mt-1 max-h-24 overflow-y-auto space-y-1" style="color: #6B6560;">
                                                <template x-for="[row, error] in Object.entries(importTest.invalid_rows || {})" :key="`invalid-${row}`">
                                                    <li class="flex gap-1.5">
                                                        <span class="shrink-0 font-medium text-red-600" x-text="`Baris ${row}:`"></span>
                                                        <span x-text="error"></span>
                                                    </li>
                                                </template>
                                            </ul>
                                            <p class="text-xs mt-2" style="color: #C0392B;">
                                                Perbaiki file Excel, lalu pilih ulang file dan periksa kembali.
                                            </p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <details class="rounded-xl border px-4 py-3 text-xs" style="border-color: rgba(0,0,0,0.08); color: #6B6560;">
                            <summary class="cursor-pointer font-semibold" style="color: #2C2C2C;">Tips penting</summary>
                            <ul class="mt-2 space-y-1.5 list-disc pl-4 leading-relaxed">
                                <li>Kolom wajib: <strong>indikator</strong> dan <strong>deskripsi</strong>.</li>
                                <li>Kolom opsional: aspek, tujuan, strategi.</li>
                                <li>Import menambah data baru; tidak mengubah indikator yang sudah ada.</li>
                            </ul>
                        </details>
                    </div>

                    <div class="modal-footer border-t pt-4" style="border-color: rgba(0,0,0,0.06);">
                        <button type="button" @click="showImportModal = false" class="btn-secondary">Tutup</button>
                        <button type="submit" x-show="importTest?.can_import" class="btn-primary" style="display:none;">
                            <svg class="h-4 w-4 mr-1.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                            Simpan Data Matrikulasi
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- DETAIL MODAL --}}
        <div x-show="showDetailModal" data-tour="modal-detail" class="modal-overlay" style="display:none;">
            <div x-show="showDetailModal" x-transition class="modal-box max-w-2xl" @click.away="showDetailModal=false">
                <div class="modal-header flex items-center justify-between">
                    <h3 class="section-title">Detail Indikator Matrikulasi</h3>
                    <button @click="showDetailModal=false" class="text-gray-400 hover:text-gray-600"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
                </div>
                <div class="modal-body space-y-5" data-tour="modal-detail-content">
                    <div class="grid grid-cols-2 gap-4">
                        <div><p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Aspek / Bidang</p><p class="text-sm font-semibold text-gray-900" x-text="detailData.aspek || 'Umum'"></p></div>
                        <div><p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Indikator</p><p class="text-sm font-semibold text-gray-900" x-text="detailData.indicator"></p></div>
                    </div>
                    <div><p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Tujuan Pembelajaran</p><p class="text-sm text-gray-700 whitespace-pre-line" x-text="detailData.tujuan || '-'"></p></div>
                    <div><p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Strategi / Metode Edukasi</p><p class="text-sm text-gray-700 whitespace-pre-line" x-text="detailData.strategi || '-'"></p></div>
                    <div><p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Deskripsi Lengkap</p><p class="text-sm text-gray-700 whitespace-pre-line" x-text="detailData.description"></p></div>
                </div>
                <div class="modal-footer"><button @click="showDetailModal=false" class="btn-secondary w-full sm:w-auto">Tutup</button></div>
            </div>
        </div>

        <div x-show="showCreateModal" class="modal-overlay" style="display:none;">
            <div x-show="showCreateModal" x-transition class="modal-box" @click.away="showCreateModal=false">
                <form action="{{ route('admin.matrikulasi.store') }}" method="POST">
                    @csrf
                    <div class="modal-header"><h3 class="section-title">Tambah indikator</h3></div>
                    <div class="modal-body space-y-4">
                        <div data-tour="modal-create-section-indikator" class="space-y-4">
                            <div>
                                <label class="input-label">Aspek / faktor</label>
                                <input type="text" name="aspek" class="input-field @error('aspek') border-red-500 @enderror" placeholder="Contoh: Kognitif, Motorik halus…" value="{{ old('aspek') }}">
                                @error('aspek')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="input-label">Indikator <span class="text-red-600">*</span></label>
                                <input type="text" name="indicator" required class="input-field @error('indicator') border-red-500 @enderror" placeholder="Contoh: Mampu menghitung 1–10" value="{{ old('indicator') }}">
                                @error('indicator')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div data-tour="modal-create-section-detail" class="space-y-4">
                            <div>
                                <label class="input-label">Tujuan pembelajaran</label>
                                <textarea name="tujuan" rows="2" class="input-field @error('tujuan') border-red-500 @enderror">{{ old('tujuan') }}</textarea>
                                @error('tujuan')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="input-label">Strategi / metode</label>
                                <textarea name="strategi" rows="2" class="input-field @error('strategi') border-red-500 @enderror">{{ old('strategi') }}</textarea>
                                @error('strategi')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="input-label">Deskripsi <span class="text-red-600">*</span></label>
                                <textarea name="description" rows="2" required class="input-field @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                                @error('description')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" @click="showCreateModal=false" class="btn-secondary">Batal</button><button type="submit" data-tour="modal-create-submit" class="btn-primary">Simpan</button></div>
                </form>
            </div>
        </div>
        <div x-show="showEditModal" class="modal-overlay" style="display:none;">
            <div x-show="showEditModal" x-transition class="modal-box" @click.away="showEditModal=false">
                <form :action="`/admin/matrikulasi/${editData.id}`" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-header"><h3 class="section-title">Edit indikator</h3></div>
                    <div class="modal-body space-y-4">
                        <div data-tour="modal-edit-section-indikator" class="space-y-4">
                            <div>
                                <label class="input-label">Aspek / faktor</label>
                                <input type="text" name="aspek" x-model="editData.aspek" class="input-field @error('aspek') border-red-500 @enderror">
                                @error('aspek')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="input-label">Indikator</label>
                                <input type="text" name="indicator" x-model="editData.indicator" required class="input-field @error('indicator') border-red-500 @enderror">
                                @error('indicator')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div data-tour="modal-edit-section-detail" class="space-y-4">
                            <div>
                                <label class="input-label">Tujuan pembelajaran</label>
                                <textarea name="tujuan" x-model="editData.tujuan" rows="2" class="input-field @error('tujuan') border-red-500 @enderror"></textarea>
                                @error('tujuan')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="input-label">Strategi / metode</label>
                                <textarea name="strategi" x-model="editData.strategi" rows="2" class="input-field @error('strategi') border-red-500 @enderror"></textarea>
                                @error('strategi')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="input-label">Deskripsi</label>
                                <textarea name="description" x-model="editData.description" rows="2" required class="input-field @error('description') border-red-500 @enderror"></textarea>
                                @error('description')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" @click="showEditModal=false" class="btn-secondary">Batal</button><button type="submit" data-tour="modal-edit-submit" class="btn-primary">Simpan</button></div>
                </form>
            </div>
        </div>
        <div x-show="showDeleteModal" data-tour="modal-delete" class="modal-overlay" style="display:none;">
            <div x-show="showDeleteModal" x-transition class="modal-box max-w-sm" @click.away="showDeleteModal=false">
                <form :action="deleteRoute" method="POST">
                    @csrf @method('DELETE')
                    <div class="modal-body text-center py-6"><div class="h-14 w-14 rounded-2xl mx-auto mb-4 flex items-center justify-center" style="background:#FAD7D2;"><svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:#C0392B;"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></div><h3 class="section-title">Hapus indikator?</h3><p class="section-subtitle mt-1">Data penilaian yang memakai indikator ini dapat terpengaruh.</p></div>
                    <div class="modal-footer"><button type="button" @click="showDeleteModal=false" class="btn-secondary">Batal</button><button type="submit" class="btn-danger">Ya, hapus</button></div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
