<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background: #1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <h2 class="font-bold text-xl" style="color: #2C2C2C;">Kehadiran</h2>
        </div>
    </x-slot>
    <div class="app-page-shell">
        <div class="card overflow-hidden mb-6">
            <div class="card-pad border-b" style="border-color: rgba(0,0,0,0.06); background: #FAF9F6;">
                <div class="min-w-0 mb-3 md:mb-4">
                    <h3 class="section-title mb-0">Rekap Kehadiran</h3>
                    <p class="section-subtitle mt-1">Pantau kehadiran anak Anda per bulan atau per minggu.</p>
                </div>
                <form data-tour="ortu-presensi-period-filter" method="GET" action="{{ route('orangtua.presensi.index') }}" class="toolbar-form w-full">
                    @if($anaks->count() > 1)
                        <div class="toolbar-field md:min-w-[10rem] md:max-w-[14rem]">
                            <label class="input-label">Anak</label>
                            <select name="anak_id" class="input-field w-full" onchange="this.form.submit()">
                                <option value="">Semua Anak</option>
                                @foreach($anaks as $anak)
                                    <option value="{{ $anak->id }}" @selected($selectedAnakId === $anak->id)>{{ $anak->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="toolbar-field md:min-w-[8rem] md:max-w-[10rem]">
                        <label class="input-label">Rentang</label>
                        <select name="periode" class="input-field w-full" onchange="this.form.submit()">
                            <option value="bulan" @selected(($filter['periode'] ?? 'bulan') === 'bulan')>Per Bulan</option>
                            <option value="minggu" @selected(($filter['periode'] ?? '') === 'minggu')>Per Minggu</option>
                        </select>
                    </div>
                    @if(($filter['periode'] ?? 'bulan') === 'bulan')
                        <div class="toolbar-field md:min-w-[9rem] md:max-w-[11rem]">
                            <label class="input-label">Bulan</label>
                            <select name="month" class="input-field w-full" onchange="this.form.submit()">
                                @foreach(range(1, 12) as $m)
                                    <option value="{{ $m }}" @selected((int) ($filter['bulan'] ?? now()->month) === $m)>
                                        {{ \Carbon\Carbon::createFromDate(null, $m, 1)->translatedFormat('F') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="toolbar-field md:min-w-[6rem] md:max-w-[8rem]">
                            <label class="input-label">Tahun</label>
                            <select name="year" class="input-field w-full" onchange="this.form.submit()">
                                @foreach(range(now()->year - 2, now()->year + 1) as $y)
                                    <option value="{{ $y }}" @selected((int) ($filter['tahun'] ?? now()->year) === $y)>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <div class="toolbar-field md:min-w-[10rem] md:max-w-[12rem]">
                            <label class="input-label">Minggu</label>
                            <input type="week" name="week" value="{{ $filter['minggu'] ?? '' }}" class="input-field w-full">
                        </div>
                    @endif
                    <div class="toolbar-actions">
                        <x-toolbar.filter-icon label="Terapkan" />
                        <x-filter-reset :href="route('orangtua.presensi.index')" compact />
                    </div>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Summary Sidebar --}}
            <div class="lg:col-span-1 space-y-4" data-tour="ortu-presensi-summary-cards">
                <h3 class="section-title text-base">Ringkasan {{ $filter['label'] }}</h3>
                @foreach($anaks as $anak)
                    @php
                        $stats = $presensis->where('anak_id', $anak->id);
                        $hadir = $stats->where('hadir', true)->count();
                        $tidakHadir = $stats->where('hadir', false)->count();
                    @endphp
                    <div class="card card-body-pad bg-white shadow-sm border-l-4 border-l-[#1A6B6B]">
                        <div class="flex items-center gap-4 mb-4">
                            <x-foto-profil :path="$anak->photo" :name="$anak->name" size="md" />
                            <div>
                                <h4 class="font-bold text-gray-900 leading-tight">{{ $anak->name }}</h4>
                                <p class="text-xs text-gray-500">{{ $anak->kelas->name ?? 'Tanpa Kelas' }}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-gray-50 rounded-xl p-3 text-center">
                                <p class="text-[10px] font-bold uppercase tracking-tighter text-gray-400 mb-1">Hadir</p>
                                <p class="text-xl font-bold text-[#1A6B6B]">{{ $hadir }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-3 text-center">
                                <p class="text-[10px] font-bold uppercase tracking-tighter text-gray-400 mb-1">Izin/Sakit/Alpa</p>
                                <p class="text-xl font-bold text-amber-600">{{ $tidakHadir }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Detail List --}}
            <div class="lg:col-span-2">
                <div class="card overflow-hidden">
                    <div class="card-pad border-b flex flex-wrap items-center justify-between gap-2" style="border-color:rgba(0,0,0,0.06);">
                        <div>
                            <h3 class="section-title mb-0">Riwayat Harian</h3>
                            <p class="section-subtitle mt-1 mb-0">{{ $presensis->count() }} record · {{ $filter['label'] }}</p>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table" data-tour="ortu-presensi-table">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    @if($anaks->count() > 1)
                                        <th>Anak</th>
                                    @endif
                                    <th>Status</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($presensis as $p)
                                    <tr>
                                        <td class="whitespace-nowrap">
                                            <p class="text-sm font-semibold" style="color:#2C2C2C;">{{ \Carbon\Carbon::parse($p->tanggal)->translatedFormat('d M Y') }}</p>
                                            <p class="text-xs" style="color:#9E9790;">{{ \Carbon\Carbon::parse($p->tanggal)->translatedFormat('l') }}</p>
                                        </td>
                                        @if($anaks->count() > 1)
                                            <td class="font-medium" style="color:#2C2C2C;">{{ $p->anak->name ?? '-' }}</td>
                                        @endif
                                        <td>
                                            @if($p->hadir)
                                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-50 text-green-700 border border-green-100">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                                                    Hadir
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-100">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                                    {{ \App\Models\Presensi::labelForStatus($p->status) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-sm" style="color:#6B6560;">
                                            {{ $p->keterangan ?: '-' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $anaks->count() > 1 ? 4 : 3 }}" class="py-12 text-center">
                                            <div class="inline-flex items-center justify-center h-12 w-12 rounded-2xl bg-gray-50 mb-3 text-gray-300">
                                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            </div>
                                            <p class="text-sm font-medium text-gray-400">Tidak ada riwayat kehadiran dalam periode ini.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
