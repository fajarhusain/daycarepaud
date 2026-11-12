@props(['label' => 'Tambah'])

<x-icon-btn
    type="button"
    :label="$label"
    icon="M12 4v16m8-8H4"
    variant="primary"
    {{ $attributes->merge(['class' => 'toolbar-primary shrink-0']) }}
/>
