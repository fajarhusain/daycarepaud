@props([
    'name',
    'alt' => '',
    'class' => 'guest-illustration',
    'eager' => false,
])
@php
    use App\Support\GuestIllustrations;
    $url = GuestIllustrations::url($name);
    $emoji = $url ? null : GuestIllustrations::emojiFallback($name);
@endphp
@if($url)
    <img
        src="{{ $url }}"
        alt="{{ $alt }}"
        {{ $attributes->merge(['class' => $class]) }}
        @unless($eager) loading="lazy" @endunless
        decoding="async"
    >
@elseif($emoji)
    <span {{ $attributes->merge(['class' => trim($class.' guest-illustration-emoji'), 'role' => 'img', 'aria-label' => $alt ?: $name]) }}>{{ $emoji }}</span>
@endif
