@php
    $pillars = [
        ['icon' => 'favorite', 'title' => $cms['value_empathy_title'] ?? 'Empati', 'desc' => $cms['value_empathy_desc'] ?? ''],
        ['icon' => 'nature', 'title' => $cms['value_explore_title'] ?? 'Eksplorasi', 'desc' => $cms['value_explore_desc'] ?? ''],
        ['icon' => 'sync', 'title' => $cms['value_routine_title'] ?? 'Keteraturan', 'desc' => $cms['value_routine_desc'] ?? ''],
    ];
@endphp
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 {{ $class ?? 'pt-3' }}">
    @foreach($pillars as $pillar)
        <div class="bg-surface-mint/70 p-5 rounded-2xl shadow-sm border border-border-subtle/50">
            <span class="material-symbols-outlined text-forest-deep text-[26px] mb-2">{{ $pillar['icon'] }}</span>
            <h3 class="text-sm font-semibold text-text-primary mb-1">{{ $pillar['title'] }}</h3>
            <p class="text-xs text-text-secondary leading-relaxed">{{ $pillar['desc'] }}</p>
        </div>
    @endforeach
</div>
