<x-guest-layout>
    <div class="mb-6">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-surface-mint text-forest-deep text-xs font-semibold tracking-wide uppercase">
            <span class="material-symbols-outlined text-[15px]">security</span>
            Verifikasi Keamanan
        </span>
        <h1 class="font-serif text-2xl sm:text-3xl lg:text-4xl font-bold text-text-primary tracking-tight mt-2.5">
            Konfirmasi kata sandi
        </h1>
        <p class="text-sm text-text-secondary mt-1.5 leading-relaxed">
            Halaman ini memerlukan otorisasi. Masukkan kata sandi Anda untuk melanjutkan.
        </p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="password" :value="__('Kata Sandi')" />
            <x-auth-field-wrap icon="fa-solid fa-lock">
                <x-text-input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
            </x-auth-field-wrap>
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <x-ascent-button>
            <span class="material-symbols-outlined text-[19px]">verified_user</span>
            <span>Konfirmasi &amp; Lanjutkan</span>
        </x-ascent-button>
    </form>
</x-guest-layout>
