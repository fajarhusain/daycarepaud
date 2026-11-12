<x-app-layout>
    <div class="app-page-shell dashboard-stack">

        <!-- ═══ LEMBAGA DASHBOARD ═══ -->
        @hasrole('Lembaga')
        @if(session('warning'))
            <div class="alert-danger mb-5">{{ session('warning') }}</div>
        @endif
        @if(!session('active_sekolah_id') && empty($activeSekolah))
            <div class="card p-4 sm:p-5 mb-5 sm:mb-6 border" style="border-color:#F0B84233; background:#FEF9EC;">
                <p class="text-sm font-semibold" style="color:#2C2C2C;">Pilih cabang sekolah aktif</p>
                <p class="text-xs mt-1 mb-2 sm:mb-3" style="color:#6B6560;">Gunakan dropdown <strong>Cabang aktif</strong> di header untuk mengakses menu operasional sekolah (siswa, kurikulum, keuangan, dll).</p>
            </div>
        @endif
        <div class="dashboard-kpi-grid--3" data-tour="dashboard-stats">
            <x-dashboard.kpi label="Total Cabang" :value="$totalSekolah ?? 0" :href="route('lembaga.sekolah.index')" link-label="Kelola" data-tour="dashboard-welcome"
                icon="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            <x-dashboard.kpi label="Admin Sekolah" :value="$totalAdmin ?? 0" :href="route('lembaga.admin-sekolah.index')" link-label="Kelola"
                icon="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
            <x-dashboard.kpi label="Total Masukan" :value="$totalKritikSaran ?? 0" :href="route('lembaga.kritik-saran.index')" link-label="Lihat" class="col-span-2 sm:col-span-1"
                icon="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
            <x-dashboard.kpi label="Total Murid" :value="$totalMurid ?? 0"
                icon="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            <x-dashboard.kpi label="Total Guru" :value="$totalGuru ?? 0"
                icon="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            <x-dashboard.kpi label="Kegiatan Bulan Ini" :value="$kegiatanBulanIni ?? 0" class="col-span-2 sm:col-span-1"
                icon="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </div>

        @if(!empty($dashboardCharts))
            <x-dashboard-charts :charts="$dashboardCharts" />
        @endif

        <x-dashboard.panel title="Masukan Terbaru" :href="route('lembaga.kritik-saran.index')" link-label="Lihat semua" data-tour="dashboard-recent">
            <div class="divide-y divide-black/5">
                @forelse ($recentFeedback ?? [] as $f)
                    @if($f)
                    <div class="card-pad flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm leading-relaxed line-clamp-2" style="color: #6B6560;">"{{ data_get($f, 'message', '-') }}"</p>
                            <p class="text-xs mt-1" style="color: #9E9790;">{{ data_get($f, 'created_at') ? \Carbon\Carbon::parse(data_get($f, 'created_at'))->format('d M Y') : '-' }}</p>
                        </div>
                        <span class="badge badge-amber shrink-0">{{ data_get($f, 'status', 'Terkirim') }}</span>
                    </div>
                    @endif
                @empty
                    <div class="px-6 py-10 text-center text-sm" style="color: #9E9790;">Belum ada masukan terbaru.</div>
                @endforelse
            </div>
        </x-dashboard.panel>
        @endhasrole

        <!-- ═══ ADMIN SEKOLAH DASHBOARD ═══ -->
        @hasrole('Admin Sekolah')
            @include('dashboard.partials.admin-sekolah')
        @endhasrole

        <!-- ═══ ADMIN KELAS / WALI KELAS ═══ -->
        @hasrole('Wali Kelas')
        <x-dashboard.hero eyebrow="Wali Kelas" :title="'Kelola '.($kelasWaliCount ?? 0).' kelas wali'" description="Pantau siswa, presensi, kesehatan, dan evaluasi kelas dari satu tempat."
            icon="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
            <x-slot:actions>
                <x-icon-btn :href="route('adminkelas.anak.index')" label="Siswa" icon="{{ \App\Support\DashboardIcons::path('users') }}" variant="hero-ghost" />
                <x-icon-btn :href="route('adminkelas.presensi.index')" label="Presensi" icon="{{ \App\Support\DashboardIcons::path('check') }}" variant="hero-primary" />
                <x-icon-btn :href="route('adminkelas.monev.index')" label="Monev" icon="{{ \App\Support\DashboardIcons::path('chart') }}" variant="hero-ghost" />
            </x-slot:actions>
        </x-dashboard.hero>

        <div class="dashboard-kpi-grid" data-tour="dashboard-stats">
            <x-dashboard.kpi label="Siswa kelasku" :value="$kelasAnakCount ?? 0" icon="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            <x-dashboard.kpi label="Presensi hari ini" :value="$presensiHariIniCount ?? 0" icon="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            <x-dashboard.kpi label="Cek kesehatan" :value="$kesehatanHariIniCount ?? 0" icon="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            <x-dashboard.kpi label="Evaluasi hari ini" :value="$monevHariIniCount ?? 0" class="col-span-2 lg:col-span-1" icon="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </div>

        @if(!empty($dashboardCharts))
            <x-dashboard-charts :charts="$dashboardCharts" />
        @endif

        <div class="dashboard-split-main">
            <x-dashboard.panel class="xl:col-span-3" title="Ringkasan Kelas Wali" subtitle="Daftar kelas yang saat ini Anda pegang sebagai wali kelas." data-tour="dashboard-recent">
                <div class="divide-y divide-black/5">
                    @forelse(($waliKelasList ?? collect()) as $kelas)
                        <div class="card-pad flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-bold text-sm sm:text-base truncate" style="color:#2C2C2C;">{{ $kelas->name }}</p>
                                <p class="text-xs sm:text-sm" style="color:#9E9790;">{{ $kelas->anaks_count }} siswa terdaftar</p>
                            </div>
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 sm:px-3 sm:py-1 text-[11px] sm:text-xs font-bold shrink-0" style="background:#D0E8E8;color:#1A6B6B;">Kelas Wali</span>
                        </div>
                    @empty
                        <div class="px-6 py-8 text-center">
                            <p class="text-sm" style="color:#9E9790;">Belum ada kelas yang ditetapkan sebagai kelas wali.</p>
                        </div>
                    @endforelse
                </div>
            </x-dashboard.panel>

            <x-dashboard.panel class="xl:col-span-2" title="Akses cepat" subtitle="Masuk ke pekerjaan utama wali kelas lebih cepat.">
                <div class="p-3 sm:p-5 grid grid-cols-2 xl:grid-cols-1 gap-2.5 sm:gap-3" data-tour="dashboard-quick-links">
                    <x-dashboard.quick-link :href="route('adminkelas.anak.index')" title="Data siswa" description="Lihat siswa di kelas wali." icon="{{ \App\Support\DashboardIcons::path('users') }}" />
                    <x-dashboard.quick-link :href="route('adminkelas.presensi.index')" title="Presensi" description="Input dan cek kehadiran harian." icon="{{ \App\Support\DashboardIcons::path('check') }}" />
                    <x-dashboard.quick-link :href="route('adminkelas.kesehatan.index')" title="Kesehatan" description="Pantau cek fisik siswa." icon="{{ \App\Support\DashboardIcons::path('heart') }}" />
                    <x-dashboard.quick-link :href="route('adminkelas.monev.index')" title="Monev" description="Evaluasi berkala siswa." icon="{{ \App\Support\DashboardIcons::path('chart') }}" />
                    <x-dashboard.quick-link :href="route('pengajar.kegiatan.index')" title="Agenda belajar" description="Kelola jurnal harian." icon="{{ \App\Support\DashboardIcons::path('calendar') }}" />
                    <x-dashboard.quick-link :href="route('pengajar.pencapaian.index')" title="Pencapaian" description="Isi skor pencapaian." icon="{{ \App\Support\DashboardIcons::path('book') }}" />
                </div>
            </x-dashboard.panel>
        </div>
        @endhasrole

        <!-- ═══ PENGAJAR DASHBOARD ═══ -->
        @if(auth()->user()->hasRole('Pengajar') && ! auth()->user()->hasRole('Wali Kelas'))
        <x-dashboard.hero eyebrow="Guru" :title="'Mengampu '.($kelasAjarCount ?? 0).' kelas'" description="Kelola pembelajaran, jurnal, dan evaluasi siswa dari satu tempat."
            icon="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
            <x-slot:actions>
                <x-icon-btn :href="route('pengajar.kegiatan.index')" label="Agenda belajar" icon="{{ \App\Support\DashboardIcons::path('calendar') }}" variant="hero-primary" />
                <x-icon-btn :href="route('pengajar.pencapaian.index')" label="Pencapaian" icon="{{ \App\Support\DashboardIcons::path('book') }}" variant="hero-ghost" />
            </x-slot:actions>
        </x-dashboard.hero>

        <div class="dashboard-kpi-grid" data-tour="dashboard-stats">
            <x-dashboard.kpi :label="$dashboardAnakLabel ?? 'Siswa'" :value="$totalAnakSekolah ?? 0" icon="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            <x-dashboard.kpi label="Kelas diampu" :value="$kelasAjarCount ?? 0" icon="M3 7h18M6 7V5a2 2 0 012-2h8a2 2 0 012 2v2m-1 14H7a2 2 0 01-2-2V7h14v12a2 2 0 01-2 2z" />
            <x-dashboard.kpi label="Jurnal hari ini" :value="$kegiatanSayaHariIni ?? 0" icon="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            <x-dashboard.kpi label="Evaluasi" :value="$totalEvaluasiSaya ?? 0" class="col-span-2 lg:col-span-1" icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </div>

        @if(!empty($dashboardCharts))
            <x-dashboard-charts :charts="$dashboardCharts" />
        @endif

        <div class="dashboard-split-main">
            <x-dashboard.panel class="xl:col-span-3" title="Ringkasan kelas ajar" subtitle="Daftar kelas yang sedang Anda ampu saat ini." data-tour="dashboard-recent">
                <div class="divide-y divide-black/5">
                    @forelse(($pengajarKelasList ?? collect()) as $kelas)
                        <div class="card-pad flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-bold text-sm sm:text-base truncate" style="color:#2C2C2C;">{{ $kelas->name }}</p>
                                <p class="text-xs sm:text-sm" style="color:#9E9790;">{{ $kelas->anaks_count }} siswa terdaftar</p>
                            </div>
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 sm:px-3 sm:py-1 text-[11px] sm:text-xs font-bold shrink-0" style="background:#EFECE8;color:#6B6560;">Kelas Ajar</span>
                        </div>
                    @empty
                        <div class="px-6 py-8 text-center">
                            <p class="text-sm" style="color:#9E9790;">Belum ada kelas yang ditugaskan ke akun guru ini.</p>
                        </div>
                    @endforelse
                </div>
            </x-dashboard.panel>

            <x-dashboard.panel class="xl:col-span-2" title="Akses cepat" subtitle="Pekerjaan utama guru untuk hari ini.">
                <div class="p-3 sm:p-5 grid grid-cols-2 xl:grid-cols-1 gap-2.5 sm:gap-3" data-tour="dashboard-quick-links">
                    <x-dashboard.quick-link :href="route('pengajar.master-kegiatan-rutin.index')" title="Kegiatan rutin" description="Aktivitas harian kelas." icon="{{ \App\Support\DashboardIcons::path('calendar') }}" />
                    <x-dashboard.quick-link :href="route('pengajar.kegiatan.index')" title="Agenda belajar" description="Buat jurnal kegiatan." icon="{{ \App\Support\DashboardIcons::path('book') }}" />
                    <x-dashboard.quick-link :href="route('pengajar.matrikulasi.index')" title="Matrikulasi" description="Aspek dan indikator." icon="{{ \App\Support\DashboardIcons::path('grid') }}" />
                    <x-dashboard.quick-link :href="route('pengajar.pencapaian.index')" title="Pencapaian" description="Input evaluasi siswa." icon="{{ \App\Support\DashboardIcons::path('chart') }}" />
                </div>
            </x-dashboard.panel>
        </div>
        @endif

        <!-- ═══ ORANG TUA DASHBOARD ═══ -->
        @hasrole('Orang Tua')
        <div class="space-y-4 sm:space-y-6" x-data="{
            selectedActivity: null,
            showActivityModal: false,
            showImageModal: false,
            activeImage: null,
            activeMediaType: 'image',
            openMedia(url) {
                this.activeImage = url;
                this.activeMediaType = (typeof window.isVideoPath === 'function' && window.isVideoPath(url)) ? 'video' : 'image';
                this.showImageModal = true;
            },
            openActivity(k) {
                this.selectedActivity = k;
                this.showActivityModal = true;
            },
            selectedRutin: null,
            showRutinModal: false,
            openRutin(k) {
                this.selectedRutin = k;
                this.showRutinModal = true;
            }
        }">

            {{-- ═══ Welcome & Attendance Summary Card ═══ --}}
            <div class="relative rounded-2xl overflow-hidden text-white shadow-lg" data-tour="dashboard-welcome"
                 style="background: linear-gradient(135deg, #1A6B6B 0%, #155959 50%, #0f4040 100%);
                        box-shadow: 0 8px 32px rgba(26,107,107,0.30);">

                {{-- Decorative background blobs --}}
                <div class="absolute -right-10 -top-10 pointer-events-none opacity-[0.08]">
                    <svg class="h-52 w-52" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 01-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                    </svg>
                </div>
                <div class="absolute -left-6 -bottom-6 pointer-events-none opacity-[0.05]">
                    <svg class="h-40 w-40" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </div>

                {{-- 1. Welcome Section --}}
                <div class="relative px-4 py-4 sm:px-7 sm:py-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
                    <div class="flex items-center gap-4">
                        <div>
                            <p class="text-[10px] sm:text-[11px] font-semibold uppercase tracking-widest text-white/60 mb-0.5">Selamat Datang</p>
                            <h1 class="text-lg sm:text-2xl font-bold leading-tight text-white">Assalamu'alaikum, Bunda / Ayah</h1>
                            <p class="text-xs sm:text-sm text-white/70 mt-0.5 leading-relaxed">Pantau tumbuh kembang si kecil hari ini.</p>
                        </div>
                    </div>
                </div>

                {{-- 2. Attendance Overview Title & Filter --}}
                <div class="relative px-4 pt-3.5 pb-3 sm:px-5 sm:pt-5 sm:pb-4 border-t border-b border-white/10">
                    <div class="flex items-center justify-between mb-0">
                        <div class="flex items-center gap-2.5 sm:gap-3">
                            <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-lg sm:rounded-xl bg-white/15 flex items-center justify-center shrink-0 border border-white/20">
                                <svg class="h-4 w-4 sm:h-5 sm:w-5 text-white/95" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1 pt-0.5">
                                <p class="text-[10px] sm:text-[11px] font-semibold uppercase tracking-widest text-white/75 leading-tight">Ringkasan Kehadiran</p>
                                <p class="text-xs sm:text-sm font-medium text-white/95 mt-0.5">{{ \Carbon\Carbon::now()->locale('id')->translatedFormat('l, d F Y') }}</p>
                            </div>
                        </div>
                        <a href="{{ route('orangtua.presensi.index') }}" class="h-8 w-8 rounded-lg bg-white/10 flex items-center justify-center hover:bg-white/20 transition border border-white/10" title="Filter & Detail">
                            <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- 3. Children Attendance Stats List --}}
                <div class="relative px-3.5 py-3.5 sm:px-5 sm:py-5 md:px-6 md:py-6" data-tour="dashboard-stats">
                    @forelse($anaks ?? [] as $anak)
                        @php
                            $summary = $presensiSummaryPerAnak[$anak->id] ?? ['hadir' => 0, 'tidak_hadir' => 0, 'efektif' => 0];
                            $statusLabel = match ($anak->status ?? '') {
                                'approved' => null,
                                'pending' => 'Menunggu persetujuan',
                                'rejected' => 'Ditolak',
                                default => $anak->status ? ucfirst((string) $anak->status) : null,
                            };
                            $isPending = ($anak->status ?? '') !== 'approved';
                        @endphp
                        <div class="rounded-xl bg-white/10 border border-white/15 backdrop-blur-sm p-3 sm:p-4 mb-2.5 sm:mb-3 last:mb-0">
                            <div class="flex items-start sm:items-center gap-3">
                                <x-foto-profil :path="$anak->photo" :name="$anak->name" size="md" class="border border-white/25 shadow-sm shrink-0 sm:hidden" />
                                <x-foto-profil :path="$anak->photo" :name="$anak->name" size="lg" class="border border-white/25 shadow-sm shrink-0 hidden sm:block" />
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <p class="font-bold text-sm sm:text-[15px] leading-tight">{{ $anak->name }}</p>
                                        @if($statusLabel)
                                            <span class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wide px-2 py-0.5 rounded-full {{ ($anak->status ?? '') === 'rejected' ? 'bg-red-500/30 text-red-100' : 'bg-amber-400/25 text-amber-100' }}">{{ $statusLabel }}</span>
                                        @endif
                                    </div>
                                    @if($anak->kelas?->name)
                                        <p class="text-[11px] sm:text-xs text-white/75 mt-0.5 font-medium">{{ $anak->kelas->name }}</p>
                                    @elseif(($anak->status ?? '') === 'approved')
                                        <p class="text-[11px] sm:text-xs text-white/55 mt-0.5">Belum ada kelas</p>
                                    @endif
                                    @if($isPending)
                                        <p class="text-xs text-white/70 mt-1 sm:mt-2">Kehadiran akan tampil setelah pendaftaran disetujui admin.</p>
                                    @else
                                    <div class="grid grid-cols-3 gap-2 sm:gap-3 mt-2 pt-2 border-t border-white/10 text-center sm:text-left">
                                        <div class="flex flex-col">
                                            <span class="text-[8px] sm:text-[9px] text-white/60 uppercase font-bold tracking-wider">Hadir</span>
                                            <span class="text-xs sm:text-sm font-bold">{{ $summary['hadir'] }} <span class="text-[10px] sm:text-xs font-normal opacity-80">hr</span></span>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-[8px] sm:text-[9px] text-white/60 uppercase font-bold tracking-wider">Izin/Sakit</span>
                                            <span class="text-xs sm:text-sm font-bold text-amber-200">{{ $summary['tidak_hadir'] }} <span class="text-[10px] sm:text-xs font-normal opacity-80">hr</span></span>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-[8px] sm:text-[9px] text-white/60 uppercase font-bold tracking-wider">Efektif</span>
                                            <span class="text-xs sm:text-sm font-bold tabular-nums text-white">{{ $summary['efektif'] }} <span class="text-[10px] sm:text-xs font-normal opacity-80">hr</span></span>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-white/75 py-2">Belum ada anak tertaut pada akun ini.</p>
                    @endforelse

                    <div class="mt-3 sm:mt-4 pt-3 sm:pt-4 border-t border-white/10">
                        <x-icon-btn :href="route('orangtua.anak.create')" label="Tambah Anak" icon="{{ \App\Support\DashboardIcons::path('plus') }}" variant="hero-ghost" data-tour="dashboard-quick-links" />
                    </div>
                </div>
            </div>

            @if(!empty($dashboardCharts))
                <x-dashboard-charts :charts="$dashboardCharts" />
            @endif

            @if(($pengumumans ?? collect())->isNotEmpty())
            <div class="card overflow-hidden" data-tour="dashboard-pengumuman">
                <div class="card-pad border-b flex items-center justify-between gap-3" style="border-color:rgba(0,0,0,0.06);">
                    <div>
                        <h3 class="section-title mb-0">Pengumuman Sekolah</h3>
                        <p class="text-xs sm:text-sm m-0 mt-0.5" style="color:#9E9790;">Riwayat informasi dari sekolah dan wali kelas</p>
                    </div>
                </div>
                <ul class="divide-y" style="divide-color:rgba(0,0,0,0.06);">
                    @foreach($pengumumans as $pengumuman)
                        @php $isRead = in_array($pengumuman->id, $readPengumumanIds ?? [], true); @endphp
                        <li>
                            <button type="button"
                                    @click="$dispatch('open-pengumuman', { index: {{ $loop->index }} })"
                                    class="w-full text-left px-4 py-3 sm:px-5 sm:py-4 hover:bg-[#FAF6F0] transition flex items-start gap-3">
                                <div class="h-9 w-9 sm:h-10 sm:w-10 rounded-xl shrink-0 flex items-center justify-center text-white font-bold text-xs sm:text-sm" style="background:#1A6B6B;">
                                    {{ mb_substr($pengumuman->judul, 0, 1) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 mb-0.5 sm:mb-1">
                                        <p class="font-semibold text-xs sm:text-sm m-0" style="color:#2C2C2C;">{{ $pengumuman->judul }}</p>
                                        @unless($isRead)
                                            <span class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full" style="background:#FEF3C7;color:#92400E;">Baru</span>
                                        @endunless
                                    </div>
                                    <p class="text-[11px] sm:text-xs m-0" style="color:#9E9790;">
                                        {{ $pengumuman->mulai_tayang->translatedFormat('d M Y') }}
                                        @if($pengumuman->kelas)
                                            · {{ $pengumuman->kelas->name }}
                                        @else
                                            · Semua kelas
                                        @endif
                                        · {{ $pengumuman->kategori }}
                                    </p>
                                </div>
                                <svg class="h-4 w-4 sm:h-5 sm:w-5 shrink-0 mt-1" style="color:#9E9790;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 lg:gap-8" data-tour="dashboard-recent">
                <div class="lg:col-span-1 flex flex-col gap-4 sm:gap-5">
                    

                    <div class="card overflow-hidden">
                        <div class="card-pad border-b flex items-center justify-between gap-3" style="border-color: rgba(0,0,0,0.06);">
                            <h3 class="section-title mb-0">Menu Makanan hari ini</h3>
                            <a href="{{ route('orangtua.menu-makanan.index') }}" class="text-[11px] sm:text-xs font-bold px-2 py-1 rounded bg-gray-100 hover:bg-gray-200 transition" style="color: #1A6B6B;">Jadwal lengkap</a>
                        </div>
                        <div class="p-4 sm:p-5">
                            @if(!empty($menuHariIni))
                                @php
                                    $photos = [];
                                    if($menuHariIni->photo) $photos[] = Storage::url($menuHariIni->photo);
                                    if($menuHariIni->photo_kegiatan) $photos[] = Storage::url($menuHariIni->photo_kegiatan);
                                @endphp

                                @if(count($photos) > 0)
                                    <div class="mb-3 -mx-4 -mt-4 sm:-mx-5 sm:-mt-5 relative overflow-hidden" x-data="{ 
                                        active: 0, 
                                        photos: {{ json_encode($photos) }},
                                        next() { this.active = (this.active + 1) % this.photos.length },
                                        init() { if(this.photos.length > 1) setInterval(() => this.next(), 4000) }
                                    }">
                                        <div class="h-36 sm:h-48 w-full relative">
                                            <template x-for="(photo, index) in photos" :key="index">
                                                <img x-show="active === index" 
                                                     x-transition:enter="transition ease-out duration-500"
                                                     x-transition:enter-start="opacity-0 transform scale-105"
                                                     x-transition:enter-end="opacity-100 transform scale-100"
                                                     :src="photo" 
                                                     class="absolute inset-0 w-full h-full object-cover">
                                            </template>
                                        </div>
                                        
                                        @if(count($photos) > 1)
                                            <div class="absolute bottom-2.5 left-1/2 -translate-x-1/2 flex gap-1.5">
                                                <template x-for="(photo, index) in photos" :key="index">
                                                    <div class="h-1.5 rounded-full transition-all duration-300" 
                                                         :class="active === index ? 'w-4 bg-white' : 'w-1.5 bg-white/40'"></div>
                                                </template>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                                <p class="font-bold text-sm sm:text-base mb-1.5 whitespace-pre-line leading-snug" style="color: #2C2C2C;">{{ $menuHariIni->menu }}</p>
                                @if($menuHariIni->nutrition_info)
                                    <p class="text-xs sm:text-sm leading-relaxed mb-3 sm:mb-4" style="color: #9E9790;">{{ $menuHariIni->nutrition_info }}</p>
                                @endif
                                
                                <div class="flex items-center gap-2.5 pt-3 sm:pt-4 border-t border-gray-50">
                                    <form action="{{ route('orangtua.menu-makanan.vote') }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="menu_makanan_id" value="{{ $menuHariIni->id }}">
                                        <button type="submit" name="vote_type" value="like" 
                                            class="flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-lg text-xs font-bold transition {{ ($myVote?->vote_type === 'like') ? 'bg-green-100 text-green-700 ring-1 ring-green-200' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                            <svg class="h-3.5 w-3.5" fill="{{ ($myVote?->vote_type === 'like') ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.708C19.747 10 21 11.253 21 12.8c0 .964-.46 1.83-1.18 2.373l-.403.303a11.953 11.953 0 011.583 3.992c.114.71-.46 1.332-1.173 1.332H8.3a2 2 0 01-2-2V10h2l3-6h2v6h3z" /></svg>
                                            {{ $menuHariIni->likes_count ?? 0 }} Suka
                                        </button>
                                    </form>
                                    <form action="{{ route('orangtua.menu-makanan.vote') }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="menu_makanan_id" value="{{ $menuHariIni->id }}">
                                        <button type="submit" name="vote_type" value="dislike" 
                                            class="flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-lg text-xs font-bold transition {{ ($myVote?->vote_type === 'dislike') ? 'bg-red-100 text-red-700 ring-1 ring-red-200' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                            <svg class="h-3.5 w-3.5" fill="{{ ($myVote?->vote_type === 'dislike') ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14H5.292C4.253 14 3 12.747 3 11.2c0-.964.46-1.83 1.18-2.373l.403-.303A11.953 11.953 0 013.001 4.532c-.114-.71.46-1.332 1.173-1.332H15.7a2 2 0 012 2V14h-2l-3 6h-2v-6z" /></svg>
                                            {{ $menuHariIni->dislikes_count ?? 0 }} Tidak Suka
                                        </button>
                                    </form>
                                </div>
                            @else
                                <p class="text-sm" style="color: #9E9790;">Belum ada informasi menu untuk hari ini.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2 flex flex-col gap-4 sm:gap-5">
                    <div class="card overflow-hidden">
                        <div class="px-3 py-3 sm:card-pad border-b flex items-center justify-between gap-2" style="border-color: rgba(0,0,0,0.06);">
                            <h3 class="section-title mb-0 text-base sm:text-lg" style="color:#2C2C2C;">Agenda & Kegiatan</h3>
                            <div class="flex gap-1 shrink-0">
                                <a href="{{ route('orangtua.kegiatan.index') }}" class="text-[11px] sm:text-xs font-semibold px-2.5 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 transition" style="color: #1A6B6B;">Agenda</a>
                                <a href="{{ route('orangtua.kegiatan-rutin.index') }}" class="text-[11px] sm:text-xs font-semibold px-2.5 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 transition" style="color: #1A6B6B;">Kegiatan</a>
                            </div>
                        </div>
                        <div class="divide-y divide-gray-50">
                            @forelse($dashboardFeed ?? [] as $item)
                                @if($item['type'] === 'kegiatan')
                                    @php
                                        $keg = $item['data'];
                                        $activityData = [
                                            'title' => $keg->title,
                                            'date' => \Carbon\Carbon::parse($keg->date)->translatedFormat('d M Y'),
                                            'description' => $keg->description,
                                            'pengajar' => $keg->pengajar->name ?? '-',
                                            'photos' => collect($keg->photos ?? [])->map(fn($p) => Storage::url($p))->values()->all(),
                                            'pencapaians' => $keg->pencapaians->map(fn($p) => [
                                                'aspek' => $p->matrikulasi->aspek ?? '',
                                                'indicator' => $p->matrikulasi->indicator ?? '',
                                                'score_label' => \App\Support\LabelSkorPencapaian::label($p->score, $p->anak?->sekolah_id),
                                                'score_color' => \App\Support\LabelSkorPencapaian::color($p->score, $p->anak?->sekolah_id),
                                                'feedback' => $p->feedback,
                                                'anak_name' => $p->anak->name ?? '-'
                                            ])->values()->all()
                                        ];
                                    @endphp
                                    <div class="px-3 py-3 sm:px-6 sm:py-4 flex gap-2.5 sm:gap-4 cursor-pointer hover:bg-[#FAF9F6] active:bg-[#FAF9F6] transition" @click="openActivity(@js($activityData))">
                                        <x-foto-profil :path="$keg->pengajar?->photo" :name="$keg->pengajar?->name ?? '?'" size="sm" rounded="full" class="shrink-0 sm:!h-10 sm:!w-10 mt-0.5" />
                                        <div class="flex-1 min-w-0">
                                            <p class="text-[11px] font-medium mb-1" style="color:#9E9790;">
                                                <span style="color:#1A6B6B;">Agenda belajar</span>
                                                <span aria-hidden="true"> · </span>
                                                {{ \Carbon\Carbon::parse($keg->date)->translatedFormat('d M Y') }}
                                            </p>
                                            <h4 class="font-bold text-[13px] sm:text-base text-gray-900 leading-snug">{{ $keg->title }}</h4>
                                            @if(filled($keg->description) && trim($keg->description) !== trim($keg->title))
                                                <p class="text-xs text-gray-500 leading-relaxed mt-1 line-clamp-2">{{ $keg->description }}</p>
                                            @endif
                                            @if(!empty($keg->photos))
                                                    <div class="flex gap-1.5 overflow-x-auto pt-2.5 pb-0.5 no-scrollbar -mr-1">
                                                        @foreach(collect($keg->photos)->take(4) as $photo)
                                                            @php $photoUrl = Storage::url($photo); $isVid = \App\Support\DocumentationMedia::isVideoPath($photo); @endphp
                                                            <div class="h-16 w-16 sm:h-24 sm:w-24 rounded-lg border border-gray-100 overflow-hidden shrink-0 cursor-pointer hover:opacity-90 transition relative"
                                                                 @click.stop="openMedia('{{ $photoUrl }}')">
                                                                @if($isVid)
                                                                    <video src="{{ $photoUrl }}" class="h-full w-full object-cover" muted playsinline></video>
                                                                    <span class="absolute inset-0 flex items-center justify-center text-white text-xl bg-black/25">▶</span>
                                                                @else
                                                                    <img src="{{ $photoUrl }}" class="h-full w-full object-cover">
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                        @if(count($keg->photos) > 4)
                                                            <div class="h-16 w-16 sm:h-24 sm:w-24 rounded-lg border border-gray-100 bg-gray-50 flex items-center justify-center text-gray-500 text-xs font-semibold shrink-0">
                                                                +{{ count($keg->photos) - 4 }}
                                                            </div>
                                                        @endif
                                                    </div>
                                            @endif
                                        </div>
                                    </div>
                                @elseif($item['type'] === 'kegiatan_rutin')
                                    @php $kr = $item['data'];
                                    $rutinData = [
                                        'kegiatan' => $kr->kegiatan,
                                        'tanggal'  => \Carbon\Carbon::parse($kr->tanggal)->translatedFormat('d M Y'),
                                        'anak'     => $kr->anak->name ?? '-',
                                        'aspek'    => $kr->aspek,
                                        'status'   => $kr->status_pencapaian ?? null,
                                        'keterangan' => $kr->keterangan ?? null,
                                    ];
                                    @endphp
                                    <div class="px-3 py-3 sm:px-6 sm:py-4 flex gap-2.5 sm:gap-4 cursor-pointer hover:bg-[#FAF9F6] active:bg-[#FAF9F6] transition" @click="openRutin(@js($rutinData))">
                                        <x-foto-profil :path="$kr->anak?->photo" :name="$kr->anak?->name ?? '?'" size="sm" rounded="full" class="shrink-0 sm:!h-10 sm:!w-10 mt-0.5" />
                                        <div class="flex-1 min-w-0">
                                            <p class="text-[11px] font-medium mb-1" style="color:#9E9790;">
                                                <span style="color:#1A6B6B;">Kegiatan rutin</span>
                                                <span aria-hidden="true"> · </span>
                                                {{ \Carbon\Carbon::parse($kr->tanggal)->translatedFormat('d M Y') }}
                                            </p>
                                            <p class="text-xs text-gray-500 truncate">{{ $kr->anak->name ?? '' }}</p>
                                            <h4 class="font-bold text-[13px] sm:text-base text-gray-900 leading-snug mt-0.5">{{ $kr->kegiatan }}</h4>
                                            <div class="flex items-center gap-1.5 flex-wrap mt-1.5">
                                                <span class="inline-block px-2 py-0.5 rounded-md bg-gray-100 text-gray-700 text-[10px] font-semibold">{{ $kr->aspek }}</span>
                                                @if($kr->status_pencapaian)
                                                    <span class="inline-block px-2 py-0.5 rounded-md bg-[#E8F5E9] text-[#2E7D32] text-[10px] font-semibold">{{ $kr->status_pencapaian }}</span>
                                                @endif
                                            </div>
                                            @if($kr->keterangan)
                                                <p class="text-xs text-gray-500 mt-1 leading-relaxed line-clamp-2">{{ $kr->keterangan }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @empty
                                <div class="px-6 py-12 text-center">
                                    <p class="text-sm" style="color: #9E9790;">Belum ada agenda belajar atau kegiatan rutin hari ini.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- Detail Activity Modal --}}
            <template x-teleport="body">
            <div x-show="showActivityModal" class="modal-overlay modal-overlay--blur" style="display:none;">
                <div x-show="showActivityModal" x-transition class="modal-box max-w-2xl w-full" @click.away="showActivityModal=false">
                    <div class="modal-header flex justify-between items-center">
                        <h3 class="section-title mb-0" x-text="selectedActivity?.title || 'Detail Kegiatan'"></h3>
                        <button @click="showActivityModal=false" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <div class="modal-body space-y-5">
                        <div class="flex flex-wrap gap-x-4 gap-y-2 text-xs font-semibold uppercase tracking-wider" style="color: #9E9790;">
                            <span class="flex items-center gap-1"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a12 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg><span x-text="selectedActivity?.date"></span></span>
                            <span class="flex items-center gap-1"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg><span x-text="selectedActivity?.pengajar"></span></span>
                        </div>

                        <div class="text-sm leading-relaxed" style="color: #5A5A5A;" x-text="selectedActivity?.description"></div>

                        <template x-if="selectedActivity?.photos?.length > 0">
                            <div class="space-y-3">
                                <h4 class="text-xs font-bold uppercase tracking-widest" style="color: #9E9790;">Dokumentasi</h4>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                    <template x-for="(url, index) in selectedActivity.photos" :key="index">
                                        <div class="aspect-square rounded-xl overflow-hidden ring-1 ring-black/5 cursor-pointer hover:opacity-90 transition relative" @click="openMedia(url)">
                                            <template x-if="typeof window.isVideoPath === 'function' && window.isVideoPath(url)">
                                                <video :src="url" class="w-full h-full object-cover" muted playsinline></video>
                                                <span class="absolute inset-0 flex items-center justify-center text-white text-2xl bg-black/25">▶</span>
                                            </template>
                                            <template x-if="!(typeof window.isVideoPath === 'function' && window.isVideoPath(url))">
                                                <img :src="url" class="w-full h-full object-cover">
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <template x-if="(selectedActivity?.pencapaians?.length ?? 0) > 0">
                            <div class="space-y-3 pt-4 border-t" style="border-color: rgba(0,0,0,0.06);">
                                <h4 class="text-xs font-bold uppercase tracking-widest" style="color: #975A16;">Laporan Perkembangan Anak</h4>
                                <div class="space-y-2">
                                    <template x-for="(p, pi) in selectedActivity.pencapaians" :key="pi">
                                        <details class="rounded-xl border overflow-hidden group/ind" style="background:#FFFBF0; border-color:rgba(151,90,22,0.12);">
                                            <summary class="p-3 sm:p-3.5 cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                                                <div class="flex items-start justify-between gap-2">
                                                    <p class="text-[10px] font-bold uppercase tracking-wide text-amber-800/90 leading-tight" x-text="p.aspek || 'Evaluasi'"></p>
                                                    <svg class="h-4 w-4 shrink-0 text-amber-700/50 transition-transform duration-200 group-open/ind:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                                </div>
                                                <p class="text-[13px] sm:text-sm font-semibold text-gray-900 mt-1 line-clamp-2 leading-snug" x-text="p.indicator || 'Indikator'"></p>
                                                <p class="text-[11px] text-gray-500 mt-1 truncate" x-show="p.anak_name" x-text="p.anak_name"></p>
                                                <span class="inline-block mt-2 text-[10px] font-bold px-2.5 py-1 rounded-full leading-snug" :style="'background:'+p.score_color+';color:white;'" x-text="p.score_label"></span>
                                            </summary>
                                            <div class="px-3 sm:px-3.5 pb-3 pt-0 border-t text-sm leading-relaxed space-y-2" style="border-color:rgba(151,90,22,0.08); color:#5A5A5A;">
                                                <p class="pt-2.5 text-gray-800" x-text="p.indicator"></p>
                                                <template x-if="p.feedback">
                                                    <p class="text-xs sm:text-sm italic border-l-2 pl-2.5" style="border-color:rgba(151,90,22,0.25);"><span class="font-semibold not-italic text-gray-500">Catatan guru:</span> <span x-text="p.feedback"></span></p>
                                                </template>
                                            </div>
                                        </details>
                                    </template>
                                </div>
                            </div>
                        </template>
                        <template x-if="!(selectedActivity?.pencapaians?.length)">
                            <p class="text-sm pt-4 border-t" style="border-color: rgba(0,0,0,0.06); color: #9E9790;">Belum ada laporan perkembangan untuk kegiatan ini.</p>
                        </template>
                    </div>
                </div>
            </div>
            </template>

            {{-- Modal Kegiatan Rutin --}}
            <template x-teleport="body">
            <div x-show="showRutinModal" class="modal-overlay modal-overlay--blur" style="display:none;">
                <div x-show="showRutinModal" x-transition class="modal-box max-w-lg w-full" @click.away="showRutinModal=false">
                    <div class="modal-header flex justify-between items-center" style="border-bottom: 2px solid #3B82F6;">
                        <div class="flex items-center gap-2">
                            <div class="h-7 w-7 rounded-lg bg-blue-100 flex items-center justify-center">
                                <svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                            </div>
                            <h3 class="section-title mb-0 text-blue-800">Detail Kegiatan Rutin</h3>
                        </div>
                        <button @click="showRutinModal=false" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <div class="modal-body space-y-4">
                        {{-- Info Anak & Tanggal --}}
                        <div class="flex flex-wrap gap-x-4 gap-y-1.5 text-xs font-semibold" style="color: #9E9790;">
                            <span class="flex items-center gap-1.5">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                <span x-text="selectedRutin?.anak"></span>
                            </span>
                            <span class="flex items-center gap-1.5">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                <span x-text="selectedRutin?.tanggal"></span>
                            </span>
                        </div>

                        {{-- Kegiatan --}}
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-blue-600 mb-1">Kegiatan</p>
                            <p class="text-base font-bold text-gray-900" x-text="selectedRutin?.kegiatan"></p>
                        </div>

                        {{-- Aspek & Status --}}
                        <div class="flex items-center gap-2 flex-wrap">
                            <template x-if="selectedRutin?.aspek">
                                <span class="px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 text-xs font-bold" x-text="selectedRutin.aspek"></span>
                            </template>
                            <template x-if="selectedRutin?.status">
                                <span class="px-3 py-1.5 rounded-lg bg-[#E8F5E9] text-[#2E7D32] text-xs font-bold" x-text="selectedRutin.status"></span>
                            </template>
                        </div>

                        {{-- Keterangan --}}
                        <template x-if="selectedRutin?.keterangan">
                            <div class="rounded-xl p-4 border" style="background:#EFF6FF; border-color:rgba(59,130,246,0.15);">
                                <p class="text-[10px] font-bold uppercase tracking-widest text-blue-600 mb-2">Keterangan</p>
                                <p class="text-sm leading-relaxed text-gray-700" x-text="selectedRutin.keterangan"></p>
                            </div>
                        </template>
                        <template x-if="!selectedRutin?.keterangan">
                            <p class="text-xs text-gray-400 italic">Tidak ada keterangan tambahan.</p>
                        </template>
                    </div>
                </div>
            </div>
            </template>

            {{-- Modal Preview Gambar --}}
            <template x-teleport="body">
            <div x-show="showImageModal" 
                 class="modal-overlay modal-overlay--elevated modal-overlay--dark"
                 style="display: none;"
                 x-transition
                 @keydown.escape.window="showImageModal = false">
                <div class="relative max-w-4xl w-full" @click.away="showImageModal = false">
                    <button class="absolute -top-12 right-0 text-white hover:text-gray-300 transition flex items-center gap-2" @click="showImageModal = false">
                        <span class="text-xs font-bold uppercase tracking-widest text-white/50">Klik di mana saja untuk tutup</span>
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                    <template x-if="activeMediaType === 'video'">
                        <video :src="activeImage" class="w-full h-auto max-h-[85vh] rounded-2xl shadow-2xl bg-black shadow-black/20" controls playsinline></video>
                    </template>
                    <template x-if="activeMediaType !== 'video'">
                        <img :src="activeImage" class="w-full h-auto max-h-[85vh] object-contain rounded-2xl shadow-2xl bg-white shadow-black/20">
                    </template>
                </div>
            </div>
            </template>
        </div>

        <x-pengumuman-popup :pengumumans="$pengumumans ?? collect()" />
        @endhasrole

        @unless(auth()->user()->roles->count() > 0)
        <div class="card p-12 text-center">
            <div class="h-14 w-14 rounded-2xl mx-auto mb-4 flex items-center justify-center" style="background: #EDE8DF;">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color: #9E9790;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            </div>
            <h3 class="text-lg font-bold" style="color: #2C2C2C;">Akun Tanpa Peran</h3>
            <p class="text-sm mt-2 max-w-sm mx-auto" style="color: #9E9790;">Hubungi administrator untuk menetapkan peran pada akun Anda.</p>
        </div>
        @endunless
    </div>

    @if(!empty($dashboardCharts ?? []))
        @push('scripts')
            @vite(['resources/js/dashboard-charts.js'])
            <script type="application/json" id="dashboard-charts-json">@json($dashboardCharts)</script>
        @endpush
    @endif
</x-app-layout>
