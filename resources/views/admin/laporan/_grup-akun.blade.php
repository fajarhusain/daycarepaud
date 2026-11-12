@php
    /** @var \Illuminate\Support\Collection $grup */
    $no = $no ?? 1;
@endphp
@foreach($grup as $snp => $byKomponen)
    <tr>
        <td colspan="3" class="font-semibold text-xs py-2" style="background:#F5F2ED; color:#6B5B3A;">{{ $snp }}</td>
    </tr>
    @foreach($byKomponen as $komponen => $items)
        @if($komponen && $komponen !== '')
            <tr>
                <td colspan="3" class="text-xs pl-4 py-1" style="color:#9E9790;">{{ $komponen }}</td>
            </tr>
        @endif
        @foreach($items as $item)
            <tr>
                <td class="text-sm" style="color:#9E9790;">{{ $no++ }}</td>
                <td class="text-sm" style="color:#2C2C2C;">{{ $item['akun']->kode }}  {{ $item['akun']->nama }}</td>
                <td class="text-right font-semibold text-sm" style="color:{{ $warna ?? '#1A6B6B' }};">
                    Rp {{ number_format($item['saldo'], 0, ',', '.') }}
                </td>
            </tr>
        @endforeach
    @endforeach
@endforeach
