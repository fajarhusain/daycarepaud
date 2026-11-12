@props(['text', 'align' => 'left'])

<span
    {{ $attributes->class('relative inline-flex align-middle shrink-0 '.($align === 'right' ? 'ml-auto' : '')) }}
    x-data="{ open: false }"
    @click.outside="open = false"
>
    <button
        type="button"
        class="inline-flex h-4 w-4 items-center justify-center rounded-full text-[10px] font-bold leading-none transition-colors"
        style="color:#9E9790; background:rgba(0,0,0,0.06);"
        :style="open ? 'color:#1A6B6B; background:#D0E8E8;' : ''"
        @click.stop="open = !open"
        :aria-expanded="open"
        aria-label="Penjelasan"
    >i</button>
    <div
        x-show="open"
        x-cloak
        x-transition.opacity.duration.150ms
        class="absolute z-30 mt-1.5 w-56 max-w-[min(16rem,calc(100vw-2rem))] rounded-lg border px-3 py-2 text-xs font-normal normal-case tracking-normal shadow-lg {{ $align === 'right' ? 'right-0' : 'left-0' }} top-full"
        style="border-color:rgba(0,0,0,0.08); background:#fff; color:#6B5B3A; line-height:1.45;"
        role="tooltip"
    >{{ $text }}</div>
</span>
