@php
    $status = (int) ($status ?? $exception?->getStatusCode() ?? 500);

    $statusContent = [
        400 => [
            'title' => 'Ada yang perlu diperiksa',
            'message' => 'Permintaan Anda belum dapat dilanjutkan. Silakan kembali dan periksa data yang diberikan.',
        ],
        401 => [
            'title' => 'Silakan masuk terlebih dahulu',
            'message' => 'Demi keamanan akun, silakan masuk untuk melanjutkan.',
        ],
        402 => [
            'title' => 'Fitur belum dapat digunakan',
            'message' => 'Fitur ini belum tersedia untuk akun Anda saat ini.',
        ],
        403 => [
            'title' => 'Halaman ini belum dapat dibuka',
            'message' => 'Akun Anda belum memiliki akses ke halaman tersebut.',
        ],
        404 => [
            'title' => 'Halaman tidak ditemukan',
            'message' => 'Halaman mungkin sudah dipindahkan atau tidak lagi tersedia.',
        ],
        405 => [
            'title' => 'Tindakan belum dapat dilakukan',
            'message' => 'Silakan kembali dan lakukan tindakan melalui menu yang tersedia.',
        ],
        419 => [
            'title' => 'Waktu masuk telah berakhir',
            'message' => 'Demi keamanan, silakan masuk kembali untuk melanjutkan.',
        ],
        422 => [
            'title' => 'Periksa kembali data Anda',
            'message' => 'Ada data yang belum sesuai atau masih perlu dilengkapi.',
        ],
        429 => [
            'title' => 'Mohon tunggu sebentar',
            'message' => 'Tindakan dilakukan beberapa kali dalam waktu singkat. Silakan coba kembali beberapa saat lagi.',
        ],
        500 => [
            'title' => 'Kami sedang memperbaiki kendala',
            'message' => 'Halaman belum dapat dibuka saat ini. Silakan coba kembali beberapa saat lagi.',
        ],
        503 => [
            'title' => 'Aplikasi sedang disiapkan kembali',
            'message' => 'Layanan sedang dirawat agar dapat digunakan dengan baik.',
        ],
    ];

    $fallback = $status >= 500
        ? [
            'title' => 'Kami sedang memperbaiki kendala',
            'message' => 'Halaman belum dapat dibuka saat ini. Silakan coba kembali beberapa saat lagi.',
        ]
        : [
            'title' => 'Halaman belum dapat dibuka',
            'message' => 'Silakan kembali dan coba melalui menu yang tersedia.',
        ];

    $content = $statusContent[$status] ?? $fallback;
    $brand = \App\Support\GuestBrand::name();

    $homeUrl = route('guest.beranda');
    $loginUrl = route('login');
    $primaryUrl = in_array($status, [401, 419], true) ? $loginUrl : $homeUrl;
    $primaryLabel = in_array($status, [401, 419], true) ? 'Masuk kembali' : 'Buka halaman utama';
    $currentUrl = request()->fullUrl();
    $previousUrl = url()->previous();
    $previousHost = parse_url($previousUrl, PHP_URL_HOST);
    $canGoBack = $previousUrl !== $currentUrl
        && rtrim($previousUrl, '/') !== rtrim($homeUrl, '/')
        && ($previousHost === null || $previousHost === request()->getHost());
@endphp

{{--
    Design rationale:
    - Cream and teal preserve the product's established visual identity.
    - DM Sans matches the authenticated application and keeps operational copy readable.
    - One elevated panel creates a single focal point without depending on the database-heavy app layout.
    - The large status number gives each response a clear visual identity.
    - Short copy and two actions keep the interruption easy to understand.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $content['title'] }} | {{ $brand }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">
    @vite('resources/css/app.css')
</head>
<body class="min-h-screen overflow-x-hidden bg-[#F5F0E8] font-sans text-[#2C2C2C] antialiased">
    <main class="flex min-h-screen items-center px-4 py-8 sm:px-6 sm:py-12 lg:px-8">
        <section class="mx-auto w-full max-w-5xl overflow-hidden rounded-2xl border border-white/70 bg-[#FAF6F0] shadow-raised" aria-labelledby="error-title">
            <div class="grid min-h-[32rem] lg:grid-cols-[0.82fr_1.18fr]">
                <div class="relative flex min-h-52 items-end overflow-hidden border-b border-black/[0.07] bg-[#EDE8DF] p-6 sm:min-h-64 sm:p-10 lg:min-h-full lg:border-b-0 lg:border-r">
                    <div class="absolute left-6 top-6 h-2 w-16 rounded-sm bg-[#1A6B6B] sm:left-10 sm:top-10" aria-hidden="true"></div>
                    <p class="select-none text-[7rem] font-bold leading-none tracking-[-0.08em] text-[#1A6B6B] sm:text-[10rem] lg:text-[12rem]" aria-hidden="true">
                        {{ $status }}
                    </p>
                </div>

                <div class="flex flex-col justify-between gap-12 p-6 sm:p-10 lg:p-14">
                    <div>
                        <a href="{{ $homeUrl }}" class="inline-flex min-h-11 items-center gap-3 rounded-lg text-sm font-semibold text-[#14504F] outline-none transition-colors hover:text-[#1A6B6B] focus-visible:ring-2 focus-visible:ring-[#1A6B6B] focus-visible:ring-offset-4 focus-visible:ring-offset-[#FAF6F0]">
                            <img src="{{ asset('images/logo/logo.png') }}" alt="" class="h-9 w-9 object-contain" onerror="this.style.display='none'">
                            <span>{{ $brand }}</span>
                        </a>

                        <div class="mt-12 max-w-xl sm:mt-16">
                            <h1 id="error-title" class="text-balance text-3xl font-bold leading-tight tracking-[-0.03em] text-[#2C2C2C] sm:text-4xl">
                                {{ $content['title'] }}
                            </h1>
                            <p class="mt-5 max-w-[58ch] text-pretty text-base leading-7 text-[#6B6560]">
                                {{ $content['message'] }}
                            </p>
                        </div>
                    </div>

                    <nav class="flex flex-col gap-3 sm:flex-row" aria-label="Pilihan untuk melanjutkan">
                        @if ($canGoBack)
                            <a href="{{ $previousUrl }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-black/10 bg-[#FAF6F0] px-5 py-3 text-sm font-semibold text-[#2C2C2C] shadow-sm transition-all hover:-translate-y-0.5 hover:border-black/20 hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#1A6B6B] focus-visible:ring-offset-2">
                                Kembali
                            </a>
                        @endif
                        <a href="{{ $primaryUrl }}" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-[#1A6B6B] px-5 py-3 text-sm font-semibold text-white shadow-teal transition-all hover:-translate-y-0.5 hover:bg-[#14504F] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#1A6B6B] focus-visible:ring-offset-2">
                            {{ $primaryLabel }}
                        </a>
                    </nav>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
