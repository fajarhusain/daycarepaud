<x-guest-layout max-width="max-w-xl">
    <x-auth-session-status class="mb-5" :status="session('status')" />

    <div class="mb-6">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-surface-mint text-forest-deep text-xs font-semibold tracking-wide uppercase">
            <span class="material-symbols-outlined text-[15px]">child_care</span>
            Pendaftaran Siswa / Anak
        </span>
        <h1 class="font-serif text-2xl sm:text-3xl lg:text-4xl font-bold text-text-primary tracking-tight mt-2.5">
            {{ $cms['page_pendaftaran_h2'] }}
        </h1>
        <p class="text-sm text-text-secondary mt-1.5 leading-relaxed">
            {{ $cms['page_pendaftaran_intro'] }}
            Sudah punya akun sebelumnya?
            <a href="{{ route('login') }}" class="font-semibold text-forest-deep hover:underline">Masuk di sini</a>.
        </p>
    </div>

    @include('auth.partials.pendaftaran-form', [
        'action' => route('guest.pendaftaran.store'),
        'sekolahs' => $sekolahs,
    ])
</x-guest-layout>
