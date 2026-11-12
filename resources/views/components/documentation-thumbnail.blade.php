@props([
    'path',
    'class' => 'h-12 w-12 rounded-lg',
])

@php
    use App\Support\DocumentationMedia;
    $url = DocumentationMedia::publicAssetPath($path);
    $isVideo = DocumentationMedia::isVideoPath($path);
    $boxClass = trim($class);
    $videoSrc = $url.'#t=0.1';
@endphp

<div {{ $attributes->merge(['class' => 'relative shrink-0 overflow-hidden inline-block bg-stone-200 '.$boxClass]) }}>
    @if($isVideo)
        <video
            src="{{ $videoSrc }}"
            class="block h-full w-full min-h-full min-w-full object-cover bg-stone-300"
            muted
            playsinline
            preload="metadata"
            onloadedmetadata="if (this.duration > 0) { this.currentTime = Math.min(0.25, this.duration * 0.05); }"
        ></video>
        <span class="absolute bottom-0.5 right-0.5 flex h-5 w-5 items-center justify-center rounded bg-black/55 text-white text-[9px] pointer-events-none shadow-sm" aria-hidden="true">▶</span>
    @else
        <img src="{{ $url }}" alt="" class="block h-full w-full min-h-full min-w-full object-cover" loading="lazy" decoding="async">
    @endif
</div>
