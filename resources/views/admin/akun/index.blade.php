<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background: #1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
            </div>
            <h2 class="font-bold text-xl" style="color: #2C2C2C;">Kode Rekening & Akun</h2>
        </div>
    </x-slot>

    <div class="app-page-shell"
         x-data="{
            showCreateModal: false, showEditModal: false, showDeleteModal: false, showImportModal: @js($errors->has('file')), showDetailModal: false,
            editData: {}, detailData: {}, detailHistory: [], detailHistoryLoading: false, detailHistoryError: null, deleteRoute: '',
            importFileName: null,
            rupiah(n) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(Number(n) || 0); },
            formatMutasi(n) { const v = Number(n) || 0; return v > 0 ? new Intl.NumberFormat('id-ID').format(v) : ''; },
            openImportModal() {
                this.importTest = null;
                this.importTestError = null;
                this.importTesting = false;
                this.importFileName = null;
                this.ignoreDuplicates = false;
                this.showImportModal = true;
            },
            onImportFileSelected(event) {
                const file = event.target.files?.[0];
                this.importFileName = file ? file.name : null;
                this.resetImport();
            },
            onImportFileDropped(event) {
                const file = event.dataTransfer.files?.[0];
                if (!file) return;
                const input = this.$refs.importForm?.querySelector('input[type=file]');
                if (input) {
                    input.files = event.dataTransfer.files;
                }
                this.importFileName = file.name;
                this.resetImport();
            },
            async openDetail(payload) {
                this.detailData = payload;
                this.showDetailModal = true;
                this.detailHistory = [];
                this.detailHistoryError = null;
                this.detailHistoryLoading = true;
                try {
                    const res = await fetch(`{{ url('admin/akun') }}/${payload.id}/riwayat-jurnal`, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    const json = await res.json();
                    if (!res.ok) {
                        this.detailHistoryError = json.message ?? 'Gagal memuat riwayat jurnal.';
                        return;
                    }
                    this.detailHistory = json.rows ?? [];
                } catch (e) {
                    this.detailHistoryError = 'Gagal memuat riwayat jurnal.';
                } finally {
                    this.detailHistoryLoading = false;
                }
            },
            importTesting: false, importTest: null, importTestError: null, ignoreDuplicates: false,
            canImport() {
                if (!this.importTest?.can_import) return false;
                if (this.importTest.duplicate_count > 0 && !this.ignoreDuplicates) return false;
                return true;
            },
            resetImport() { this.importTest = null; this.importTestError = null; this.ignoreDuplicates = false; this.importTesting = false; },
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
                    const res = await fetch('{{ route('admin.akun.import.test') }}', {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: new FormData(form),
                    });
                    const json = await res.json();
                    if (!res.ok) {
                        this.importTest = null;
                        this.importTestError = json.errors?.file?.[0] ?? json.message ?? 'Gagal mengetes file.';
                        return;
                    }
                    this.importTest = json;
                    this.ignoreDuplicates = false;
                } catch (e) {
                    this.importTest = null;
                    this.importTestError = 'Gagal mengetes file. Coba lagi.';
                } finally {
                    this.importTesting = false;
                }
            },
         }">

        @if(session('success'))<div class="alert-success mb-5">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert-danger mb-5"><ul class="list-disc pl-5 text-sm">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul></div>@endif

        <div class="card overflow-hidden">
            <div class="px-6 py-4 flex flex-wrap items-center justify-between gap-3 border-b" style="border-color:rgba(0,0,0,0.06);">
                <div>
                    <h3 class="section-title">Daftar Kode Rekening & Akun</h3>
                    <p class="section-subtitle">Kode akun, jenis, nama, kelompok/subkelompok (SNP), dan uraian  untuk RKAS, cashflow, dan jurnal</p>
                </div>
                <div class="flex items-center gap-2">
                    <x-export-excel route="admin.akun.export" />
                    <button type="button" @click="openImportModal()" class="btn-secondary text-sm">Import Excel</button>
                    <x-toolbar.add-icon label="Tambah Akun" @click="showCreateModal=true" />
                </div>
            </div>

            <div class="px-6 py-3 border-b flex flex-wrap gap-2" data-tour="admin-akun-filter-tabs" style="border-color:rgba(0,0,0,0.06);">
                <form method="GET" class="ml-auto flex flex-wrap gap-2 items-center">
                    <select name="jenis" class="input-field text-sm w-36">
                        <option value="">Semua jenis</option>
                        @foreach($jenisOptions as $jenisOpt)
                            <option value="{{ $jenisOpt }}" @selected(request('jenis') === $jenisOpt)>{{ $jenisOpt }}</option>
                        @endforeach
                    </select>
                    <select name="kelompok" class="input-field text-sm w-44" onchange="this.form.subkelompok.value=''; this.form.submit()">
                        <option value="">Semua kelompok</option>
                        @foreach($kelompokOptions as $opt)
                            <option value="{{ $opt }}" @selected(request('kelompok') === $opt)>{{ $opt }}</option>
                        @endforeach
                    </select>
                    <select name="subkelompok" class="input-field text-sm w-48">
                        <option value="">Semua subkelompok</option>
                        @foreach($subkelompokOptions as $opt)
                            <option value="{{ $opt }}" @selected(request('subkelompok') === $opt)>{{ Str::limit($opt, 40) }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari..." class="input-field text-sm w-40">
                    <x-toolbar.filter-icon variant="secondary" />
                    <x-filter-reset :href="route('admin.akun.index')" />
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="data-table" data-tour="admin-akun-table">
                    <thead>
                        <tr>
                            <th>Kode Akun</th>
                            <th class="text-center">Jenis</th>
                            <th>Nama Akun</th>
                            <th>Kelompok</th>
                            <th>Subkelompok</th>
                            <th>Uraian</th>
                            <th class="text-right">Saldo</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($akunList as $akun)
                            @php
                                $rowPayload = $akun->only(['id','kode','nama','snp','komponen','uraian','tipe','jenis','kategori_arus_kas','saldo_normal','deskripsi']);
                                $rowPayload['saldo'] = (float) ($akun->saldo ?? 0);
                                $rowPayload['saldo_awal'] = (float) ($akun->saldo_awal ?? 0);
                            @endphp
                            <tr>
                                <td class="font-mono font-semibold whitespace-nowrap" style="color:#1A6B6B;">{{ $akun->kode }}</td>
                                <td class="text-center"><span class="badge badge-gray text-xs">{{ ucfirst($akun->jenis) }}</span></td>
                                <td class="font-medium">{{ $akun->nama }}</td>
                                <td class="text-xs" style="color:#9E9790;">{{ $akun->snp ?? '-' }}</td>
                                <td class="text-xs" style="color:#9E9790;">{{ $akun->komponen ?? '-' }}</td>
                                <td class="text-xs max-w-xs truncate" style="color:#9E9790;" @if($akun->uraian) title="{{ $akun->uraian }}" @endif>{{ $akun->uraian ? Str::limit($akun->uraian, 50) : '-' }}</td>
                                <td class="text-right font-semibold whitespace-nowrap" style="color:{{ ($akun->saldo ?? 0) < 0 ? '#C0392B' : '#1A6B6B' }};">Rp {{ number_format($akun->saldo ?? 0, 0, ',', '.') }}</td>
                                <td class="text-right">
                                    <div class="inline-flex items-center justify-end gap-1.5">
                                        <button type="button" title="Lihat" @click="openDetail({{ json_encode($rowPayload) }})" class="h-8 w-8 rounded-lg flex items-center justify-center" style="color:#1A6B6B;background:#E8F5F5;">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </button>
                                        <button type="button" title="Edit" @click="editData={{ json_encode($rowPayload) }}; showEditModal=true" class="h-8 w-8 rounded-lg flex items-center justify-center" style="color:#1A6B6B;background:#D0E8E8;">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        @if(!$akun->isSistem())
                                            <button type="button" title="Hapus" @click="deleteRoute='{{ route('admin.akun.destroy', $akun) }}'; showDeleteModal=true" class="h-8 w-8 rounded-lg flex items-center justify-center" style="color:#C0392B;background:#FAD7D2;">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="py-12 text-center" style="color:#9E9790;">Belum ada akun.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t" style="border-color:rgba(0,0,0,0.06);">
                <x-per-page-selector :paginator="$akunList" />
                {{ $akunList->links() }}
            </div>
        </div>

        <div x-show="showCreateModal" class="modal-overlay" style="display:none;">
            <div x-show="showCreateModal" x-transition class="modal-box max-w-lg" @click.away="showCreateModal=false">
                <form action="{{ route('admin.akun.store') }}" method="POST">
                    @csrf
                    <div class="modal-header"><h3 class="section-title">Tambah Kode Rekening</h3></div>
                    <div class="modal-body grid grid-cols-2 gap-3">
                        <div><label class="input-label">Kode</label><input type="text" name="kode" required class="input-field"></div>
                        <div>
                            <label class="input-label">Jenis</label>
                            <select name="jenis" required class="input-field">
                                @foreach(\App\Support\JenisAkun::ALL as $jenis)
                                    <option value="{{ $jenis }}" @selected(old('jenis', \App\Support\JenisAkun::ASSETS) === $jenis)>{{ $jenis }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-2"><label class="input-label">Nama</label><input type="text" name="nama" required class="input-field"></div>
                        <div class="col-span-2"><label class="input-label">Uraian</label><textarea name="uraian" rows="2" class="input-field"></textarea></div>
                        <div><label class="input-label">Kelompok</label><input type="text" name="snp" class="input-field" placeholder="SNP / kelompok RKAS"></div>
                        <div><label class="input-label">Subkelompok</label><input type="text" name="komponen" class="input-field"></div>
                        <div class="col-span-2"><label class="input-label">Saldo Normal</label><select name="saldo_normal" class="input-field"><option value="debit">Debit</option><option value="kredit">Kredit</option></select></div>
                        <p class="col-span-2 text-xs" style="color:#9E9790;">Saldo awal (opening balance) hanya diisi lewat <strong>Import Excel</strong> saat migrasi/go-live.</p>
                        <input type="hidden" name="tipe" value="rkas">
                    </div>
                    <div class="modal-footer"><button type="button" @click="showCreateModal=false" class="btn-secondary">Batal</button><button type="submit" class="btn-primary">Simpan</button></div>
                </form>
            </div>
        </div>

        <div x-show="showEditModal" class="modal-overlay" style="display:none;">
            <div x-show="showEditModal" x-transition class="modal-box max-w-lg" @click.away="showEditModal=false">
                <form :action="`{{ url('admin/akun') }}/${editData.id}`" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-header"><h3 class="section-title">Edit Akun</h3></div>
                    <div class="modal-body grid grid-cols-2 gap-3">
                        <div><label class="input-label">Kode</label><input type="text" name="kode" x-model="editData.kode" required class="input-field"></div>
                        <div>
                            <label class="input-label">Jenis</label>
                            <select name="jenis" x-model="editData.jenis" required class="input-field">
                                @foreach(\App\Support\JenisAkun::ALL as $jenis)
                                    <option value="{{ $jenis }}">{{ $jenis }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-2"><label class="input-label">Nama</label><input type="text" name="nama" x-model="editData.nama" required class="input-field"></div>
                        <div class="col-span-2"><label class="input-label">Uraian</label><textarea name="uraian" x-model="editData.uraian" rows="2" class="input-field"></textarea></div>
                        <div><label class="input-label">Kelompok</label><input type="text" name="snp" x-model="editData.snp" class="input-field"></div>
                        <div><label class="input-label">Subkelompok</label><input type="text" name="komponen" x-model="editData.komponen" class="input-field"></div>
                        <div class="col-span-2"><label class="input-label">Saldo Normal</label><select name="saldo_normal" x-model="editData.saldo_normal" class="input-field"><option value="debit">Debit</option><option value="kredit">Kredit</option></select></div>
                        <p class="col-span-2 text-xs" style="color:#9E9790;">Saldo awal tidak bisa diubah di sini. Gunakan jurnal umum atau import ulang (akun baru).</p>
                    </div>
                    <div class="modal-footer"><button type="button" @click="showEditModal=false" class="btn-secondary">Batal</button><button type="submit" class="btn-primary">Simpan</button></div>
                </form>
            </div>
        </div>

        <div x-show="showImportModal" class="modal-overlay" style="display:none;" @click.self="showImportModal=false">
            <div x-show="showImportModal" x-transition class="modal-box max-w-lg">
                <form x-ref="importForm" action="{{ route('admin.akun.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header border-b pb-4" style="border-color:rgba(0,0,0,0.06);">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-lg flex items-center justify-center shrink-0" style="background:#E8F5F5;">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:#1A6B6B;">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="section-title">Import Kode Rekening dari Excel</h3>
                                <p class="section-subtitle">Ikuti 3 langkah di bawah secara berurutan.</p>
                            </div>
                        </div>
                    </div>

                    <div class="modal-body space-y-3 py-4">
                        <div class="rounded-xl border p-4" style="border-color:rgba(0,0,0,0.08);background:#FAFAF8;">
                            <div class="flex items-start gap-3">
                                <span class="h-7 w-7 shrink-0 rounded-full flex items-center justify-center text-xs font-bold text-white" style="background:#1A6B6B;">1</span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold" style="color:#2C2C2C;">Unduh formulir Excel</p>
                                    <p class="text-xs mt-1 leading-relaxed" style="color:#6B6560;">
                                        File berisi sheet <strong>Data Akun</strong> (kolom contoh) dan <strong>Petunjuk</strong>. Isi data di komputer Anda, hapus baris contoh jika tidak dipakai.
                                    </p>
                                    <a href="{{ route('admin.akun.import.template') }}"
                                        class="btn-secondary mt-3 inline-flex items-center text-sm w-full sm:w-auto justify-center">
                                        <svg class="h-4 w-4 mr-1.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        Unduh Formulir Excel
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-xl border p-4" style="border-color:rgba(0,0,0,0.08);background:#FAFAF8;">
                            <div class="flex items-start gap-3">
                                <span class="h-7 w-7 shrink-0 rounded-full flex items-center justify-center text-xs font-bold text-white" style="background:#1A6B6B;">2</span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold" style="color:#2C2C2C;">Pilih file yang sudah diisi</p>
                                    <p class="text-xs mt-1 leading-relaxed" style="color:#6B6560;">
                                        Format <strong>.xlsx</strong> atau <strong>.xls</strong> (maks. 5 MB). Saldo awal hanya untuk akun neraca; trial balance dicek saat periksa file.
                                    </p>
                                    <input type="file" name="file" accept=".xlsx,.xls" required class="sr-only" id="import-akun-file"
                                        @change="onImportFileSelected($event)">
                                    <label for="import-akun-file" x-show="!importFileName"
                                        @dragover.prevent @drop.prevent="onImportFileDropped($event)"
                                        class="mt-3 flex flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed px-4 py-8 cursor-pointer transition hover:bg-white"
                                        style="border-color:#D0E8E8;background:#F5FAFA;">
                                        <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" style="color:#1A6B6B;">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                        </svg>
                                        <span class="text-sm font-semibold" style="color:#1A6B6B;">Klik untuk memilih file Excel</span>
                                        <span class="text-xs" style="color:#9E9790;">atau seret file ke sini</span>
                                    </label>
                                    <div x-show="importFileName" class="mt-3 space-y-3" style="display:none;">
                                        <div class="flex items-center gap-3 rounded-xl border px-4 py-3" style="border-color:#D0E8E8;background:#E8F5F5;">
                                            <div class="min-w-0 flex-1">
                                                <p class="text-xs font-medium" style="color:#6B6560;">File terpilih:</p>
                                                <p class="text-sm font-semibold truncate" style="color:#2C2C2C;" x-text="importFileName"></p>
                                            </div>
                                            <label for="import-akun-file" class="text-xs font-semibold shrink-0 cursor-pointer px-2 py-1 rounded-lg" style="color:#1A6B6B;background:white;">Ganti</label>
                                        </div>
                                        <button type="button" @click="runImportTest()" :disabled="importTesting" class="btn-secondary w-full justify-center">
                                            <span x-text="importTesting ? 'Sedang memeriksa...' : 'Periksa File'"></span>
                                        </button>
                                    </div>
                                    @error('file')<p class="text-xs text-red-500 mt-2">{{ $message }}</p>@enderror
                                    <p x-show="importTestError" x-text="importTestError" class="text-xs text-red-500 mt-2" style="display:none;"></p>
                                </div>
                            </div>
                        </div>

                        <div x-show="importTest" class="rounded-xl border p-4" style="display:none;border-color:rgba(0,0,0,0.08);"
                            :style="{ background: canImport() ? '#E8F5F5' : '#FAD7D2' }">
                            <div class="flex items-start gap-3">
                                <span class="h-7 w-7 shrink-0 rounded-full flex items-center justify-center text-xs font-bold text-white"
                                    :style="{ background: importTest?.can_import ? '#1A6B6B' : '#C0392B' }">!</span>
                                <div class="flex-1 min-w-0 text-sm space-y-2">
                                    <p class="font-semibold" style="color:#2C2C2C;" x-text="importTest?.can_import ? 'File siap disimpan' : 'Ada data yang perlu diperbaiki'"></p>
                                    <p class="text-xs" style="color:#6B6560;" x-text="importTest?.message"></p>
                                    <template x-if="importTest?.opening_trial">
                                        <p class="text-xs" :style="{ color: importTest.opening_trial.balanced ? '#1A6B6B' : '#C0392B' }">
                                            Trial balance saldo awal: debit
                                            <span x-text="new Intl.NumberFormat('id-ID').format(importTest.opening_trial.total_debit)"></span>
                                            / kredit
                                            <span x-text="new Intl.NumberFormat('id-ID').format(importTest.opening_trial.total_kredit)"></span>
                                            (<span x-text="importTest.opening_trial.opening_rows"></span> akun ber-saldo awal)
                                        </p>
                                    </template>
                                    <ul class="text-xs max-h-32 overflow-y-auto space-y-1" style="color:#6B6560;">
                                        <template x-for="row in (importTest?.rows || []).filter(r => r.status !== 'ok')" :key="row.row + row.status">
                                            <li>
                                                <span class="font-semibold" x-text="`Baris ${row.row}:`"></span>
                                                <span x-text="row.label"></span>  <span x-text="row.message"></span>
                                            </li>
                                        </template>
                                    </ul>
                                    <label x-show="importTest?.duplicate_count > 0" class="flex items-center gap-2 text-sm">
                                        <input type="checkbox" name="ignore_duplicates" value="1" x-model="ignoreDuplicates" class="rounded">
                                        Abaikan baris duplikat, import sisanya
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-t pt-4" style="border-color:rgba(0,0,0,0.06);">
                        <button type="button" @click="showImportModal=false" class="btn-secondary">Tutup</button>
                        <button type="submit" class="btn-primary" :disabled="!canImport()" :class="!canImport() && 'opacity-50 pointer-events-none'">
                            Simpan ke Sistem
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div x-show="showDetailModal" class="modal-overlay" style="display:none;">
            <div x-show="showDetailModal" x-transition class="modal-box max-w-3xl max-h-[90vh] flex flex-col" @click.away="showDetailModal=false">
                <div class="modal-header shrink-0"><h3 class="section-title">Detail Akun</h3></div>
                <div class="modal-body overflow-y-auto text-sm space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div><p class="input-label">Kode</p><p class="font-mono font-semibold" style="color:#1A6B6B;" x-text="detailData.kode"></p></div>
                        <div><p class="input-label">Jenis</p><p x-text="detailData.jenis"></p></div>
                        <div class="col-span-2"><p class="input-label">Nama</p><p class="font-medium" x-text="detailData.nama"></p></div>
                        <div><p class="input-label">Kelompok</p><p x-text="detailData.snp || '-'"></p></div>
                        <div><p class="input-label">Subkelompok</p><p x-text="detailData.komponen || '-'"></p></div>
                        <div class="col-span-2"><p class="input-label">Uraian</p><p x-text="detailData.uraian || '-'"></p></div>
                        <div><p class="input-label">Saldo Normal</p><p class="capitalize" x-text="detailData.saldo_normal"></p></div>
                        <div><p class="input-label">Saldo Awal</p><p class="font-semibold" x-text="rupiah(detailData.saldo_awal)"></p></div>
                        <div class="col-span-2"><p class="input-label">Saldo saat ini</p><p class="font-semibold text-base" style="color:#1A6B6B;" x-text="rupiah(detailData.saldo)"></p></div>
                    </div>

                    <div class="border-t pt-4" style="border-color:rgba(0,0,0,0.06);">
                        <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                            <p class="input-label mb-0">Riwayat jurnal (50 terbaru)</p>
                            @can('menu.laporan-keuangan')
                                <a :href="`{{ route('admin.laporan-keuangan.buku-besar') }}?akun_id=${detailData.id}&tipe=bulanan&tahun={{ now()->year }}&bulan={{ now()->month }}`"
                                   class="text-xs font-semibold" style="color:#1A6B6B;" x-show="detailData.id">Buku besar lengkap →</a>
                            @endcan
                        </div>
                        <p x-show="detailHistoryLoading" class="text-xs py-4 text-center" style="color:#9E9790;">Memuat riwayat…</p>
                        <p x-show="detailHistoryError" x-text="detailHistoryError" class="text-xs py-2" style="display:none;color:#C0392B;"></p>
                        <div x-show="!detailHistoryLoading && !detailHistoryError && detailHistory.length === 0" class="text-xs py-4 text-center" style="color:#9E9790;">
                            Belum ada mutasi jurnal untuk akun ini.
                        </div>
                        <div x-show="detailHistory.length > 0" class="overflow-x-auto rounded-lg border" style="border-color:rgba(0,0,0,0.08);">
                            <table class="data-table text-xs">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>No. Jurnal</th>
                                        <th>Keterangan</th>
                                        <th class="text-right">Debit</th>
                                        <th class="text-right">Kredit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="row in detailHistory" :key="row.id">
                                        <tr>
                                            <td class="whitespace-nowrap" x-text="row.tanggal"></td>
                                            <td class="whitespace-nowrap">
                                                <template x-if="row.show_url">
                                                    <a :href="row.show_url" class="font-mono font-semibold underline" style="color:#1A6B6B;" x-text="row.no_jurnal"></a>
                                                </template>
                                                <template x-if="!row.show_url">
                                                    <span x-text="row.no_jurnal"></span>
                                                </template>
                                            </td>
                                            <td class="max-w-[12rem] truncate" :title="row.deskripsi" x-text="row.deskripsi"></td>
                                            <td class="text-right whitespace-nowrap" x-text="formatMutasi(row.debit)"></td>
                                            <td class="text-right whitespace-nowrap" x-text="formatMutasi(row.kredit)"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer shrink-0">
                    <button type="button" @click="showDetailModal=false" class="btn-secondary">Tutup</button>
                    <button type="button" @click="editData=detailData; showDetailModal=false; showEditModal=true" class="btn-primary">Edit</button>
                </div>
            </div>
        </div>

        <x-confirm-modal
            show="showDeleteModal"
            action-binding="deleteRoute"
            method="DELETE"
            title="Hapus Akun?"
            message="Akun yang sudah dipakai di transaksi tidak disarankan dihapus."
        />
    </div>
</x-app-layout>
