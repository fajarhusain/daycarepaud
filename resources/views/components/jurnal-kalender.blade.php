@props([
    'events' => [],
    'year' => null,
    'month' => null,
])

@php
    $year = $year ?? (int) now()->year;
    $month = $month ?? (int) now()->month;
    $monthValue = sprintf('%04d-%02d', $year, $month);
@endphp

@vite(['resources/js/kegiatan-calendar.js'])

@php
    // JSON_HEX_* + JSON_HEX_TAG: aman di dalam <script>, hindari pemutus </script>
    $flags = JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
    $eventsJson = json_encode($events ?? [], $flags);
    if ($eventsJson === false) {
        $eventsJson = '[]';
    }
@endphp

{{-- Date picker + Calendar wrapper --}}
<div class="rounded-xl border overflow-hidden bg-white" style="border-color:rgba(0,0,0,0.08);">
    {{-- Month picker --}}
    <div class="flex items-center gap-2 px-3 py-2 border-b" style="border-color:rgba(0,0,0,0.06); background:#FAFAF9;">
        <svg class="w-4 h-4 shrink-0" style="color:#1A6B6B;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <label for="kalender-date-picker" class="text-xs font-semibold shrink-0" style="color:#5A5A5A;">Pilih Tanggal:</label>
        <input
            id="kalender-date-picker"
            type="date"
            value="{{ request('year') && request('month') ? sprintf('%04d-%02d-%02d', request('year'), request('month'), request('day', 1)) : now()->format('Y-m-d') }}"
            class="text-sm rounded-lg px-2 py-1 border focus:outline-none"
            style="border-color:rgba(26,107,107,0.4); color:#2C2C2C; background:#fff; accent-color:#1A6B6B;"
            onchange="(function(val){
                if(!val) return;
                const [y, m, d] = val.split('-');
                const target = new window.URL(window.location.href);
                target.searchParams.set('year', y);
                target.searchParams.set('month', String(parseInt(m,10)));
                target.searchParams.set('day', d);
                target.searchParams.set('view', target.searchParams.get('view') || 'listMonth');
                window.location.href = target.toString();
            })(this.value)"
        >
    </div>

    <div
        id="kegiatan-calendar-mount"
        class="jurnal-fc"
        data-year="{{ $year }}"
        data-month="{{ $month }}"
        data-day="{{ request('day', 1) }}"
    >
        <script type="application/json" id="kegiatan-calendar-json">{!! $eventsJson !!}</script>
    </div>
</div>
