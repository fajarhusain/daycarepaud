@props(['cms' => [], 'title' => 'Beranda', 'metaDesc' => '', 'canonical' => null])
@php
    use App\Support\GuestBrand;
    use App\Support\GuestSeo;
    use App\Support\GuestWhatsApp;
    $brand = GuestBrand::name();
    $isHome = request()->routeIs('guest.beranda');
    if ($isHome) {
        $seo = GuestSeo::forHome($cms);
        $jsonLd = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Organization',
                    'name' => $brand,
                    'url' => url('/'),
                    'logo' => asset('images/logo/logo.png'),
                    'description' => $cms['footer_text'] ?? '',
                    'email' => $cms['kontak_email'] ?? null,
                    'telephone' => $cms['kontak_telepon'] ?? null,
                    'address' => isset($cms['kontak_alamat']) ? ['@type' => 'PostalAddress', 'streetAddress' => $cms['kontak_alamat']] : null,
                ],
                [
                    '@type' => 'WebSite',
                    'name' => $brand,
                    'url' => url('/'),
                    'inLanguage' => 'id-ID',
                ],
                [
                    '@type' => 'EducationalOrganization',
                    'name' => $brand,
                    'description' => $seo['description'] ?? '',
                ],
            ],
        ];
    } else {
        $homeSeo = GuestSeo::forHome($cms);
        $pageTitle = $title;
        $description = trim($metaDesc) !== ''
            ? $metaDesc
            : $brand . '  Daycare, PAUD & Taman Kanak-Kanak Terpadu.';
        $seo = GuestSeo::forPage(
            $pageTitle,
            $description,
            $canonical ?? url()->current(),
            $homeSeo['og_image_url'],
        );
        $jsonLd = null;
    }
@endphp
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <x-guest-seo-head :cms="$cms" :page-title="$seo['title']" :meta-description="$seo['description']"
        :canonical="$seo['canonical']" :og-image-url="$seo['og_image_url']" :json-ld="$jsonLd" />

    <!-- Fonts: EB Garamond & Plus Jakarta Sans + Material Symbols -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400..800;1,400..800&family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap"
        rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }

        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
            line-height: 1;
        }

        .material-symbols-outlined.filled {
            font-variation-settings: 'FILL' 1;
        }
    </style>
</head>

<body
    class="bg-canvas-cream font-sans text-text-primary antialiased min-h-screen flex flex-col selection:bg-surface-sage selection:text-forest-deep"
    x-data="{ mobileOpen: false }">

    <!-- STICKY SCANDINAVIAN HEADER -->
    <header
        class="fixed top-0 left-0 right-0 z-50 bg-canvas-cream/90 backdrop-blur-md border-b border-border-subtle/50 shadow-[0_1px_8px_rgba(45,63,53,0.03)] transition-all duration-200">
        <div class="h-20 max-w-7xl mx-auto px-5 sm:px-8 lg:px-12 flex items-center justify-between gap-4">

            <!-- Brand Logo -->
            <a href="{{ route('guest.beranda') }}" class="flex items-center gap-3 group focus:outline-none">
                <img src="{{ asset('images/logo/logo.png') }}" alt="{{ $brand }}"
                    class="h-9 w-auto object-contain transition-transform group-hover:scale-105"
                    onerror="this.style.display='none'">
                <div class="flex flex-col">
                    <span
                        class="font-serif text-xl sm:text-2xl font-semibold text-text-primary tracking-tight leading-none group-hover:text-forest-deep transition-colors">
                        {{ $brand }}
                    </span>
                </div>
            </a>

            <!-- Desktop Nav Pill -->
            <nav class="hidden lg:flex items-center gap-1 p-1.5 rounded-full bg-surface-mint/60 border border-border-subtle/60"
                aria-label="Navigasi Utama">
                <a href="{{ route('guest.beranda') }}"
                    class="px-4 py-2 text-sm rounded-full transition-all duration-200 {{ request()->routeIs('guest.beranda') ? 'bg-forest-deep text-white font-semibold shadow-sm' : 'text-text-secondary hover:text-text-primary hover:bg-surface-mint' }}">
                    Beranda
                </a>
                <a href="{{ route('guest.tentang') }}"
                    class="px-4 py-2 text-sm rounded-full transition-all duration-200 {{ request()->routeIs('guest.tentang') ? 'bg-forest-deep text-white font-semibold shadow-sm' : 'text-text-secondary hover:text-text-primary hover:bg-surface-mint' }}">
                    Tentang
                </a>
                <a href="{{ route('guest.fasilitas') }}"
                    class="px-4 py-2 text-sm rounded-full transition-all duration-200 {{ request()->routeIs('guest.fasilitas') ? 'bg-forest-deep text-white font-semibold shadow-sm' : 'text-text-secondary hover:text-text-primary hover:bg-surface-mint' }}">
                    {{ $cms['nav_label_features'] ?? 'Fitur' }}
                </a>
                <a href="{{ route('guest.harga') }}"
                    class="px-4 py-2 text-sm rounded-full transition-all duration-200 {{ request()->routeIs('guest.harga') ? 'bg-forest-deep text-white font-semibold shadow-sm' : 'text-text-secondary hover:text-text-primary hover:bg-surface-mint' }}">
                    Harga
                </a>
                <a href="{{ route('guest.kontak') }}"
                    class="px-4 py-2 text-sm rounded-full transition-all duration-200 {{ request()->routeIs('guest.kontak') ? 'bg-forest-deep text-white font-semibold shadow-sm' : 'text-text-secondary hover:text-text-primary hover:bg-surface-mint' }}">
                    Kontak
                </a>
            </nav>

            <!-- Actions / Auth -->
            <div class="flex items-center gap-2.5 sm:gap-3.5">
                @auth
                    <a href="{{ route('dashboard') }}"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-forest-deep text-white text-xs sm:text-sm font-semibold hover:bg-primary transition-all shadow-sm">
                        <span class="material-symbols-outlined text-[18px]">dashboard</span>
                        <span>Dashboard</span>
                    </a>
                @else
                    <a href="{{ route('login') }}"
                        class="hidden sm:inline-flex items-center px-4 py-2 rounded-full text-forest-deep text-xs sm:text-sm font-semibold hover:bg-surface-mint transition-colors">
                        Masuk
                    </a>

                    <!-- Dropdown Daftar -->
                    <div class="relative" x-data="{ daftarOpen: false }">
                        <button type="button" @click="daftarOpen = !daftarOpen" @click.outside="daftarOpen = false"
                            @keydown.escape.window="daftarOpen = false"
                            class="inline-flex items-center gap-1.5 px-4 sm:px-5 py-2 rounded-full bg-forest-deep text-white text-xs sm:text-sm font-semibold hover:bg-forest-mid transition-all shadow-[0_2px_8px_-2px_rgba(45,63,53,0.18)] hover:-translate-y-0.5 focus:outline-none"
                            :aria-expanded="daftarOpen">
                            <span>Daftar</span>
                            <span class="material-symbols-outlined text-[18px] transition-transform duration-200"
                                :class="daftarOpen ? 'rotate-180' : ''">keyboard_arrow_down</span>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-show="daftarOpen" x-cloak x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                            x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                            class="absolute right-0 top-full mt-2 w-64 rounded-2xl bg-white border border-border-subtle shadow-xl p-2 z-50">
                            <a href="{{ route('guest.pendaftaran') }}" @click="daftarOpen = false"
                                class="flex items-start gap-3 p-3 rounded-xl transition-colors hover:bg-surface-mint/80 group">
                                <div>
                                    <div class="font-semibold text-sm text-text-primary">Daftar Siswa / Anak</div>
                                </div>
                            </a>
                            <a href="{{ route('guest.daftar-sekolah') }}" @click="daftarOpen = false"
                                class="flex items-start gap-3 p-3 rounded-xl transition-colors hover:bg-surface-mint/80 group">
                                <div>
                                    <div class="font-semibold text-sm text-text-primary">Daftar Sekolah</div>
                                </div>
                            </a>
                        </div>
                    </div>
                @endauth

                <!-- Mobile Menu Button -->
                <button type="button" @click="mobileOpen = !mobileOpen"
                    class="lg:hidden p-2 rounded-full bg-surface-mint text-forest-deep hover:bg-surface-sage transition-colors focus:outline-none"
                    aria-label="Toggle Menu">
                    <span class="material-symbols-outlined text-[24px]" x-show="!mobileOpen">menu</span>
                    <span class="material-symbols-outlined text-[24px]" x-show="mobileOpen" x-cloak>close</span>
                </button>
            </div>
        </div>

        <!-- Mobile Drawer -->
        <div x-show="mobileOpen" x-cloak x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="lg:hidden border-b border-border-subtle bg-canvas-cream/95 backdrop-blur-md px-6 py-5 shadow-lg space-y-3">
            <nav class="flex flex-col gap-1.5">
                <a href="{{ route('guest.beranda') }}" @click="mobileOpen = false"
                    class="px-4 py-2.5 rounded-xl text-base font-semibold {{ request()->routeIs('guest.beranda') ? 'bg-forest-deep text-white' : 'text-text-primary hover:bg-surface-mint' }}">
                    Beranda
                </a>
                <a href="{{ route('guest.tentang') }}" @click="mobileOpen = false"
                    class="px-4 py-2.5 rounded-xl text-base font-semibold {{ request()->routeIs('guest.tentang') ? 'bg-forest-deep text-white' : 'text-text-primary hover:bg-surface-mint' }}">
                    Tentang
                </a>
                <a href="{{ route('guest.fasilitas') }}" @click="mobileOpen = false"
                    class="px-4 py-2.5 rounded-xl text-base font-semibold {{ request()->routeIs('guest.fasilitas') ? 'bg-forest-deep text-white' : 'text-text-primary hover:bg-surface-mint' }}">
                    {{ $cms['nav_label_features'] ?? 'Fitur' }}
                </a>
                <a href="{{ route('guest.harga') }}" @click="mobileOpen = false"
                    class="px-4 py-2.5 rounded-xl text-base font-semibold {{ request()->routeIs('guest.harga') ? 'bg-forest-deep text-white' : 'text-text-primary hover:bg-surface-mint' }}">
                    Harga
                </a>
                <a href="{{ route('guest.kontak') }}" @click="mobileOpen = false"
                    class="px-4 py-2.5 rounded-xl text-base font-semibold {{ request()->routeIs('guest.kontak') ? 'bg-forest-deep text-white' : 'text-text-primary hover:bg-surface-mint' }}">
                    Kontak
                </a>
            </nav>
            <div class="pt-3 border-t border-border-subtle/80 flex flex-col gap-2">
                @auth
                    <a href="{{ route('dashboard') }}"
                        class="w-full py-2.5 text-center rounded-xl bg-forest-deep text-white font-semibold">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" @click="mobileOpen = false"
                        class="w-full py-2.5 text-center rounded-xl bg-surface-mint text-forest-deep font-semibold">
                        Masuk
                    </a>
                    <div x-data="{ mobileDaftarOpen: false }" class="space-y-1.5">
                        <button type="button" @click="mobileDaftarOpen = !mobileDaftarOpen"
                            class="w-full py-2.5 px-4 flex items-center justify-between rounded-xl bg-forest-deep text-white font-semibold">
                            <span>Daftar</span>
                            <span class="material-symbols-outlined text-[20px] transition-transform duration-200"
                                :class="mobileDaftarOpen ? 'rotate-180' : ''">keyboard_arrow_down</span>
                        </button>
                        <div x-show="mobileDaftarOpen" x-cloak class="pl-2 space-y-1.5 pt-1">
                            <a href="{{ route('guest.pendaftaran') }}" @click="mobileOpen = false"
                                class="flex items-center gap-3 p-3 rounded-xl bg-surface-mint/70 text-forest-deep hover:bg-surface-mint transition-colors">
                                <span class="material-symbols-outlined text-[20px]">child_care</span>
                                <div>
                                    <div class="font-semibold text-sm">Daftar Siswa / Anak</div>
                                    <div class="text-xs text-text-secondary">Pendaftaran murid baru daycare</div>
                                </div>
                            </a>
                            <a href="{{ route('guest.daftar-sekolah') }}" @click="mobileOpen = false"
                                class="flex items-center gap-3 p-3 rounded-xl bg-surface-mint/70 text-forest-deep hover:bg-surface-mint transition-colors">
                                <span class="material-symbols-outlined text-[20px]">school</span>
                                <div>
                                    <div class="font-semibold text-sm">Daftar Sekolah</div>
                                    <div class="text-xs text-text-secondary">Registrasi lembaga sekolah baru</div>
                                </div>
                            </a>
                        </div>
                    </div>
                @endauth
            </div>
        </div>
    </header>

    <!-- MAIN CONTENT -->
    <main class="w-full pt-20 flex-1 bg-canvas-cream">
        {{ $slot }}
    </main>

    <!-- SCANDINAVIAN FOOTER -->
    <footer class="w-full bg-surface-mint text-text-primary pt-16 pb-12 border-t border-border-subtle/40">
        <div class="max-w-7xl mx-auto px-6 lg:px-12">
            <div
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-10 lg:gap-8 pb-12 border-b border-border-subtle/60">
                <!-- Brand -->
                <div class="sm:col-span-2 lg:col-span-4 flex flex-col gap-4">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/logo/logo.png') }}" alt="{{ $brand }}"
                            class="h-9 w-auto object-contain" onerror="this.style.display='none'">
                        <span class="font-serif text-2xl font-semibold text-text-primary leading-none">{{ $brand }}</span>
                    </div>
                    <p class="text-sm text-text-secondary max-w-md leading-relaxed">
                        {{ $cms['footer_text'] }}
                    </p>
                </div>

                <!-- Navigasi -->
                <div class="lg:col-span-2">
                    <p class="text-base font-semibold text-text-primary mb-4">Navigasi</p>
                    <ul class="space-y-2.5 text-sm text-text-secondary">
                        <li><a href="{{ route('guest.beranda') }}"
                                class="hover:text-forest-deep transition-colors">Beranda</a></li>
                        <li><a href="{{ route('guest.tentang') }}"
                                class="hover:text-forest-deep transition-colors">Tentang Kami</a></li>
                        <li><a href="{{ route('guest.fasilitas') }}"
                                class="hover:text-forest-deep transition-colors">{{ $cms['nav_label_features'] ?? 'Fitur' }}</a></li>
                        <li><a href="{{ route('guest.harga') }}"
                                class="hover:text-forest-deep transition-colors">Harga</a></li>
                        <li><a href="{{ route('guest.kontak') }}"
                                class="hover:text-forest-deep transition-colors">Hubungi Kami</a></li>
                    </ul>
                </div>

                <!-- Kontak -->
                <div class="sm:col-span-2 lg:col-span-6 flex flex-col gap-3">
                    <p class="text-base font-semibold text-text-primary mb-1">Kontak</p>
                    @if(!empty($cms['kontak_alamat']))
                        <p class="text-sm text-text-secondary flex items-start gap-2">
                            <span
                                class="material-symbols-outlined text-[18px] text-forest-deep mt-0.5 shrink-0">location_on</span>
                            <span>{{ $cms['kontak_alamat'] }}</span>
                        </p>
                    @endif
                    @if(!empty($cms['kontak_telepon']))
                        <p class="text-sm text-text-secondary flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px] text-forest-deep shrink-0">call</span>
                            <a href="{{ GuestWhatsApp::url(GuestWhatsApp::demoIntro()) }}" target="_blank"
                                rel="noopener noreferrer" class="hover:text-forest-deep transition-colors">
                                {{ $cms['kontak_telepon'] }}
                            </a>
                        </p>
                    @endif
                    @if(!empty($cms['kontak_email']))
                        <p class="text-sm text-text-secondary flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px] text-forest-deep shrink-0">mail</span>
                            <a href="mailto:{{ $cms['kontak_email'] }}" class="hover:text-forest-deep transition-colors">
                                {{ $cms['kontak_email'] }}
                            </a>
                        </p>
                    @endif
                    <div class="pt-2">
                        <a href="{{ request()->routeIs('guest.beranda') ? '#tour-section' : route('guest.kontak') }}"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full bg-forest-deep text-white text-xs font-semibold hover:bg-primary transition-all shadow-sm">
                            <span class="material-symbols-outlined text-[16px]">calendar_month</span>
                            <span>Minta jadwal demo</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Bottom Copyright -->
            <div class="pt-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 text-xs text-text-secondary">
                <p class="text-center sm:text-left">&copy; {{ date('Y') }} {{ $brand }}. Hak cipta dilindungi.</p>
                <div class="flex flex-wrap items-center justify-center sm:justify-end gap-x-6 gap-y-2">
                    <a href="{{ route('guest.tentang') }}" class="hover:text-forest-deep transition-colors">Kebijakan Privasi</a>
                    <a href="{{ route('guest.kontak') }}" class="hover:text-forest-deep transition-colors">Bantuan &amp; FAQ</a>
                    <a href="{{ route('login') }}" class="hover:text-forest-deep transition-colors">Portal Masuk</a>
                </div>
            </div>
        </div>
    </footer>

</body>

</html>