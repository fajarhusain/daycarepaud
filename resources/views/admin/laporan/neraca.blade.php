<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background: #1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h2 class="font-bold text-xl" style="color: #2C2C2C;">Neraca</h2>
        </div>
    </x-slot>

    <div class="app-page-shell">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('admin.laporan-keuangan.index') }}" class="btn-secondary text-sm">← Laporan Keuangan</a>
            <div class="flex items-center gap-2">
                <x-export-excel route="admin.laporan-keuangan.neraca.export" class="text-sm" />
                <a href="{{ route('admin.laporan-keuangan.neraca.pdf', request()->only(['tipe', 'bulan', 'tahun'])) }}" class="btn-secondary text-sm">Export PDF</a>
            </div>
        </div>
        <x-laporan-periode-filter :action="route('admin.laporan-keuangan.neraca')" :periode="$periode" />

        <div class="card overflow-hidden mb-6">
            <div class="px-6 py-4 text-center border-b" style="border-color:rgba(0,0,0,0.06);">
                <p class="font-bold text-lg" style="color:#2C2C2C;">{{ auth()->user()->sekolah->name ?? 'Sekolah' }}</p>
                <p class="section-title text-base">Laporan Posisi Keuangan (Neraca)</p>
                <p class="section-subtitle">Per {{ \Carbon\Carbon::parse($periode['neracaSampai'])->translatedFormat('d F Y') }}  {{ $periode['label'] }}</p>
            </div>

            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="w-8" style="background:#1A6B6B; color:white;">No</th>
                            <th style="background:#1A6B6B; color:white;">Keterangan</th>
                            <th class="text-right w-40" style="background:#1A6B6B; color:white;">Jumlah (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="3" class="font-bold text-xs uppercase py-2" style="background:#D0E8E8; color:#1A6B6B;">Aset</td></tr>
                        @php $no = 1; @endphp
                        @include('admin.laporan._grup-akun', ['grup' => $grupAset, 'warna' => '#1A6B6B'])
                        <tr>
                            <td></td>
                            <td class="font-bold text-sm">Total Aset</td>
                            <td class="text-right font-bold text-sm" style="border-top:2px solid #1A6B6B; color:#1A6B6B;">Rp {{ number_format($totalAset, 0, ',', '.') }}</td>
                        </tr>

                        <tr><td colspan="3" class="font-bold text-xs uppercase py-2" style="background:#FAD7D2; color:#C0392B;">Liabilitas</td></tr>
                        @php $no = 1; @endphp
                        @include('admin.laporan._grup-akun', ['grup' => $grupLiabilitas, 'warna' => '#C0392B'])
                        <tr>
                            <td></td>
                            <td class="font-bold text-sm">Total Liabilitas</td>
                            <td class="text-right font-bold text-sm" style="border-top:2px solid #C0392B; color:#C0392B;">Rp {{ number_format($totalLiabilitas, 0, ',', '.') }}</td>
                        </tr>

                        <tr><td colspan="3" class="font-bold text-xs uppercase py-2" style="background:#E0D6C8; color:#6B5B3A;">Ekuitas</td></tr>
                        @php $no = 1; @endphp
                        @include('admin.laporan._grup-akun', ['grup' => $grupEkuitas, 'warna' => '#6B5B3A'])
                        <tr>
                            <td></td>
                            <td class="font-bold text-sm">Surplus (defisit) tahun berjalan</td>
                            <td class="text-right font-semibold text-sm" style="color:#6B5B3A;">Rp {{ number_format($surplusBerjalan, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td class="font-bold text-sm">Total Ekuitas + Surplus</td>
                            <td class="text-right font-bold text-sm" style="border-top:2px solid #6B5B3A; color:#6B5B3A;">Rp {{ number_format($totalEkuitas + $surplusBerjalan, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr style="background:#F5F2ED;">
                            <td></td>
                            <td class="font-bold text-sm">Total Liabilitas + Ekuitas + Surplus</td>
                            <td class="text-right font-bold text-sm" style="border-top:3px double #2C2C2C; color:#2C2C2C;">Rp {{ number_format($totalPasiva, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-xs py-2 text-center" style="color: {{ abs($totalAset - $totalPasiva) < 0.01 ? '#1A6B6B' : '#C0392B' }};">
                                @if(abs($totalAset - $totalPasiva) < 0.01)
                                    Aset = Liabilitas + Ekuitas (seimbang)
                                @else
                                    Selisih: Rp {{ number_format(abs($totalAset - $totalPasiva), 0, ',', '.') }}
                                @endif
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
