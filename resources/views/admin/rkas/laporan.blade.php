<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background:#1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <h2 class="font-bold text-xl" style="color:#2C2C2C;">Laporan RKAS</h2>
        </div>
    </x-slot>

    <div class="app-page-shell">
        @if(session('success'))<div class="alert-success mb-5">{{ session('success') }}</div>@endif

        <div class="card overflow-hidden mb-5">
            <div class="px-4 md:px-6 py-4 border-b" style="border-color:rgba(0,0,0,0.06);">
                <h3 class="section-title">Filter Laporan</h3>
                <p class="section-subtitle">Anggaran vs realisasi per sumber dana untuk periode yang dipilih.</p>
            </div>

            <form method="GET" data-tour="admin-rkas-laporan-filter" class="card-pad toolbar-form border-b w-full" style="border-color:rgba(0,0,0,0.06);">
                <div class="toolbar-field md:min-w-[10rem] md:max-w-[14rem]">
                    <label class="input-label">Tahun Ajaran</label>
                    <select name="tahun_ajaran" class="input-field w-full">
                        @foreach($tahunOptions as $ta)
                            <option value="{{ $ta }}" @selected($tahunAjaran === $ta)>{{ $ta }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="toolbar-field md:min-w-[9rem] md:max-w-[11rem]">
                    <label class="input-label">Semester</label>
                    <select name="semester" class="input-field w-full">
                        <option value="1" @selected($semester === 1)>Semester 1</option>
                        <option value="2" @selected($semester === 2)>Semester 2</option>
                    </select>
                </div>
                <div class="toolbar-actions">
                    <x-toolbar.filter-icon label="Tampilkan" variant="secondary" />
                    <x-filter-reset :href="route('admin.rkas.laporan')" compact />
                    <a href="{{ route('admin.rkas.index', ['tahun_ajaran' => $tahunAjaran]) }}"
                        class="btn-secondary toolbar-icon shrink-0 inline-flex items-center justify-center !p-0 h-9 w-9 md:h-11 md:w-11"
                        title="Kembali ke RKAS" aria-label="Kembali ke RKAS">
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    </a>
                    @if($rka)
                        <x-export-excel route="admin.rkas.laporan.export" :icon-only="true" />
                        <a href="{{ route('admin.rkas.export-pdf', ['tahun_ajaran' => $tahunAjaran, 'semester' => $semester]) }}"
                            class="btn-secondary toolbar-icon shrink-0 inline-flex items-center justify-center !p-0 h-9 w-9 md:h-11 md:w-11"
                            title="Export PDF" aria-label="Export PDF">
                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        @if(!$rka)
            <div class="card p-8 text-center text-sm" style="color:#9E9790;">
                RKAS belum dibuat untuk periode ini.
                <a href="{{ route('admin.rkas.index', ['tahun_ajaran' => $tahunAjaran]) }}" class="underline font-semibold" style="color:#1A6B6B;">Buat RKAS</a>
            </div>
        @else
            @if($health)
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
                    <div class="stat-card"><p class="text-xs uppercase" style="color:#9E9790;">Total Cashflow</p><p class="text-xl font-bold tabular-nums">{{ $health['total_cashflow'] }}</p></div>
                    <div class="stat-card"><p class="text-xs uppercase" style="color:#9E9790;">Belum Dialokasi</p><p class="text-xl font-bold tabular-nums" style="color:{{ $health['unallocated_pct'] > 0 ? '#C0392B' : '#1A6B6B' }};">{{ $health['unallocated_count'] }} ({{ $health['unallocated_pct'] }}%)</p></div>
                    <div class="stat-card"><p class="text-xs uppercase" style="color:#9E9790;">Sync Terakhir</p><p class="text-sm font-bold">{{ $health['synced_at']?->format('d M Y H:i') ?? '—' }}</p></div>
                </div>
            @endif

            <div class="card overflow-hidden">
                <div class="px-4 md:px-6 py-4 border-b" style="border-color:rgba(0,0,0,0.06);">
                    <h3 class="section-title">{{ \App\Support\TahunAjaran::label($rka->tahun_ajaran, $rka->semester) }}</h3>
                    <p class="section-subtitle">Anggaran vs realisasi per sumber dana</p>
                </div>
                <div class="table-responsive">
                    <table class="data-table text-xs" data-tour="admin-rkas-laporan-table">
                        <thead>
                            <tr>
                                <th rowspan="2">Kode</th>
                                <th rowspan="2">Uraian</th>
                                @foreach($sumberDanas as $sd)
                                    <th colspan="3" class="text-center border-l">{{ $sd->kode }}</th>
                                @endforeach
                                <th colspan="3" class="text-center border-l">Total</th>
                            </tr>
                            <tr>
                                @foreach($sumberDanas as $sd)
                                    <th class="text-right border-l">Anggaran</th>
                                    <th class="text-right">Realisasi</th>
                                    <th class="text-right">%</th>
                                @endforeach
                                <th class="text-right border-l">Anggaran</th>
                                <th class="text-right">Realisasi</th>
                                <th class="text-right">Sisa</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $row)
                                <tr>
                                    <td class="font-mono">{{ $row['akun']->kode }}</td>
                                    <td class="max-w-[200px] truncate" title="{{ $row['akun']->uraian ?? $row['akun']->nama }}">{{ Str::limit($row['akun']->uraian ?? $row['akun']->nama, 40) }}</td>
                                    @foreach($sumberDanas as $sd)
                                        @php $c = $row['cells'][$sd->id]; @endphp
                                        <td class="text-right border-l tabular-nums">{{ number_format($c['anggaran'], 0, ',', '.') }}</td>
                                        <td class="text-right tabular-nums" style="color:{{ $c['realisasi'] > $c['anggaran'] && $c['anggaran'] > 0 ? '#C0392B' : '#2C2C2C' }};">{{ number_format($c['realisasi'], 0, ',', '.') }}</td>
                                        <td class="text-right tabular-nums">{{ $c['persen'] !== null ? $c['persen'].'%' : '—' }}</td>
                                    @endforeach
                                    <td class="text-right border-l font-semibold tabular-nums">{{ number_format($row['total_anggaran'], 0, ',', '.') }}</td>
                                    <td class="text-right font-semibold tabular-nums">{{ number_format($row['total_realisasi'], 0, ',', '.') }}</td>
                                    <td class="text-right font-semibold tabular-nums" style="color:{{ $row['total_sisa'] < 0 ? '#C0392B' : '#1A6B6B' }};">{{ number_format($row['total_sisa'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-4 md:px-6 py-4 border-t" style="border-color:rgba(0,0,0,0.06);">
                    <x-per-page-selector :paginator="$rows" />
                    {{ $rows->withQueryString()->links() }}
                </div>
            </div>

            <form action="{{ route('admin.rkas.update-realisasi', $rka) }}" method="POST" class="card mt-6 overflow-hidden">
                @csrf
                <div class="px-4 md:px-6 py-4 border-b" style="border-color:rgba(0,0,0,0.06);">
                    <h3 class="section-title">Koreksi Manual Realisasi</h3>
                </div>
                <div class="p-4 space-y-3 max-h-96 overflow-y-auto">
                    @foreach($rka->lines as $line)
                        @foreach($line->realisasis as $real)
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-2 items-center text-sm border-b pb-2" style="border-color:rgba(0,0,0,0.06);">
                                <div class="md:col-span-2">{{ $line->akun->kode }} — {{ $real->sumberDana->kode }} <span class="text-xs" style="color:#9E9790;">(otomatis: {{ number_format($real->nominal_otomatis, 0, ',', '.') }})</span></div>
                                <input type="number" name="realisasi[{{ $real->id }}][nominal_manual]" value="{{ $real->nominal_manual }}" min="0" step="1000" placeholder="Override manual" class="input-field text-sm">
                                <input type="text" name="realisasi[{{ $real->id }}][catatan]" value="{{ $real->catatan }}" placeholder="Catatan koreksi" class="input-field text-sm">
                            </div>
                        @endforeach
                    @endforeach
                </div>
                <div class="px-4 md:px-6 py-4 border-t flex justify-end" style="border-color:rgba(0,0,0,0.06);">
                    <button type="submit" class="btn-primary text-sm">Simpan Koreksi</button>
                </div>
            </form>
        @endif
    </div>
</x-app-layout>
