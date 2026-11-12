<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background: #1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
            </div>
            <h2 class="font-bold text-xl" style="color: #2C2C2C;">Kesehatan & Kebersihan Siswa</h2>
        </div>
    </x-slot>

    <div class="app-page-shell"
         x-data="{
            showInputModal: false,
            showHistoryModal: false,
            isLoadingHistory: false,
            selectedAnak: null,
            historyData: [],
            form: {
                berat_badan: '',
                tinggi_badan: '',
                lingkar_kepala: '',
                gigi: 'Bersih',
                telinga: 'Bersih',
                kuku: 'Bersih',
                alergi: '',
                tanggal_pemeriksaan: '{{ date('Y-m-d') }}'
            },
            openInput(anak) {
                this.selectedAnak = anak;
                let latest = (anak.kesehatans && anak.kesehatans.length > 0) ? anak.kesehatans[0] : null;
                if (latest) {
                    this.form.berat_badan = latest.berat_badan;
                    this.form.tinggi_badan = latest.tinggi_badan;
                    this.form.lingkar_kepala = latest.lingkar_kepala;
                    this.form.gigi = latest.gigi || 'Bersih';
                    this.form.telinga = latest.telinga || 'Bersih';
                    this.form.kuku = latest.kuku || 'Bersih';
                    this.form.alergi = latest.alergi || '';
                } else {
                    this.form.berat_badan = '';
                    this.form.tinggi_badan = '';
                    this.form.lingkar_kepala = '';
                    this.form.gigi = 'Bersih';
                    this.form.telinga = 'Bersih';
                    this.form.kuku = 'Bersih';
                    this.form.alergi = '';
                }
                this.showInputModal = true;
            },
            async openHistory(anak) {
                this.selectedAnak = anak;
                this.showHistoryModal = true;
                this.isLoadingHistory = true;
                try {
                    let res = await fetch(`/admin/kesehatan/history/${anak.id}`);
                    this.historyData = await res.json();
                } catch(e) {
                    console.error(e);
                    this.historyData = [];
                }
                this.isLoadingHistory = false;
            }
         }" @tour-close-modals.window="showInputModal=false; showHistoryModal=false">

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
                <h3 class="section-title">Data kesehatan & kebersihan</h3>
                <p class="section-subtitle">Antropometri terbaru per siswa, kebersihan gigi/telinga/kuku, dan riwayat pemeriksaan.</p>
            </div>

            <form data-tour="admin-kesehatan-filter" method="get" action="{{ route('admin.kesehatan.index') }}" class="card-pad toolbar-form border-b w-full" style="border-color:rgba(0,0,0,0.06);">
                <div class="toolbar-field md:min-w-[10rem] md:max-w-[12rem]">
                    <label class="input-label">Kelas</label>
                    <select name="kelas_id" class="input-field w-full">
                        <option value="">Semua kelas</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id }}" @selected((string) request('kelas_id') === (string) $k->id)>{{ $k->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="toolbar-field md:min-w-[12rem] md:flex-[2]">
                    <label class="input-label">Cari siswa</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="input-field w-full" placeholder="Nama siswa…">
                </div>
                <div class="toolbar-field md:min-w-[9rem] md:max-w-[11rem]">
                    <label class="input-label">Pemeriksaan</label>
                    <select name="pemeriksaan" class="input-field w-full">
                        <option value="" @selected(! request('pemeriksaan'))>Semua</option>
                        <option value="sudah" @selected(request('pemeriksaan') === 'sudah')>Sudah pernah</option>
                        <option value="belum" @selected(request('pemeriksaan') === 'belum')>Belum ada data</option>
                    </select>
                </div>
                <div class="toolbar-actions">
                    <x-toolbar.filter-icon label="Filter" variant="secondary" />
                    <x-filter-reset :href="route('admin.kesehatan.index')" compact />
                    <x-export-excel route="admin.kesehatan.export" :icon-only="true" />
                </div>
            </form>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Siswa</th>
                            <th>Kelas</th>
                            <th>BB/TB</th>
                            <th>Kebersihan (G/T/K)</th>
                            <th>Alergi</th>
                            <th>Pemeriksaan Terakhir</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($anaks as $anak)
                            @php $latest = $anak->kesehatans->first(); @endphp
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <x-foto-profil :path="$anak->photo" :name="$anak->name" size="sm" />
                                        <div>
                                            <span class="font-semibold block text-[#2C2C2C]">{{ $anak->name }}</span>
                                            <div class="flex items-center gap-1.5 mt-0.5">
                                                <span class="text-[10px] text-gray-500 uppercase tracking-wider">{{ $anak->jenis_kelamin }}</span>
                                                <span class="text-[10px] font-bold text-[#1A6B6B]">• {{ $anak->age }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($anak->kelas)<span class="badge badge-teal font-medium">{{ $anak->kelas->name }}</span>
                                    @else<span class="text-xs italic text-gray-400">N/A</span>@endif
                                </td>
                                <td>
                                    @if($latest)
                                        <div class="text-sm">
                                            <span class="font-bold text-[#1A6B6B]">{{ $latest->berat_badan ?? '-' }}</span> kg / 
                                            <span class="font-bold text-[#1A6B6B]">{{ $latest->tinggi_badan ?? '-' }}</span> cm
                                        </div>
                                        <div class="text-[10px] text-gray-500">LK: {{ $latest->lingkar_kepala ?? '-' }} cm</div>
                                    @else
                                        <span class="text-xs text-gray-400">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($latest)
                                        <div class="flex flex-col gap-1 text-[10px] py-1">
                                            @foreach(['gigi' => 'Gigi', 'telinga' => 'Telinga', 'kuku' => 'Kuku'] as $field => $label)
                                                @php 
                                                    $val = $latest->$field;
                                                    $isGood = Str::contains(strtolower($val), 'bersih') || Str::contains(strtolower($val), 'rapi');
                                                    $color = $isGood ? 'text-[#2E7D32]' : 'text-[#C62828]';
                                                    $dot = $isGood ? 'bg-[#4CAF50]' : 'bg-[#F44336]';
                                                @endphp
                                                <div class="flex items-center gap-1.5">
                                                    <span class="w-1.5 h-1.5 rounded-full {{ $dot }} shrink-0"></span>
                                                    <span class="font-bold text-gray-600 whitespace-nowrap">{{ $label }}:</span>
                                                    <span class="{{ $color }} font-medium">{{ $val }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($latest && $latest->alergi)
                                        <span class="text-xs text-red-600 font-medium">{{ Str::limit($latest->alergi, 20) }}</span>
                                    @else
                                        <span class="text-xs text-gray-400">Tidak ada</span>
                                    @endif
                                </td>
                                <td>
                                    @if($latest)
                                        <span class="text-xs">{{ \Carbon\Carbon::parse($latest->tanggal_pemeriksaan)->format('d/m/Y') }}</span>
                                    @else
                                        <span class="text-xs text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <div class="flex flex-wrap items-center justify-end gap-1.5">
                                        <x-table.action-icon type="button" label="Riwayat" variant="detail"
                                            @if($loop->first) data-tour="admin-kesehatan-action-riwayat" data-tour-open-modal="history" @endif
                                            @click="openHistory(@js($anak))" />
                                        <x-table.action-icon type="button" label="Input data" variant="edit"
                                            @if($loop->first) data-tour="admin-kesehatan-action-input" data-tour-open-modal="create" @endif
                                            @click="openInput(@js($anak))" />
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-6 md:py-12 text-center" style="color:#9E9790;">Tidak ada siswa sesuai filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 md:px-6 py-4 border-t" style="border-color:rgba(0,0,0,0.06);">
                <x-per-page-selector :paginator="$anaks" />
                {{ $anaks->links() }}
            </div>
        </div>

        <!-- INPUT MODAL -->
        <div x-show="showInputModal" class="modal-overlay" style="display:none;">
            <div class="modal-box max-w-lg" @click.away="showInputModal = false">
                <form action="{{ route('admin.kesehatan.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="anak_id" :value="selectedAnak?.id">
                    <div class="modal-header">
                        <h3 class="section-title">Input Kesehatan & Kebersihan</h3>
                        <p class="section-subtitle">Siswa: <span class="font-bold text-[#1A6B6B]" x-text="selectedAnak?.name || ''"></span></p>
                    </div>
                    <div class="modal-body grid grid-cols-2 gap-4" data-tour="modal-create-section-form">
                            <div class="col-span-2">
                                <label class="input-label">Tanggal Pemeriksaan</label>
                                <input type="date" name="tanggal_pemeriksaan" required class="input-field" x-model="form.tanggal_pemeriksaan">
                            </div>
                            <div>
                                <label class="input-label">Berat Badan (kg)</label>
                                <input type="number" step="0.01" name="berat_badan" class="input-field" placeholder="0.00" x-model="form.berat_badan">
                            </div>
                            <div>
                                <label class="input-label">Tinggi Badan (cm)</label>
                                <input type="number" step="0.01" name="tinggi_badan" class="input-field" placeholder="0.00" x-model="form.tinggi_badan">
                            </div>
                            <div class="col-span-2">
                                <label class="input-label">Lingkar Kepala (cm)</label>
                                <input type="number" step="0.01" name="lingkar_kepala" class="input-field" placeholder="0.00" x-model="form.lingkar_kepala">
                            </div>
                            
                            <div class="col-span-2 text-xs font-bold text-[#1A6B6B] border-b pb-1 mt-2">Kebersihan</div>
                            <div>
                                <label class="input-label">Gigi</label>
                                <select name="gigi" class="input-field" x-model="form.gigi">
                                    <option value="Bersih">Bersih</option>
                                    <option value="Cukup Bersih">Cukup Bersih</option>
                                    <option value="Kotor">Kotor</option>
                                    <option value="Berlubang">Berlubang</option>
                                    <option value="Perlu Tindakan">Perlu Tindakan</option>
                                </select>
                            </div>
                            <div>
                                <label class="input-label">Telinga</label>
                                <select name="telinga" class="input-field" x-model="form.telinga">
                                    <option value="Bersih">Bersih</option>
                                    <option value="Cukup Bersih">Cukup Bersih</option>
                                    <option value="Kotor">Kotor</option>
                                    <option value="Perlu Tindakan">Perlu Tindakan</option>
                                </select>
                            </div>
                            <div>
                                <label class="input-label">Kuku</label>
                                <select name="kuku" class="input-field" x-model="form.kuku">
                                    <option value="Bersih dan rapi">Bersih dan rapi</option>
                                    <option value="Cukup Bersih">Cukup Bersih</option>
                                    <option value="Kotor dan panjang">Kotor dan panjang</option>
                                    <option value="Perlu Tindakan">Perlu Tindakan</option>
                                </select>
                            </div>
                            <div class="col-span-2 mt-2">
                                <label class="input-label">Alergi (jika ada)</label>
                                <textarea name="alergi" class="input-field" rows="2" placeholder="Sebutkan alergi jika ada..." x-model="form.alergi"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" @click="showInputModal = false" class="btn-secondary">Batal</button>
                            <button type="submit" data-tour="modal-create-submit" class="btn-primary">Simpan Data</button>
                        </div>
                    </form>
                </div>
            </div>

        <!-- HISTORY MODAL -->
        <div x-show="showHistoryModal" data-tour="modal-history" class="modal-overlay" style="display:none;" x-transition>
            <div class="modal-box max-w-4xl w-full" @click.away="showHistoryModal = false">
                <div class="modal-header shrink-0 flex items-start justify-between gap-3">
                    <div>
                        <h3 class="section-title">Riwayat Kesehatan</h3>
                        <p class="section-subtitle mt-1">Siswa: <span class="font-bold text-[#1A6B6B]" x-text="selectedAnak?.name || ''"></span></p>
                    </div>
                    <button type="button" @click="showHistoryModal = false" class="shrink-0 p-1.5 rounded-lg hover:bg-black/5 transition" aria-label="Tutup">
                        <svg class="h-5 w-5" style="color:#5A5A5A;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="modal-body modal-scroll" data-tour="modal-history-content">
                    <div x-show="isLoadingHistory" class="flex justify-center items-center py-12">
                        <svg class="animate-spin h-8 w-8 text-[#1A6B6B]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </div>
                    
                    <div x-show="!isLoadingHistory && historyData.length === 0" class="text-center py-12 text-gray-400 italic">
                        Belum ada riwayat kesehatan untuk siswa ini.
                    </div>

                    <div x-show="!isLoadingHistory && historyData.length > 0" class="overflow-x-auto">
                        <table class="w-full text-left bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-100">
                                    <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-400">Tanggal</th>
                                    <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-400">Dimensi</th>
                                    <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-400">Kebersihan</th>
                                    <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-400">Alergi</th>
                                    <th class="px-4 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-400 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <template x-for="item in historyData" :key="item.id">
                                    <tr class="hover:bg-gray-50/50 transition">
                                        <td class="px-4 py-3 whitespace-nowrap font-bold text-gray-900 text-sm" x-text="item.tanggal_formatted"></td>
                                        <td class="px-4 py-3">
                                            <div class="text-sm">
                                                <span class="font-bold text-[#1A6B6B]" x-text="item.berat_badan || '-'"></span> kg / 
                                                <span class="font-bold text-[#1A6B6B]" x-text="item.tinggi_badan || '-'"></span> cm
                                            </div>
                                            <div class="text-[10px] text-gray-500" x-text="'LK: ' + (item.lingkar_kepala || '-') + ' cm'"></div>
                                        </td>
                                        <td class="px-4 py-3 text-xs">
                                            <div class="font-semibold text-gray-600">Gigi: <span x-text="item.gigi || 'Bersih'"></span></div>
                                            <div class="font-semibold text-gray-600">Tel: <span x-text="item.telinga || 'Bersih'"></span></div>
                                            <div class="font-semibold text-gray-600">Kuku: <span x-text="item.kuku || 'Bersih'"></span></div>
                                        </td>
                                        <td class="px-4 py-3 text-xs text-red-600 font-semibold" x-text="item.alergi || '-'"></td>
                                        <td class="px-4 py-3 text-center">
                                            <form :action="`/admin/kesehatan/${item.id}`" method="POST" onsubmit="return confirm('Yakin ingin menghapus data riwayat ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="h-8 w-8 rounded-lg bg-red-50 flex items-center justify-center hover:bg-red-500 hover:text-white transition text-red-600 mx-auto" title="Hapus Riwayat">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer shrink-0">
                    <button type="button" @click="showHistoryModal = false" class="btn-secondary">Tutup</button>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
