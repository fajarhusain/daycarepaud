@php
    $hideOnMobileOrtu = auth()->user()?->hasRole('Orang Tua');
    $currentRoute = Route::currentRouteName();
    $hasPageTour = \App\Support\TourRegistry::has($currentRoute);
    $hasBreadcrumb = count($pageBreadcrumbs ?? []) > 0;
@endphp
<header class="bg-[#FAF6F0] border-b border-black/5 shadow-[0_2px_8px_rgba(0,0,0,0.04)] sticky top-0 z-30 min-h-[3.5rem] md:min-h-[4rem] h-auto pt-safe md:pt-0 shrink-0 {{ $hideOnMobileOrtu ? 'hidden lg:flex lg:flex-col lg:justify-center' : 'flex flex-col justify-center' }}">
    <div class="app-shell-bar flex flex-col gap-1.5 sm:gap-2 py-2 md:py-2.5 w-full">
        <div class="flex items-center justify-between gap-2 sm:gap-3 w-full min-h-[2.25rem]">
            <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3 min-w-0 flex-1">
                <a href="{{ route($homeRoute) }}" @class(['shrink-0 items-center gap-2', 'hidden' => $hasBreadcrumb, 'flex lg:hidden' => ! $hasBreadcrumb])>
                    <div class="h-7 w-7 rounded-lg flex items-center justify-center bg-[#1A6B6B] shadow-sm">
                        <svg class="h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <span class="font-bold text-sm text-[#2C2C2C] sm:hidden">{{ config('app.name') }}</span>
                </a>
                @if($hasBreadcrumb)
                    <x-app-breadcrumb :items="$pageBreadcrumbs" class="min-w-0" />
                @endif
            </div>

            <div class="flex items-center gap-2 shrink-0">
                @if($lembagaSchools ?? null)
                    <form action="{{ route('lembaga.active-sekolah.update') }}" method="POST" class="hidden md:flex items-center gap-2">
                        @csrf
                        <label class="text-xs font-semibold whitespace-nowrap" style="color:#6B6560;">Cabang aktif:</label>
                        <select name="sekolah_id" class="input-field text-sm py-1.5 min-w-[10rem]" onchange="this.form.submit()">
                            <option value="">-- Pilih cabang --</option>
                            @foreach($lembagaSchools as $s)
                                <option value="{{ $s->id }}" @selected(($activeSekolah?->id ?? null) === $s->id)>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </form>
                @endif
                @if(($ortuSchools ?? null) && $ortuSchools->count() > 1)
                    <form action="{{ route('orangtua.active-sekolah.update') }}" method="POST" class="hidden md:flex items-center gap-2">
                        @csrf
                        <label class="text-xs font-semibold whitespace-nowrap" style="color:#6B6560;">Sekolah aktif:</label>
                        <select name="sekolah_id" class="input-field text-sm py-1.5 min-w-[10rem]" onchange="this.form.submit()">
                            @foreach($ortuSchools as $s)
                                <option value="{{ $s->id }}" @selected(($activeOrtuSekolah?->id ?? null) === $s->id)>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </form>
                @endif
                @if($hasPageTour)
                <button type="button" data-tour-trigger title="Ulangi panduan halaman" class="h-8 w-8 md:h-9 md:w-9 rounded-xl flex items-center justify-center text-[#1A6B6B] bg-[#1A6B6B]/10 hover:bg-[#1A6B6B]/20 transition-colors focus:outline-none focus:ring-2 focus:ring-[#1A6B6B] focus:ring-offset-2 ring-offset-[#FAF6F0]" aria-label="Ulangi panduan halaman">
                    <svg class="h-4 w-4 md:h-5 md:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </button>
                @endif
                <x-profile-menu :show-name="true" :has-page-tour="$hasPageTour" />
            </div>
        </div>

        @if(isset($header) && ! $hasBreadcrumb)
            <div class="page-header-toolbar flex flex-wrap items-center justify-end gap-2 w-full min-w-0">
                {{ $header }}
            </div>
        @endif
    </div>

    @if($lembagaSchools ?? null)
        <div class="md:hidden w-full px-3 pt-0 pb-2 border-t border-black/5">
            <form action="{{ route('lembaga.active-sekolah.update') }}" method="POST" class="flex items-center gap-2">
                @csrf
                <label class="text-[11px] font-semibold whitespace-nowrap" style="color:#6B6560;">Cabang:</label>
                <select name="sekolah_id" class="input-field text-xs py-1 flex-1 min-w-0" onchange="this.form.submit()">
                    <option value="">-- Pilih cabang --</option>
                    @foreach($lembagaSchools as $s)
                        <option value="{{ $s->id }}" @selected(($activeSekolah?->id ?? null) === $s->id)>{{ $s->name }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    @endif
</header>
