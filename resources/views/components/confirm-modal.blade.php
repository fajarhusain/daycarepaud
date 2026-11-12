@props([
    'show' => 'showConfirmModal',
    'action' => null,
    'actionBinding' => 'confirmRoute',
    'method' => 'POST',
    'title',
    'message' => null,
    'submit' => 'Ya, Hapus',
    'cancel' => 'Batal',
    'submitClass' => 'btn-danger',
    'icon' => 'trash',
    'maxWidth' => 'max-w-sm',
])

@php
    $iconBg = $icon === 'warning' ? '#FFF8E7' : '#FAD7D2';
    $iconColor = $icon === 'warning' ? '#D97706' : '#C0392B';
@endphp

<div x-show="{{ $show }}" {{ $attributes->merge(['class' => 'modal-overlay']) }} style="display:none;">
    <div x-show="{{ $show }}" x-transition class="modal-box {{ $maxWidth }}" @click.away="{{ $show }} = false">
        <form @if($action) action="{{ $action }}" @else x-bind:action="{{ $actionBinding }}" @endif method="POST">
            @csrf
            @if(strtoupper($method) !== 'POST')
                @method($method)
            @endif
            <div class="modal-body text-center py-6">
                <div class="h-14 w-14 rounded-2xl mx-auto mb-4 flex items-center justify-center" style="background:{{ $iconBg }};">
                    @if($icon === 'warning')
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:{{ $iconColor }};">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    @else
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:{{ $iconColor }};">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    @endif
                </div>
                <h3 class="section-title">{{ $title }}</h3>
                @if($message)
                    <p class="section-subtitle mt-1">{{ $message }}</p>
                @endif
                {{ $slot }}
            </div>
            <div class="modal-footer">
                <button type="button" @click="{{ $show }} = false" class="btn-secondary">{{ $cancel }}</button>
                <button type="submit" class="{{ $submitClass }}">{{ $submit }}</button>
            </div>
        </form>
    </div>
</div>
