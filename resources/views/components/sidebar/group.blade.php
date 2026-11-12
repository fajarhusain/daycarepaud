@props([
    'nav',
    'level' => 1,
    'leading' => false,
])

@php
    $patterns = is_array($nav['pattern'] ?? null) ? $nav['pattern'] : [$nav['pattern'] ?? ''];
    $groupActive = request()->routeIs($patterns);
    $collapsedLinks = \App\Support\MenuBuilder::flattenCollapsibleLinks($nav);
    $chevronClass = $level >= 2 ? 'h-4 w-4' : 'h-5 w-5';
    $iconClass = $level >= 2 ? 'h-4 w-4' : 'h-5 w-5';
    $headerTextClass = $level >= 2 ? 'text-xs' : 'text-sm';
@endphp

@foreach ($collapsedLinks as $item)
    <a href="{{ route($item['route']) }}"
       x-show="sidebarCollapsed"
       @if (request()->routeIs($item['pattern'])) id="nav-active-item" @endif
       data-tour="nav-{{ $item['route'] }}"
       class="flex items-center justify-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs($item['pattern']) ? 'bg-[#1A6B6B] text-white shadow-sm' : 'text-[#2C2C2C] hover:bg-black/5' }}"
       title="{{ $item['label'] }}">
        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
        </svg>
    </a>
@endforeach

<div x-show="!sidebarCollapsed"
     x-data="{ open: {{ $groupActive ? 'true' : 'false' }} }"
     data-sidebar-group="{{ $nav['group'] ?? '' }}"
     class="{{ $level === 1 ? ($leading ? 'pt-2' : '') : 'pt-1' }}">
    <button type="button"
            @click="open = !open"
            class="w-full flex items-center gap-3 min-w-0 px-3 {{ $level === 1 ? 'py-2' : 'py-2.5' }} rounded-xl overflow-visible {{ $headerTextClass }} font-medium transition-colors {{ $groupActive ? 'bg-[#1A6B6B]/10 text-[#1A6B6B]' : 'text-[#2C2C2C] hover:bg-black/5' }}">
        @if (! empty($nav['icon']))
            <span class="inline-flex shrink-0 items-center justify-center">
            <svg class="{{ $iconClass }} shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $nav['icon'] }}" />
            </svg>
            </span>
        @endif
        <span class="truncate min-w-0 flex-1 text-left">{{ $nav['group'] }}</span>
        <span class="flex items-center gap-2 shrink-0">
            @if (! empty($nav['has_badge_warning']))
                <span class="w-[7px] h-[7px] rounded-full shrink-0 bg-[#FF8C42]" aria-hidden="true"></span>
            @endif
            @if (($nav['count'] ?? 0) > 0)
                <span class="inline-flex items-center justify-center h-[18px] min-w-[18px] px-[7px] rounded-full text-[11px] font-medium tabular-nums text-[#9E9790]"
                      style="background: rgba(0,0,0,0.04);">{{ $nav['count'] }}</span>
            @endif
            <svg class="{{ $chevronClass }} shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </span>
    </button>
    <div x-show="open" x-transition class="mt-1 overflow-visible">
        <div class="relative ml-3 border-l border-black/10 space-y-0.5 pl-3 pr-0.5 overflow-visible">
            @foreach ($nav['sections'] as $section)
                @if (! empty($section['label']))
                    <div class="pt-2 pb-1 text-[9px] font-semibold text-[#9E9790] uppercase tracking-wider">
                        {{ $section['label'] }}
                    </div>
                @endif
                @foreach ($section['items'] as $item)
                    @if (! empty($item['collapsible']))
                        <div class="relative -ml-3 pl-0 border-l-0">
                            <x-sidebar.group :nav="$item" :level="$level + 1" />
                        </div>
                    @else
                        <a href="{{ route($item['route']) }}"
                           @if (request()->routeIs($item['pattern'])) id="nav-active-item" @endif
                           data-tour="nav-{{ $item['route'] }}"
                           class="relative flex items-center gap-2.5 py-2 pl-2 pr-2 rounded-xl text-sm font-medium transition-colors before:pointer-events-none before:absolute before:left-0 before:top-1/2 before:w-3 before:h-px before:-translate-y-1/2 before:bg-black/10 {{ request()->routeIs($item['pattern']) ? 'bg-[#1A6B6B] text-white shadow-sm' : 'text-[#2C2C2C] hover:bg-black/5' }}">
                            <svg class="h-4 w-4 shrink-0 relative z-[1]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                            </svg>
                            <span class="truncate">{{ $item['label'] }}</span>
                            @if (! empty($item['badge']) && $item['badge'] > 0)
                                <span class="ml-auto inline-flex items-center justify-center h-5 px-2 rounded-full text-white text-[10px] font-bold" style="background:#FF8C42;">{{ $item['badge'] }}</span>
                            @endif
                        </a>
                    @endif
                @endforeach
            @endforeach
        </div>
    </div>
</div>
