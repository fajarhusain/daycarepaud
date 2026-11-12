@props(['flip' => false, 'color' => 'var(--guest-bg)', 'fill' => null])
@php
    $fillColor = $fill ?? $color;
@endphp
<div class="guest-wave-divider {{ $flip ? 'rotate-180' : '' }}" aria-hidden="true">
    <svg viewBox="0 0 1440 80" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
        <path fill="{{ $fillColor }}" d="M0,40 C240,80 480,0 720,40 C960,80 1200,0 1440,40 L1440,80 L0,80 Z"/>
    </svg>
</div>
