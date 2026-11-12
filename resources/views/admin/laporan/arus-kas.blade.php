<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background: #1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <h2 class="font-bold text-xl" style="color: #2C2C2C;">Laporan Arus Kas (PSAK 2)</h2>
        </div>
    </x-slot>

    <div class="app-page-shell">
        <form method="GET" class="card p-4 flex flex-wrap gap-3 items-end mb-6">
            <select name="bulan" class="input-field w-36">
                @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                @endforeach
            </select>
            <select name="tahun" class="input-field w-24">
                @foreach(range(now()->year - 2, now()->year) as $y)
                    <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
            <x-toolbar.filter-icon label="Tampilkan" />
            <x-filter-reset :href="url()->current()" />
        </form>

        <div class="card overflow-hidden mb-6">
            <div class="px-6 py-4 text-center border-b" style="border-color:rgba(0,0,0,0.06);">
                <p class="font-bold text-lg" style="color:#2C2C2C;">{{ auth()->user()->sekolah->name ?? 'Sekolah' }}</p>
                <p class="section-title text-base">Laporan Arus Kas</p>
                <p class="section-subtitle">Periode: {{ \Carbon\Carbon::create()->month($bulan)->format('F Y') }}</p>
            </div>

            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="w-8">No</th>
                            <th>Keterangan</th>
                            <th class="text-right w-40">Jumlah (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="background:#F5F2ED;">
                            <td></td>
                            <td class="font-bold text-sm">Saldo Awal</td>
                            <td class="text-right font-bold text-sm" style="color:#2C2C2C;">Rp {{ number_format($saldoAwalVal, 0, ',', '.') }}</td>
                        </tr>

                        @foreach(['operasi','investasi','pendanaan','tanpa_kategori'] as $key)
                            @php $group = $items->get($key, collect()); @endphp
                            @if($group->isNotEmpty())
                                <tr>
                                    <td colspan="3" class="font-bold text-xs uppercase tracking-wider py-2" style="background:#D0E8E8; color:#1A6B6B;">
                                        {{ $labelKategori[$key] }}
                                    </td>
                                </tr>
                                @php $no = 1; @endphp
                                @foreach($group->groupBy('akun_id') as $akunCashflows)
                                    @php
                                        $akun = $akunCashflows->first()->akun;
                                        $totalIn = $akunCashflows->where('type','in')->sum('amount');
                                        $totalOut = $akunCashflows->where('type','out')->sum('amount');
                                        $net = $totalIn - $totalOut;
                                    @endphp
                                    <tr>
                                        <td class="text-sm" style="color:#9E9790;">{{ $no++ }}</td>
                                        <td class="text-sm" style="color:#2C2C2C;">{{ $akun->kode ?? '' }} - {{ $akun->nama ?? '-' }}</td>
                                        <td class="text-right text-sm font-semibold" style="color: {{ $net >= 0 ? '#1A6B6B' : '#C0392B' }};">
                                            {{ $net >= 0 ? '    ' : '  ( ' }}{{ number_format(abs($net), 0, ',', '.') }}{{ $net >= 0 ? '' : ' )' }}
                                        </td>
                                    </tr>
                                @endforeach
                                @php
                                    $katIn = $group->where('type','in')->sum('amount');
                                    $katOut = $group->where('type','out')->sum('amount');
                                    $katNet = $katIn - $katOut;
                                @endphp
                                <tr>
                                    <td></td>
                                    <td class="font-semibold text-sm" style="color:#2C2C2C;">Arus Kas Bersih {{ str_replace('Arus Kas dari ', '', $labelKategori[$key]) }}</td>
                                    <td class="text-right font-bold text-sm" style="color: {{ $katNet >= 0 ? '#1A6B6B' : '#C0392B' }};">
                                        Rp {{ number_format($katNet, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background:#F5F2ED;">
                            @php
                                $allIn = 0; $allOut = 0;
                                foreach ($items as $group) {
                                    $allIn += $group->where('type','in')->sum('amount');
                                    $allOut += $group->where('type','out')->sum('amount');
                                }
                                $saldoAkhir = $saldoAwalVal + $allIn - $allOut;
                            @endphp
                            <td></td>
                            <td class="font-bold text-sm">Saldo Akhir</td>
                            <td class="text-right font-bold text-sm" style="color:#1A6B6B;">Rp {{ number_format($saldoAkhir, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        @if($items->isEmpty())
            <div class="text-center py-8" style="color:#9E9790;">Tidak ada transaksi pada periode ini.</div>
        @endif
    </div>
</x-app-layout>
