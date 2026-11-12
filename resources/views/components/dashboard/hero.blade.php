@props([
    'eyebrow',
    'title',
    'description' => null,
    'icon' => null,
    'variant' => 'default',
])

@php
    $variantClass = match ($variant) {
        'ortu' => 'dashboard-hero dashboard-hero--ortu',
        default => 'dashboard-hero',
    };
@endphp

<div {{ $attributes->merge(['class' => $variantClass]) }} data-tour="dashboard-welcome">
    <div class="dashboard-hero-inner">
        <div class="flex items-center gap-3 sm:gap-4 min-w-0">
            @if($icon && $variant === 'default')
                <div class="dashboard-hero-icon" aria-hidden="true">
                    <svg class="h-5 w-5 sm:h-6 sm:w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" />
                    </svg>
                </div>
            @endif
            <div class="min-w-0">
                <p class="dashboard-hero-eyebrow">{{ $eyebrow }}</p>
                <h1 class="dashboard-hero-title">{{ $title }}</h1>
                @if($description)
                    <p class="dashboard-hero-desc">{{ $description }}</p>
                @endif
            </div>
        </div>
        @isset($actions)
            <div class="dashboard-hero-actions">
                {{ $actions }}
            </div>
        @endisset
    </div>
</div>
