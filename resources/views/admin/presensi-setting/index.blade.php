<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background:#1A6B6B;">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h2 class="font-bold text-xl" style="color:#2C2C2C;">Pengaturan Presensi Guru</h2>
        </div>
    </x-slot>

    <div class="app-page-shell">
        <x-settings-tabs />

        @if(session('success'))
            <div class="alert-success mb-5">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="alert-danger mb-5">
                <ul class="list-disc pl-5 text-sm">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
            </div>
        @endif

        @if(! $activeShift->location_coordinate)
            <div class="alert-danger mb-5">
                Titik koordinat shift <strong>{{ $activeShift->name }}</strong> belum diatur, jadi guru di shift ini belum bisa absen.
                Atur di <strong>Lokasi &amp; Radius</strong> lalu simpan.
            </div>
        @endif

        <div class="card overflow-hidden mb-6">
            <div class="px-6 py-4 border-b flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3" style="border-color:rgba(0,0,0,0.06);">
                <div>
                    <h3 class="section-title">Shift Presensi</h3>
                    <p class="section-subtitle">Tiap shift punya jam kerja, titik GPS, dan radius sendiri. Hari libur berlaku untuk semua shift.</p>
                </div>
                @role('Admin Sekolah')
                    <form method="POST" action="{{ route('admin.presensi-setting.shifts.store') }}" class="flex gap-2 items-end shrink-0">
                        @csrf
                        <div>
                            <label class="input-label" for="shift_name">Shift baru</label>
                            <input type="text" id="shift_name" name="name" maxlength="100" required placeholder="Contoh: Shift Siang" class="input-field py-1.5 text-sm w-44">
                        </div>
                        <button type="submit" class="btn-secondary px-4 py-2 text-sm font-bold whitespace-nowrap">Tambah</button>
                    </form>
                @endrole
            </div>
            <div class="px-6 py-3 flex flex-wrap gap-2 border-b" style="border-color:rgba(0,0,0,0.06);">
                @foreach($shifts as $shift)
                    <a href="{{ route('admin.presensi-setting.index', ['shift' => $shift->id]) }}"
                        class="px-4 py-2 rounded-xl text-sm font-bold transition {{ $shift->id === $activeShift->id ? 'text-white' : '' }}"
                        style="{{ $shift->id === $activeShift->id ? 'background:#1A6B6B;' : 'background:#FAF6F0;color:#6B6560;' }}">
                        {{ $shift->name }}
                        @if($shift->is_default)
                            <span class="text-[10px] font-normal opacity-80">(default)</span>
                        @endif
                    </a>
                @endforeach
            </div>
            @role('Admin Sekolah')
                @if(! $activeShift->is_default)
                    <div class="px-6 py-3 flex flex-wrap gap-2 items-center border-b" style="border-color:rgba(0,0,0,0.06);">
                        <form method="POST" action="{{ route('admin.presensi-setting.shifts.update', $activeShift) }}" class="flex gap-2 items-end">
                            @csrf
                            @method('PATCH')
                            <div>
                                <label class="input-label">Nama shift</label>
                                <input type="text" name="name" value="{{ old('name', $activeShift->name) }}" maxlength="100" required class="input-field py-1.5 text-sm">
                            </div>
                            <button type="submit" class="btn-secondary px-3 py-2 text-xs font-bold">Ubah nama</button>
                        </form>
                        <form method="POST" action="{{ route('admin.presensi-setting.shifts.destroy', $activeShift) }}" class="ml-auto" onsubmit="return confirm('Hapus shift ini? Guru yang masih terikat tidak bisa dihapus.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs font-bold px-3 py-2 rounded-lg" style="color:#C0392B;background:#FDECEA;">Hapus shift</button>
                        </form>
                    </div>
                @endif
            @endrole
        </div>

        <form method="POST" action="{{ route('admin.presensi-setting.update') }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="presensi_shift_id" value="{{ $activeShift->id }}">

            <div class="card overflow-hidden mb-6" data-tour="presensi-setting-location">
                <div class="px-6 py-4 border-b" style="border-color:rgba(0,0,0,0.06);">
                    <h3 class="section-title">Lokasi &amp; Radius — {{ $activeShift->name }}</h3>
                    <p class="section-subtitle">Titik absensi shift ini. Guru dengan shift {{ $activeShift->name }} harus berada dalam radius saat check-in/out.</p>
                </div>
                <div class="card-body-pad grid grid-cols-1 xl:grid-cols-2 gap-6">
                    <div class="space-y-4 min-w-0">
                        <div>
                            <label class="input-label" for="map-search-query">Cari daerah / alamat</label>
                            <div class="flex gap-2">
                                <input type="text" id="map-search-query" class="input-field flex-1 min-w-0" placeholder="Contoh: PAUD Harapan, Bandung" autocomplete="off">
                                <button type="button" id="map-search-btn" class="btn-secondary shrink-0 px-4 py-2 text-sm font-bold">Cari</button>
                            </div>
                            <div id="map-search-results" class="hidden mt-2 rounded-xl border overflow-hidden bg-white shadow-sm max-h-48 overflow-y-auto" style="border-color:rgba(0,0,0,0.08);"></div>
                        </div>
                        <div>
                            <label class="input-label" for="location_coordinate">Titik koordinat sekolah (lat,lng)</label>
                            <div class="flex flex-col sm:flex-row gap-2">
                                <input type="text" id="location_coordinate" name="location_coordinate"
                                    value="{{ old('location_coordinate', $activeShift->location_coordinate) }}"
                                    placeholder="-6.200000,106.816666" class="input-field w-full">
                                <button type="button" id="btn-deteksi-lokasi" class="btn-secondary px-4 py-2 rounded-xl text-sm font-bold whitespace-nowrap shrink-0">
                                    Deteksi lokasi saya
                                </button>
                            </div>
                            <p class="text-xs mt-2" style="color:#9A9188;">Klik peta, geser penanda, atau isi manual. Format: latitude,longitude.</p>
                            <p id="deteksi-status" class="text-xs mt-1" style="color:#9A9188;"></p>
                        </div>
                        <div>
                            <label class="input-label">Radius presensi (meter)</label>
                            <input type="number" name="radius_m" min="10" max="5000"
                                value="{{ old('radius_m', $activeShift->radius_m) }}"
                                placeholder="100" class="input-field w-full sm:max-w-xs">
                            <p class="text-xs mt-2" style="color:#9A9188;">Kosongkan untuk default 100 meter.</p>
                        </div>
                        <div class="text-xs rounded-xl px-4 py-3" style="background:#FAF6F0;color:#6B6560;">
                            Koordinat tersimpan:
                            <span id="deteksi-hasil" class="font-bold" style="color:#2C2C2C;">{{ $activeShift->location_coordinate ?: 'belum ada koordinat tersimpan' }}</span>
                        </div>
                    </div>
                    <div class="min-w-0">
                        <div id="presensi-setting-map" class="w-full h-72 md:h-96 rounded-xl border overflow-hidden z-0" style="border-color:rgba(0,0,0,0.08);"></div>
                        <p class="text-xs mt-2" style="color:#9A9188;">Peta OpenStreetMap. Gunakan pencarian untuk menemukan wilayah, lalu sesuaikan titik sekolah.</p>
                    </div>
                </div>
            </div>

            <div class="card overflow-hidden mb-6">
                <div class="px-6 py-4 border-b" style="border-color:rgba(0,0,0,0.06);">
                    <h3 class="section-title">Jadwal Jam Kerja</h3>
                    <p class="section-subtitle">Hari tidak dicentang = libur (presensi ditutup).</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="data-table w-full">
                        <thead>
                            <tr>
                                <th>Hari</th>
                                <th class="text-center">Kerja</th>
                                <th>Jam Masuk</th>
                                <th>Jam Pulang</th>
                                <th>Toleransi Masuk (mnt)</th>
                                <th>Toleransi Pulang (mnt)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($jadwals as $hari => $jadwal)
                                <tr>
                                    <td class="font-semibold">{{ \App\Models\SekolahJamKerja::HARI[$hari] ?? $hari }}</td>
                                    <td class="text-center">
                                        <input type="checkbox" name="jadwal[{{ $hari }}][is_kerja]" value="1"
                                            @checked(old("jadwal.$hari.is_kerja", $jadwal->is_kerja))
                                            class="h-4 w-4 rounded border-gray-300">
                                    </td>
                                    <td>
                                        <input type="time" name="jadwal[{{ $hari }}][jam_masuk]"
                                            value="{{ old("jadwal.$hari.jam_masuk", $jadwal->jam_masuk ? \Illuminate\Support\Carbon::parse($jadwal->jam_masuk)->format('H:i') : '') }}"
                                            class="input-field py-1.5 text-sm w-32">
                                    </td>
                                    <td>
                                        <input type="time" name="jadwal[{{ $hari }}][jam_pulang]"
                                            value="{{ old("jadwal.$hari.jam_pulang", $jadwal->jam_pulang ? \Illuminate\Support\Carbon::parse($jadwal->jam_pulang)->format('H:i') : '') }}"
                                            class="input-field py-1.5 text-sm w-32">
                                    </td>
                                    <td>
                                        <input type="number" name="jadwal[{{ $hari }}][toleransi_masuk_menit]" min="0" max="600"
                                            value="{{ old("jadwal.$hari.toleransi_masuk_menit", $jadwal->toleransi_masuk_menit) }}"
                                            class="input-field py-1.5 text-sm w-24">
                                    </td>
                                    <td>
                                        <input type="number" name="jadwal[{{ $hari }}][toleransi_pulang_menit]" min="0" max="600"
                                            value="{{ old("jadwal.$hari.toleransi_pulang_menit", $jadwal->toleransi_pulang_menit) }}"
                                            class="input-field py-1.5 text-sm w-24">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t flex justify-end" style="border-color:rgba(0,0,0,0.06);">
                    <button type="submit" class="btn-primary">Simpan Pengaturan</button>
                </div>
            </div>
        </form>

        <div class="card overflow-hidden">
            <div class="px-6 py-4 border-b" style="border-color:rgba(0,0,0,0.06);">
                <h3 class="section-title">Hari Libur Khusus</h3>
                <p class="section-subtitle">Tanggal merah, libur semester, dan sejenisnya. Presensi ditutup pada tanggal ini.</p>
            </div>

            <form method="POST" action="{{ route('admin.presensi-setting.libur.store') }}" class="card-pad toolbar-form border-b w-full" style="border-color:rgba(0,0,0,0.06);">
                @csrf
                <input type="hidden" name="shift" value="{{ $activeShift->id }}">
                <div class="toolbar-field md:min-w-[10rem]">
                    <label class="input-label">Tanggal</label>
                    <input type="date" name="tanggal" value="{{ old('tanggal') }}" class="input-field w-full" required>
                </div>
                <div class="toolbar-field flex-1 min-w-[12rem]">
                    <label class="input-label">Keterangan</label>
                    <input type="text" name="keterangan" value="{{ old('keterangan') }}" maxlength="255" placeholder="Contoh: Libur Idul Fitri" class="input-field w-full">
                </div>
                <div class="toolbar-field shrink-0 flex items-end">
                    <button type="submit" class="btn-primary">Tambah</button>
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="data-table w-full">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Keterangan</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($hariLiburs as $libur)
                            <tr>
                                <td class="font-semibold">{{ $libur->tanggal?->translatedFormat('d F Y') }}</td>
                                <td>{{ $libur->keterangan ?: '-' }}</td>
                                <td class="text-right">
                                    <form method="POST" action="{{ route('admin.presensi-setting.libur.destroy', $libur) }}" class="inline" onsubmit="return confirm('Hapus hari libur ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-bold px-3 py-1.5 rounded-lg" style="color:#C0392B;background:#FDECEA;">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-10 text-sm" style="color:#9A9188;">Belum ada hari libur khusus.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        @vite('resources/js/presensi-setting-map.js')
    @endpush
</x-app-layout>
