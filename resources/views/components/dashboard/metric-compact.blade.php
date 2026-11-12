@props([
    'title',
    'value',
    'valueSuffix' => null,
    'meta' => null,
    'href' => null,
    'linkLabel' => 'Detail',
])

<div {{ $attributes->merge(['class' => 'card dashboard-compact-card dashboard-metric-compact']) }}>
    <div class="dashboard-compact-head">
        <h3 class="section-title">{{ $title }}</h3>
    </div>
    <div class="dashboard-compact-body">
        <p class="dashboard-compact-value m-0">
            {{ $value }}
            @if($valueSuffix)
                <span class="dashboard-compact-value-suffix">{{ $valueSuffix }}</span>
            @endif
        </p>
        @if($meta)
            <p class="dashboard-compact-meta m-0">{{ $meta }}</p>
        @endif
        @if($href)
            <div class="dashboard-compact-foot">
                <a href="{{ $href }}" class="dashboard-compact-link">{{ $linkLabel }}</a>
            </div>
        @endif
    </div>
</div>
