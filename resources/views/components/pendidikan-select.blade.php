@props([
    'name' => 'pendidikan',
    'value' => null,
    'required' => false,
])

@php
    $opts = \App\Support\PendidikanTerakhir::options();
    $current = old($name, $value);
    if (filled($current) && ! in_array($current, $opts, true)) {
        $opts = array_values(array_unique(array_merge([$current], $opts)));
    }
@endphp
<select
    name="{{ $name }}"
    @if($required) required @endif
    {{ $attributes->merge(['class' => 'input-field']) }}
>
    <option value=""> Pilih pendidikan terakhir </option>
    @foreach ($opts as $opt)
        <option value="{{ $opt }}" @selected($current === $opt)>{{ $opt }}</option>
    @endforeach
</select>
