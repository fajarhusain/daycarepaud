{{-- Form pendaftaran orang tua + anak; dipakai di /register dan /pendaftaran ($action, $sekolahs) --}}

@if($sekolahs->isEmpty())
    <div class="auth-callout auth-callout--danger" role="alert">
        <span class="auth-callout__icon">
            <span class="material-symbols-outlined text-[20px] text-rose-600">error</span>
        </span>
        <span>Belum ada data sekolah terdaftar di sistem. Hubungi pihak pengelola agar pendaftaran dapat diproses.</span>
    </div>
@endif

<div class="auth-callout auth-callout--warm" role="note">
    <span class="auth-callout__icon">
        <span class="material-symbols-outlined text-[20px] text-forest-deep">info</span>
    </span>
    <span>Pendaftaran Anda akan diverifikasi oleh Admin Sekolah. Anda dapat masuk ke aplikasi setelah status akun disetujui.</span>
</div>

<div class="auth-callout auth-callout--muted auth-callout--row" role="note">
    <span class="text-xs sm:text-sm text-text-secondary">Sudah terdaftar dan ingin mendaftarkan adik/anak lain?</span>
    <a href="{{ route('orangtua.anak.create') }}" class="auth-callout-link text-xs sm:text-sm">
        <span>Masuk untuk tambah anak</span>
        <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
    </a>
</div>

<form method="POST" action="{{ $action }}" class="space-y-6" enctype="multipart/form-data">
    @csrf

    <!-- SECTION 1: DATA ORANG TUA -->
    <div class="space-y-4 pt-1">
        <div class="flex items-center gap-2.5 mb-1">
            <span class="inline-flex w-7 h-7 rounded-full bg-forest-deep text-white items-center justify-center font-bold text-xs shrink-0">1</span>
            <span class="text-xs font-bold uppercase tracking-wider text-forest-deep">Data Orang Tua / Wali</span>
        </div>

        <div>
            <x-input-label for="name" :value="__('Nama Lengkap Orang Tua / Wali *')" />
            <x-text-input id="name" type="text" name="name" :value="old('name')" required autocomplete="name" placeholder="Contoh: Budi Santoso, S.T." />
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Alamat Email Aktif *')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="email.aktif@contoh.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>
    </div>

    <!-- SECTION 2: PILIHAN SEKOLAH -->
    <div class="space-y-4 pt-3 border-t border-border-subtle/80">
        <div class="flex items-center gap-2.5 mb-1">
            <span class="inline-flex w-7 h-7 rounded-full bg-forest-deep text-white items-center justify-center font-bold text-xs shrink-0">2</span>
            <span class="text-xs font-bold uppercase tracking-wider text-forest-deep">Pilihan Sekolah Daycare</span>
        </div>

        <div>
            <x-input-label for="sekolah_id" :value="__('Sekolah Daycare / PAUD Tujuan *')" />
            <select id="sekolah_id" name="sekolah_id" class="input-field" required{{ $sekolahs->isEmpty() ? ' disabled' : '' }}>
                <option value=""> Pilih Sekolah Daycare </option>
                @foreach($sekolahs as $s)
                    <option value="{{ $s->id }}" @selected(old('sekolah_id') == $s->id)>
                        {{ $s->name }}{{ $s->address ? '  '.$s->address : '' }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('sekolah_id')" class="mt-1" />
        </div>
    </div>

    <!-- SECTION 3: DATA ANAK -->
    <div class="space-y-4 pt-3 border-t border-border-subtle/80">
        <div class="flex items-center gap-2.5 mb-1">
            <span class="inline-flex w-7 h-7 rounded-full bg-forest-deep text-white items-center justify-center font-bold text-xs shrink-0">3</span>
            <span class="text-xs font-bold uppercase tracking-wider text-forest-deep">Data Identitas Anak</span>
        </div>

        <div>
            <x-input-label for="anak_name" :value="__('Nama Lengkap Anak *')" />
            <x-text-input id="anak_name" type="text" name="anak_name" :value="old('anak_name')" required placeholder="Nama sesuai akta kelahiran / panggilan" />
            <x-input-error :messages="$errors->get('anak_name')" class="mt-1" />
        </div>

        <div>
            <div class="flex items-center justify-between mb-1">
                <x-input-label for="anak_dob" :value="__('Tanggal Lahir Anak *')" class="!mb-0" />
                <span id="age-preview" class="inline-flex items-center gap-1 text-[11px] font-bold text-forest-deep bg-surface-mint px-2.5 py-0.5 rounded-full" style="display: none;"></span>
            </div>
            <x-text-input id="anak_dob" type="date" name="anak_dob" :value="old('anak_dob')" required max="{{ now()->subDay()->format('Y-m-d') }}" onchange="updateAgePreview(this.value)" />
            <x-input-error :messages="$errors->get('anak_dob')" class="mt-1" />
        </div>

        <script>
            function updateAgePreview(dobString) {
                const preview = document.getElementById('age-preview');
                if (!dobString) {
                    preview.style.display = 'none';
                    return;
                }
                
                const birthDate = new Date(dobString);
                const today = new Date();
                
                let years = today.getFullYear() - birthDate.getFullYear();
                let months = today.getMonth() - birthDate.getMonth();
                
                if (months < 0 || (months === 0 && today.getDate() < birthDate.getDate())) {
                    years--;
                    months += 12;
                }
                
                if (today.getDate() < birthDate.getDate()) {
                    months--;
                }
                
                if (months < 0) {
                    months += 12;
                }

                let text = '';
                if (years > 0) text += years + ' tahun ';
                if (months > 0) text += months + ' bulan';
                if (text === '') text = '0 bulan';
                
                preview.innerHTML = '<span class="material-symbols-outlined text-[13px]">cake</span> Usia: ' + text.trim();
                preview.style.display = 'inline-flex';
            }
            window.addEventListener('DOMContentLoaded', () => {
                const dob = document.getElementById('anak_dob').value;
                if(dob) updateAgePreview(dob);
            });
        </script>

        <div>
            <x-input-label for="photo" :value="__('Foto Anak (Opsional)')" />
            <input id="photo" type="file" name="photo" class="input-field py-2 text-xs file:mr-3 file:py-1.5 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-surface-mint file:text-forest-deep hover:file:bg-surface-sage" accept="image/*" />
            <p class="text-[11px] text-text-secondary mt-1">Format: JPG, PNG, atau WEBP. Maks. 2MB.</p>
            <x-input-error :messages="$errors->get('photo')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="catatan_ortu" :value="__('Catatan Khusus (Opsional)')" />
            <textarea id="catatan_ortu" name="catatan_ortu" rows="2" class="input-field" placeholder="Misal: Riwayat alergi, kebutuhan khusus, kebiasaan tidur…">{{ old('catatan_ortu') }}</textarea>
            <x-input-error :messages="$errors->get('catatan_ortu')" class="mt-1" />
        </div>
    </div>

    <!-- SECTION 4: KEAMANAN AKUN -->
    <div class="space-y-4 pt-3 border-t border-border-subtle/80">
        <div class="flex items-center gap-2.5 mb-1">
            <span class="inline-flex w-7 h-7 rounded-full bg-forest-deep text-white items-center justify-center font-bold text-xs shrink-0">4</span>
            <span class="text-xs font-bold uppercase tracking-wider text-forest-deep">Keamanan Akun</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="password" :value="__('Kata Sandi *')" />
                <x-text-input id="password" type="password" name="password" required autocomplete="new-password" placeholder="Min. 8 karakter" />
                <x-input-error :messages="$errors->get('password')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="password_confirmation" :value="__('Ulangi Kata Sandi *')" />
                <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Ulangi kata sandi" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
            </div>
        </div>
    </div>

    <div class="pt-2">
        <x-ascent-button :disabled="$sekolahs->isEmpty()">
            <span class="material-symbols-outlined text-[19px]">how_to_reg</span>
            <span>Kirim Pendaftaran Anak</span>
        </x-ascent-button>
    </div>

    <div class="text-center pt-2">
        <a href="{{ route('login') }}" class="text-xs sm:text-sm font-semibold text-forest-deep hover:underline inline-flex items-center gap-1">
            <span>Sudah memiliki akun? Masuk</span>
            <span class="material-symbols-outlined text-[15px]">arrow_forward</span>
        </a>
    </div>
</form>
