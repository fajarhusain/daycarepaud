@props([
    'href',
    'compact' => true,
    'label' => 'Reset',
])

@if($compact)
    <a href="{{ $href }}"
        {{ $attributes->class(['btn-secondary h-9 w-9 md:h-11 md:w-11 p-0 shrink-0 inline-flex items-center justify-center']) }}
        title="Reset filter" aria-label="Reset filter">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
    </a>
@else
    <a href="{{ $href }}"
        {{ $attributes->class(['btn-secondary text-xs h-9 md:h-11 inline-flex items-center justify-center px-3 shrink-0']) }}
        title="Reset filter">{{ $label }}</a>
@endif
