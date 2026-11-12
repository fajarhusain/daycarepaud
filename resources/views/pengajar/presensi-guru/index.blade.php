<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background: #1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <div>
                <h2 class="font-bold text-xl" style="color: #2C2C2C;">Presensi Saya</h2>
                <p class="text-xs" style="color: #6B6560;">Check in &amp; check out dengan lokasi</p>
            </div>
        </div>
    </x-slot>

    <div class="py-4 md:py-8 px-3 md:px-4 sm:px-6 lg:px-8 max-w-3xl mx-auto">
        @if(session('success'))
            <div class="alert-success mb-5">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="alert-danger mb-5">
                <ul class="list-disc pl-5 text-sm">
                    @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                </ul>
            </div>
        @endif

        <div class="card overflow-hidden mb-6">
            <div class="px-6 py-4 border-b flex flex-wrap items-center justify-between gap-3" style="border-color: rgba(0,0,0,0.06);">
                <div>
                    <h3 class="section-title">{{ $status['tanggal']->translatedFormat('l, d F Y') }}</h3>
                    <p class="section-subtitle">{{ $sekolah->name }} · {{ $pengajar->name }}</p>
                </div>
                @if($status['jadwal'] && $status['jadwal']->is_kerja)
                    <div class="text-right text-sm" style="color: #6B6560;">
                        <div>Jam kerja: <strong style="color:#2C2C2C;">{{ \Illuminate\Support\Carbon::parse($status['jadwal']->jam_masuk)->format('H:i') }} – {{ \Illuminate\Support\Carbon::parse($status['jadwal']->jam_pulang)->format('H:i') }}</strong></div>
                        <div class="text-xs">Toleransi: {{ $status['jadwal']->toleransi_masuk_menit }} / {{ $status['jadwal']->toleransi_pulang_menit }} menit</div>
                    </div>
                @endif
            </div>

            <div class="px-6 py-6">
                @if(! $status['koordinat_set'])
                    <div class="alert-danger mb-0">
                        Lokasi sekolah belum diatur. Hubungi admin sekolah agar presensi dapat dibuka.
                    </div>
                @elseif($status['libur'])
                    <div class="p-4 rounded-xl text-sm font-bold" style="background:#FAF6F0;color:#6B6560;">
                        Hari ini libur, presensi tidak dibuka.
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                        <div class="rounded-xl p-4" style="background:#FAF6F0;">
                            <div class="text-[11px] uppercase tracking-widest font-bold" style="color:#9A9188;">Check In</div>
                            <div class="text-2xl font-bold" style="color:#2C2C2C;">
                                {{ $status['presensi']?->check_in_at?->format('H:i') ?? '--:--' }}
                            </div>
                            @if($status['presensi']?->terlambat_masuk)
                                <div class="text-xs font-bold mt-1" style="color:#C0392B;">Telat {{ $status['presensi']->menit_telat_masuk }} menit</div>
                            @endif
                        </div>
                        <div class="rounded-xl p-4" style="background:#FAF6F0;">
                            <div class="text-[11px] uppercase tracking-widest font-bold" style="color:#9A9188;">Check Out</div>
                            <div class="text-2xl font-bold" style="color:#2C2C2C;">
                                {{ $status['presensi']?->check_out_at?->format('H:i') ?? '--:--' }}
                            </div>
                            @if($status['presensi']?->telat_pulang)
                                <div class="text-xs font-bold mt-1" style="color:#C0392B;">Telat pulang {{ $status['presensi']->menit_telat_pulang }} menit</div>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <form id="form-check-in" method="POST" action="{{ route('pengajar.presensi-guru.check-in') }}" class="flex-1">
                            @csrf
                            <input type="hidden" name="latitude" data-field="latitude">
                            <input type="hidden" name="longitude" data-field="longitude">
                            <input type="hidden" name="accuracy" data-field="accuracy">
                            <button type="button"
                                data-geo-submit="form-check-in"
                                @disabled(! $status['bisa_check_in'])
                                class="btn-primary w-full py-3 rounded-2xl font-bold text-sm shadow-lg disabled:opacity-40 disabled:cursor-not-allowed">
                                {{ $status['presensi']?->check_in_at ? 'Sudah Check In' : 'Check In Sekarang' }}
                            </button>
                        </form>

                        <form id="form-check-out" method="POST" action="{{ route('pengajar.presensi-guru.check-out') }}" class="flex-1">
                            @csrf
                            <input type="hidden" name="latitude" data-field="latitude">
                            <input type="hidden" name="longitude" data-field="longitude">
                            <input type="hidden" name="accuracy" data-field="accuracy">
                            <button type="button"
                                data-geo-submit="form-check-out"
                                @disabled(! $status['bisa_check_out'])
                                class="btn-primary w-full py-3 rounded-2xl font-bold text-sm shadow-lg disabled:opacity-40 disabled:cursor-not-allowed"
                                style="background:#2C2C2C;">
                                {{ $status['presensi']?->check_out_at ? 'Sudah Check Out' : 'Check Out Sekarang' }}
                            </button>
                        </form>
                    </div>

                    @if($status['presensi']?->check_in_at && ! $status['presensi']?->check_out_at && ! $status['bisa_check_out'] && $status['jadwal']?->jam_pulang)
                        <p class="text-xs font-bold mt-3" style="color:#C0392B;">
                            Check-out baru dibuka mulai pukul {{ \Illuminate\Support\Carbon::parse($status['jadwal']->jam_pulang)->format('H:i') }}.
                        </p>
                    @endif

                    <p id="geo-status" class="text-xs mt-4" style="color:#9A9188;">
                        Shift: <strong>{{ $status['shift_nama'] }}</strong>.
                        Aktifkan izin lokasi di browser. Titik presensi wajib berada dalam radius {{ $status['radius_meter'] }} meter dari titik shift ini.
                    </p>
                @endif
            </div>
        </div>
    </div>

    <script>
        // Dijalankan langsung (bukan DOMContentLoaded): navigasi Turbo tidak memicu event itu lagi.
        (function () {
            const statusEl = document.getElementById('geo-status');

            function setStatus(text, color) {
                if (statusEl) {
                    statusEl.textContent = text;
                    statusEl.style.color = color || '#9A9188';
                }
            }

            document.querySelectorAll('[data-geo-submit]').forEach(function (button) {
                button.addEventListener('click', function () {
                    const form = document.getElementById(button.dataset.geoSubmit);

                    if (!form) {
                        return;
                    }

                    if (!navigator.geolocation) {
                        setStatus('Browser tidak mendukung lokasi. Gunakan browser lain.', '#C0392B');
                        return;
                    }

                    button.disabled = true;
                    setStatus('Mengambil lokasi…', '#6B6560');

                    navigator.geolocation.getCurrentPosition(
                        function (position) {
                            form.querySelector('[data-field="latitude"]').value = position.coords.latitude;
                            form.querySelector('[data-field="longitude"]').value = position.coords.longitude;
                            const accuracyField = form.querySelector('[data-field="accuracy"]');
                            if (accuracyField && position.coords.accuracy != null) {
                                accuracyField.value = position.coords.accuracy;
                            }
                            form.submit();
                        },
                        function (error) {
                            button.disabled = false;
                            const messages = {
                                1: 'Izin lokasi ditolak. Aktifkan izin lokasi untuk aplikasi ini di pengaturan HP, lalu coba lagi.',
                                2: 'Lokasi tidak tersedia. Pastikan GPS/Lokasi HP menyala, lalu coba lagi.',
                                3: 'Pengambilan lokasi terlalu lama. Coba di tempat terbuka atau pastikan GPS menyala, lalu coba lagi.',
                            };
                            setStatus(messages[error && error.code] || 'Gagal mengambil lokasi. Coba lagi.', '#C0392B');
                        },
                        { enableHighAccuracy: true, timeout: 20000, maximumAge: 0 }
                    );
                });
            });
        })();
    </script>
</x-app-layout>
