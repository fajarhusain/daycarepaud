@props(['tag' => 'button'])
@php
    $classes = 'bg-forest-deep text-white rounded-full inline-flex items-center justify-center gap-2 w-full py-3 sm:py-3.5 px-6 font-semibold text-sm sm:text-base hover:bg-forest-mid active:scale-[0.99] transition-all shadow-[0_4px_14px_rgba(73,108,92,0.22)] hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0';
@endphp
@if($tag === 'button')
<button type="{{ $attributes->get('type', 'submit') }}" {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</button>
@else
<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
@endif

