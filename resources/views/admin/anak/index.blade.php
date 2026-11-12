<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background: #1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
            <h2 class="font-bold text-xl" style="color: #2C2C2C;">Kelola Data Siswa</h2>
        </div>
    </x-slot>

    <div class="app-page-shell" x-data="{
            showCreateModal: @js($errors->any() && ! $errors->has('file')),
            showImportModal: @js($errors->has('file')),
            showEditModal: false,
            showDeleteModal: false,
            editData: {},
            deleteRoute: '',
            importTesting: false,
            importTest: null,
            importTestError: null,
            importFileName: null,
            parentMode: @js(old('parent_mode', 'new')),
            parentSearch: '',
            parentResults: [],
            selectedParent: null,
            parentSearchLoading: false,
            parentSearchTimer: null,
            openEdit(d) { 
                this.editData = { ...d }; 
                if (d.dob) {
                    this.editData.dob = d.dob.split('T')[0];
                }
                this.editData.parent_name = d.parent_name;
                this.editData.parent_email = d.user ? d.user.email : '';
                this.showEditModal = true; 
            },
            openDelete(r) { this.deleteRoute = r; this.showDeleteModal = true; },
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
                    const res = await fetch('{{ route('admin.anak.import.test') }}', {
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
            resetParentSearch() {
                this.parentSearch = '';
                this.parentResults = [];
                this.selectedParent = null;
            },
            searchParents() {
                clearTimeout(this.parentSearchTimer);
                if (this.parentSearch.trim().length < 2) {
                    this.parentResults = [];
                    return;
                }
                this.parentSearchTimer = setTimeout(async () => {
                    this.parentSearchLoading = true;
                    try {
                        const res = await fetch(`{{ route('admin.orang-tua.search') }}?q=${encodeURIComponent(this.parentSearch.trim())}`, {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        const json = await res.json();
                        this.parentResults = json.data || [];
                    } catch (e) {
                        this.parentResults = [];
                    } finally {
                        this.parentSearchLoading = false;
                    }
                }, 300);
            },
            selectParent(parent) {
                this.selectedParent = parent;
                this.parentResults = [];
                this.parentSearch = `${parent.name} (${parent.email})`;
            }
         }" @tour-close-modals.window="showCreateModal=false; showImportModal=false; showEditModal=false; showDeleteModal=false">

        @if(session('success'))
            <div class="alert-success mb-5">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ session('success') }}
            </div>
        @endif
        @if(session('warning'))
            <div class="alert-error mb-5">
                {{ session('warning') }}
            </div>
        @endif
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
        @if($errors->any())
            <div class="alert-danger mb-5">
                <ul class="list-disc pl-5 text-sm">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
        </div>@endif

        <div class="card overflow-hidden">
            <div class="px-4 md:px-6 py-4 border-b" style="border-color:rgba(0,0,0,0.06);">
                <h3 class="section-title">Daftar Siswa &amp; Orang Tua</h3>
                <p class="section-subtitle">Registrasi, import Excel, dan kelola data siswa beserta orang tua.</p>
            </div>
            <form data-tour="admin-anak-filter" method="get" class="card-pad toolbar-form border-b w-full" style="border-color:rgba(0,0,0,0.06);">
                <div class="toolbar-field md:min-w-[12rem] md:flex-[2]">
                    <label class="input-label">Cari Nama Siswa</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="input-field"
                        placeholder="Nama atau panggilan...">
                </div>
                <div class="toolbar-field md:min-w-[9rem] md:max-w-[11rem]">
                    <label class="input-label">Filter Kelas</label>
                    <select name="kelas_id" class="input-field w-full" onchange="this.form.submit()">
                        <option value="">Semua Kelas</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id }}" @selected(request('kelas_id') == $k->id)>{{ $k->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="toolbar-actions">
                    <x-toolbar.filter-icon label="Filter" variant="secondary" />
                    <x-filter-reset :href="route('admin.anak.index')" compact />
                    <x-export-excel route="admin.anak.export" :icon-only="true" />
                    <x-icon-btn type="button" variant="toolbar-teal" label="Import Excel"
                        icon="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                        style="color:#1A6B6B;background:#E8F5F5;border-color:#D0E8E8;"
                        @click="openImportModal()" />
                    <x-toolbar.add-icon label="Registrasi siswa baru" type="button"
                        data-tour="admin-anak-add-btn" data-tour-open-modal="create"
                        @click="showCreateModal = true" />
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nama Anak</th>
                            <th>Panggilan</th>
                            <th>Kelas</th>
                            <th>J/K</th>
                            <th>Umur</th>
                            <th>Nama Orang Tua</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($anaks as $anak)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <x-foto-profil :path="$anak->photo" :name="$anak->name" size="sm" />
                                        <div>
                                            <span class="font-semibold block" style="color: #2C2C2C;">{{ $anak->name }}</span>
                                            @if(filled($anak->nickname))
                                                <span class="text-[10px] block mt-0.5" style="color:#6B6560;">Nama panggilan: {{ $anak->nickname }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $anak->nickname ?? '-' }}</td>
                                <td>
                                    @if($anak->kelas)<span
                                        class="badge badge-teal font-medium">{{ $anak->kelas->name }}</span>
                                    @else<span class="text-xs italic" style="color:#9E9790;">Belum Ditugaskan</span>@endif
                                </td>
                                <td>{{ $anak->jenis_kelamin ?? '-' }}</td>
                                <td>
                                    @if($anak->dob)
                                        <span class="text-[10px] block font-bold text-[#1A6B6B]">{{ $anak->age }}</span>
                                    @endif
                                </td>
                                <td>{{ $anak->parent_name }}</td>
                                <td class="text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.anak.show', $anak) }}"
                                            @if($loop->first) data-tour="admin-anak-action-detail" @endif
                                            class="row-action-icon"
                                            style="color: #1A6B6B; background: #E8F5F5;" title="Detail" aria-label="Detail"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a>
                                        <button @if($loop->first) data-tour="admin-anak-action-edit" data-tour-open-modal="edit" @endif @click="openEdit({{ json_encode($anak) }})"
                                            class="row-action-icon"
                                            style="color: #1A6B6B; background: #D0E8E8;" title="Edit" aria-label="Edit"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                                        <button @if($loop->first) data-tour="admin-anak-action-delete" data-tour-demo-action="delete" @endif @click="openDelete('{{ route('admin.anak.destroy', $anak) }}')"
                                            class="row-action-icon"
                                            style="color: #C0392B; background: #FAD7D2;" title="Hapus" aria-label="Hapus"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-6 md:py-12 text-center" style="color: #9E9790;">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                            stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                        Belum ada data siswa terdaftar.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t" style="border-color: rgba(0,0,0,0.06);">
                <x-per-page-selector :paginator="$anaks" />
                {{ $anaks->links() }}
            </div>
        </div>

        <!-- IMPORT MODAL -->
        <div x-show="showImportModal" class="modal-overlay" style="display:none;">
            <div x-show="showImportModal" x-transition class="modal-box max-w-lg" @click.away="showImportModal = false">
                <form x-ref="importForm" action="{{ route('admin.anak.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header border-b pb-4" style="border-color: rgba(0,0,0,0.06);">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-lg flex items-center justify-center shrink-0"
                                style="background: #E8F5F5;">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2" style="color: #1A6B6B;">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="section-title">Import Data Siswa dari Excel</h3>
                                <p class="section-subtitle">Ikuti 3 langkah di bawah ini secara berurutan.</p>
                            </div>
                        </div>
                    </div>

                    <div class="modal-body space-y-3 py-4">
                        {{-- Langkah 1 --}}
                        <div class="rounded-xl border p-4" style="border-color: rgba(0,0,0,0.08); background: #FAFAF8;">
                            <div class="flex items-start gap-3">
                                <span class="h-7 w-7 shrink-0 rounded-full flex items-center justify-center text-xs font-bold text-white"
                                    style="background: #1A6B6B;">1</span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold" style="color: #2C2C2C;">Unduh formulir Excel</p>
                                    <p class="text-xs mt-1 leading-relaxed" style="color: #6B6560;">
                                        Klik tombol di bawah untuk mengunduh file kosong. Isi data siswa di komputer Anda,
                                        lalu simpan file tersebut.
                                    </p>
                                    <a href="{{ route('admin.anak.import.template') }}"
                                        class="btn-secondary mt-3 inline-flex items-center text-sm w-full sm:w-auto justify-center">
                                        <svg class="h-4 w-4 mr-1.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                            stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                        Unduh Formulir Excel
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Langkah 2 --}}
                        <div class="rounded-xl border p-4" style="border-color: rgba(0,0,0,0.08); background: #FAFAF8;">
                            <div class="flex items-start gap-3">
                                <span class="h-7 w-7 shrink-0 rounded-full flex items-center justify-center text-xs font-bold text-white"
                                    style="background: #1A6B6B;">2</span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold" style="color: #2C2C2C;">Pilih file yang sudah diisi</p>
                                    <p class="text-xs mt-1 leading-relaxed" style="color: #6B6560;">
                                        Hapus baris contoh di Excel sebelum upload. File harus berformat
                                        <strong>.xlsx</strong> atau <strong>.xls</strong> (maks. 5 MB).
                                    </p>

                                    <input type="file" name="file" accept=".xlsx,.xls" required
                                        @change="onImportFileSelected($event)"
                                        class="sr-only" id="import-anak-file">

                                    <label for="import-anak-file" x-show="!importFileName"
                                        @dragover.prevent
                                        @drop.prevent="onImportFileDropped($event)"
                                        class="mt-3 flex flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed px-4 py-8 cursor-pointer transition hover:bg-white"
                                        style="border-color: #D0E8E8; background: #F5FAFA;">
                                        <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                            stroke-width="1.5" style="color: #1A6B6B;">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                        </svg>
                                        <span class="text-sm font-semibold" style="color: #1A6B6B;">Klik untuk memilih file Excel</span>
                                        <span class="text-xs" style="color: #9E9790;">atau seret file ke sini</span>
                                    </label>

                                    <div x-show="importFileName" class="mt-3 space-y-3" style="display:none;">
                                        <div class="flex items-center gap-3 rounded-xl border px-4 py-3"
                                            style="border-color: #D0E8E8; background: #E8F5F5;">
                                            <svg class="h-8 w-8 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                stroke-width="1.5" style="color: #1A6B6B;">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-xs font-medium" style="color: #6B6560;">File terpilih:</p>
                                                <p class="text-sm font-semibold truncate" style="color: #2C2C2C;" x-text="importFileName"></p>
                                            </div>
                                            <label for="import-anak-file"
                                                class="text-xs font-semibold shrink-0 cursor-pointer px-2 py-1 rounded-lg"
                                                style="color: #1A6B6B; background: white;">Ganti</label>
                                        </div>

                                        <button type="button" @click="runImportTest()" :disabled="importTesting"
                                            class="btn-secondary w-full justify-center">
                                            <svg class="h-4 w-4 mr-1.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span x-text="importTesting ? 'Sedang memeriksa...' : 'Periksa File'"></span>
                                        </button>
                                        <p class="text-xs text-center" style="color: #9E9790;">
                                            Periksa dulu agar data tidak salah sebelum disimpan.
                                        </p>
                                    </div>

                                    @error('file')<p class="text-xs text-red-500 mt-2">{{ $message }}</p>@enderror
                                    <p x-show="importTestError" x-text="importTestError"
                                        class="text-xs text-red-500 mt-2" style="display:none;"></p>
                                </div>
                            </div>
                        </div>

                        {{-- Hasil periksa --}}
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
                                                x-text="`${importTest.valid_count} data siswa siap diimport`"></p>
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
                                <li>Email wali yang sudah terdaftar akan otomatis ditautkan ke akun yang ada.</li>
                                <li>Akun wali baru memakai password awal: <code>password123</code></li>
                                <li>Jangan ubah format kolom NIK  biarkan sebagai teks agar tidak rusak.</li>
                            </ul>
                        </details>
                    </div>

                    <div class="modal-footer border-t pt-4" style="border-color: rgba(0,0,0,0.06);">
                        <button type="button" @click="showImportModal = false" class="btn-secondary">Tutup</button>
                        <button type="submit" x-show="importTest?.can_import" class="btn-primary" style="display:none;">
                            <svg class="h-4 w-4 mr-1.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                            Simpan Data Siswa
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- CREATE MODAL -->
        <div x-show="showCreateModal" class="modal-overlay" style="display:none;">
            <div x-show="showCreateModal" x-transition class="modal-box" @click.away="showCreateModal = false">
                <form action="{{ route('admin.anak.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h3 class="section-title">Registrasi Siswa & Orang Tua</h3>
                        <p class="section-subtitle">Akun login orang tua akan dibuat otomatis (pass:
                            <code>password123</code>)</p>
                    </div>
                    <div class="modal-body grid grid-cols-2 gap-4">
                        <div class="col-span-2 text-[13px] font-bold text-[#1A6B6B] mt-1 border-b pb-1" data-tour="modal-create-section-anak">Data Anak
                            (Siswa)</div>
                        <div class="col-span-1">
                            <label class="input-label">Nama Lengkap Anak</label>
                            <input type="text" name="name" required
                                class="input-field @error('name') border-red-500 @enderror"
                                placeholder="Contoh: Budi Santoso" value="{{ old('name') }}">
                            @error('name')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="col-span-1">
                            <label class="input-label">Nama Panggilan</label>
                            <input type="text" name="nickname" maxlength="50"
                                class="input-field @error('nickname') border-red-500 @enderror"
                                placeholder="Opsional, maks. 50 karakter  dipakai saran AI pencapaian"
                                value="{{ old('nickname') }}">
                            @error('nickname')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="col-span-1">
                            <label class="input-label">Penempatan Kelas</label>
                            <select name="kelas_id" class="input-field @error('kelas_id') border-red-500 @enderror">
                                <option value="">-- Belum Ditugaskan --</option>
                                @foreach($kelas as $k)<option value="{{ $k->id }}" @selected(old('kelas_id') == $k->id)>
                                {{ $k->name }}</option>@endforeach
                            </select>
                            @error('kelas_id')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="col-span-1">
                            <label class="input-label">NIK Anak</label>
                            <input type="text" name="nik" class="input-field @error('nik') border-red-500 @enderror"
                                placeholder="NIK" value="{{ old('nik') }}">
                            @error('nik')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="col-span-1">
                            <label class="input-label">Jenis Kelamin</label>
                            <select name="jenis_kelamin"
                                class="input-field @error('jenis_kelamin') border-red-500 @enderror">
                                <option value="">Pilih...</option>
                                <option value="Laki-laki" @selected(old('jenis_kelamin') == 'Laki-laki')>Laki-laki
                                </option>
                                <option value="Perempuan" @selected(old('jenis_kelamin') == 'Perempuan')>Perempuan
                                </option>
                            </select>
                            @error('jenis_kelamin')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="col-span-1">
                            <label class="input-label">Tanggal Lahir</label>
                            <input type="date" name="dob" class="input-field @error('dob') border-red-500 @enderror"
                                value="{{ old('dob') }}">
                            @error('dob')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="col-span-1">
                            <label class="input-label">Foto Siswa</label>
                            <input type="file" name="photo" accept="image/*" class="input-field py-1.5">
                        </div>
                        <div class="col-span-2">
                            <label class="input-label">Alamat Lengkap</label>
                            <textarea name="alamat" class="input-field @error('alamat') border-red-500 @enderror"
                                rows="2" placeholder="Prov, Kab, Kec, Kel...">{{ old('alamat') }}</textarea>
                            @error('alamat')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="col-span-2 text-[13px] font-bold text-[#1A6B6B] mt-2 border-b pb-1" data-tour="modal-create-section-ortu">Data Orang Tua
                        </div>
                        <div class="col-span-1">
                            <label class="input-label">NIK Bapak</label>
                            <input type="text" name="nik_bapak" class="input-field" placeholder="NIK">
                        </div>
                        <div class="col-span-1">
                            <label class="input-label">Nama Bapak</label>
                            <input type="text" name="nama_bapak" class="input-field" placeholder="Nama">
                        </div>
                        <div class="col-span-1">
                            <label class="input-label">NIK Ibu</label>
                            <input type="text" name="nik_ibu" class="input-field" placeholder="NIK">
                        </div>
                        <div class="col-span-1">
                            <label class="input-label">Nama Ibu</label>
                            <input type="text" name="nama_ibu" class="input-field" placeholder="Nama">
                        </div>

                        <div class="col-span-2 text-[13px] font-bold text-[#1A6B6B] mt-2 border-b pb-1" data-tour="modal-create-section-wali">Akun Login Utama
                            (Wali)</div>

                        <div class="col-span-2 flex flex-wrap gap-4">
                            <label class="inline-flex items-center gap-2 text-sm cursor-pointer">
                                <input type="radio" name="parent_mode" value="new" x-model="parentMode" @change="resetParentSearch()" class="rounded-full border-gray-300 text-[#1A6B6B] focus:ring-[#1A6B6B]">
                                <span>Orang tua baru</span>
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm cursor-pointer">
                                <input type="radio" name="parent_mode" value="existing" x-model="parentMode" @change="resetParentSearch()" class="rounded-full border-gray-300 text-[#1A6B6B] focus:ring-[#1A6B6B]">
                                <span>Orang tua sudah terdaftar</span>
                            </label>
                        </div>

                        <template x-if="parentMode === 'new'">
                            <div class="col-span-2 grid grid-cols-2 gap-4">
                                <div class="col-span-1">
                                    <label class="input-label">Nama Wali Untuk Login</label>
                                    <input type="text" name="parent_name"
                                        class="input-field @error('parent_name') border-red-500 @enderror"
                                        placeholder="Nama lengkap" value="{{ old('parent_name') }}"
                                        :required="parentMode === 'new'">
                                    @error('parent_name')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div class="col-span-1">
                                    <label class="input-label">Email Wali</label>
                                    <input type="email" name="parent_email"
                                        class="input-field @error('parent_email') border-red-500 @enderror"
                                        placeholder="email@contoh.com" value="{{ old('parent_email') }}"
                                        :required="parentMode === 'new'">
                                    @error('parent_email')
                                        <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </template>

                        <template x-if="parentMode === 'existing'">
                            <div class="col-span-2 space-y-3">
                                <div class="relative">
                                    <label class="input-label">Cari orang tua (nama atau email)</label>
                                    <input type="text" x-model="parentSearch" @input="searchParents()"
                                        class="input-field" placeholder="Ketik minimal 2 karakter..."
                                        autocomplete="off">
                                    <input type="hidden" name="parent_user_id" :value="selectedParent ? selectedParent.id : ''">
                                    <p x-show="parentSearchLoading" class="text-[10px] text-gray-500 mt-1">Mencari...</p>
                                    <div x-show="parentResults.length > 0" class="absolute z-20 mt-1 w-full rounded-lg border bg-white shadow-lg max-h-48 overflow-y-auto" style="border-color:rgba(0,0,0,0.08);">
                                        <template x-for="parent in parentResults" :key="parent.id">
                                            <button type="button" @click="selectParent(parent)"
                                                class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50 border-b last:border-b-0"
                                                style="border-color:rgba(0,0,0,0.06);">
                                                <span class="font-semibold block" x-text="parent.name"></span>
                                                <span class="text-xs text-gray-500" x-text="parent.email"></span>
                                                <span class="text-[10px] text-[#1A6B6B] font-bold" x-text="`${parent.anaks_count} anak terdaftar`"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                                <div x-show="selectedParent" class="rounded-lg px-3 py-2 text-sm" style="background:#EDE8DF; color:#6B6560;">
                                    <span class="font-semibold" x-text="selectedParent?.name"></span>
                                    <span class="block text-xs" x-text="selectedParent?.email"></span>
                                </div>
                                @error('parent_user_id')<p class="text-[10px] text-red-500">{{ $message }}</p>@enderror
                            </div>
                        </template>
                    </div>
                    <div class="modal-footer">
                        <button type="button" @click="showCreateModal = false" class="btn-secondary">Batal</button>
                        <button type="submit" data-tour="modal-create-submit" class="btn-primary">Simpan Registrasi</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- EDIT MODAL -->
        <div x-show="showEditModal" class="modal-overlay" style="display:none;">
            <div x-show="showEditModal" x-transition class="modal-box" @click.away="showEditModal = false">
                <form :action="`/admin/anak/${editData.id}`" method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="modal-header">
                        <h3 class="section-title">Edit Data Anak</h3>
                    </div>
                    <div class="modal-body grid grid-cols-2 gap-4">
                        <div class="col-span-2 text-[13px] font-bold text-[#1A6B6B] mt-1 border-b pb-1" data-tour="modal-edit-section-anak">Data Anak
                            (Siswa)</div>
                        <div class="col-span-1">
                            <label class="input-label">Nama Lengkap Anak</label>
                            <input type="text" name="name" x-model="editData.name" required
                                class="input-field @error('name') border-red-500 @enderror">
                            @error('name')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="col-span-1">
                            <label class="input-label">Nama Panggilan</label>
                            <input type="text" name="nickname" maxlength="50" x-model="editData.nickname"
                                class="input-field @error('nickname') border-red-500 @enderror"
                                placeholder="Opsional, maks. 50 karakter  dipakai saran AI pencapaian">
                            @error('nickname')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="col-span-1">
                            <label class="input-label">Pindah Kelas</label>
                            <select name="kelas_id" x-model="editData.kelas_id"
                                class="input-field @error('kelas_id') border-red-500 @enderror">
                                <option value="">-- Kosongkan Kelas --</option>
                                @foreach($kelas as $k)<option value="{{ $k->id }}">{{ $k->name }}</option>@endforeach
                            </select>
                            @error('kelas_id')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="col-span-1">
                            <label class="input-label">NIK Anak</label>
                            <input type="text" name="nik" x-model="editData.nik"
                                class="input-field @error('nik') border-red-500 @enderror">
                            @error('nik')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="col-span-1">
                            <label class="input-label">Jenis Kelamin</label>
                            <select name="jenis_kelamin" x-model="editData.jenis_kelamin"
                                class="input-field @error('jenis_kelamin') border-red-500 @enderror">
                                <option value="">Pilih...</option>
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                            @error('jenis_kelamin')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="col-span-1">
                            <label class="input-label">Tanggal Lahir</label>
                            <input type="date" name="dob" x-model="editData.dob"
                                class="input-field @error('dob') border-red-500 @enderror">
                            @error('dob')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="col-span-1">
                            <label class="input-label">Ganti Foto</label>
                            <div class="flex items-center gap-2">
                                <template x-if="editData.photo">
                                    <img :src="'/storage/' + editData.photo"
                                        class="h-11 w-11 rounded-lg object-cover border border-black/5 shadow-sm">
                                </template>
                                <template x-if="!editData.photo">
                                    <div
                                        class="h-11 w-11 rounded-lg bg-gray-100 flex items-center justify-center text-[8px] font-bold text-gray-400 border border-dashed border-gray-300">
                                        NO FOTO</div>
                                </template>
                                <input type="file" name="photo" accept="image/*" class="input-field py-1.5 flex-1">
                            </div>
                        </div>
                        <div class="col-span-2">
                            <label class="input-label">Alamat Lengkap</label>
                            <textarea name="alamat" x-model="editData.alamat"
                                class="input-field @error('alamat') border-red-500 @enderror" rows="2"></textarea>
                            @error('alamat')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="col-span-2 text-[13px] font-bold text-[#1A6B6B] mt-2 border-b pb-1" data-tour="modal-edit-section-ortu">Data Orang Tua
                        </div>
                        <div class="col-span-1">
                            <label class="input-label">NIK Bapak</label>
                            <input type="text" name="nik_bapak" x-model="editData.nik_bapak" class="input-field">
                        </div>
                        <div class="col-span-1">
                            <label class="input-label">Nama Bapak</label>
                            <input type="text" name="nama_bapak" x-model="editData.nama_bapak" class="input-field">
                        </div>
                        <div class="col-span-1">
                            <label class="input-label">NIK Ibu</label>
                            <input type="text" name="nik_ibu" x-model="editData.nik_ibu" class="input-field">
                        </div>
                        <div class="col-span-1">
                            <label class="input-label">Nama Ibu</label>
                            <input type="text" name="nama_ibu" x-model="editData.nama_ibu"
                                class="input-field @error('nama_ibu') border-red-500 @enderror">
                            @error('nama_ibu')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div class="col-span-2 text-[13px] font-bold text-[#1A6B6B] mt-2 border-b pb-1" data-tour="modal-edit-section-wali">Akun Login Utama
                            (Wali)</div>
                        <div class="col-span-1">
                            <label class="input-label">Nama Wali Untuk Login</label>
                            <input type="text" name="parent_name" x-model="editData.parent_name" required
                                class="input-field @error('parent_name') border-red-500 @enderror">
                            @error('parent_name')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="col-span-1">
                            <label class="input-label">Email Wali</label>
                            <input type="email" name="parent_email" x-model="editData.parent_email" required
                                class="input-field @error('parent_email') border-red-500 @enderror">
                            @error('parent_email')
                                <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" @click="showEditModal = false" class="btn-secondary">Batal</button>
                        <button type="submit" data-tour="modal-edit-submit" class="btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- DELETE MODAL -->
        <div x-show="showDeleteModal" data-tour="modal-delete" class="modal-overlay" style="display:none;">
            <div x-show="showDeleteModal" x-transition class="modal-box max-w-sm" @click.away="showDeleteModal = false">
                <form :action="deleteRoute" method="POST">
                    @csrf @method('DELETE')
                    <div class="modal-body text-center py-6">
                        <div class="h-14 w-14 rounded-2xl mx-auto mb-4 flex items-center justify-center"
                            style="background: #FAD7D2;">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                                style="color: #C0392B;">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </div>
                        <h3 class="section-title">Hapus Data Siswa?</h3>
                        <p class="section-subtitle mt-1">Tindakan ini tidak dapat dibatalkan. Semua data terkait juga
                            akan dihapus.</p>
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