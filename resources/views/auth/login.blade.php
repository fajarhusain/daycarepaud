<x-guest-layout>
    <x-auth-session-status class="mb-5" :status="session('status')" />

    <div class="mb-6">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-surface-mint text-forest-deep text-xs font-semibold tracking-wide uppercase">
            <span class="material-symbols-outlined text-[15px]">login</span>
            Portal Masuk
        </span>
        <h1 class="font-serif text-2xl sm:text-3xl lg:text-4xl font-bold text-text-primary tracking-tight mt-2.5">
            Selamat datang kembali
        </h1>
        <p class="text-sm text-text-secondary mt-1.5 leading-relaxed">
            Masuk ke akun Anda untuk mengelola daycare atau memantau kegiatan ananda.
        </p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Alamat Email')" />
            <x-auth-field-wrap icon="fa-solid fa-envelope">
                <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="email@contoh.com" />
            </x-auth-field-wrap>
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Kata Sandi')" />
            <x-auth-field-wrap icon="fa-solid fa-lock">
                <x-text-input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
            </x-auth-field-wrap>
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <div class="flex items-center justify-between gap-3 flex-wrap pt-1 pb-1">
            <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer select-none">
                <input id="remember_me" type="checkbox" class="rounded-md border-border-subtle text-forest-deep focus:ring-forest-deep/20" name="remember">
                <span class="text-sm text-text-secondary">Ingat saya</span>
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-sm font-semibold text-forest-deep hover:underline">
                    Lupa kata sandi?
                </a>
            @endif
        </div>

        <x-ascent-button>
            <span class="material-symbols-outlined text-[19px]">login</span>
            <span>Masuk ke Sistem</span>
        </x-ascent-button>

        <div class="pt-5 mt-6 border-t border-border-subtle/80 text-center space-y-2">
            <p class="text-xs text-text-secondary">Belum memiliki akun terdaftar?</p>
            <div class="flex items-center justify-center gap-3 text-xs sm:text-sm font-semibold">
                <a href="{{ route('guest.pendaftaran') }}" class="text-forest-deep hover:underline inline-flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px]">child_care</span>
                    Daftar Siswa
                </a>
                <span class="text-text-secondary/40">•</span>
                <a href="{{ route('guest.daftar-sekolah') }}" class="text-forest-deep hover:underline inline-flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px]">school</span>
                    Daftar Sekolah
                </a>
            </div>
        </div>
    </form>
</x-guest-layout>
