@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'text-xs mt-1.5 space-y-0.5']) }} style="color: #C0392B;">
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
