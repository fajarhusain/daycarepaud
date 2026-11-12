<!-- Sidebar -->
<aside class="hidden lg:flex lg:flex-col lg:static bg-[#FAF6F0] border-r border-black/5 h-screen shrink-0 shadow-[2px_0_8px_rgba(0,0,0,0.04)] sidebar-transition"
       :class="[sidebarCollapsed ? 'w-[72px]' : 'w-64']">
    <!-- Logo -->
    <div class="flex items-center h-16 border-b border-black/5 shrink-0"
         :class="sidebarCollapsed ? 'justify-center px-3' : 'px-6'">
        <a href="{{ route($homeRoute) }}" class="flex items-center gap-2.5 min-w-0">
            <div class="h-9 w-9 rounded-xl flex items-center justify-center bg-[#1A6B6B] shadow-[2px_3px_8px_rgba(26,107,107,0.35)] shrink-0">
                <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
            <span x-show="!sidebarCollapsed" x-transition:enter="fade-enter" x-transition:leave="fade-leave" class="font-bold text-base text-[#2C2C2C] whitespace-nowrap truncate">{{ config('app.name') }}</span>
        </a>
    </div>

    <!-- Navigation Links -->
    <div id="sidebar-nav-scroll"
         class="flex-1 overflow-y-auto scroll-smooth py-4 space-y-1"
         :class="sidebarCollapsed ? 'px-2' : 'px-3'">
        @if($showDashboardNav)
        <a href="{{ route($dashboardRoute) }}"
           @if(request()->routeIs($dashboardRoute)) id="nav-active-item" @endif
           data-tour="nav-dashboard"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs($dashboardRoute) ? 'bg-[#1A6B6B] text-white shadow-sm' : 'text-[#2C2C2C] hover:bg-black/5' }}"
           :class="sidebarCollapsed ? 'justify-center' : ''">
            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span x-show="!sidebarCollapsed" x-transition:enter="fade-enter" x-transition:leave="fade-leave" class="truncate">Dashboard</span>
        </a>
        @endif
        @php $firstCollapsibleGroup = true; @endphp
        @foreach ($roleNavItems as $nav)
            @if(!empty($nav['collapsible']))
                <x-sidebar.group :nav="$nav" :leading="$firstCollapsibleGroup" />
                @php $firstCollapsibleGroup = false; @endphp
            @elseif(isset($nav['group']))
                <div x-show="!sidebarCollapsed" x-transition:enter="fade-enter" x-transition:leave="fade-leave" class="px-3 pt-4 pb-2 text-[10px] font-bold text-[#9E9790] uppercase tracking-wider">
                    {{ $nav['group'] }}
                </div>
                @foreach($nav['items'] as $item)
                    @php
                        $groupItemPatterns = is_array($item['pattern'] ?? null) ? $item['pattern'] : [$item['pattern'] ?? ''];
                        $groupItemActive = request()->routeIs($groupItemPatterns);
                    @endphp
                    <a href="{{ route($item['route']) }}"
                       @if($groupItemActive) id="nav-active-item" @endif
                       data-tour="nav-{{ $item['route'] }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors {{ $groupItemActive ? 'bg-[#1A6B6B] text-white shadow-sm' : 'text-[#2C2C2C] hover:bg-black/5' }}"
                       :class="sidebarCollapsed ? 'justify-center' : ''">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                        </svg>
                        <span x-show="!sidebarCollapsed" x-transition:enter="fade-enter" x-transition:leave="fade-leave" class="truncate">{{ $item['label'] }}</span>
                        @if(!empty($item['badge']) && $item['badge'] > 0)
                        <span x-show="!sidebarCollapsed" x-transition:enter="fade-enter" x-transition:leave="fade-leave" class="ml-auto inline-flex items-center justify-center h-5 px-2 rounded-full text-white text-[10px] font-bold" style="background:#FF8C42;">{{ $item['badge'] }}</span>
                        @endif
                    </a>
                @endforeach
            @else
                @php
                    $itemPattern = is_array($nav['pattern'] ?? null) ? $nav['pattern'] : [$nav['pattern'] ?? ''];
                    $itemActive = request()->routeIs($itemPattern);
                @endphp
                <a href="{{ route($nav['route']) }}"
                   @if($itemActive) id="nav-active-item" @endif
                   data-tour="nav-{{ $nav['route'] }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors {{ $itemActive ? 'bg-[#1A6B6B] text-white shadow-sm' : 'text-[#2C2C2C] hover:bg-black/5' }}"
                   :class="sidebarCollapsed ? 'justify-center' : ''">
                    @if(!empty($nav['icon']))
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $nav['icon'] }}" />
                    </svg>
                    @endif
                    <span x-show="!sidebarCollapsed" x-transition:enter="fade-enter" x-transition:leave="fade-leave" class="truncate">{{ $nav['label'] }}</span>
                    @if(!empty($nav['badge']) && $nav['badge'] > 0)
                    <span x-show="!sidebarCollapsed" x-transition:enter="fade-enter" x-transition:leave="fade-leave" class="ml-auto inline-flex items-center justify-center h-5 px-2 rounded-full text-white text-[10px] font-bold" style="background:#FF8C42;">{{ $nav['badge'] }}</span>
                    @endif
                </a>
            @endif
        @endforeach
    </div>

    <div class="shrink-0 border-t border-black/5 px-3">
        <button type="button"
                @click="sidebarCollapsed = !sidebarCollapsed"
                class="w-full flex items-center gap-2 h-12 rounded-lg text-[#9E9790] hover:bg-black/5 hover:text-[#2C2C2C] transition-colors"
                :class="sidebarCollapsed ? 'justify-center' : 'px-3'"
                title="Toggle sidebar">
            <svg class="h-4 w-4 shrink-0 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"
                      x-show="!sidebarCollapsed" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M6 5l7 7-7 7"
                      x-show="sidebarCollapsed" />
            </svg>
            <span x-show="!sidebarCollapsed" x-transition:enter="fade-enter" x-transition:leave="fade-leave" class="text-xs font-medium whitespace-nowrap">Ciutkan</span>
        </button>
    </div>
</aside>
