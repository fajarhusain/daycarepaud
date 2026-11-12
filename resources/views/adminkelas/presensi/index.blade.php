<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background: #1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
            </div>
            <h2 class="font-bold text-xl" style="color: #2C2C2C;">Presensi Harian Kelas</h2>
        </div>
    </x-slot>

    <div class="app-page-shell">
        @if(session('success'))
            <div class="alert-success mb-5">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="alert-danger mb-5">
                <ul class="list-disc pl-5 text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card overflow-hidden mb-6">
            <div class="px-4 md:px-6 py-4 md:py-5 border-b space-y-4" style="background:#FAF6F0; border-color: rgba(0,0,0,0.06);">
                <div class="min-w-0">
                    <h3 class="text-lg md:text-xl font-bold" style="color:#2C2C2C;">Filter Kehadiran</h3>
                    <p class="text-xs md:text-sm font-medium mt-0.5" style="color:#9E9790;">Pilih kelas dan tanggal untuk mengelola checklist siswa</p>
                </div>
                <form data-tour="ak-presensi-filter" method="get" action="{{ route('adminkelas.presensi.index') }}" class="filter-toolbar-inline w-full">
                    <div class="filter-toolbar-field">
                        <label class="text-[11px] font-bold uppercase tracking-wider mb-1.5 block" style="color:#1A6B6B;">Pilih Kelas</label>
                        <select name="filter_kelas_id" class="input-field w-full text-xs font-bold h-9 md:h-10 border-black/10 transition focus:border-teal-500" onchange="this.form.submit()" style="background:white;">
                            <option value="">Semua Siswa Terdaftar</option>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id }}" @selected($filterKelasId == $k->id)>{{ $k->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-toolbar-field !flex-none basis-auto w-full sm:min-w-[10.5rem]">
                        <label class="text-[11px] font-bold uppercase tracking-wider mb-1.5 block" style="color:#1A6B6B;">Pilih Tanggal</label>
                        <input type="date" name="tanggal" value="{{ $tanggal }}" class="input-field w-full text-xs font-bold h-9 md:h-10 border-black/10 transition focus:border-teal-500 @error('tanggal') border-red-500 @enderror" required onchange="this.form.submit()" style="background:white;">
                        @error('tanggal')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="filter-toolbar-actions w-full sm:w-auto">
                        <x-filter-reset :href="route('adminkelas.presensi.index')" compact />
                    </div>
                </form>
            </div>
            <div class="px-4 md:px-6 py-3 text-xs sm:text-sm flex flex-wrap items-center gap-x-4 gap-y-2" style="background: #FAF6F0; color: #6B6560;">
                <span class="inline-flex items-center gap-1.5 font-medium" title="Tanggal">
                    <svg class="h-4 w-4 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    <strong style="color:#2C2C2C;">{{ $tanggal }}</strong>
                </span>
                <span class="inline-flex items-center gap-1.5" title="Total siswa">
                    <span class="hidden sm:inline">Total siswa:</span>
                    <strong style="color:#2C2C2C;">{{ $anaks->count() }}</strong>
                </span>
                <span class="inline-flex items-center gap-1.5" title="Hadir">
                    <svg class="h-4 w-4 shrink-0 text-teal-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    
                    <strong data-presensi-hadir style="color:#1A6B6B;">{{ $hadirCount }}</strong>
                </span>
                <span class="inline-flex items-center gap-1.5" title="Tidak hadir">
                    <svg class="h-4 w-4 shrink-0 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    
                    <strong data-presensi-absen style="color:#C0392B;">{{ $anaks->count() - $hadirCount }}</strong>
                </span>
                <span class="inline-flex items-center gap-1.5 w-full sm:w-auto sm:ml-auto" title="Rekap bulan">
                    <span class="hidden lg:inline">Rekap:</span>
                    <strong style="color:#6B6560;">{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('F Y') }}</strong>
                </span>
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="px-6 py-4 border-b flex items-center justify-between" style="border-color: rgba(0,0,0,0.06);">
                <div>
                    <h3 class="section-title">Checklist kehadiran</h3>
                    <p class="section-subtitle">Status tersimpan otomatis begitu dipilih atau diubah.</p>
                </div>
            </div>

            @if($anaks->isEmpty())
                <div class="px-6 py-16 text-center text-sm" style="color:#9E9790;">Belum ada data siswa di kelas ini.</div>
            @else
                <form data-tour="ak-presensi-checklist" data-presensi-form method="post" action="{{ route('adminkelas.presensi.store') }}">
                    @csrf
                    <input type="hidden" name="tanggal" value="{{ $tanggal }}">
                    <input type="hidden" name="filter_kelas_id" value="{{ $filterKelasId }}">
                    <div class="overflow-x-auto">
                        <table class="data-table presensi-checklist-table">
                            <thead>
                                <tr>
                                    <x-presensi.checklist-th label="Nama siswa" short="Siswa" icon="user" class="min-w-[8rem]" />
                                    <x-presensi.checklist-th label="Status" icon="status" class="w-28 md:w-32" />
                                    <x-presensi.checklist-th label="Keterangan" short="Ket." icon="note" class="min-w-[6rem]" />
                                    <x-presensi.checklist-th label="Rekap bulan ini" short="Rekap" icon="rekap" class="w-20 md:w-24 text-center [&>span]:justify-center" />
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($anaks as $anak)
                                    @php
                                        $row = $presensiByAnak->get($anak->id);
                                        $currentStatus = $row?->status ?? ($row?->hadir ? 'hadir' : 'alpha');
                                    @endphp
                                    <tr class="hover:bg-teal-50/30 transition-colors">
                                        <td class="py-4">
                                            <div class="flex items-center gap-3">
                                                <x-foto-profil :path="$anak->photo" :name="$anak->name" size="sm" />
                                                <div class="min-w-0">
                                                    <span class="font-bold block text-sm sm:text-base" style="color:#2C2C2C;">{{ $anak->name }}</span>
                                                    @if($anak->dob)<span class="text-[10px] font-bold text-teal-600 uppercase tracking-tight">Umur: {{ $anak->age }}</span>@endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-4">
                                            <select name="presensi[{{ $anak->id }}][status]" data-presensi-status class="input-field py-2 text-xs w-full min-w-[7rem]">
                                                @foreach($statusLabels as $value => $label)
                                                    <option value="{{ $value }}" @selected($currentStatus === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="py-4">
                                            <input type="text" name="presensi[{{ $anak->id }}][keterangan]" data-presensi-note value="{{ $row?->keterangan }}"
                                                class="input-field py-2 text-xs w-full min-w-[10rem]" maxlength="500"
                                                placeholder="Catatan opsional">
                                        </td>
                                        <td class="py-4 text-center md:text-left">
                                            <div class="flex flex-col items-center md:items-start">
                                                <span data-presensi-bulan class="font-black text-base sm:text-lg tabular-nums leading-none" style="color:#1A6B6B;">{{ (int)($hadirBulanan[$anak->id] ?? 0) }}</span>
                                                <span class="text-[10px] font-bold uppercase text-gray-400 tracking-wide mt-1 hidden sm:inline">Hari hadir</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>
                <x-presensi-autosave />
            @endif
        </div>
    </div>
</x-app-layout>
