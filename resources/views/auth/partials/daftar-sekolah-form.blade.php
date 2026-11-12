<div class="auth-callout auth-callout--warm" role="note">
    <span class="auth-callout__icon">
        <span class="material-symbols-outlined text-[20px] text-forest-deep">info</span>
    </span>
    <span>Pendaftaran sekolah akan ditinjau oleh Superadmin. Anda dapat masuk ke panel manajemen setelah lembaga disetujui.</span>
</div>

@if($errors->any())
<div class="auth-callout auth-callout--danger" role="alert">
    <span class="auth-callout__icon">
        <span class="material-symbols-outlined text-[20px] text-rose-600">error</span>
    </span>
    <ul class="list-disc pl-4 space-y-1 m-0 text-xs sm:text-sm">
        @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
        @endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('guest.daftar-sekolah.store') }}" class="space-y-6">
    @csrf

    <!-- SECTION 1: DATA SEKOLAH -->
    <div class="space-y-4 pt-1">
        <div class="flex items-center gap-2.5 mb-2">
            <span class="inline-flex w-7 h-7 rounded-full bg-forest-deep text-white items-center justify-center font-bold text-xs shrink-0">1</span>
            <span class="text-xs font-bold uppercase tracking-wider text-forest-deep">Data Lembaga &amp; Sekolah</span>
        </div>
        
        <div>
            <x-input-label for="sekolah_name" value="Nama Sekolah / Lembaga Daycare *" />
            <x-text-input id="sekolah_name" name="sekolah_name" type="text" :value="old('sekolah_name')" required autofocus placeholder="Contoh: Daycare Bintang Kecil" />
            <x-input-error :messages="$errors->get('sekolah_name')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="sekolah_address" value="Alamat Lengkap" />
            <textarea id="sekolah_address" name="sekolah_address" rows="3" class="input-field min-h-24" placeholder="Jalan, nomor, kelurahan, kecamatan, kota/kabupaten">{{ old('sekolah_address') }}</textarea>
            <x-input-error :messages="$errors->get('sekolah_address')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="sekolah_phone" value="Nomor Telepon Sekolah" />
            <x-text-input id="sekolah_phone" name="sekolah_phone" type="tel" :value="old('sekolah_phone')" inputmode="tel" placeholder="08xx atau (021) xxx" />
            <x-input-error :messages="$errors->get('sekolah_phone')" class="mt-1" />
        </div>
    </div>

    <!-- SECTION 2: AKUN ADMIN SEKOLAH -->
    <div class="space-y-4 pt-4 border-t border-border-subtle/80">
        <div class="flex items-center gap-2.5 mb-2">
            <span class="inline-flex w-7 h-7 rounded-full bg-forest-deep text-white items-center justify-center font-bold text-xs shrink-0">2</span>
            <span class="text-xs font-bold uppercase tracking-wider text-forest-deep">Akun Administrator Sekolah</span>
        </div>

        <div>
            <x-input-label for="admin_name" value="Nama Lengkap Penanggung Jawab *" />
            <x-text-input id="admin_name" name="admin_name" type="text" :value="old('admin_name')" required placeholder="Nama kepala sekolah atau penanggung jawab" />
            <x-input-error :messages="$errors->get('admin_name')" class="mt-1" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="min-w-0">
                <x-input-label for="admin_email" value="Alamat Email Login *" />
                <x-text-input id="admin_email" name="admin_email" type="email" :value="old('admin_email')" required autocomplete="email" placeholder="admin@sekolah.com" />
                <x-input-error :messages="$errors->get('admin_email')" class="mt-1" />
            </div>
            <div class="min-w-0">
                <x-input-label for="admin_phone" value="Nomor Telepon / WhatsApp" />
                <x-text-input id="admin_phone" name="admin_phone" type="tel" :value="old('admin_phone')" inputmode="tel" placeholder="08xxxxxxxxxx" />
                <x-input-error :messages="$errors->get('admin_phone')" class="mt-1" />
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="min-w-0">
                <x-input-label for="password" value="Kata Sandi *" />
                <x-text-input id="password" name="password" type="password" required autocomplete="new-password" placeholder="Min. 8 karakter" />
                <x-input-error :messages="$errors->get('password')" class="mt-1" />
            </div>
            <div class="min-w-0">
                <x-input-label for="password_confirmation" value="Ulangi Kata Sandi *" />
                <x-text-input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" placeholder="Ulangi kata sandi" />
            </div>
        </div>
    </div>

    <div class="pt-2">
        <x-ascent-button>
            <span class="material-symbols-outlined text-[19px]">domain_add</span>
            <span>Kirim Pendaftaran Sekolah</span>
        </x-ascent-button>
    </div>

    <div class="text-center pt-2">
        <a href="{{ route('login') }}" class="text-xs sm:text-sm font-semibold text-forest-deep hover:underline inline-flex items-center gap-1">
            <span>Sudah memiliki akun? Masuk</span>
            <span class="material-symbols-outlined text-[15px]">arrow_forward</span>
        </a>
    </div>
</form>
