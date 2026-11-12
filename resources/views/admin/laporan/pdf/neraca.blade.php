<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Neraca {{ $periode['label'] }}</title>
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
    <p class="muted">Laporan Posisi Keuangan (Neraca)  {{ $periode['label'] }}</p>
    <p class="muted">Per {{ \Carbon\Carbon::parse($periode['neracaSampai'])->format('d/m/Y') }}</p>

    <table>
        <thead>
            <tr>
                <th>Keterangan</th>
                <th class="text-right">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr><td colspan="2"><strong>Aset</strong></td></tr>
            @foreach($aset as $item)
                <tr>
                    <td>{{ $item['akun']->kode }}  {{ $item['akun']->nama }}</td>
                    <td class="text-right">{{ number_format($item['saldo'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr><td><strong>Total Aset</strong></td><td class="text-right"><strong>{{ number_format($totalAset, 0, ',', '.') }}</strong></td></tr>

            <tr><td colspan="2"><strong>Liabilitas</strong></td></tr>
            @foreach($liabilitas as $item)
                <tr>
                    <td>{{ $item['akun']->kode }}  {{ $item['akun']->nama }}</td>
                    <td class="text-right">{{ number_format($item['saldo'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr><td><strong>Total Liabilitas</strong></td><td class="text-right"><strong>{{ number_format($totalLiabilitas, 0, ',', '.') }}</strong></td></tr>

            <tr><td colspan="2"><strong>Ekuitas</strong></td></tr>
            @foreach($ekuitas as $item)
                <tr>
                    <td>{{ $item['akun']->kode }}  {{ $item['akun']->nama }}</td>
                    <td class="text-right">{{ number_format($item['saldo'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr>
                <td>Surplus (defisit) tahun berjalan</td>
                <td class="text-right">{{ number_format($surplusBerjalan, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td><strong>Total Liabilitas + Ekuitas + Surplus</strong></td>
                <td class="text-right"><strong>{{ number_format($totalPasiva, 0, ',', '.') }}</strong></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
