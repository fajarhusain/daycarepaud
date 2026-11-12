@php
    use App\Support\MonevSummaryPresenter;

    $scoreTotal = array_sum($scoreDist);
    $indikatorCount = count($summary->data_snapshot['indikator_tercatat'] ?? []);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Monev {{ $anak->name }}  {{ $summary->periodeLabel() }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #2C2C2C;
            line-height: 1.5;
            margin: 0;
            padding: 24px;
        }
        h1, h2, h3, h4, p { margin: 0; }
        .header {
            border-bottom: 2px solid #1A6B6B;
            padding-bottom: 14px;
            margin-bottom: 18px;
        }
        .header h1 {
            font-size: 18px;
            color: #1A6B6B;
            margin-bottom: 4px;
        }
        .header .meta {
            font-size: 10px;
            color: #6B6560;
            margin-top: 6px;
        }
        .badge {
            display: inline-block;
            font-size: 9px;
            font-weight: bold;
            padding: 3px 8px;
            border-radius: 4px;
            margin-right: 6px;
        }
        .badge-teal { background: #D0E8E8; color: #1A6B6B; }
        .badge-amber { background: #FDE9BC; color: #8A6D00; }
        .badge-gray { background: #F0F0F0; color: #6B6560; }
        .stats-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }
        .stats-table td {
            width: 25%;
            padding: 10px 12px;
            border: 1px solid #E8E4DE;
            text-align: center;
            vertical-align: top;
        }
        .stats-table .label {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            color: #9E9790;
            letter-spacing: 0.04em;
        }
        .stats-table .value {
            font-size: 16px;
            font-weight: bold;
            color: #1A6B6B;
            margin-top: 4px;
        }
        .section {
            margin-bottom: 16px;
            page-break-inside: avoid;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #1A6B6B;
            border-bottom: 1px solid #E8E4DE;
            padding-bottom: 6px;
            margin-bottom: 10px;
        }
        .subsection-title {
            font-size: 11px;
            font-weight: bold;
            color: #2C2C2C;
            margin-bottom: 6px;
        }
        ul {
            margin: 0;
            padding: 0 0 0 14px;
        }
        li {
            margin-bottom: 5px;
        }
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        table.data th,
        table.data td {
            border: 1px solid #E8E4DE;
            padding: 6px 8px;
            text-align: left;
            font-size: 10px;
        }
        table.data th {
            background: #FAF6F0;
            color: #6B6560;
            font-weight: bold;
        }
        .color-dot {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 6px;
            vertical-align: middle;
        }
        .aspek-grid {
            width: 100%;
            border-collapse: collapse;
        }
        .aspek-grid td {
            width: 50%;
            vertical-align: top;
            padding: 0 8px 12px 0;
        }
        .aspek-card {
            border: 1px solid #E8E4DE;
            background: #FAF6F0;
            border-radius: 6px;
            padding: 10px;
        }
        .aspek-card h4 {
            font-size: 10px;
            margin-bottom: 6px;
        }
        .feedback-item {
            border-left: 3px solid #1A6B6B;
            padding: 6px 10px;
            margin-bottom: 8px;
            font-style: italic;
            color: #444;
        }
        .footer {
            margin-top: 24px;
            padding-top: 10px;
            border-top: 1px solid #E8E4DE;
            font-size: 9px;
            color: #9E9790;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Monev Matrikulasi</h1>
        <p style="font-size: 13px; font-weight: bold;">{{ $anak->name }}</p>
        <p class="meta">
            {{ $anak->sekolah?->name ?? 'Sekolah' }}
            · Kelas {{ $anak->kelas?->name ?? '' }}
            · {{ $anak->age }}
            · Periode {{ $summary->periodeLabel() }}
        </p>
        <p class="meta" style="margin-top: 8px;">
            @if($summary->sumber === 'otomatis')
                <span class="badge badge-teal">Otomatis</span>
            @else
                <span class="badge badge-amber">Manual</span>
            @endif
            <span class="badge badge-gray">Dibuat {{ $summary->generated_at->translatedFormat('d M Y H:i') }}</span>
        </p>
    </div>

    <table class="stats-table">
        <tr>
            <td>
                <div class="label">Total Entri</div>
                <div class="value">{{ $totalEntri }}</div>
            </td>
            <td>
                <div class="label">Aspek Dinilai</div>
                <div class="value" style="color:#2C2C2C;">{{ count($perAspek) }}</div>
            </td>
            <td>
                <div class="label">Indikator</div>
                <div class="value" style="color:#2C2C2C;">{{ $indikatorCount }}</div>
            </td>
            <td>
                <div class="label">Umpan Balik</div>
                <div class="value" style="color:#2C2C2C;">{{ count($feedbacks) }}</div>
            </td>
        </tr>
    </table>

    <div class="section">
        <h2 class="section-title">Ringkasan AI</h2>
        @foreach($sections as $section)
            <div style="margin-bottom: 12px;">
                <h3 class="subsection-title">{{ $section['title'] }}</h3>
                <ul>
                    @foreach($section['points'] ?? [] as $point)
                        <li>{{ $point }}</li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>

    @if($pieSegments !== [])
        <div class="section">
            <h2 class="section-title">Distribusi Skor Capaian</h2>
            <table class="data">
                <thead>
                    <tr>
                        <th>Skor</th>
                        <th>Jumlah</th>
                        <th>Persentase</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pieSegments as $segment)
                        <tr>
                            <td>
                                <span class="color-dot" style="background: {{ $segment['color'] }};"></span>
                                {{ $segment['label'] }}
                            </td>
                            <td>{{ $segment['count'] }}</td>
                            <td>{{ $segment['percent'] }}%</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td><strong>Total</strong></td>
                        <td><strong>{{ $scoreTotal }}</strong></td>
                        <td>100%</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif

    @if($perAspek !== [])
        <div class="section">
            <h2 class="section-title">Capaian per Aspek</h2>
            <table class="aspek-grid">
                @foreach(array_chunk($perAspek, 2, true) as $row)
                    <tr>
                        @foreach($row as $aspek => $data)
                            @php
                                $aspekNarrative = MonevSummaryPresenter::perAspekNarrativePoints($aspek, $data);
                                $aspekStats = MonevSummaryPresenter::perAspekSummaryPoints($aspek, $data);
                            @endphp
                            <td>
                                <div class="aspek-card">
                                    <h4>{{ $aspek }} <span style="color:#1A6B6B;">({{ $data['jumlah'] }} entri)</span></h4>
                                    <ul>
                                        @foreach($aspekNarrative as $point)
                                            <li>{{ $point }}</li>
                                        @endforeach
                                    </ul>
                                    <p style="font-size: 9px; font-weight: bold; color: #9E9790; margin: 8px 0 4px; text-transform: uppercase;">Statistik</p>
                                    <ul style="font-size: 9px; color: #6B6560;">
                                        @foreach($aspekStats as $point)
                                            <li>{{ $point }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </td>
                        @endforeach
                        @if(count($row) === 1)
                            <td></td>
                        @endif
                    </tr>
                @endforeach
            </table>
        </div>
    @endif

    <div class="section">
        <h2 class="section-title">Cuplikan Umpan Balik Guru</h2>
        @if($feedbacks === [])
            <p style="color:#9E9790;">Belum ada umpan balik tercatat.</p>
        @else
            @foreach($feedbacks as $fb)
                <div class="feedback-item">"{{ $fb }}"</div>
            @endforeach
        @endif
    </div>

    <div class="footer">
        Dokumen ini digenerate otomatis dari sistem Monev · {{ now()->translatedFormat('d M Y H:i') }}
    </div>
</body>
</html>
