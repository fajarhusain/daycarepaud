@props([
    'href',
    'title',
    'description' => '',
    'icon' => null,
])

@php
    $iconPath = $icon ?? \App\Support\DashboardIcons::path('grid');
    $tip = $description !== '' ? $title.': '.$description : $title;
@endphp

<a href="{{ $href }}" title="{{ $tip }}" {{ $attributes->merge(['class' => 'dashboard-quick-link']) }}>
    @if($iconPath)
        <span class="dashboard-quick-link-icon" aria-hidden="true">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}" />
            </svg>
        </span>
    @endif
    <span class="min-w-0 flex-1">
        <span class="dashboard-quick-link-title">{{ $title }}</span>
        @if($description !== '')
            <span class="dashboard-quick-link-desc">{{ $description }}</span>
        @endif
    </span>
</a>
