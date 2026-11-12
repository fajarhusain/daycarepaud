@php
    $brand = \App\Support\GuestBrand::name();
    $pageSeo = \App\Support\GuestSeo::forInnerPage(
        $cms,
        'kontak',
        'Hubungi Kami',
        route('guest.kontak'),
        $cms['seo_kontak_description'] ?? '',
    );
    $phone = $cms['kontak_telepon'] ?? \App\Support\GuestWhatsApp::DISPLAY;
    $email = $cms['kontak_email'] ?? '';
    $address = $cms['kontak_alamat'] ?? '';
@endphp

<x-guest-scandinavian :cms="$cms" title="Kontak" :metaDesc="$pageSeo['description']" :canonical="route('guest.kontak')">

    <!-- ==========================================
         PAGE HERO
         ========================================== -->
    <section
        class="w-full bg-canvas-cream pt-10 pb-12 lg:pt-14 lg:pb-16 border-b border-border-subtle/50 relative overflow-hidden"
        aria-labelledby="contact-hero-heading">
        <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-12 relative z-10">
            <!-- Breadcrumb Pill -->
            <nav class="flex items-center gap-2 mb-4 text-xs font-semibold" aria-label="Breadcrumb">
                <a href="{{ route('guest.beranda') }}"
                    class="text-text-secondary hover:text-forest-deep transition-colors">Beranda</a>
                <span class="text-text-secondary/60">/</span>
                <span class="text-forest-deep" aria-current="page">Kontak</span>
            </nav>

            <div class="max-w-3xl">
                <h1 id="contact-hero-heading"
                    class="font-serif text-3xl sm:text-4xl lg:text-5xl font-medium text-text-primary leading-tight mb-3">
                    {{ $pageSeo['h1'] }}
                </h1>
                <p class="text-sm sm:text-base text-text-secondary leading-relaxed">
                    {{ $cms['page_kontak_hero_lead'] }}
                </p>
            </div>
        </div>
    </section>

    <!-- ==========================================
         CONTACT INFO CARDS
         ========================================== -->
    <section class="w-full bg-canvas-cream py-12 lg:py-16" aria-labelledby="contact-info-heading">
        <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-12">
            <h2 id="contact-info-heading" class="sr-only">Informasi Kontak</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Address Card -->
                @if($address)
                    <div
                        class="bg-surface-card rounded-3xl p-8 border border-border-subtle/60 shadow-sm flex flex-col items-start hover:shadow-md transition-shadow">
                        <div
                            class="w-14 h-14 rounded-2xl bg-surface-mint flex items-center justify-center text-forest-deep mb-5">
                            <span class="material-symbols-outlined text-[28px]">location_on</span>
                        </div>
                        <h3 class="font-serif text-xl font-semibold text-text-primary mb-2">Alamat</h3>
                        <p class="text-xs sm:text-sm text-text-secondary leading-relaxed">
                            {{ $address }}
                        </p>
                    </div>
                @endif

                <!-- Email Card -->
                @if($email)
                    <div
                        class="bg-surface-card rounded-3xl p-8 border border-border-subtle/60 shadow-sm flex flex-col items-start hover:shadow-md transition-shadow">
                        <div
                            class="w-14 h-14 rounded-2xl bg-surface-mint flex items-center justify-center text-forest-deep mb-5">
                            <span class="material-symbols-outlined text-[28px]">mail</span>
                        </div>
                        <h3 class="font-serif text-xl font-semibold text-text-primary mb-2">Email</h3>
                        <p class="text-xs sm:text-sm text-text-secondary leading-relaxed mb-4">
                            Kirimkan pertanyaan resmi atau proposal kemitraan via email.
                        </p>
                        <a href="mailto:{{ $email }}"
                            class="text-sm font-semibold text-forest-deep hover:underline break-all mt-auto">
                            {{ $email }}
                        </a>
                    </div>
                @endif

                <!-- WhatsApp Card -->
                @if($phone)
                    <div
                        class="bg-surface-card rounded-3xl p-8 border border-border-subtle/60 shadow-sm flex flex-col items-start hover:shadow-md transition-shadow">
                        <div
                            class="w-14 h-14 rounded-2xl bg-surface-mint flex items-center justify-center text-forest-deep mb-5">
                            <span class="material-symbols-outlined text-[28px]">call</span>
                        </div>
                        <h3 class="font-serif text-xl font-semibold text-text-primary mb-2">WhatsApp &amp; Telepon</h3>
                        <p class="text-xs sm:text-sm text-text-secondary leading-relaxed mb-4">
                            Konsultasi langsung bersama tim admin kami pada jam kerja.
                        </p>
                        <a href="{{ \App\Support\GuestWhatsApp::url(\App\Support\GuestWhatsApp::demoIntro()) }}"
                            target="_blank" rel="noopener noreferrer"
                            class="text-sm font-semibold text-forest-deep hover:underline mt-auto">
                            {{ $phone }}
                        </a>
                    </div>
                @endif
            </div>

        </div>
    </section>

    <!-- ROLLING HILL DIVIDER (Cream to Mint) -->
    <div class="w-full overflow-hidden leading-none -mt-1 text-surface-mint bg-canvas-cream">
        <svg class="w-full h-12 lg:h-20" fill="none" preserveAspectRatio="none" viewBox="0 0 1440 120"
            xmlns="http://www.w3.org/2000/svg">
            <path d="M0,40 C320,110 480,10 780,60 C1080,110 1260,20 1440,50 L1440,120 L0,120 Z" fill="currentColor">
            </path>
        </svg>
    </div>

    <!-- ==========================================
         CONTACT FORM SECTION
         ========================================== -->
    <section class="w-full bg-surface-mint py-16 lg:py-24" aria-labelledby="contact-form-heading">
        <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-12">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-center">

                <!-- Left Column: Friendly Reassurance & Times -->
                <div class="lg:col-span-5 flex flex-col gap-6">
                    <div>
                        <span
                            class="inline-block px-3.5 py-1 rounded-full bg-surface-sage/50 text-forest-deep text-xs font-semibold uppercase tracking-wider mb-2">
                            Pesan Terbuka
                        </span>
                        <h2 id="contact-form-heading"
                            class="font-serif text-3xl sm:text-4xl text-text-primary font-medium leading-tight mb-3">
                            {{ $cms['contact_form_h2'] ?? 'Minta Demo & Penawaran' }}
                        </h2>
                        <p class="text-sm text-text-secondary leading-relaxed">
                            {{ $cms['contact_form_lead'] }}
                        </p>
                    </div>

                    <div
                        class="bg-canvas-cream rounded-3xl p-6 shadow-sm border border-border-subtle/60 flex flex-col gap-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-full bg-surface-mint flex items-center justify-center text-forest-deep">
                                <span class="material-symbols-outlined text-[20px]">schedule</span>
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold text-text-primary">Jam Operasional Layanan</h4>
                                <p class="text-xs text-text-secondary">Senin – Jumat: 08:00 – 16:30 WIB</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-full bg-surface-mint flex items-center justify-center text-forest-deep">
                                <span class="material-symbols-outlined text-[20px]">verified</span>
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold text-text-primary">Respons Cepat</h4>
                                <p class="text-xs text-text-secondary">Pesan diteruskan langsung ke nomor WhatsApp tim
                                    kami</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-full bg-surface-mint flex items-center justify-center text-forest-deep">
                                <span class="material-symbols-outlined text-[20px]">verified</span>
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold text-text-primary">Alamat</h4>
                                <p class="text-xs text-text-secondary">{{ $address }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Interactive Form -->
                <div class="lg:col-span-7">
                    <div class="bg-surface-card rounded-3xl p-8 lg:p-10 shadow-lg border border-border-subtle/60">
                        <h3 class="font-serif text-2xl font-semibold text-text-primary mb-2">Tulis Pesan Anda</h3>
                        <p class="text-xs sm:text-sm text-text-secondary mb-6">Lengkapi data berikut untuk terhubung
                            langsung dengan konsultan kami.</p>

                        <form method="POST" action="{{ route('guest.kontak.send') }}" class="space-y-4">
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="kontak-nama"
                                        class="block text-xs font-semibold text-text-primary mb-1.5 uppercase tracking-wide">Nama
                                        Lengkap</label>
                                    <input type="text" name="nama" id="kontak-nama" value="{{ old('nama') }}" required
                                        placeholder="Nama Anda"
                                        class="w-full px-4 py-3 rounded-2xl bg-canvas-cream text-text-primary placeholder:text-text-secondary/40 text-sm focus:outline-none focus:ring-2 focus:ring-forest-deep shadow-sm border border-border-subtle">
                                    @error('nama')
                                    <p class="text-xs mt-1 text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label for="kontak-email"
                                        class="block text-xs font-semibold text-text-primary mb-1.5 uppercase tracking-wide">Alamat
                                        Email</label>
                                    <input type="email" name="email" id="kontak-email" value="{{ old('email') }}"
                                        required placeholder="nama@domain.com"
                                        class="w-full px-4 py-3 rounded-2xl bg-canvas-cream text-text-primary placeholder:text-text-secondary/40 text-sm focus:outline-none focus:ring-2 focus:ring-forest-deep shadow-sm border border-border-subtle">
                                    @error('email')
                                    <p class="text-xs mt-1 text-red-600">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            <div>
                                <label for="kontak-pesan"
                                    class="block text-xs font-semibold text-text-primary mb-1.5 uppercase tracking-wide">Isi
                                    Pesan / Pertanyaan</label>
                                <textarea name="pesan" id="kontak-pesan" required rows="4"
                                    placeholder="Ceritakan kebutuhan PAUD atau pertanyaan Anda..."
                                    class="w-full px-4 py-3 rounded-2xl bg-canvas-cream text-text-primary placeholder:text-text-secondary/40 text-sm focus:outline-none focus:ring-2 focus:ring-forest-deep shadow-sm border border-border-subtle">{{ old('pesan') }}</textarea>
                                @error('pesan')
                                <p class="text-xs mt-1 text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div class="pt-2">
                                <button type="submit"
                                    class="w-full py-3.5 px-6 rounded-full bg-forest-deep text-white text-sm font-semibold hover:bg-primary transition-all shadow-md flex items-center justify-center gap-2 cursor-pointer">
                                    <span class="material-symbols-outlined text-[18px]">send</span>
                                    <span>Kirimkan Pesan Sekarang</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ROLLING HILL DIVIDER (Mint to Cream) -->
    <div class="w-full overflow-hidden leading-none -mt-1 text-canvas-cream bg-surface-mint">
        <svg class="w-full h-12 lg:h-20" fill="none" preserveAspectRatio="none" viewBox="0 0 1440 100"
            xmlns="http://www.w3.org/2000/svg">
            <path d="M0,60 C380,10 650,85 960,35 C1200,-5 1360,70 1440,40 L1440,100 L0,100 Z" fill="currentColor">
            </path>
        </svg>
    </div>

    <!-- ==========================================
         CTA BANNER
         ========================================== -->
    <section class="w-full bg-canvas-cream py-16 lg:py-20" aria-labelledby="contact-cta-heading">
        <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-12">
            <div
                class="bg-surface-mint rounded-3xl p-8 lg:p-14 shadow-md border border-surface-sage/50 flex flex-col md:flex-row items-center justify-between gap-8">
                <div class="max-w-xl">
                    <span class="text-xs font-semibold text-forest-deep uppercase tracking-wider block mb-2">{{ $cms['kontak_cta_eyebrow'] }}</span>
                    <h2 id="contact-cta-heading"
                        class="font-serif text-2xl sm:text-3xl lg:text-4xl text-text-primary font-medium leading-tight mb-3">
                        {{ $cms['kontak_cta_title'] }}
                    </h2>
                    <p class="text-sm text-text-secondary leading-relaxed">
                        {{ $cms['kontak_cta_body'] }}
                    </p>
                </div>
                <div class="flex flex-wrap gap-3 shrink-0">
                    <a href="{{ route('guest.daftar-sekolah') }}"
                        class="inline-flex items-center gap-2 px-6 py-3.5 rounded-full bg-forest-deep text-white text-sm font-semibold hover:bg-primary transition-all shadow-sm">
                        <span>Daftar Sekolah Sekarang</span>
                        <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

</x-guest-scandinavian>