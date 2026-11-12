<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Buku Besar {{ $gl['akun']->kode }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ccc; padding: 3px; }
        th { background: #f5f5f5; }
        .text-right { text-align: right; }
        h1 { font-size: 14px; margin: 0 0 4px; }
        .muted { color: #666; font-size: 9px; }
    </style>
</head>
<body>
    <h1>{{ $sekolah->name ?? 'Sekolah' }}</h1>
    <p class="muted">Buku Besar  {{ $gl['akun']->kode }} {{ $gl['akun']->nama }}</p>
    <p class="muted">{{ $periode['label'] }}</p>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>No. Jurnal</th>
                <th>Keterangan</th>
                <th class="text-right">Debit</th>
                <th class="text-right">Kredit</th>
                <th class="text-right">Saldo</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="3"><strong>Saldo awal</strong></td>
                <td></td>
                <td></td>
                <td class="text-right"><strong>{{ number_format($gl['saldoAwal'], 0, ',', '.') }}</strong></td>
            </tr>
            @foreach($gl['mutasi'] as $row)
                @php $j = $row['line']->jurnal; @endphp
                <tr>
                    <td>{{ $j?->tanggal?->format('d/m/Y') ?? '' }}</td>
                    <td>{{ $j?->no_jurnal ?? '' }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($j?->deskripsi ?? '', 40) }}</td>
                    <td class="text-right">{{ $row['debit'] > 0 ? number_format($row['debit'], 0, ',', '.') : '' }}</td>
                    <td class="text-right">{{ $row['kredit'] > 0 ? number_format($row['kredit'], 0, ',', '.') : '' }}</td>
                    <td class="text-right">{{ number_format($row['saldo'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="3"><strong>Saldo akhir</strong></td>
                <td></td>
                <td></td>
                <td class="text-right"><strong>{{ number_format($gl['saldoAkhir'], 0, ',', '.') }}</strong></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
