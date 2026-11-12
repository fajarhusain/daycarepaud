@php
    use App\Support\GuestBrand;
    $brand = GuestBrand::name();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $brand }}  Masuk &amp; Pendaftaran</title>

    <!-- Google Fonts: EB Garamond & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400..800;1,400..800&family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <x-guest-favicon />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Scandinavian Auth & Form Elements */
        .auth-scandinavian .input-label,
        .auth-ascent .input-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.375rem;
            color: #2D3F35;
        }

        .auth-scandinavian .input-field,
        .auth-scandinavian select.input-field,
        .auth-scandinavian textarea.input-field,
        .auth-ascent .input-field,
        .auth-ascent select.input-field,
        .auth-ascent textarea.input-field {
            width: 100%;
            border-radius: 1rem;
            border: 1px solid #D5E2D1;
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            color: #2D3F35;
            background-color: #FAFAF7;
            transition: all 0.2s ease;
            outline: none;
        }

        .auth-scandinavian .input-field:focus,
        .auth-scandinavian select.input-field:focus,
        .auth-scandinavian textarea.input-field:focus,
        .auth-ascent .input-field:focus,
        .auth-ascent select.input-field:focus,
        .auth-ascent textarea.input-field:focus {
            background-color: #ffffff;
            border-color: #496C5C;
            box-shadow: 0 0 0 3px rgba(73, 108, 92, 0.15);
        }

        .auth-scandinavian .auth-field-wrap,
        .auth-ascent .auth-field-wrap {
            position: relative;
            width: 100%;
        }

        .auth-scandinavian .auth-field-wrap .input-field,
        .auth-ascent .auth-field-wrap .input-field {
            padding-right: 2.75rem;
        }

        .auth-scandinavian .auth-field-icon,
        .auth-ascent .auth-field-icon {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            color: #7B8F82;
            font-size: 0.95rem;
            line-height: 1;
        }

        .auth-scandinavian .auth-callout,
        .auth-ascent .auth-callout {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            margin-bottom: 1.25rem;
            padding: 0.875rem 1.125rem;
            border-radius: 1rem;
            font-size: 0.875rem;
            line-height: 1.5;
            border: 1px solid #D5E2D1;
        }

        .auth-scandinavian .auth-callout__icon,
        .auth-ascent .auth-callout__icon {
            flex-shrink: 0;
            margin-top: 0.125rem;
        }

        .auth-scandinavian .auth-callout--warm,
        .auth-ascent .auth-callout--warm {
            background: #E8F1E4;
            color: #2D3F35;
            border-color: #D5E2D1;
        }

        .auth-scandinavian .auth-callout--warm .auth-callout__icon,
        .auth-ascent .auth-callout--warm .auth-callout__icon {
            color: #496C5C;
        }

        .auth-scandinavian .auth-callout--muted,
        .auth-ascent .auth-callout--muted {
            background: #FFFFFF;
            color: #586B60;
            border-color: #D5E2D1;
        }

        .auth-scandinavian .auth-callout--danger,
        .auth-ascent .auth-callout--danger {
            background: #FFF1F2;
            color: #9F1239;
            border-color: #FECDD3;
        }

        .auth-scandinavian .auth-callout--danger .auth-callout__icon,
        .auth-ascent .auth-callout--danger .auth-callout__icon {
            color: #E11D48;
        }

        .auth-scandinavian .auth-callout--row,
        .auth-ascent .auth-callout--row {
            flex-direction: column;
            align-items: stretch;
            gap: 0.75rem;
        }

        @media (min-width: 640px) {

            .auth-scandinavian .auth-callout--row,
            .auth-ascent .auth-callout--row {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
        }

        .auth-scandinavian .auth-callout-link,
        .auth-ascent .auth-callout-link {
            font-weight: 600;
            color: #496C5C;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .auth-scandinavian .auth-callout-link:hover,
        .auth-ascent .auth-callout-link:hover {
            text-decoration: underline;
        }

        .auth-scandinavian .alert-success,
        .auth-ascent .alert-success {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.875rem 1.125rem;
            margin-bottom: 1.25rem;
            border-radius: 1rem;
            font-size: 0.875rem;
            background: #E8F1E4;
            color: #2D3F35;
            border: 1px solid #D5E2D1;
        }

        .auth-scandinavian .text-input-error,
        .auth-ascent .text-input-error {
            font-size: 0.75rem;
            color: #DC2626;
            margin-top: 0.25rem;
        }
    </style>
</head>

<body
    class="auth-scandinavian auth-ascent bg-canvas-cream font-sans text-text-primary min-h-screen selection:bg-forest-deep selection:text-white">
    <div class="container max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 sm:py-8">
        <!-- Top Bar Navigation -->
        <header class="flex items-center justify-between py-3 mb-6 sm:mb-10">
            <a href="{{ route('guest.beranda') }}" class="inline-flex items-center gap-3 group focus:outline-none">
                <img src="{{ asset('images/logo/logo.png') }}" alt="{{ $brand }}"
                    class="h-9 sm:h-10 w-auto object-contain transition-transform group-hover:scale-105"
                    onerror="this.style.display='none'">
                <span
                    class="font-serif text-xl sm:text-2xl font-bold text-forest-deep tracking-tight leading-none group-hover:text-forest-mid transition-colors">
                    {{ $brand }}
                </span>
            </a>
            <div class="flex items-center gap-2 sm:gap-3">
                <a href="{{ route('guest.beranda') }}"
                    class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-text-secondary hover:text-forest-deep transition-colors px-2 py-1">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                    <span class="hidden sm:inline">Kembali ke Beranda</span>
                    <span class="sm:hidden">Beranda</span>
                </a>
                <a href="{{ route('guest.kontak') }}"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 sm:px-4 sm:py-2 rounded-full bg-surface-mint text-forest-deep text-xs font-semibold hover:bg-surface-sage transition-colors">
                    <span class="material-symbols-outlined text-[16px]">support_agent</span>
                    <span>Bantuan</span>
                </a>
            </div>
        </header>

        <!-- Main Layout Grid -->
        <main class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start pb-12">
            <!-- Left Column: Visual & Reassurance (Desktop Sticky) -->
            <aside class="hidden lg:block lg:col-span-5 sticky top-8" aria-hidden="true">
                <div class="rounded-3xl bg-surface-mint/50 border border-border-subtle p-6 space-y-6">
                    <!-- Photo Card -->
                    <div class="relative rounded-2xl overflow-hidden aspect-[4/3] shadow-sm">
                        <img src="{{ asset('images/guest/scandinavian/hero-nature.jpg') }}" alt="Daycare &amp; PAUD"
                            class="w-full h-full object-cover">
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-forest-deep/60 via-transparent to-transparent">
                        </div>
                        <div class="absolute bottom-4 left-4 right-4">
                        </div>
                    </div>

                    <!-- Highlight Features -->
                    <div class="space-y-4">
                        <div class="flex items-start gap-3.5">
                            <div
                                class="w-9 h-9 rounded-xl bg-white flex items-center justify-center text-forest-deep shadow-sm shrink-0">
                                <span class="material-symbols-outlined text-[20px]">psychology_alt</span>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-text-primary">{{ $cms['auth_highlight_1_title'] }}</h3>
                                <p class="text-xs text-text-secondary mt-0.5 leading-relaxed">{{ $cms['auth_highlight_1_desc'] }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3.5">
                            <div
                                class="w-9 h-9 rounded-xl bg-white flex items-center justify-center text-forest-deep shadow-sm shrink-0">
                                <span class="material-symbols-outlined text-[20px]">monitor_heart</span>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-text-primary">{{ $cms['auth_highlight_2_title'] }}</h3>
                                <p class="text-xs text-text-secondary mt-0.5 leading-relaxed">{{ $cms['auth_highlight_2_desc'] }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3.5">
                            <div
                                class="w-9 h-9 rounded-xl bg-white flex items-center justify-center text-forest-deep shadow-sm shrink-0">
                                <span class="material-symbols-outlined text-[20px]">verified_user</span>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-text-primary">{{ $cms['auth_highlight_3_title'] }}</h3>
                                <p class="text-xs text-text-secondary mt-0.5 leading-relaxed">{{ $cms['auth_highlight_3_desc'] }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Warm Quote -->
                    <div class="p-4 rounded-2xl bg-white/80 border border-border-subtle/80 flex items-start gap-3">
                        <span
                            class="material-symbols-outlined text-forest-deep text-[22px] shrink-0">format_quote</span>
                        <p class="text-xs italic text-text-secondary leading-relaxed">
                            "{{ $cms['auth_quote'] }}"
                        </p>
                    </div>
                </div>
            </aside>

            <!-- Right Column: Form Container Card -->
            <div
                class="col-span-1 lg:col-span-7 w-full {{ ($maxWidth ?? 'max-w-md') === 'max-w-xl' ? 'max-w-2xl' : 'max-w-xl' }} mx-auto lg:max-w-none">
                <div
                    class="bg-white rounded-3xl border border-border-subtle shadow-[0_12px_40px_-8px_rgba(45,63,53,0.08)] p-6 sm:p-8 lg:p-10">
                    {{ $slot }}
                </div>
            </div>
        </main>
    </div>
</body>

</html>