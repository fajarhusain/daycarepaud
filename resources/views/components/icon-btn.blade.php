@props([
    'href' => null,
    'type' => 'button',
    'variant' => 'secondary',
    'label',
    'icon',
])

@php
    $square = 'inline-flex items-center justify-center shrink-0 h-9 w-9 md:h-11 md:w-11 p-0';
    $variantClass = match ($variant) {
        'primary' => 'btn-primary '.$square,
        'danger' => 'btn-danger '.$square,
        'hero-primary' => 'dashboard-hero-btn dashboard-hero-btn--primary '.$square,
        'hero-ghost' => 'dashboard-hero-btn dashboard-hero-btn--ghost '.$square,
        'toolbar-teal' => 'toolbar-icon rounded-lg flex items-center justify-center transition border '.$square,
        'link' => 'dashboard-link-icon '.$square,
        default => 'btn-secondary toolbar-icon '.$square,
    };
@endphp

@if($href)
    <a href="{{ $href }}" title="{{ $label }}" aria-label="{{ $label }}" {{ $attributes->merge(['class' => $variantClass]) }}>
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" />
        </svg>
    </a>
@else
    <button type="{{ $type }}" title="{{ $label }}" aria-label="{{ $label }}" {{ $attributes->merge(['class' => $variantClass]) }}>
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" />
        </svg>
    </button>
@endif
