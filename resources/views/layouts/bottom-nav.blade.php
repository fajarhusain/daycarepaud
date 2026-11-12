@php
    $isOrangTua = auth()->user()?->hasRole('Orang Tua') && !empty($orangTuaBottomNavItems);
    $homeLabel = $isOrangTua ? 'Beranda' : 'Home';

    if ($isOrangTua) {
        $bottomItems = $orangTuaBottomNavItems;
        $moreItems = $orangTuaMoreNavItems ?? [];
    } else {
        $flatItems = [];
        foreach($roleNavItems as $nav) {
            if(!empty($nav['collapsible']) && isset($nav['sections'])) {
                foreach($nav['sections'] as $section) {
                    foreach($section['items'] as $item) {
                        $flatItems[] = $item;
                    }
                }
            } elseif(isset($nav['group']) && isset($nav['items'])) {
                foreach($nav['items'] as $item) {
                    if (isset($item['route'])) {
                        $flatItems[] = $item;
                    }
                }
            } elseif(isset($nav['route'])) {
                $flatItems[] = $nav;
            }
        }

        $maxBottomItems = 4;
        $navSlots = ($showDashboardNav ?? true) ? $maxBottomItems - 1 : $maxBottomItems;

        if (count($flatItems) <= $maxBottomItems) {
            $bottomItems = $flatItems;
            $moreItems = [];
        } else {
            $bottomItems = array_slice($flatItems, 0, $navSlots);
            $moreItems = array_slice($flatItems, $navSlots);
        }
    }
@endphp

<nav class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-black/5 shadow-[0_-4px_10px_rgba(0,0,0,0.03)] px-1 flex items-end justify-around h-[64px]" style="padding-bottom: max(env(safe-area-inset-bottom), 0.25rem); height: calc(64px + env(safe-area-inset-bottom));">
    @if($showDashboardNav ?? true)
    <a href="{{ route($dashboardRoute ?? 'dashboard') }}" class="flex flex-col items-center justify-center w-full h-full text-[9.5px] font-medium transition-colors pt-1 {{ request()->routeIs($dashboardRoute ?? 'dashboard') ? 'text-[#1A6B6B]' : 'text-gray-400 hover:text-gray-600' }}">
        <svg class="h-5 w-5 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
        </svg>
        <span class="truncate w-full text-center px-0.5">{{ $homeLabel }}</span>
    </a>
    @endif

    @foreach($bottomItems as $item)
        @if(!empty($item['center']))
        <a href="{{ route($item['route']) }}" data-tour="nav-{{ $item['route'] }}" class="relative flex flex-col items-center justify-center w-full -mt-4">
            <span class="flex h-12 w-12 items-center justify-center rounded-full shadow-lg transition-transform {{ request()->routeIs($item['pattern']) ? 'bg-[#145252] scale-105' : 'bg-[#1A6B6B]' }}">
                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] ?? 'M4 6h16M4 10h16M4 14h16M4 18h16' }}" />
                </svg>
            </span>
            <span class="truncate w-full text-center text-[9px] font-semibold mt-0.5 px-0.5 {{ request()->routeIs($item['pattern']) ? 'text-[#1A6B6B]' : 'text-gray-500' }}">{{ $item['label'] }}</span>
        </a>
        @else
        <a href="{{ route($item['route']) }}" data-tour="nav-{{ $item['route'] }}" class="relative flex flex-col items-center justify-center w-full h-full text-[9.5px] font-medium transition-colors pt-1 {{ request()->routeIs($item['pattern']) ? 'text-[#1A6B6B]' : 'text-gray-400 hover:text-gray-600' }}">
            <svg class="h-5 w-5 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] ?? 'M4 6h16M4 10h16M4 14h16M4 18h16' }}" />
            </svg>
            <span class="truncate w-full text-center px-0.5">{{ $item['label'] }}</span>
            @if(!empty($item['badge']) && $item['badge'] > 0)
            <span class="absolute top-1 right-2 inline-flex items-center justify-center h-4 min-w-[1rem] px-1 rounded-full text-white text-[9px] font-bold" style="background:#FF8C42;">{{ $item['badge'] }}</span>
            @endif
        </a>
        @endif
    @endforeach

    @if(count($moreItems) > 0)
    <button @click="moreMenuOpen = true" class="flex flex-col items-center justify-center w-full h-full text-[9.5px] font-medium text-gray-400 hover:text-gray-600 focus:outline-none transition-colors pt-1">
        <svg class="h-5 w-5 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
        <span class="truncate w-full text-center px-0.5">Lainnya</span>
    </button>
    @endif
</nav>

<!-- More Menu Bottom Sheet -->
@if(count($moreItems) > 0)
<div x-show="moreMenuOpen" class="lg:hidden fixed inset-0 z-50 flex flex-col justify-end" style="display: none;">
    <!-- Backdrop -->
    <div x-show="moreMenuOpen" x-transition.opacity class="absolute inset-0 bg-gray-900 bg-opacity-50" @click="moreMenuOpen = false"></div>

    <!-- Sheet -->
    <div x-show="moreMenuOpen"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="translate-y-full"
         x-transition:enter-end="translate-y-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="translate-y-0"
         x-transition:leave-end="translate-y-full"
         class="relative bg-white w-full rounded-t-3xl flex flex-col shadow-2xl"
         style="padding-bottom: env(safe-area-inset-bottom);">

        <div class="px-6 py-4 border-b border-black/5 flex justify-between items-center shrink-0">
            <h3 class="font-bold text-lg text-[#2C2C2C]">Menu Lainnya</h3>
            <button @click="moreMenuOpen = false" class="text-gray-400 hover:text-gray-700 bg-gray-100 p-2 rounded-full focus:outline-none transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto px-4 py-4 space-y-2 max-h-[60vh]">
            @foreach($moreItems as $item)
            <a href="{{ route($item['route']) }}" data-tour="nav-{{ $item['route'] }}" @click="moreMenuOpen = false" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-medium transition-all {{ request()->routeIs($item['pattern']) ? 'bg-[#1A6B6B] text-white shadow-md shadow-[#1A6B6B]/20' : 'text-[#2C2C2C] bg-[#FAF6F0] hover:bg-black/5' }}">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] ?? 'M4 6h16M4 10h16M4 14h16M4 18h16' }}" />
                </svg>
                <div class="flex-1">{{ $item['label'] }}</div>
                @if(!empty($item['badge']) && $item['badge'] > 0)
                <span class="inline-flex items-center justify-center h-5 px-2 rounded-full text-white text-[10px] font-bold" style="background:#FF8C42;">{{ $item['badge'] }}</span>
                @endif
                <svg class="w-4 h-4 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
            </a>
            @endforeach

            @if($isOrangTua)
            <div class="border-t border-black/5 mt-2 pt-3 space-y-2">
                @php
                    $currentRoute = Route::currentRouteName();
                    $hasPageTour = \App\Support\TourRegistry::has($currentRoute);
                @endphp
                <!-- @if($hasPageTour)
                <button type="button" data-tour-trigger @click="moreMenuOpen = false" class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-medium transition-all text-[#2C2C2C] bg-[#FAF6F0] hover:bg-black/5">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="flex-1 text-left">Ulangi panduan halaman</div>
                </button>
                @endif -->
                <a href="{{ route('profile.edit') }}" data-tour="nav-profile.edit" @click="moreMenuOpen = false" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-medium transition-all {{ request()->routeIs('profile.*') ? 'bg-[#1A6B6B] text-white shadow-md shadow-[#1A6B6B]/20' : 'text-[#2C2C2C] bg-[#FAF6F0] hover:bg-black/5' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <div class="flex-1">Profil Saya</div>
                    <svg class="w-4 h-4 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" @click="moreMenuOpen = false" class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 transition-all">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span class="flex-1 text-left">Keluar</span>
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>
@endif
