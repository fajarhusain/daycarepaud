<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background: #1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
            </div>
            <h2 class="font-bold text-xl" style="color: #2C2C2C;">Rekap Presensi</h2>
        </div>
    </x-slot>

    <div class="app-page-shell">
        @if(session('success'))<div class="alert-success mb-5"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert-danger mb-5"><ul class="list-disc pl-5 text-sm">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul></div>@endif

        {{-- TABS --}}
        <div class="flex gap-1 mb-4 border-b" style="border-color: rgba(0,0,0,0.08);">
            <a href="{{ route('admin.presensi.index') }}"
               class="px-5 py-2.5 text-sm font-semibold rounded-t-lg border-b-2 transition-colors"
               style="border-color: transparent; color: #6B6560;">
                Kehadiran Harian
            </a>
            <a href="{{ route('admin.presensi.rekap') }}"
               class="px-5 py-2.5 text-sm font-semibold rounded-t-lg border-b-2 transition-colors"
               style="border-color: #1A6B6B; color: #1A6B6B; background: #E8F5F5;">
                Rekap Periode
            </a>
        </div>

        <div class="card overflow-hidden">
            <div class="px-4 md:px-6 py-4 md:py-5 border-b space-y-4" style="border-color: rgba(0,0,0,0.06); background:#FAF6F0;" data-tour="presensi-rekap-filter">
                <div class="min-w-0">
                    <h3 class="text-lg md:text-xl font-bold" style="color:#2C2C2C;">Rekap Kehadiran per Periode</h3>
                    <p class="text-xs md:text-sm font-medium mt-0.5" style="color:#9E9790;">Jumlah hari hadir setiap siswa dalam periode yang dipilih.</p>
                </div>
                <form method="get" action="{{ route('admin.presensi.rekap') }}" class="filter-toolbar-inline w-full">
                    <div class="filter-toolbar-field min-w-[9rem]">
                        <label class="text-[11px] font-bold uppercase tracking-wider mb-1.5 block" style="color:#1A6B6B;">Kelas</label>
                        <select name="kelas_id" class="input-field w-full text-xs font-bold h-9 md:h-10" onchange="this.form.submit()" style="background:white;">
                            <option value="">Semua Kelas</option>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id }}" @selected(request('kelas_id') == $k->id)>{{ $k->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-toolbar-field !flex-none basis-auto min-w-[8.5rem]">
                        <label class="text-[11px] font-bold uppercase tracking-wider mb-1.5 block" style="color:#1A6B6B;">Periode</label>
                        <select name="periode" class="input-field w-full text-xs font-bold h-9 md:h-10" onchange="this.form.submit()" style="background:white;">
                            <option value="bulan" @selected(($presensiFilter['periode'] ?? 'bulan') === 'bulan')>Bulanan</option>
                            <option value="minggu" @selected(($presensiFilter['periode'] ?? '') === 'minggu')>Mingguan</option>
                        </select>
                    </div>
                    @if(($presensiFilter['periode'] ?? 'bulan') === 'bulan')
                        <div class="filter-toolbar-field !flex-none basis-auto min-w-[8.5rem]">
                            <label class="text-[11px] font-bold uppercase tracking-wider mb-1.5 block" style="color:#1A6B6B;">Bulan</label>
                            <select name="month" class="input-field w-full text-xs font-bold h-9 md:h-10" onchange="this.form.submit()" style="background:white;">
                                @foreach(range(1, 12) as $m)
                                    <option value="{{ $m }}" @selected((int)($presensiFilter['bulan'] ?? now()->month) === $m)>{{ \Carbon\Carbon::createFromDate((int)($presensiFilter['tahun'] ?? now()->year), $m, 1)->translatedFormat('F') }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-toolbar-field !flex-none basis-auto min-w-[6.5rem] sm:max-w-[7.5rem]">
                            <label class="text-[11px] font-bold uppercase tracking-wider mb-1.5 block" style="color:#1A6B6B;">Tahun</label>
                            <select name="year" class="input-field w-full text-xs font-bold h-9 md:h-10" onchange="this.form.submit()" style="background:white;">
                                @foreach(range(now()->year - 2, now()->year + 1) as $y)
                                    <option value="{{ $y }}" @selected((int)($presensiFilter['tahun'] ?? now()->year) === $y)>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        @php
                            $weekFrom = \Carbon\Carbon::parse($presensiFilter['from'] ?? now())->translatedFormat('d M Y');
                            $weekTo = \Carbon\Carbon::parse($presensiFilter['to'] ?? now())->translatedFormat('d M Y');
                        @endphp
                        <div class="filter-toolbar-field !flex-none basis-auto w-full sm:min-w-[12rem] sm:max-w-[15rem]"
                            x-data="{
                                range: @js($weekFrom.' – '.$weekTo),
                                formatId(d) {
                                    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }).replace(/\./g, '');
                                },
                                updateRange(value) {
                                    if (!value) return;
                                    const d = new Date(value + 'T12:00:00');
                                    const day = d.getDay();
                                    const toMonday = day === 0 ? -6 : 1 - day;
                                    const mon = new Date(d);
                                    mon.setDate(d.getDate() + toMonday);
                                    const sun = new Date(mon);
                                    sun.setDate(mon.getDate() + 6);
                                    this.range = this.formatId(mon) + ' – ' + this.formatId(sun);
                                }
                            }">
                            <label class="text-[11px] font-bold uppercase tracking-wider mb-1.5 block" style="color:#1A6B6B;">Pilih tanggal</label>
                            <input type="date" name="week_date" value="{{ $presensiFilter['week_date'] ?? now()->toDateString() }}"
                                class="input-field w-full text-xs font-bold h-9 md:h-10 border-black/10" required
                                @input="updateRange($event.target.value)"
                                onchange="this.form.submit()" style="background:white;">
                            
                        </div>
                    @endif
                    <div class="filter-toolbar-actions w-full sm:w-auto">
                        <x-filter-reset :href="route('admin.presensi.rekap')" compact />
                        <x-export-excel route="admin.presensi.rekap.export" :icon-only="true" />
                    </div>
                </form>
            </div>
            <div class="px-4 md:px-6 py-3 text-xs sm:text-sm flex flex-wrap items-center gap-x-4 gap-y-2" style="background: #FAF6F0; color: #6B6560;">
                <span class="inline-flex items-center gap-1.5" title="Periode">
                    <svg class="h-4 w-4 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    <span class="hidden sm:inline">Periode:</span>
                    <strong style="color:#2C2C2C;">{{ $presensiFilter['label'] ?? '' }}</strong>
                </span>
                <span class="inline-flex items-center gap-1.5" title="Total siswa">
                    <span class="hidden sm:inline">Total siswa:</span>
                    <strong style="color:#2C2C2C;">{{ $anaks->count() }}</strong>
                </span>
                <span class="inline-flex items-center gap-1.5 sm:ml-auto" title="Total hadir akumulasi">
                    <svg class="h-4 w-4 shrink-0 text-teal-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    
                    <strong style="color:#1A6B6B;">{{ $hadirPeriode->sum() }}</strong>
                    <span class="hidden sm:inline">hari</span>
                </span>
            </div>
            <div class="overflow-x-auto" data-tour="presensi-rekap-table">
                <table class="data-table presensi-checklist-table">
                    <thead>
                        <tr>
                            <x-presensi.checklist-th label="Nama siswa" short="Siswa" icon="user" class="min-w-[8rem]" />
                            <x-presensi.checklist-th label="Kelas" icon="class" class="min-w-[6.25rem] w-[6.25rem] md:min-w-[7.5rem] md:w-auto" />
                            <x-presensi.checklist-th label="Hadir" icon="status" class="w-16 md:w-20 text-center [&>span]:justify-center" />
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($anaks as $anak)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <x-foto-profil :path="$anak->photo" :name="$anak->name" size="sm" />
                                        <span class="font-semibold" style="color:#2C2C2C;">{{ $anak->name }}</span>
                                        @if($anak->dob)<span class="text-[10px] font-bold text-[#1A6B6B]">({{ $anak->age }})</span>@endif
                                    </div>
                                </td>
                                <td class="whitespace-nowrap">
                                    @if($anak->kelas)<span class="badge badge-teal whitespace-nowrap">{{ $anak->kelas->name }}</span>
                                    @else<span class="text-xs italic" style="color:#9E9790;"></span>@endif
                                </td>
                                <td class="text-center md:text-left">
                                    <span class="font-bold text-sm tabular-nums" style="color:#1A6B6B;">{{ (int)($hadirPeriode[$anak->id] ?? 0) }}</span>
                                    <span class="text-xs hidden sm:inline" style="color:#9E9790;"> hari</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-10 text-center text-sm" style="color:#9E9790;">Belum ada data siswa.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t" style="border-color:rgba(0,0,0,0.06);">
                <x-per-page-selector :paginator="$anaks" />
                {{ $anaks->withQueryString()->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
