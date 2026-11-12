@props(['cms' => [], 'title' => null, 'metaDesc' => '', 'canonical' => null])
@php
    use App\Support\GuestBrand;
    use App\Support\GuestSeo;
    use App\Support\GuestWhatsApp;
    $brand = GuestBrand::name();
    $pageTitle = $title ?? $brand;
    $pageDesc = $metaDesc ?: $brand.'  Sistem Informasi PAUD Terpadu untuk lembaga, admin sekolah, pengajar, dan orang tua.';
    $canonicalUrl = $canonical ?? url()->current();
@endphp
<!DOCTYPE html>
<html lang="id" class="guest-site scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <x-guest-seo-head :cms="$cms" :page-title="$pageTitle" :meta-description="$pageDesc" :canonical="$canonicalUrl" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @vite(['resources/css/guest.css', 'resources/js/guest.js'])
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="guest-body">
    <nav class="guest-nav" id="guest-nav" x-data="{ open: false }" aria-label="Navigasi utama">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 flex justify-between items-center gap-3 min-h-16 py-2">
            <a href="{{ route('guest.beranda') }}" class="flex items-center gap-2.5 min-w-0 cursor-pointer">
                <div class="h-9 w-9 rounded-full flex items-center justify-center shrink-0" style="background: var(--guest-sage);">
                    <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </div>
                <span class="text-lg sm:text-xl guest-heading" style="color: var(--guest-sage-dark);">{{ $brand }}</span>
            </a>

            <div class="hidden md:flex items-center gap-0.5 absolute left-1/2 -translate-x-1/2">
                <a href="{{ route('guest.beranda') }}" class="guest-nav-link {{ request()->routeIs('guest.beranda') ? 'active' : '' }}">Beranda</a>
                <a href="{{ route('guest.tentang') }}" class="guest-nav-link {{ request()->routeIs('guest.tentang') ? 'active' : '' }}">Tentang</a>
                <a href="{{ route('guest.fasilitas') }}" class="guest-nav-link {{ request()->routeIs('guest.fasilitas') ? 'active' : '' }}">Fitur</a>
                <!-- <a href="{{ route('guest.galeri') }}" class="guest-nav-link {{ request()->routeIs('guest.galeri') ? 'active' : '' }}">Galeri</a> -->
                <a href="{{ route('guest.kontak') }}" class="guest-nav-link {{ request()->routeIs('guest.kontak') ? 'active' : '' }}">Kontak</a>
            </div>

            <div class="hidden md:flex items-center gap-2">
                @auth
                    <a href="{{ route('dashboard') }}" class="guest-btn guest-btn-ghost text-sm py-2 px-4">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="guest-btn guest-btn-ghost text-sm py-2 px-4">Masuk</a>
                    <a href="{{ route('guest.daftar-sekolah') }}" class="guest-btn guest-btn-secondary text-sm py-2 px-4">Daftar Sekolah</a>
                @endauth
                <a href="{{ GuestWhatsApp::url(GuestWhatsApp::demoIntro()) }}" target="_blank" rel="noopener noreferrer" class="guest-btn guest-btn-primary text-sm py-2 px-5">Hubungi Kami</a>
            </div>

            <div class="md:hidden flex items-center gap-2 shrink-0 ml-auto">
                <button type="button" @click="open = !open" :aria-expanded="open" class="p-2 rounded-full cursor-pointer" style="color: var(--guest-sage-dark); background: var(--guest-sage-light);">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                        <path x-show="!open" stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        <path x-show="open" x-cloak stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <div x-show="open" x-transition x-cloak class="md:hidden border-t px-4 py-5 space-y-1 max-h-[min(70vh,calc(100dvh-4rem))] overflow-y-auto" style="border-color: var(--guest-border); background: var(--guest-bg);">
            <a href="{{ route('guest.beranda') }}" @click="open=false" class="guest-nav-link block text-base py-3">Beranda</a>
            <a href="{{ route('guest.tentang') }}" @click="open=false" class="guest-nav-link block text-base py-3">Tentang</a>
            <a href="{{ route('guest.fasilitas') }}" @click="open=false" class="guest-nav-link block text-base py-3">Fitur</a>
            <!-- <a href="{{ route('guest.galeri') }}" @click="open=false" class="guest-nav-link block text-base py-3">Galeri</a> -->
            <a href="{{ route('guest.kontak') }}" @click="open=false" class="guest-nav-link block text-base py-3">Kontak</a>
            <hr class="my-3" style="border-color: var(--guest-border);">
            @auth
                <a href="{{ route('dashboard') }}" @click="open=false" class="guest-btn guest-btn-ghost block text-center w-full">Dashboard</a>
            @else
                <a href="{{ route('login') }}" @click="open=false" class="guest-btn guest-btn-ghost block text-center w-full">Masuk</a>
                <a href="{{ route('guest.daftar-sekolah') }}" @click="open=false" class="guest-btn guest-btn-secondary block text-center w-full mt-2">Daftar Sekolah</a>
            @endauth
            <a href="{{ GuestWhatsApp::url(GuestWhatsApp::demoIntro()) }}" target="_blank" rel="noopener noreferrer" @click="open=false" class="guest-btn guest-btn-primary block text-center w-full mt-2">Hubungi Kami</a>
            <a href="{{ route('guest.pendaftaran') }}" @click="open=false" class="guest-btn guest-btn-secondary block text-center w-full mt-2">Pendaftaran Orang Tua</a>
        </div>
    </nav>

    <main>{{ $slot }}</main>

    <footer class="guest-footer">
        <div class="guest-footer-watermark">
            @include('guest.partials.doodles', ['variant' => 'footer'])
        </div>
        <div class="max-w-6xl mx-auto px-4 sm:px-6 relative z-10">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-10 pb-10 border-b" style="border-color: var(--guest-border);">
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <div class="h-9 w-9 rounded-full flex items-center justify-center" style="background: var(--guest-sage);">
                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                        </div>
                        <span class="text-lg sm:text-xl guest-heading text-[var(--guest-text)]">{{ $brand }}</span>
                    </div>
                    <p class="text-sm leading-relaxed">{{ $cms['footer_text'] ?? 'Platform manajemen PAUD terpadu untuk lembaga multi-cabang di Indonesia.' }}</p>
                </div>
                <div>
                    <h4 class="font-bold mb-3 text-sm text-[var(--guest-text)]">Menu</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('guest.beranda') }}" class="hover:text-[var(--guest-sage-dark)] transition-colors duration-200 cursor-pointer">Beranda</a></li>
                        <li><a href="{{ route('guest.tentang') }}" class="hover:text-[var(--guest-sage-dark)] transition-colors duration-200 cursor-pointer">Tentang</a></li>
                        <li><a href="{{ route('guest.fasilitas') }}" class="hover:text-[var(--guest-sage-dark)] transition-colors duration-200 cursor-pointer">Fitur</a></li>
                        <!-- <li><a href="{{ route('guest.galeri') }}" class="hover:text-[var(--guest-sage-dark)] transition-colors duration-200 cursor-pointer">Galeri</a></li> -->
                        <li><a href="{{ route('guest.kontak') }}" class="hover:text-[var(--guest-sage-dark)] transition-colors duration-200 cursor-pointer">Kontak</a></li>
                        <li><a href="{{ route('guest.daftar-sekolah') }}" class="hover:text-[var(--guest-sage-dark)] transition-colors duration-200 cursor-pointer">Daftar Sekolah</a></li>
                        <li><a href="{{ route('guest.pendaftaran') }}" class="hover:text-[var(--guest-sage-dark)] transition-colors duration-200 cursor-pointer">Pendaftaran Orang Tua</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold mb-3 text-sm text-[var(--guest-text)]">Kontak</h4>
                    <ul class="space-y-2 text-sm">
                        @if(!empty($cms['kontak_alamat']))<li>{{ $cms['kontak_alamat'] }}</li>@endif
                        @if(!empty($cms['kontak_telepon']))
                            <li><a href="{{ GuestWhatsApp::url(GuestWhatsApp::demoIntro()) }}" target="_blank" rel="noopener noreferrer" class="hover:text-[var(--guest-sage-dark)] transition-colors">{{ $cms['kontak_telepon'] }}</a></li>
                        @endif
                        @if(!empty($cms['kontak_email']))
                            <li><a href="mailto:{{ $cms['kontak_email'] }}" class="hover:text-[var(--guest-sage-dark)] transition-colors">{{ $cms['kontak_email'] }}</a></li>
                        @endif
                        @if(!empty($cms['kontak_jam']))<li>{{ $cms['kontak_jam'] }}</li>@endif
                    </ul>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-xs mt-6">
                <div class="text-center sm:text-left">
                    <p>© {{ date('Y') }} {{ $brand }}</p>
                    <p class="mt-1 opacity-80">Illustrations by <a href="https://storyset.com/education" target="_blank" rel="noopener noreferrer" class="underline hover:text-[var(--guest-sage-dark)]">Storyset</a></p>
                </div>
                <div class="flex flex-col sm:flex-row items-center gap-3 sm:gap-6">
                    <a href="{{ route('guest.daftar-sekolah') }}" class="font-semibold hover:text-[var(--guest-sage-dark)] transition-colors cursor-pointer" style="color: var(--guest-sage);">Daftar Sekolah →</a>
                    <a href="{{ route('guest.pendaftaran') }}" class="font-semibold hover:text-[var(--guest-sage-dark)] transition-colors cursor-pointer" style="color: var(--guest-sage);">Pendaftaran Orang Tua →</a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
