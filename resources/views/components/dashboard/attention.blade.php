@props([
    'items' => [],
    'title' => 'Perlu perhatian',
    'overflowHref' => null,
    'maxVisible' => 5,
])

@php
    $maxVisible = (int) $maxVisible;
    $total = count($items);
    $visible = array_slice($items, 0, $maxVisible);
    $overflowHref = $overflowHref ?: ($visible[0]['href'] ?? null);
    $hasAside = isset($aside);
    $hasBelowPanel = isset($belowPanel);
    $hasSplit = $hasAside || $hasBelowPanel;
    $severityLabel = fn (string $severity) => match ($severity) {
        'critical' => 'Segera',
        'info' => 'Info',
        default => 'Perlu',
    };
@endphp

<section {{ $attributes->merge(['class' => 'dashboard-attention'.($hasSplit ? ' dashboard-attention--split' : '')]) }} aria-labelledby="dashboard-attention-heading">
    <div class="dashboard-section-head">
        <h2 id="dashboard-attention-heading" class="dashboard-section-title">{{ $title }}</h2>
        <p class="dashboard-section-desc">Tindakan untuk hari ini</p>
    </div>

    <div @class(['dashboard-attention-split' => $hasSplit])>
        <div class="dashboard-attention-main-col">
            <div class="dashboard-attention-panel">
            @if($total === 0)
                <div class="dashboard-all-clear" role="status">
                    <span class="dashboard-all-clear-icon" aria-hidden="true">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </span>
                    <div>
                        <p class="dashboard-all-clear-title">Semua beres</p>
                        <p class="dashboard-all-clear-desc">Tidak ada antrian yang perlu ditindak sekarang.</p>
                    </div>
                </div>
            @else
                <ul class="dashboard-attention-list">
                    @foreach($visible as $item)
                        @php
                            $href = ! empty($item['href']) ? $item['href'] : '#';
                            if (empty($item['href'])) {
                                \Illuminate\Support\Facades\Log::warning('Dashboard attention item missing href', ['title' => $item['title'] ?? '']);
                            }
                            $severity = $item['severity'] ?? 'warning';
                            $label = $severityLabel($severity);
                            $action = $item['action'] ?? 'Buka';
                        @endphp
                        <li>
                            <a
                                href="{{ $href }}"
                                class="dashboard-attention-item dashboard-attention-item--{{ $severity }}"
                                aria-label="{{ $label }}: {{ $item['title'] }}. {{ $action }}"
                            >
                                <span class="dashboard-attention-severity dashboard-attention-severity--{{ $severity }}">{{ $label }}</span>
                                <div class="min-w-0 flex-1">
                                    <p class="dashboard-attention-title">{{ $item['title'] }}</p>
                                    <p class="dashboard-attention-detail">{{ $item['detail'] }}</p>
                                </div>
                                <span class="dashboard-attention-action dashboard-attention-action--{{ $severity }}">{{ $action }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
                @if($total > $maxVisible && $overflowHref)
                    <p class="m-0 mt-2">
                        <a href="{{ $overflowHref }}" class="dashboard-compact-link">Lihat semua ({{ $total }})</a>
                    </p>
                @endif
            @endif
            </div>

            @if($hasBelowPanel)
                <div class="dashboard-attention-below-panel">
                    {{ $belowPanel }}
                </div>
            @endif
        </div>

        @if($hasAside)
            <div class="dashboard-attention-aside">
                {{ $aside }}
            </div>
        @endif
    </div>
</section>
