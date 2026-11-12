@props([
    'title',
    'subtitle' => null,
    'href' => null,
    'linkLabel' => 'Lihat semua',
])

<div {{ $attributes->merge(['class' => 'card overflow-hidden']) }}>
    <div class="card-pad border-b dashboard-panel-head">
        <div class="min-w-0 flex-1">
            <h3 class="section-title mb-0">{{ $title }}</h3>
            @if($subtitle)
                <p class="section-subtitle mt-0.5">{{ $subtitle }}</p>
            @endif
        </div>
        @if($href)
            <a href="{{ $href }}" class="dashboard-panel-link shrink-0">{{ $linkLabel }}</a>
        @endif
    </div>
    {{ $slot }}
</div>
