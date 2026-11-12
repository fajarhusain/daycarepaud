<x-app-layout>
    @php
        $profileMode = $profileMode ?? 'akun';
        $isSchoolAdmin = $user->hasRole('Admin Sekolah') || $user->hasRole('Lembaga');
        $pageTitle = $profileMode === 'sekolah'
            ? 'Profil Sekolah'
            : ($isSchoolAdmin ? 'Profil Admin' : 'Profil Akun Saya');
    @endphp
    <x-slot name="header">
        <div class="flex items-center gap-3" data-tour="page-header">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background:#1A6B6B;"><svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg></div>
            <h2 class="font-bold text-xl" style="color:#2C2C2C;">{{ $pageTitle }}</h2>
        </div>
    </x-slot>

    <div class="app-page-shell space-y-6">

        @if(session('status') && in_array(session('status'), ['profile-updated','profile-sekolah-updated','profile-pengajar-updated','profile-orangtua-updated','profile-anak-updated','password-updated']))
            <div class="alert-success"><svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Perubahan berhasil disimpan.</div>
        @endif

        @if($errors->any())
            <div class="alert-danger"><ul class="list-disc pl-5 text-sm">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul></div>
        @endif

        @if(\App\Support\AdminSettingsNav::canAccessHub($user))
            <x-settings-tabs data-tour="admin-settings-tabs" />
        @endif

        @if($profileMode !== 'sekolah')
            {{-- SECTION: Akun Login --}}
            @php
                $akunFotoPath = filled(optional($user->pengajar)->photo)
                    ? $user->pengajar->photo
                    : ($user->anaks->first(fn ($a) => filled($a->photo))?->photo);
            @endphp
            <div class="card w-full overflow-hidden" data-tour="profile-account">
                <div class="px-4 py-5 md:px-6 md:py-6 border-b flex flex-col sm:flex-row sm:items-center gap-4 sm:gap-6" style="border-color:rgba(0,0,0,0.06); background:rgba(26,107,107,0.04);">
                    <x-foto-profil :path="$akunFotoPath" :name="$user->name" size="2xl" rounded="full" class="ring-4 ring-white shadow-md mx-auto sm:mx-0 shrink-0" />
                    <div class="min-w-0 flex-1 text-center sm:text-left">
                        <h3 class="section-title">Informasi Akun Login</h3>
                        <p class="section-subtitle mt-1">Nama tampilan dan email untuk masuk ke sistem.</p>
                        <p class="text-sm font-medium mt-2 truncate" style="color:#2C2C2C;">{{ $user->name }}</p>
                        <p class="text-xs truncate" style="color:#9E9790;">{{ $user->email }}</p>
                    </div>
                </div>
                <form method="post" action="{{ route('profile.update') }}" class="card-body-pad space-y-5">
                    @csrf @method('patch')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-5">
                        <div class="md:col-span-1"><label class="input-label">Nama Tampilan</label><input type="text" name="name" value="{{ old('name', $user->name) }}" required class="input-field"></div>
                        <div class="md:col-span-1"><label class="input-label">Alamat Email</label><input type="email" name="email" value="{{ old('email', $user->email) }}" required class="input-field"></div>
                    </div>
                    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                        <p class="text-xs text-yellow-600">Email Anda belum diverifikasi.</p>
                    @endif
                    <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-2 border-t" style="border-color:rgba(0,0,0,0.06);">
                        <button type="submit" class="btn-primary w-full sm:w-auto">{{ $isSchoolAdmin ? 'Simpan Profil Admin' : 'Simpan Akun' }}</button>
                    </div>
                </form>
            </div>
        @endif

        @if($profileMode === 'sekolah')
            {{-- SECTION: Profil Sekolah --}}
            @if(($user->hasRole('Admin Sekolah') || $user->hasRole('Lembaga')) && $sekolah)
            <div class="card" data-tour="profile-role">
                <div class="px-6 py-4 border-b flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4" style="border-color:rgba(0,0,0,0.06);">
                    <div>
                        <h3 class="section-title">Profil Sekolah</h3>
                        <p class="section-subtitle">Informasi resmi sekolah yang Anda kelola.</p>
                    </div>
                    <x-foto-profil :path="$sekolah->photo" :name="$sekolah->name" size="xl" rounded="full" />
                </div>
                <form method="post" action="{{ route('profile.sekolah.update') }}" enctype="multipart/form-data" class="px-6 py-5 space-y-4">
                    @csrf @method('patch')
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2"><label class="input-label">Nama Sekolah</label><input type="text" name="name" value="{{ old('name', $sekolah->name) }}" required class="input-field"></div>
                        <div><label class="input-label">NPSN</label><input type="text" name="npsn" value="{{ old('npsn', $sekolah->nisn) }}" class="input-field"></div>
                        <div><label class="input-label">Kontak / HP</label><input type="text" name="phone" value="{{ old('phone', $sekolah->phone) }}" class="input-field"></div>
                        <div class="col-span-2"><label class="input-label">Alamat Lengkap</label><textarea name="address" class="input-field" rows="3">{{ old('address', $sekolah->address) }}</textarea></div>
                        <div class="col-span-2 grid grid-cols-1 xl:grid-cols-2 gap-6">
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
                                    <label class="input-label" for="location_coordinate">Koordinat lokasi (opsional)</label>
                                    <div class="flex flex-col sm:flex-row gap-2">
                                        <input type="text" id="location_coordinate" name="location_coordinate"
                                            value="{{ old('location_coordinate', $sekolah->location_coordinate) }}"
                                            placeholder="-6.200000,106.816666" class="input-field w-full">
                                        <button type="button" id="btn-deteksi-lokasi" class="btn-secondary px-4 py-2 rounded-xl text-sm font-bold whitespace-nowrap shrink-0">
                                            Deteksi lokasi saya
                                        </button>
                                    </div>
                                    <p class="text-xs mt-2" style="color:#9A9188;">Klik peta, geser penanda, atau isi manual. Format: latitude,longitude.</p>
                                    <p id="deteksi-status" class="text-xs mt-1" style="color:#9A9188;"></p>
                                </div>
                                <div class="text-xs rounded-xl px-4 py-3" style="background:#FAF6F0;color:#6B6560;">
                                    Koordinat tersimpan:
                                    <span id="deteksi-hasil" class="font-bold" style="color:#2C2C2C;">{{ $sekolah->location_coordinate ?: 'belum ada koordinat tersimpan' }}</span>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <div id="presensi-setting-map" class="w-full h-72 md:h-96 rounded-xl border overflow-hidden z-0" style="border-color:rgba(0,0,0,0.08);"></div>
                                <p class="text-xs mt-2" style="color:#9A9188;">Peta OpenStreetMap. Gunakan pencarian untuk menemukan wilayah, lalu sesuaikan titik sekolah.</p>
                            </div>
                        </div>
                        <div class="col-span-2"><label class="input-label">Foto/Logo Sekolah (Opsional)</label><input type="file" id="photo-sekolah" name="photo" accept="image/*" class="input-field py-1.5 text-xs"></div>
                    </div>
                    <div class="flex justify-end"><button type="submit" class="btn-primary">Simpan Profil Sekolah</button></div>
                </form>
            </div>
            @endif
        @endif

        {{-- SECTION: Profil Pengajar / Wali Kelas --}}
        @if($profileMode !== 'sekolah' && ($user->hasRole('Pengajar') || $user->hasRole('Wali Kelas')) && $pengajar)
        <div class="card" data-tour="profile-role">
            <div class="px-6 py-4 border-b flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4" style="border-color:rgba(0,0,0,0.06);">
                <div>
                    <h3 class="section-title">Profil Pendidik</h3>
                    <p class="section-subtitle">Data personal Anda sebagai tenaga pengajar.</p>
                </div>
                <x-foto-profil :path="$pengajar->photo" :name="$pengajar->name" size="xl" rounded="full" />
            </div>
            <form method="post" action="{{ route('profile.pengajar.update') }}" enctype="multipart/form-data" class="px-6 py-5 space-y-4">
                @csrf @method('patch')
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="input-label">NIK (KTP)</label><input type="text" name="nik" value="{{ $pengajar->nik }}" class="input-field"></div>
                    <div><label class="input-label">Kontak / WA</label><input type="text" name="phone" value="{{ $pengajar->phone }}" class="input-field"></div>
                    <div>
                        <label class="input-label">Pendidikan terakhir</label>
                        <x-pendidikan-select name="pendidikan" :value="$pengajar->pendidikan" />
                    </div>
                    <div><label class="input-label">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="input-field">
                            <option value="">Pilih...</option>
                            <option value="Pria" {{ $pengajar->jenis_kelamin == 'Pria' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="Wanita" {{ $pengajar->jenis_kelamin == 'Wanita' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                    <div class="col-span-2"><label class="input-label">Alamat Lengkap</label><textarea name="alamat" class="input-field" rows="3">{{ $pengajar->alamat }}</textarea></div>
                    <div class="col-span-2"><label class="input-label">Foto profil</label><input type="file" id="photo-pengajar" name="photo" accept="image/*" class="input-field py-1.5 text-xs"></div>
                </div>
                <div class="flex justify-end"><button type="submit" class="btn-primary">Simpan Profil Pendidik</button></div>
            </form>
        </div>
        @endif

        {{-- SECTION: Profil Wali Murid --}}
        @if($profileMode !== 'sekolah' && $user->hasRole('Orang Tua') && $anaks && $anaks->count() > 0)
        @php
            $waliRef = $anaks->sortByDesc('updated_at')->first();
        @endphp
        <div class="card" data-tour="profile-role">
            <div class="px-6 py-4 border-b" style="border-color:rgba(0,0,0,0.06);">
                <h3 class="section-title">Profil Wali Murid</h3>
                <p class="section-subtitle">Data orang tua / wali tersimpan di database (sama untuk semua anak pada akun ini).</p>
            </div>
            <form method="post" action="{{ route('profile.orangtua.update') }}" class="px-6 py-5 space-y-4">
                @csrf @method('patch')
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2"><label class="input-label">Nama wali (pendaftaran)</label><input type="text" name="parent_name" value="{{ old('parent_name', $waliRef->parent_name) }}" class="input-field" placeholder="Nama orang tua / wali"></div>
                    <div><label class="input-label">Nama Bapak</label><input type="text" name="nama_bapak" value="{{ old('nama_bapak', $waliRef->nama_bapak) }}" class="input-field"></div>
                    <div><label class="input-label">NIK Bapak</label><input type="text" name="nik_bapak" value="{{ old('nik_bapak', $waliRef->nik_bapak) }}" class="input-field"></div>
                    <div><label class="input-label">Nama Ibu</label><input type="text" name="nama_ibu" value="{{ old('nama_ibu', $waliRef->nama_ibu) }}" class="input-field"></div>
                    <div><label class="input-label">NIK Ibu</label><input type="text" name="nik_ibu" value="{{ old('nik_ibu', $waliRef->nik_ibu) }}" class="input-field"></div>
                </div>
                <div class="flex justify-end"><button type="submit" class="btn-primary">Simpan Data Wali</button></div>
            </form>
        </div>

        {{-- SECTION: Data Anak (accordion) --}}
        <div class="space-y-3" x-data="{ openAnakId: null }">
            <div class="flex items-center justify-between gap-3 px-1">
                <div>
                    <h3 class="section-title">Data Anak</h3>
                    <p class="section-subtitle">{{ $anaks->count() }} anak terdaftar pada akun ini.</p>
                </div>
                <a href="{{ route('orangtua.anak.create') }}" class="text-xs font-bold px-3 py-2 rounded-lg whitespace-nowrap" style="background:#E8F4F4; color:#1A6B6B;">+ Tambah Anak</a>
            </div>

            @foreach($anaks as $anak)
            @php
                $statusLabel = match ($anak->status ?? '') {
                    'approved' => 'Disetujui',
                    'pending' => 'Menunggu persetujuan',
                    'rejected' => 'Ditolak',
                    default => $anak->status ? ucfirst((string) $anak->status) : '',
                };
                $statusClass = match ($anak->status ?? '') {
                    'approved' => 'badge-teal',
                    'pending' => 'badge-amber',
                    'rejected' => 'badge-rose',
                    default => 'badge-teal',
                };
                $jkAnak = old('jenis_kelamin', $anak->jenis_kelamin);
            @endphp
            <div class="card overflow-hidden">
                <button
                    type="button"
                    class="w-full px-6 py-4 flex items-center gap-4 text-left hover:bg-gray-50/80 transition"
                    @click="openAnakId = openAnakId === {{ $anak->id }} ? null : {{ $anak->id }}"
                    :aria-expanded="openAnakId === {{ $anak->id }}"
                >
                    <x-foto-profil :path="$anak->photo" :name="$anak->name" size="md" rounded="full" class="ring-2 ring-[#1A6B6B]/15 shrink-0" />
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-semibold text-[#2C2C2C]">{{ $anak->name }}</span>
                            <span class="badge {{ $statusClass }} text-[10px]">{{ $statusLabel }}</span>
                        </div>
                        <p class="text-xs mt-0.5" style="color:#9E9790;">
                            {{ $anak->kelas->name ?? 'Tanpa kelas' }}
                            @if($anak->dob)
                                · {{ $anak->age }}
                            @endif
                        </p>
                    </div>
                    <svg
                        class="h-5 w-5 shrink-0 transition-transform duration-200"
                        style="color:#9E9790;"
                        :class="{ 'rotate-180': openAnakId === {{ $anak->id }} }"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div
                    x-show="openAnakId === {{ $anak->id }}"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                >
                    <div class="border-t" style="border-color:rgba(0,0,0,0.06);">
                        <div class="px-6 py-4 bg-gray-50/80 border-b text-sm space-y-2" style="border-color:rgba(0,0,0,0.06);">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-[#2C2C2C]">
                                <div><span class="text-gray-500">Sekolah:</span> <span class="font-medium">{{ $anak->sekolah->name ?? '' }}</span></div>
                                <div><span class="text-gray-500">Kelas:</span> <span class="font-medium">{{ $anak->kelas->name ?? 'Tanpa kelas' }}</span></div>
                                <div><span class="text-gray-500">Status pendaftaran:</span> <span class="font-medium">{{ $statusLabel }}</span></div>
                                <div><span class="text-gray-500">Usia:</span> <span class="font-medium">{{ $anak->dob ? $anak->age : '' }}</span></div>
                                @if(filled($anak->parent_name))
                                    <div class="sm:col-span-2"><span class="text-gray-500">Nama wali (di data anak):</span> <span class="font-medium">{{ $anak->parent_name }}</span></div>
                                @endif
                            </div>
                        </div>
                        <form method="post" action="{{ route('profile.anak.update', $anak) }}" enctype="multipart/form-data" class="px-6 py-5 space-y-4">
                            @csrf @method('patch')
                            <div class="grid grid-cols-2 gap-4">
                                <div class="col-span-2"><label class="input-label">Nama Lengkap Anak</label><input type="text" name="name" value="{{ old('name', $anak->name) }}" required class="input-field"></div>
                                <div class="col-span-2">
                                    <label class="input-label">Nama Panggilan</label>
                                    <input type="text" name="nickname" maxlength="50" value="{{ old('nickname', $anak->nickname) }}" class="input-field @error('nickname') border-red-500 @enderror" placeholder="Opsional, maks. 50 karakter  dipakai saran AI pencapaian">
                                    @error('nickname')<p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div><label class="input-label">Tanggal Lahir</label><input type="date" name="dob" value="{{ old('dob', $anak->dob?->format('Y-m-d')) }}" required class="input-field"></div>
                                <div><label class="input-label">NIK Anak (opsional)</label><input type="text" name="nik" value="{{ old('nik', $anak->nik) }}" class="input-field"></div>
                                <div><label class="input-label">Jenis Kelamin</label>
                                    <select name="jenis_kelamin" class="input-field">
                                        <option value="">Pilih...</option>
                                        <option value="Laki-laki" @selected($jkAnak === 'Laki-laki')>Laki-laki</option>
                                        <option value="Perempuan" @selected($jkAnak === 'Perempuan')>Perempuan</option>
                                    </select>
                                </div>
                                <div><label class="input-label">Foto Anak</label><input type="file" id="photo-anak-{{ $anak->id }}" name="photo" accept="image/*" class="input-field py-1 text-xs"></div>
                                <div class="col-span-2"><label class="input-label">Alamat Lengkap</label><textarea name="alamat" class="input-field" rows="2">{{ old('alamat', $anak->alamat) }}</textarea></div>
                            </div>
                            <div class="flex justify-end"><button type="submit" class="btn-primary">Simpan Data Anak</button></div>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        @if($profileMode !== 'sekolah')
            {{-- SECTION: Ganti Password --}}
            <div class="card w-full overflow-hidden" data-tour="profile-security">
                <div class="card-pad border-b" style="border-color:rgba(0,0,0,0.06);">
                    <h3 class="section-title">Ganti Password</h3>
                    <p class="section-subtitle">Password default adalah &quot;password123&quot;. Silakan ganti setelah login pertama kali.</p>
                </div>
                <form method="post" action="{{ route('password.update') }}" class="card-body-pad space-y-4">
                    @csrf @method('put')
                    <div class="rounded-xl border px-4 py-3 flex items-start gap-3 text-sm" role="alert" style="background:#FFF4E5; border-color:#FFD9A0; color:#7A4F0A;">
                        <svg class="h-5 w-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div class="min-w-0 space-y-1">
                            <p class="font-semibold">Password login akan berubah</p>
                            <p class="leading-relaxed">Menyimpan form ini memperbarui kata sandi akun pengguna Anda. Login berikutnya wajib memakai password baru; password lama tidak berlaku lagi.</p>
                        </div>
                    </div>
                    <div><label class="input-label">Password Saat Ini</label><input type="password" name="current_password" class="input-field" autocomplete="current-password"></div>
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="input-label">Password Baru</label><input type="password" name="password" class="input-field" autocomplete="new-password"></div>
                        <div><label class="input-label">Konfirmasi Password</label><input type="password" name="password_confirmation" class="input-field" autocomplete="new-password"></div>
                    </div>
                    <div class="flex justify-end"><button type="submit" class="btn-primary">Ganti Password</button></div>
                </form>
            </div>
        @endif

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const handleCompress = async (input) => {
                if (input.files && input.files[0]) {
                    const file = input.files[0];
                    if (file.size > 500 * 1024) { // Only compress if > 500KB
                        try {
                            const compressedFile = await window.compressImage(file);
                            const dataTransfer = new DataTransfer();
                            dataTransfer.items.add(compressedFile);
                            input.files = dataTransfer.files;
                        } catch (e) {
                            console.error('Compression failed:', e);
                        }
                    }
                }
            };

            const photoPengajar = document.getElementById('photo-pengajar');
            if (photoPengajar) {
                photoPengajar.addEventListener('change', function() { handleCompress(this); });
            }

            const photoSekolah = document.getElementById('photo-sekolah');
            if (photoSekolah) {
                photoSekolah.addEventListener('change', function() { handleCompress(this); });
            }

            @if($user->hasRole('Orang Tua') && $anaks)
                @foreach($anaks as $anak)
                    const photoAnak{{ $anak->id }} = document.getElementById('photo-anak-{{ $anak->id }}');
                    if (photoAnak{{ $anak->id }}) {
                        photoAnak{{ $anak->id }}.addEventListener('change', function() { handleCompress(this); });
                    }
                @endforeach
            @endif
        });
    </script>

    @if($profileMode === 'sekolah')
        @push('scripts')
            @vite('resources/js/presensi-setting-map.js')
        @endpush
    @endif
</x-app-layout>
