@props([
    'path' => null,
    'name' => '?',
    'size' => 'sm',
    'rounded' => 'xl',
    'class' => '',
])
@php
    $sizes = [
        'xs' => 'h-7 w-7 text-[11px]',
        'sm' => 'h-8 w-8 text-sm',
        'md' => 'h-10 w-10 text-base',
        'lg' => 'h-12 w-12 text-lg',
        'xl' => 'h-16 w-16 text-xl',
        '2xl' => 'h-20 w-20 text-2xl',
        'hero' => 'h-24 w-24 text-4xl',
        'nav' => 'h-9 w-9 text-sm',
    ];
    $dim = $sizes[$size] ?? $sizes['sm'];
    $round = $rounded === 'full' ? 'rounded-full' : 'rounded-xl';
    $nm = (string) $name;
    $initial = $nm !== '' ? mb_strtoupper(mb_substr($nm, 0, 1)) : '?';
@endphp
@if(filled($path))
    <img
        src="{{ \App\Support\DocumentationMedia::publicAssetPath($path) }}"
        alt="{{ $nm }}"
        class="{{ $dim }} {{ $round }} object-cover shrink-0 border border-black/[0.06] {{ $class }}"
        loading="lazy"
        decoding="async"
    />
@else
    <div
        class="{{ $dim }} {{ $round }} flex items-center justify-center font-bold text-white shrink-0 bg-[#1A6B6B] shadow-sm {{ $class }}"
        aria-hidden="true"
    >{{ $initial }}</div>
@endif
