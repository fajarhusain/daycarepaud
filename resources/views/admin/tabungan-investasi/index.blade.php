<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background: #1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>
            <h2 class="font-bold text-xl" style="color: #2C2C2C;">Tabungan & Investasi</h2>
        </div>
    </x-slot>

    <div class="app-page-shell"
         x-data="{
            showSettingsModal: false,
            showMutasiModal: false,
            mutasiAksi: 'setor',
            mutasiAkunId: '',
            mutasiRekeningId: '{{ $setting->akun_kas_id ?? '' }}',
            openMutasi(aksi, akunTabunganId) {
                this.mutasiAksi = aksi;
                this.mutasiAkunId = String(akunTabunganId);
                if (!this.mutasiRekeningId) {
                    this.mutasiRekeningId = '{{ $akunRekeningOptions->first()?->id ?? '' }}';
                }
                this.showMutasiModal = true;
            }
         }">

        @if(session('success'))<div class="alert-success mb-5">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert-danger mb-5"><ul class="list-disc pl-5 text-sm">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul></div>@endif

        @if($tabunganAkuns->isNotEmpty())
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 mb-6">
                @foreach($tabunganAkuns as $row)
                    <div class="stat-card flex-col items-stretch gap-0">
                        <p class="text-xs font-semibold uppercase tracking-wider mb-1" style="color:#9E9790;">{{ $row->akun->kode }}</p>
                        <p class="font-semibold text-sm mb-2" style="color:#2C2C2C;">{{ $row->akun->nama }}</p>
                        <p class="text-xl font-bold tabular-nums whitespace-nowrap mb-4" style="color:#1A6B6B;">Rp&nbsp;{{ number_format($saldos[$row->akun_id] ?? 0, 0, ',', '.') }}</p>
                        <div class="flex flex-wrap gap-2 mt-auto pt-1 border-t" style="border-color:rgba(0,0,0,0.06);">
                            <button type="button" class="btn-secondary text-xs flex-1 min-w-[5.5rem]"
                                    @click="openMutasi('setor', {{ $row->akun_id }})">Setor</button>
                            <button type="button" class="btn-secondary text-xs flex-1 min-w-[5.5rem]"
                                    @click="openMutasi('tarik', {{ $row->akun_id }})">Tarik</button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="card overflow-hidden">
            <div class="card-pad border-b" style="border-color:rgba(0,0,0,0.06);">
                <div class="min-w-0 mb-3 md:mb-4">
                    <h3 class="section-title mb-0">Mutasi</h3>
                    <p class="section-subtitle mt-1">Transaksi kas terkait akun tabungan/investasi terpilih</p>
                </div>
                <form method="GET" class="toolbar-form w-full">
                    <div class="toolbar-field md:min-w-[9rem] md:max-w-[11rem]">
                        <label class="input-label">Bulan</label>
                        <select name="bulan" class="input-field w-full">
                            @foreach(range(1,12) as $m)
                                <option value="{{ $m }}" @selected($bulan == $m)>{{ \Carbon\Carbon::create()->month($m)->locale('id')->translatedFormat('F') }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="toolbar-field md:min-w-[6rem] md:max-w-[8rem]">
                        <label class="input-label">Tahun</label>
                        <select name="tahun" class="input-field w-full">
                            @foreach(range(now()->year - 2, now()->year + 1) as $y)
                                <option value="{{ $y }}" @selected($tahun == $y)>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="toolbar-actions">
                        <x-toolbar.filter-icon variant="secondary" label="Filter" />
                        <x-filter-reset :href="route('admin.tabungan-investasi.index')" compact />
                        <button type="button" @click="showSettingsModal = true"
                            class="btn-secondary toolbar-icon shrink-0 inline-flex items-center justify-center !p-0 h-9 w-9 md:h-11 md:w-11"
                            title="Pengaturan Akun" aria-label="Pengaturan Akun">
                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Keterangan</th>
                            <th>Akun</th>
                            <th class="text-center">Jenis</th>
                            <th class="text-right">Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mutasi as $trx)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($trx->date)->format('d M Y') }}</td>
                                <td>{{ $trx->description }}</td>
                                <td class="text-xs">{{ optional($trx->akun)->kode }} / {{ optional($trx->akunLawan)->kode }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $trx->type === 'in' ? 'badge-green' : 'badge-gray' }}">{{ $trx->type === 'in' ? 'Masuk' : 'Keluar' }}</span>
                                </td>
                                <td class="text-right font-semibold">Rp {{ number_format($trx->amount, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-10 text-center text-sm" style="color:#9E9790;">Belum ada mutasi atau akun belum dipilih.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t" style="border-color:rgba(0,0,0,0.06);">{{ $mutasi->links() }}</div>
        </div>

        <div x-show="showSettingsModal" class="modal-overlay" style="display:none;">
            <div x-show="showSettingsModal" x-transition class="modal-box max-w-lg" @click.away="showSettingsModal=false">
                <form action="{{ route('admin.tabungan-investasi.settings') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h3 class="section-title">Pengaturan Akun</h3>
                        <p class="section-subtitle mt-1">Pilih akun aset untuk tabungan, deposito, atau investasi.</p>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-1 gap-2 max-h-64 overflow-y-auto border rounded-lg p-3" style="border-color:rgba(0,0,0,0.08);">
                            @forelse($asetOptions as $akun)
                                <label class="flex items-center gap-2 text-sm cursor-pointer">
                                    <input type="checkbox" name="akun_id[]" value="{{ $akun->id }}"
                                           @checked(in_array($akun->id, $selectedIds, true))>
                                    <span class="font-mono text-xs" style="color:#1A6B6B;">{{ $akun->kode }}</span>
                                    {{ $akun->nama }}
                                </label>
                            @empty
                                <p class="text-sm" style="color:#9E9790;">Belum ada akun aset aktif.</p>
                            @endforelse
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" @click="showSettingsModal=false" class="btn-secondary">Batal</button>
                        <button type="submit" class="btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        <div x-show="showMutasiModal" class="modal-overlay" style="display:none;">
            <div x-show="showMutasiModal" x-transition class="modal-box max-w-md" @click.away="showMutasiModal=false">
                <form action="{{ route('admin.tabungan-investasi.mutasi') }}" method="POST">
                    @csrf
                    <div class="modal-header"><h3 class="section-title" x-text="mutasiAksi === 'setor' ? 'Setor ke Tabungan' : 'Tarik dari Tabungan'"></h3></div>
                    <div class="modal-body space-y-3">
                        <input type="hidden" name="aksi" :value="mutasiAksi">
                        <div>
                            <label class="input-label" x-text="mutasiAksi === 'setor' ? 'Dari (sumber)' : 'Ke (tujuan)'"></label>
                            <select name="akun_rekening_id" x-model="mutasiRekeningId" required class="input-field">
                                @foreach($akunRekeningOptions as $a)
                                    <option value="{{ $a->id }}">{{ $a->kode }}  {{ $a->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="input-label" x-text="mutasiAksi === 'setor' ? 'Ke (tabungan/investasi)' : 'Dari (tabungan/investasi)'"></label>
                            <select name="akun_tabungan_id" x-model="mutasiAkunId" required class="input-field">
                                @foreach($tabunganAkuns as $row)
                                    <option value="{{ $row->akun_id }}">{{ $row->akun->kode }}  {{ $row->akun->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="input-label">Tanggal</label>
                            <input type="date" name="date" value="{{ now()->toDateString() }}" required class="input-field">
                        </div>
                        <div>
                            <label class="input-label">Nominal (Rp)</label>
                            <input type="number" name="amount" min="0.01" step="0.01" required class="input-field">
                        </div>
                        <div>
                            <label class="input-label">Keterangan (opsional)</label>
                            <input type="text" name="description" class="input-field">
                        </div>
                        <p class="text-xs" style="color:#9E9790;">Jurnal debit/kredit dibuat otomatis dari pasangan akun di atas.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" @click="showMutasiModal=false" class="btn-secondary">Batal</button>
                        <button type="submit" class="btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
