@props([
    'cms' => [],
    'pageTitle' => null,
    'metaDescription' => null,
    'canonical' => null,
    'ogImageUrl' => null,
    'jsonLd' => null,
])
@php
    use App\Support\GuestBrand;
    use App\Support\GuestSeo;
    $seo = GuestSeo::forHome($cms);
    $title = $pageTitle ?? $seo['title'];
    $description = $metaDescription ?? $seo['description'];
    $canonicalUrl = $canonical ?? $seo['canonical'];
    $ogImage = $ogImageUrl ?? $seo['og_image_url'];
    $fullTitle = str_contains($title, GuestBrand::NAME) ? $title : $title.' | '.GuestBrand::NAME;
@endphp
<x-guest-favicon />
<title>{{ $fullTitle }}</title>
<meta name="description" content="{{ Str::limit(strip_tags($description), 320, '') }}">
<meta name="robots" content="index,follow">
<link rel="canonical" href="{{ $canonicalUrl }}">
<meta property="og:type" content="website">
<meta property="og:title" content="{{ $fullTitle }}">
<meta property="og:description" content="{{ Str::limit(strip_tags($description), 320, '') }}">
<meta property="og:url" content="{{ $canonicalUrl }}">
@if($ogImage)
<meta property="og:image" content="{{ $ogImage }}">
@endif
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $fullTitle }}">
<meta name="twitter:description" content="{{ Str::limit(strip_tags($description), 320, '') }}">
@if($ogImage)
<meta name="twitter:image" content="{{ $ogImage }}">
@endif
@if($jsonLd)
<script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endif
