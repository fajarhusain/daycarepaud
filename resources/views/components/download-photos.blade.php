@props(['route', 'params' => [], 'iconOnly' => false])

<a href="{{ route($route, array_merge(request()->except('page', 'per_page'), $params)) }}"
    @if($iconOnly) title="Unduh Foto" aria-label="Unduh Foto" @endif
    {{ $attributes->merge([
        'class' => 'btn-secondary inline-flex items-center justify-center gap-2 whitespace-nowrap' . ($iconOnly ? ' !p-0 h-9 w-9 md:h-11 md:w-11 shrink-0' : ' h-9 md:h-11'),
    ]) }}>
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round"
            d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
    </svg>
    @unless($iconOnly)
        Unduh Foto
    @endunless
</a>
