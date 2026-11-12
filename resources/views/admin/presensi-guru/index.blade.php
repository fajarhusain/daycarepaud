<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center shrink-0" style="background:#1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
            </div>
            <h2 class="font-bold text-xl" style="color:#2C2C2C;">Presensi Guru & Staf</h2>
        </div>
    </x-slot>

    <div class="app-page-shell">
        @if(session('success'))
            <div class="alert-success mb-5">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                {{ session('success') }}
            </div>
        @endif

        <div class="card overflow-hidden mb-6">
            <div class="px-4 md:px-6 py-4 border-b" style="border-color:rgba(0,0,0,0.06);">
                <h3 class="section-title">Presensi Harian</h3>
                <p class="section-subtitle">Catat kehadiran pengajar per tanggal — jam masuk/pulang mengikuti pengaturan sekolah.</p>
            </div>

            <form data-tour="admin-presensi-guru-date" method="get" action="{{ route('admin.presensi-guru.index') }}" class="card-pad toolbar-form border-b w-full" style="border-color:rgba(0,0,0,0.06);">
                @if(request('rekap_month'))
                    <input type="hidden" name="rekap_month" value="{{ request('rekap_month') }}">
                @endif
                @if(request('rekap_year'))
                    <input type="hidden" name="rekap_year" value="{{ request('rekap_year') }}">
                @endif
                <div class="toolbar-field md:min-w-[10rem] md:max-w-[12rem]">
                    <label class="input-label">Tanggal</label>
                    <input type="date" name="tanggal" value="{{ $tanggal }}" class="input-field w-full" onchange="this.form.submit()">
                </div>
                <div class="toolbar-field md:min-w-[12rem] md:flex-[2]">
                    <label class="input-label">Cari Pengajar</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="input-field w-full" placeholder="Nama atau NIP…">
                </div>
                <div class="toolbar-actions">
                    <x-toolbar.filter-icon label="Terapkan" variant="secondary" />
                    <x-filter-reset :href="route('admin.presensi-guru.index')" compact />
                    <x-export-excel route="admin.presensi-guru.export" :icon-only="true" />
                </div>
            </form>

            <div class="px-4 md:px-6 py-3 text-xs sm:text-sm flex flex-wrap items-center gap-x-4 gap-y-2 border-b" style="background:#FAF6F0; border-color:rgba(0,0,0,0.06); color:#6B6560;">
                <span class="inline-flex items-center gap-1.5 font-medium">
                    <svg class="h-4 w-4 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    <strong style="color:#2C2C2C;">{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d M Y') }}</strong>
                </span>
                @if(($shiftCount ?? 1) > 1)
                    <span class="inline-flex items-center gap-1.5">Jam kerja mengikuti <strong style="color:#1A6B6B;">shift</strong> masing-masing guru</span>
                @endif
                <span class="inline-flex items-center gap-1.5">
                    Hadir: <strong style="color:#1A6B6B;">{{ $hadirHariIni }}</strong> / {{ $totalPengajar }}
                </span>
                <span class="inline-flex items-center gap-1.5">
                    Sudah diisi: <strong style="color:#2C2C2C;">{{ $sudahDiisi }}</strong>
                </span>
            </div>

            <form action="{{ route('admin.presensi-guru.store') }}" method="POST">
                @csrf
                <input type="hidden" name="tanggal" value="{{ $tanggal }}">

                <div class="table-responsive">
                    <table class="data-table" data-tour="admin-presensi-guru-input-table">
                        <thead>
                            <tr>
                                <th>Nama Pengajar</th>
                                <th class="text-center w-44">Kehadiran</th>
                                <th class="text-center">Jam Masuk</th>
                                <th class="text-center">Jam Pulang</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pengajars as $pengajar)
                                @php $p = $presensis->get($pengajar->id); @endphp
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-3 min-w-0">
                                            <x-foto-profil :path="$pengajar->photo" :name="$pengajar->name" size="md" rounded="xl" class="border border-black/5 shrink-0" />
                                            <div class="min-w-0">
                                                <p class="font-semibold text-sm truncate" style="color:#2C2C2C;">{{ $pengajar->name }}</p>
                                                <p class="text-xs truncate" style="color:#9E9790;">{{ $pengajar->nip ?: 'NIP belum diatur' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="inline-flex items-center justify-center gap-2 flex-wrap">
                                            <label class="relative inline-flex cursor-pointer">
                                                <input type="radio" name="presensi[{{ $pengajar->id }}][hadir]" value="1" class="sr-only peer" @checked(!($p && $p->hadir == 0))>
                                                <span class="px-3 py-1 rounded-lg text-xs font-semibold border border-black/10 text-gray-500 peer-checked:bg-emerald-600 peer-checked:text-white peer-checked:border-emerald-700 transition">Hadir</span>
                                            </label>
                                            <label class="relative inline-flex cursor-pointer">
                                                <input type="radio" name="presensi[{{ $pengajar->id }}][hadir]" value="0" class="sr-only peer" @checked($p && $p->hadir == 0)>
                                                <span class="px-3 py-1 rounded-lg text-xs font-semibold border border-black/10 text-gray-500 peer-checked:bg-rose-600 peer-checked:text-white peer-checked:border-rose-700 transition">Tidak</span>
                                            </label>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <input type="time" name="presensi[{{ $pengajar->id }}][check_in]"
                                            value="{{ $p?->check_in_at?->format('H:i') }}"
                                            class="input-field py-1.5 text-xs w-28 text-center mx-auto">
                                        @if($p?->terlambat_masuk)
                                            <p class="text-[10px] font-semibold mt-1 text-red-600">Telat {{ $p->menit_telat_masuk }} mnt</p>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <input type="time" name="presensi[{{ $pengajar->id }}][check_out]"
                                            value="{{ $p?->check_out_at?->format('H:i') }}"
                                            class="input-field py-1.5 text-xs w-28 text-center mx-auto">
                                        @if($p?->telat_pulang)
                                            <p class="text-[10px] font-semibold mt-1 text-red-600">Telat {{ $p->menit_telat_pulang }} mnt</p>
                                        @endif
                                    </td>
                                    <td>
                                        <select name="presensi[{{ $pengajar->id }}][status]" class="input-field py-1.5 text-xs w-full max-w-[12rem]">
                                            <option value="">— Pilih status —</option>
                                            <option value="Sakit" @selected(($p?->status ?? '') === 'Sakit')>Sakit</option>
                                            <option value="Izin" @selected(($p?->status ?? '') === 'Izin')>Izin</option>
                                            <option value="Alpa" @selected(($p?->status ?? '') === 'Alpa')>Alpa</option>
                                        </select>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-sm" style="color:#9E9790;">Belum ada pengajar atau tidak ada hasil pencarian.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($pengajars->isNotEmpty())
                    <div class="px-4 md:px-6 py-4 border-t flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3" style="border-color:rgba(0,0,0,0.06);">
                        <div class="flex flex-wrap items-center gap-3">
                            <x-per-page-selector :paginator="$pengajars" />
                            {{ $pengajars->withQueryString()->links() }}
                        </div>
                        <button type="submit" class="btn-primary shrink-0">Simpan Presensi</button>
                    </div>
                @endif
            </form>
        </div>

        <div class="card overflow-hidden">
            <div class="px-4 md:px-6 py-4 border-b flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3" style="border-color:rgba(0,0,0,0.06);">
                <div>
                    <h3 class="section-title">Rekap Absensi</h3>
                    <p class="section-subtitle">{{ \Carbon\Carbon::create($rekapYear, $rekapMonth, 1)->translatedFormat('F Y') }}</p>
                </div>
            </div>

            <form method="get" action="{{ route('admin.presensi-guru.index') }}" class="card-pad toolbar-form border-b w-full" style="border-color:rgba(0,0,0,0.06);">
                <input type="hidden" name="tanggal" value="{{ $tanggal }}">
                @if(request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif
                <div class="toolbar-field md:min-w-[9rem] md:max-w-[11rem]">
                    <label class="input-label">Bulan</label>
                    <select name="rekap_month" class="input-field w-full" onchange="this.form.submit()">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" @selected((int) $rekapMonth === $m)>{{ \Carbon\Carbon::create(2024, $m, 1)->translatedFormat('F') }}</option>
                        @endfor
                    </select>
                </div>
                <div class="toolbar-field md:min-w-[6rem] md:max-w-[8rem]">
                    <label class="input-label">Tahun</label>
                    <select name="rekap_year" class="input-field w-full" onchange="this.form.submit()">
                        @php $currentYear = (int) date('Y'); @endphp
                        @for($y = $currentYear - 2; $y <= $currentYear + 1; $y++)
                            <option value="{{ $y }}" @selected((int) $rekapYear === $y)>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="toolbar-actions">
                    <x-filter-reset :href="route('admin.presensi-guru.index', ['tanggal' => $tanggal])" compact />
                </div>
            </form>

            <div class="table-responsive">
                <table class="data-table" data-tour="admin-presensi-guru-rekap-table">
                    <thead>
                        <tr>
                            <th>Nama Pengajar</th>
                            <th class="text-center">Hadir</th>
                            <th class="text-center">Sakit</th>
                            <th class="text-center">Izin</th>
                            <th class="text-center">Alpa</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pengajars as $pengajar)
                            @php $rekap = $rekapBulanan->get($pengajar->id) ?? ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpa' => 0]; @endphp
                            <tr>
                                <td>
                                    <div class="flex items-center gap-2 min-w-0">
                                        <x-foto-profil :path="$pengajar->photo" :name="$pengajar->name" size="sm" rounded="xl" class="border border-black/5 shrink-0" />
                                        <span class="font-medium text-sm truncate" style="color:#2C2C2C;">{{ $pengajar->name }}</span>
                                    </div>
                                </td>
                                <td class="text-center"><span class="badge badge-green tabular-nums">{{ $rekap['hadir'] }}</span></td>
                                <td class="text-center"><span class="badge badge-amber tabular-nums">{{ $rekap['sakit'] }}</span></td>
                                <td class="text-center"><span class="badge badge-teal tabular-nums">{{ $rekap['izin'] }}</span></td>
                                <td class="text-center"><span class="badge badge-rose tabular-nums">{{ $rekap['alpa'] }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-12 text-center text-sm" style="color:#9E9790;">Tidak ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 md:px-6 py-4 border-t" style="border-color:rgba(0,0,0,0.06);">
                <x-per-page-selector :paginator="$pengajars" />
                {{ $pengajars->withQueryString()->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
