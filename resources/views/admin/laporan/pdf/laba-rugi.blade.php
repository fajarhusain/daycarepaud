<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laba Rugi {{ $periode['label'] }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ccc; padding: 4px; }
        th { background: #f5f5f5; }
        .text-right { text-align: right; }
        h1 { font-size: 14px; margin: 0 0 4px; }
        .muted { color: #666; font-size: 9px; }
    </style>
</head>
<body>
    <h1>{{ $sekolah->name ?? 'Sekolah' }}</h1>
    <p class="muted">Laporan Laba Rugi  {{ $periode['label'] }}</p>

    <table>
        <thead>
            <tr>
                <th>Keterangan</th>
                <th class="text-right">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr><td colspan="2"><strong>Pendapatan</strong></td></tr>
            @foreach($pendapatan as $item)
                <tr>
                    <td>{{ $item['akun']->kode }}  {{ $item['akun']->nama }}</td>
                    <td class="text-right">{{ number_format($item['saldo'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr><td><strong>Total Pendapatan</strong></td><td class="text-right"><strong>{{ number_format($totalPendapatan, 0, ',', '.') }}</strong></td></tr>

            <tr><td colspan="2"><strong>Beban</strong></td></tr>
            @foreach($beban as $item)
                <tr>
                    <td>{{ $item['akun']->kode }}  {{ $item['akun']->nama }}</td>
                    <td class="text-right">{{ number_format($item['saldo'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr><td><strong>Total Beban</strong></td><td class="text-right"><strong>{{ number_format($totalBeban, 0, ',', '.') }}</strong></td></tr>
            <tr>
                <td><strong>Surplus (Defisit) Periode</strong></td>
                <td class="text-right"><strong>{{ number_format($surplusDefisit, 0, ',', '.') }}</strong></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
