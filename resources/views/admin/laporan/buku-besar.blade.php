<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background: #1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h2 class="font-bold text-xl" style="color: #2C2C2C;">Buku Besar</h2>
        </div>
    </x-slot>

    <div class="app-page-shell">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('admin.laporan-keuangan.index') }}" class="btn-secondary text-sm">← Laporan Keuangan</a>
            @if($gl)
                <div class="flex items-center gap-2">
                    <x-export-excel route="admin.laporan-keuangan.buku-besar.export" class="text-sm" />
                    <a href="{{ route('admin.laporan-keuangan.buku-besar.pdf', request()->only(['tipe', 'bulan', 'tahun', 'akun_id'])) }}" class="btn-secondary text-sm">Export PDF</a>
                </div>
            @endif
        </div>
        <x-laporan-periode-filter :action="route('admin.laporan-keuangan.buku-besar')" :periode="$periode">
                <div class="w-full min-w-0 sm:min-w-[12rem] sm:flex-1">
                    <label class="input-label">Akun</label>
                    <select name="akun_id" class="input-field w-full" required>
                        <option value=""> Pilih akun </option>
                        @foreach($akunOptions as $opt)
                            <option value="{{ $opt['id'] }}" @selected($akunId == $opt['id'])>{{ $opt['label'] }}</option>
                        @endforeach
                    </select>
                </div>
        </x-laporan-periode-filter>

        @if(!$gl)
            <div class="card p-8 text-center text-sm" style="color:#9E9790;">Pilih akun dan periode, lalu klik Tampilkan.</div>
        @else
            <div class="card overflow-hidden mb-6">
                <div class="px-6 py-4 text-center border-b" style="border-color:rgba(0,0,0,0.06);">
                    <p class="font-bold text-lg" style="color:#2C2C2C;">{{ auth()->user()->sekolah->name ?? 'Sekolah' }}</p>
                    <p class="section-title text-base">Buku Besar  {{ $gl['akun']->kode }} {{ $gl['akun']->nama }}</p>
                    <p class="section-subtitle">{{ $periode['label'] }}</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="data-table text-sm">
                        <thead>
                            <tr>
                                <th style="background:#1A6B6B; color:white;">Tanggal</th>
                                <th style="background:#1A6B6B; color:white;">No. Jurnal</th>
                                <th style="background:#1A6B6B; color:white;">Keterangan</th>
                                <th class="text-right" style="background:#1A6B6B; color:white;">Debit</th>
                                <th class="text-right" style="background:#1A6B6B; color:white;">Kredit</th>
                                <th class="text-right" style="background:#1A6B6B; color:white;">Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="background:#F5F2ED;">
                                <td colspan="3" class="font-semibold">Saldo awal</td>
                                <td></td>
                                <td></td>
                                <td class="text-right font-semibold">Rp {{ number_format($gl['saldoAwal'], 0, ',', '.') }}</td>
                            </tr>
                            @foreach($gl['mutasi'] as $row)
                                @php $j = $row['line']->jurnal; @endphp
                                <tr>
                                    <td>{{ $j?->tanggal?->format('d/m/Y') ?? '' }}</td>
                                    <td>{{ $j?->no_jurnal ?? '' }}</td>
                                    <td>{{ Str::limit($j?->deskripsi ?? '', 60) }}</td>
                                    <td class="text-right">{{ $row['debit'] > 0 ? number_format($row['debit'], 0, ',', '.') : '' }}</td>
                                    <td class="text-right">{{ $row['kredit'] > 0 ? number_format($row['kredit'], 0, ',', '.') : '' }}</td>
                                    <td class="text-right font-medium">Rp {{ number_format($row['saldo'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background:#F5F2ED;">
                                <td colspan="3" class="font-bold">Saldo akhir</td>
                                <td></td>
                                <td></td>
                                <td class="text-right font-bold">Rp {{ number_format($gl['saldoAkhir'], 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
