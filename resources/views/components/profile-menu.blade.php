@props(['showName' => false, 'hasPageTour' => false])

<div {{ $attributes->merge(['class' => 'flex items-center gap-3 shrink-0']) }}>
    @if($showName)
        <div class="text-right hidden md:block">
            <div class="text-sm font-semibold leading-tight text-[#2C2C2C]">{{ Auth::user()->name }}</div>
            <div class="text-xs text-[#9E9790]">{{ $roleLabel ?? '' }}</div>
        </div>
    @endif
    <x-dropdown align="right" width="48">
        <x-slot name="trigger">
            <button type="button" class="rounded-xl overflow-hidden shrink-0 transition-all hover:opacity-90 shadow-[2px_3px_8px_rgba(26,107,107,0.30)] focus:outline-none focus:ring-2 focus:ring-[#1A6B6B] focus:ring-offset-2 ring-offset-[#FAF6F0]" aria-label="Menu profil">
                <x-foto-profil :path="Auth::user()->hasRole('Orang Tua') ? null : ($topbarProfilePhotoPath ?? null)" :name="Auth::user()->name" size="nav" rounded="xl" class="pointer-events-none" />
            </button>
        </x-slot>
        <x-slot name="content">
            <!-- @if($hasPageTour)
            <button type="button" data-tour-trigger class="block w-full px-4 py-2 text-start text-sm leading-5 text-[#2C2C2C] hover:bg-black/5 focus:outline-none focus:bg-black/5 transition duration-150 ease-in-out">
                Ulangi panduan halaman
            </button>
            @endif -->
            @if(Auth::user()->hasRole('Admin Sekolah') || Auth::user()->hasRole('Lembaga'))
                @php
                    $hasProfileSekolahRoute = \Illuminate\Support\Facades\Route::has('profile.sekolah.edit');
                @endphp
                <x-dropdown-link :href="route('profile.edit')">
                    Profil Admin
                </x-dropdown-link>
                @if($hasProfileSekolahRoute)
                    <x-dropdown-link :href="route('profile.sekolah.edit')">
                        Profil Sekolah
                    </x-dropdown-link>
                @endif
            @else
                <x-dropdown-link :href="route('profile.edit')">
                    Profil Saya
                </x-dropdown-link>
            @endif
            <form method="POST" action="{{ route('logout') }}" data-turbo="false">
                @csrf
                <x-dropdown-link :href="route('logout')"
                        onclick="event.preventDefault(); this.closest('form').submit();">
                    Keluar
                </x-dropdown-link>
            </form>
        </x-slot>
    </x-dropdown>
</div>
