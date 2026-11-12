@props([
    'chart',
    'compact' => false,
    'spotlight' => false,
])

@php
    $compact = filter_var($compact, FILTER_VALIDATE_BOOLEAN);
    $spotlight = filter_var($spotlight, FILTER_VALIDATE_BOOLEAN);
    $cardClass = 'card overflow-hidden ';
    if ($spotlight) {
        $cardClass .= 'dashboard-chart-spotlight h-full flex flex-col min-h-0';
    } elseif ($compact) {
        $cardClass .= 'dashboard-compact-card';
    }
    if ($spotlight) {
        $chartMinH = 'min-h-[200px] sm:min-h-[240px] w-full flex-1';
        $bodyPad = 'dashboard-chart-spotlight-body';
    } elseif ($compact) {
        $chartMinH = 'min-h-[128px] w-full flex-1';
        $bodyPad = 'dashboard-compact-body dashboard-compact-chart-body';
    } else {
        $chartMinH = 'min-h-[220px] sm:min-h-[260px]';
        $bodyPad = 'p-3 sm:p-4';
    }
@endphp

<div {{ $attributes->merge(['class' => trim($cardClass)]) }}>
    @if($spotlight)
        <div class="dashboard-chart-spotlight-head">
            <h3 class="dashboard-chart-spotlight-title">{{ $chart['title'] ?? 'Grafik' }}</h3>
            <p class="dashboard-chart-spotlight-sub">{{ ! empty($chart['subtitle']) ? $chart['subtitle'] : 'Presensi sekolah' }}</p>
        </div>
    @elseif($compact)
        <div class="dashboard-compact-head">
            <h3 class="section-title">{{ $chart['title'] ?? 'Grafik' }}</h3>
            <p class="dashboard-compact-sub">{{ ! empty($chart['subtitle']) ? $chart['subtitle'] : 'Ringkasan presensi' }}</p>
        </div>
    @else
        <div class="card-pad border-b dashboard-panel-head">
            <div class="min-w-0">
                <h3 class="section-title mb-0">{{ $chart['title'] ?? 'Grafik' }}</h3>
                @if(!empty($chart['subtitle']))
                    <p class="section-subtitle mt-0.5">{{ $chart['subtitle'] }}</p>
                @endif
            </div>
        </div>
    @endif
    <div class="{{ $bodyPad }}">
        <div
            id="{{ $chart['id'] }}"
            class="{{ $chartMinH }}"
            role="img"
            aria-label="{{ $chart['title'] ?? 'Grafik data' }}"
        ></div>
    </div>
</div>
