@props([
    'title',
    'items' => [],
    'total' => 0,
    'href' => null,
    'linkLabel' => 'Lihat semua',
    'emptyText' => 'Belum ada kegiatan hari ini',
])

@php
    $displayItems = array_slice($items, 0, 3);
    $total = (int) $total;
    $showOverflow = $total > 3 && $href;
    $headSub = $total > 0
        ? "{$total} entri jurnal hari ini"
        : 'Agenda belum tercatat';
@endphp

<div {{ $attributes->merge(['class' => 'card dashboard-compact-card dashboard-activity-list']) }}>
    <div class="dashboard-compact-head">
        <h3 class="section-title">{{ $title }}</h3>
        <p class="dashboard-compact-sub">{{ $headSub }}</p>
    </div>
    <div class="dashboard-compact-body">
        @if(count($displayItems) === 0)
            <div class="dashboard-compact-empty">
                <p class="dashboard-compact-meta m-0 text-sm leading-snug">{{ $emptyText }}</p>
                @if($href)
                    <a href="{{ $href }}" class="btn-primary dashboard-compact-cta">Buka agenda</a>
                @endif
            </div>
        @else
            <ul class="dashboard-activity-rows">
                @foreach($displayItems as $row)
                    <li class="dashboard-activity-row">
                        <span class="dashboard-activity-time">{{ $row['time'] ?? '-' }}</span>
                        <div class="dashboard-activity-main min-w-0">
                            <p class="dashboard-activity-title m-0">{{ $row['title'] ?? 'Kegiatan' }}</p>
                            @if(!empty($row['meta']))
                                <p class="dashboard-activity-meta m-0">{{ $row['meta'] }}</p>
                            @endif
                        </div>
                        @if(!empty($row['status']))
                            <span class="dashboard-activity-badge">{{ $row['status'] }}</span>
                        @endif
                    </li>
                @endforeach
            </ul>
            @if($href)
                <div class="dashboard-compact-foot">
                    @php
                        $footLabel = $showOverflow ? "{$linkLabel} ({$total})" : $linkLabel;
                    @endphp
                    <a href="{{ $href }}" class="dashboard-compact-link">{{ $footLabel }}</a>
                </div>
            @endif
        @endif
    </div>
</div>
