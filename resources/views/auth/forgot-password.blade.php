<x-guest-layout>
    <div class="mb-6">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-surface-mint text-forest-deep text-xs font-semibold tracking-wide uppercase">
            <span class="material-symbols-outlined text-[15px]">lock_reset</span>
            Pemulihan Akun
        </span>
        <h1 class="font-serif text-2xl sm:text-3xl lg:text-4xl font-bold text-text-primary tracking-tight mt-2.5">
            Lupa kata sandi?
        </h1>
        <p class="text-sm text-text-secondary mt-1.5 leading-relaxed">
            Masukkan alamat email yang terdaftar. Kami akan mengirimkan tautan untuk mengatur ulang kata sandi Anda.
        </p>
    </div>

    <x-auth-session-status class="mb-5" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Alamat Email Terdaftar')" />
            <x-auth-field-wrap icon="fa-solid fa-envelope">
                <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="email@contoh.com" />
            </x-auth-field-wrap>
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <x-ascent-button>
            <span class="material-symbols-outlined text-[19px]">mail</span>
            <span>Kirim Tautan Reset</span>
        </x-ascent-button>

        <div class="text-center pt-2">
            <a href="{{ route('login') }}" class="text-xs sm:text-sm font-semibold text-forest-deep hover:underline inline-flex items-center gap-1">
                <span class="material-symbols-outlined text-[15px]">arrow_back</span>
                <span>Kembali ke halaman masuk</span>
            </a>
        </div>
    </form>
</x-guest-layout>
