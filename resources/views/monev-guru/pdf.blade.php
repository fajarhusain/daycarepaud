<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Monev Guru  {{ $evaluasi->pengajar->name ?? 'Guru' }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #2C2C2C; line-height: 1.5; margin: 0; padding: 24px; }
        h1, h2, h3, p { margin: 0; }
        .header { border-bottom: 2px solid #1A6B6B; padding-bottom: 14px; margin-bottom: 18px; }
        .header h1 { font-size: 18px; color: #1A6B6B; margin-bottom: 4px; }
        .header .meta { font-size: 10px; color: #6B6560; margin-top: 6px; }
        .score-box { text-align: center; background: #E8F5F5; border: 1px solid #1A6B6B; border-radius: 8px; padding: 12px; margin: 16px 0; }
        .score-box .score { font-size: 28px; font-weight: bold; color: #1A6B6B; }
        table { width: 100%; border-collapse: collapse; margin: 12px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; vertical-align: top; }
        th { background: #FAF6F0; font-size: 10px; }
        .section { margin-top: 18px; }
        .section h2 { font-size: 13px; color: #1A6B6B; margin-bottom: 8px; border-bottom: 1px solid #eee; padding-bottom: 4px; }
        .footer { margin-top: 40px; font-size: 10px; color: #6B6560; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Monev Guru</h1>
        <p class="meta">{{ $evaluasi->sekolah->name ?? 'Sekolah' }}</p>
        <p class="meta">Periode: {{ $evaluasi->periodeLabel() }}@if($evaluasi->judul)  {{ $evaluasi->judul }}@endif</p>
    </div>

    <p><strong>Nama Guru:</strong> {{ $evaluasi->pengajar->name ?? '' }}</p>
    <p><strong>Jabatan:</strong> {{ $evaluasi->pengajar->jabatan ?? '' }}</p>
    <p><strong>Evaluator:</strong> {{ $evaluasi->evaluator->name ?? '' }}</p>
    @if($evaluasi->finalized_at)
        <p><strong>Tanggal Final:</strong> {{ $evaluasi->finalized_at->format('d M Y H:i') }}</p>
    @endif

    @if($evaluasi->skor_keseluruhan !== null)
    <div class="score-box">
        <div class="score">{{ $evaluasi->skor_keseluruhan }}</div>
        <div>Skor Keseluruhan (weighted average)</div>
    </div>
    @endif

    <div class="section">
        <h2>Penilaian per Kriteria</h2>
        <table>
            <thead>
                <tr><th style="width:35%">Kriteria</th><th style="width:10%">Bobot</th><th style="width:10%">Skor</th><th>Catatan</th></tr>
            </thead>
            <tbody>
                @foreach($evaluasi->items->filter(fn ($i) => $i->kriteria?->is_active)->sortBy(fn($i) => $i->kriteria?->urutan ?? 999) as $item)
                <tr>
                    <td>{{ $item->kriteria->nama ?? '' }}</td>
                    <td>{{ $item->kriteria->bobot ?? '' }}%</td>
                    <td><strong>{{ $item->skor ?? '' }}</strong></td>
                    <td>{{ $item->catatan ?: '' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($evaluasi->catatan_umum)
    <div class="section">
        <h2>Catatan Umum</h2>
        <p>{!! nl2br(e($evaluasi->catatan_umum)) !!}</p>
    </div>
    @endif

    @if($evaluasi->rekomendasi)
    <div class="section">
        <h2>Rekomendasi Tindak Lanjut</h2>
        <p>{!! nl2br(e($evaluasi->rekomendasi)) !!}</p>
    </div>
    @endif

    <div class="footer">
        <p>Dicetak: {{ now()->format('d M Y H:i') }}</p>
    </div>
</body>
</html>
