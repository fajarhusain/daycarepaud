@props(['label' => 'Filter', 'variant' => 'primary'])

<x-icon-btn
    type="submit"
    :label="$label"
    icon="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
    :variant="$variant"
    {{ $attributes }}
/>
