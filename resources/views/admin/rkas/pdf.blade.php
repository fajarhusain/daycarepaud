<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan RKAS {{ $rka->label }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ccc; padding: 4px; }
        th { background: #f5f5f5; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <h1>Laporan RKAS  {{ \App\Support\TahunAjaran::label($rka->tahun_ajaran, $rka->semester) }}</h1>
    <p>Periode: {{ $rka->tanggal_mulai->format('d/m/Y') }} – {{ $rka->tanggal_akhir->format('d/m/Y') }}</p>
    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Uraian</th>
                @foreach($sumberDanas as $sd)
                    <th class="text-right">{{ $sd->kode }} (A)</th>
                    <th class="text-right">{{ $sd->kode }} (R)</th>
                @endforeach
                <th class="text-right">Total A</th>
                <th class="text-right">Total R</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
                <tr>
                    <td>{{ $row['akun']->kode }}</td>
                    <td>{{ Str::limit($row['akun']->uraian ?? $row['akun']->nama, 50) }}</td>
                    @foreach($sumberDanas as $sd)
                        @php $c = $row['cells'][$sd->id]; @endphp
                        <td class="text-right">{{ number_format($c['anggaran'], 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($c['realisasi'], 0, ',', '.') }}</td>
                    @endforeach
                    <td class="text-right">{{ number_format($row['total_anggaran'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($row['total_realisasi'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
