@props(['icon' => 'fa-solid fa-circle'])
<div class="auth-field-wrap">
    {{ $slot }}
    <span class="auth-field-icon" aria-hidden="true"><i class="{{ $icon }}"></i></span>
</div>
