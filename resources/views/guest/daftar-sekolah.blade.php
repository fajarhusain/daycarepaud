<x-guest-layout max-width="max-w-xl">
    <x-auth-session-status class="mb-5" :status="session('status')" />

    <div class="mb-6">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-surface-mint text-forest-deep text-xs font-semibold tracking-wide uppercase">
            <span class="material-symbols-outlined text-[15px]">school</span>
            Pendaftaran Sekolah Baru
        </span>
        <h1 class="font-serif text-2xl sm:text-3xl lg:text-4xl font-bold text-text-primary tracking-tight mt-2.5">
            {{ $cms['page_daftar_sekolah_h1'] }}
        </h1>
        <p class="text-sm text-text-secondary mt-1.5 leading-relaxed">
            {{ $cms['page_daftar_sekolah_intro'] }}
        </p>
    </div>

    @include('auth.partials.daftar-sekolah-form')
</x-guest-layout>
