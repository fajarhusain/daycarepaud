<x-guest-layout>
    <div class="mb-6">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-surface-mint text-forest-deep text-xs font-semibold tracking-wide uppercase">
            <span class="material-symbols-outlined text-[15px]">lock</span>
            Kata Sandi Baru
        </span>
        <h1 class="font-serif text-2xl sm:text-3xl lg:text-4xl font-bold text-text-primary tracking-tight mt-2.5">
            Atur ulang kata sandi
        </h1>
        <p class="text-sm text-text-secondary mt-1.5 leading-relaxed">
            Buat kata sandi baru yang aman untuk akun Anda.
        </p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <x-input-label for="email" :value="__('Alamat Email')" />
            <x-text-input id="email" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Kata Sandi Baru')" />
            <x-auth-field-wrap icon="fa-solid fa-lock">
                <x-text-input id="password" type="password" name="password" required autocomplete="new-password" placeholder="••••••••" />
            </x-auth-field-wrap>
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Ulangi Kata Sandi')" />
            <x-auth-field-wrap icon="fa-solid fa-lock">
                <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••" />
            </x-auth-field-wrap>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
        </div>

        <x-ascent-button>
            <span class="material-symbols-outlined text-[19px]">check_circle</span>
            <span>Simpan Kata Sandi Baru</span>
        </x-ascent-button>
    </form>
</x-guest-layout>
