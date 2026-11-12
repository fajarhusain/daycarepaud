@php
    use App\Support\GuestAscent;
    use App\Support\GuestBrand;
    use App\Support\GuestWhatsApp;
    $brand = GuestBrand::name();
    $navActive = fn (string $route) => request()->routeIs($route) ? 'text-primary-foreground' : '';
@endphp
<header id="header" class="sticky top-0 transition-[top] duration-300 z-40">
    <div id="header-container">
        <div id="top-header" class="bg-destructive sm:block hidden">
            <div class="container">
                <div class="flex lg:flex-row flex-col justify-between items-center gap-2 py-[13px]">
                    <div>
                        <ul class="flex flex-wrap gap-7.5">
                            @if(!empty($cms['kontak_telepon']))
                            <li>
                                <a href="{{ GuestWhatsApp::url(GuestWhatsApp::demoIntro()) }}" class="text-cream-foreground flex items-start gap-4">
                                    <span><i class="fa-solid fa-phone"></i></span>
                                    <span>{{ $cms['kontak_telepon'] }}</span>
                                </a>
                            </li>
                            @endif
                            @if(!empty($cms['kontak_email']))
                            <li>
                                <a href="mailto:{{ $cms['kontak_email'] }}" class="text-cream-foreground flex items-start gap-4">
                                    <span><i class="fa-solid fa-envelope"></i></span>
                                    <span>{{ $cms['kontak_email'] }}</span>
                                </a>
                            </li>
                            @endif
                            @if(!empty($cms['kontak_alamat']))
                            <li>
                                <p class="text-cream-foreground flex items-start gap-4">
                                    <span><i class="fa-solid fa-location-dot"></i></span>
                                    <span class="line-clamp-2">{{ $cms['kontak_alamat'] }}</span>
                                </p>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="[.header-pinned_&]:shadow-md bg-background transition-all duration-300 relative">
            <div class="container py-5">
                <div class="flex justify-between items-center">
                    <a href="{{ route('guest.beranda') }}" class="min-w-0 shrink-0">
                        <span class="font-bold text-xl sm:text-2xl lg:text-3xl font-jost truncate">{{ $brand }}</span>
                    </a>
                    <div class="flex items-center gap-6 xl:gap-10">
                        <nav class="xl:block hidden shrink-0" aria-label="Navigasi utama">
                            <ul class="flex items-center gap-[25px]">
                                <li class="leading-[164%] relative group">
                                    <a href="{{ route('guest.beranda') }}" class="font-semibold text-lg font-jost group-hover:text-primary-foreground transition-all duration-500 {{ $navActive('guest.beranda') }}">Beranda</a>
                                </li>
                                <li class="leading-[164%] relative group">
                                    <a href="{{ route('guest.tentang') }}" class="font-semibold text-lg font-jost group-hover:text-primary-foreground transition-all duration-500 {{ $navActive('guest.tentang') }}">Tentang</a>
                                </li>
                                <li class="leading-[164%] relative group">
                                    <a href="{{ route('guest.fasilitas') }}" class="font-semibold text-lg font-jost group-hover:text-primary-foreground transition-all duration-500 {{ $navActive('guest.fasilitas') }}">Fitur</a>
                                </li>
                                <li class="leading-[164%] relative group">
                                    <a href="{{ route('guest.kontak') }}" class="font-semibold text-lg font-jost group-hover:text-primary-foreground transition-all duration-500 {{ $navActive('guest.kontak') }}">Kontak</a>
                                </li>
                            </ul>
                        </nav>
                        <div class="block xl:hidden">
                            <div class="fixed left-0 top-0 w-full h-full bg-black/30 invisible transition-all offcanva-overlay z-40"></div>
                            <nav class="offcanva bg-warm border-l-2 border-l-primary w-full max-w-md min-h-screen h-full overflow-y-auto p-7 shadow-md fixed -right-full top-0 z-50 transition-all duration-500" aria-label="Navigasi mobile">
                                <div class="flex justify-between items-center">
                                    <a href="{{ route('guest.beranda') }}" class="font-bold text-2xl">{{ $brand }}</a>
                                    <button type="button" class="offcanvaClose bg-primary w-10 h-10 text-cream-foreground flex items-center justify-center rounded-[4px]" aria-label="Tutup menu">
                                        <i class="fa-solid fa-xmark text-xl"></i>
                                    </button>
                                </div>
                                <ul class="mt-6">
                                    @foreach([
                                        ['route' => 'guest.beranda', 'label' => 'Beranda'],
                                        ['route' => 'guest.tentang', 'label' => 'Tentang'],
                                        ['route' => 'guest.fasilitas', 'label' => 'Fitur'],
                                        ['route' => 'guest.kontak', 'label' => 'Kontak'],
                                    ] as $item)
                                    <li class="leading-[164%] relative w-full">
                                        <a href="{{ route($item['route']) }}" class="text-[#385469] font-jost hover:text-secondary-foreground transition-all duration-500 py-2.5 block border-b border-b-slate-300 {{ $navActive($item['route']) }}">{{ $item['label'] }}</a>
                                    </li>
                                    @endforeach
                                </ul>
                                <div class="mt-5">
                                    <h4 class="text-xl font-bold text-[#385469]">Kontak</h4>
                                    <ul class="mt-5 flex flex-col gap-[15px] text-sm">
                                        @if(!empty($cms['kontak_telepon']))
                                        <li><p><i class="fa-solid fa-phone text-primary-foreground"></i> <a href="{{ GuestWhatsApp::url(GuestWhatsApp::demoIntro()) }}" class="ml-2.5">{{ $cms['kontak_telepon'] }}</a></p></li>
                                        @endif
                                        @if(!empty($cms['kontak_email']))
                                        <li><p><i class="fa-solid fa-envelope text-primary-foreground"></i> <a href="mailto:{{ $cms['kontak_email'] }}" class="ml-2.5">{{ $cms['kontak_email'] }}</a></p></li>
                                        @endif
                                        @if(!empty($cms['kontak_alamat']))
                                        <li><p><i class="fa-solid fa-location-dot text-primary-foreground"></i> <span class="ml-2.5">{{ $cms['kontak_alamat'] }}</span></p></li>
                                        @endif
                                    </ul>
                                    <div class="mt-5 flex flex-col gap-3">
                                        <a href="{{ route('guest.kontak') }}" class="bg-primary text-cream-foreground rounded-md btn text-center">Hubungi Kami</a>
                                        <a href="{{ route('guest.daftar-sekolah') }}" class="border border-gray-200 rounded-md btn text-center">Daftar Sekolah</a>
                                    </div>
                                </div>
                            </nav>
                        </div>
                        <div class="flex items-center gap-4 sm:gap-5 shrink-0">
                            <div class="hidden sm:flex items-center gap-3 sm:gap-4 font-jost">
                                @auth
                                    <a href="{{ route('dashboard') }}" class="bg-primary text-cream-foreground rounded-md btn whitespace-nowrap">Dashboard</a>
                                @else
                                    <a href="{{ route('login') }}" class="border border-gray-200 rounded-md btn hover:text-cream-foreground whitespace-nowrap">Masuk</a>
                                    <div class="relative guest-daftar-dropdown">
                                        <button type="button" class="guest-daftar-toggle bg-secondary text-cream-foreground rounded-md btn whitespace-nowrap gap-1.5 inline-flex items-center after:bg-green" aria-expanded="false" aria-haspopup="true">
                                            <span>Daftar</span>
                                            <i class="fa-solid fa-angle-down text-sm guest-daftar-chevron transition-transform duration-500" aria-hidden="true"></i>
                                        </button>
                                        <ul class="guest-daftar-menu absolute top-full right-0 z-10 bg-background shadow-sm min-w-56 transition-all duration-500 opacity-0 invisible translate-y-5">
                                            <li>
                                                <a href="{{ route('guest.pendaftaran') }}" class="guest-daftar-link font-semibold font-jost hover:text-cream-foreground hover:bg-primary transition-all duration-500 py-3 px-2.5 block border-b border-b-slate-300">Ortu</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('guest.daftar-sekolah') }}" class="guest-daftar-link font-semibold font-jost hover:text-cream-foreground hover:bg-primary transition-all duration-500 py-3 px-2.5 block border-b border-b-slate-300">Sekolah</a>
                                            </li>
                                        </ul>
                                    </div>
                                @endauth
                            </div>
                            <div class="block xl:hidden">
                                <button type="button" class="offcanvaTragger flex flex-col items-end cursor-pointer transition-all duration-500 p-1" aria-label="Buka menu">
                                    <span class="block h-[3px] w-5 bg-muted"></span>
                                    <span class="block h-[3px] w-7.5 bg-muted mt-2"></span>
                                    <span class="block h-[3px] w-5 bg-muted mt-2"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
