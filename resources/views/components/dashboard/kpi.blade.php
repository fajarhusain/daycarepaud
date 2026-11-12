@props([
    'label',
    'value',
    'href' => null,
    'linkLabel' => 'Detail',
    'icon' => null,
    'class' => '',
])

@php
    $cardClass = 'stat-card'.($href ? ' stat-card--link' : '').' '.$class;
@endphp

@if($href)
<a href="{{ $href }}" {{ $attributes->merge(['class' => $cardClass]) }}>
@else
<div {{ $attributes->merge(['class' => $cardClass]) }}>
@endif
    @if($icon)
        <div class="stat-icon shrink-0">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" />
            </svg>
        </div>
    @endif
    <div class="min-w-0 flex-1">
        <p class="dashboard-kpi-label">{{ $label }}</p>
        <p class="dashboard-kpi-value" title="{{ is_string($value) ? $value : '' }}">{{ $value }}</p>
    </div>
@if($href)
</a>
@else
</div>
@endif
