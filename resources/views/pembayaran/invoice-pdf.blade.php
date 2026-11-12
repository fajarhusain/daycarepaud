<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoiceNo }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #2C2C2C;
            line-height: 1.5;
            margin: 0;
            padding: 28px;
        }
        h1, h2, p { margin: 0; }
        .header {
            border-bottom: 2px solid #1A6B6B;
            padding-bottom: 14px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 20px;
            color: #1A6B6B;
            margin-bottom: 4px;
        }
        .meta { font-size: 10px; color: #6B6560; margin-top: 4px; }
        .grid {
            width: 100%;
            margin-bottom: 20px;
        }
        .grid td { vertical-align: top; width: 50%; padding: 0 0 8px; }
        .label {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            color: #9E9790;
            letter-spacing: 0.04em;
            margin-bottom: 2px;
        }
        .value { font-size: 11px; font-weight: bold; }
        .badge {
            display: inline-block;
            font-size: 10px;
            font-weight: bold;
            padding: 4px 10px;
            border-radius: 4px;
            background: #D0E8E8;
            color: #1A6B6B;
        }
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        table.items th,
        table.items td {
            border: 1px solid #E8E4DE;
            padding: 8px 10px;
            text-align: left;
        }
        table.items th {
            background: #F9FAFB;
            font-size: 9px;
            text-transform: uppercase;
            color: #9E9790;
        }
        table.items td.amount { text-align: right; white-space: nowrap; }
        .total-box {
            width: 260px;
            margin-left: auto;
            border: 2px solid #1A6B6B;
            border-radius: 6px;
            padding: 12px 14px;
        }
        .total-row {
            display: table;
            width: 100%;
            margin-bottom: 6px;
        }
        .total-row span {
            display: table-cell;
        }
        .total-row span:last-child {
            text-align: right;
            font-weight: bold;
        }
        .grand-total {
            border-top: 1px solid #D0E8E8;
            margin-top: 8px;
            padding-top: 8px;
            font-size: 14px;
            color: #1A6B6B;
        }
        .footer {
            margin-top: 28px;
            padding-top: 12px;
            border-top: 1px solid #E8E4DE;
            font-size: 9px;
            color: #9E9790;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>INVOICE PEMBAYARAN</h1>
        <p class="meta">{{ $pembayaran->sekolah->name ?? 'Sekolah' }}</p>
        @if($pembayaran->sekolah?->address)
            <p class="meta">{{ $pembayaran->sekolah->address }}</p>
        @endif
        @if($pembayaran->sekolah?->phone)
            <p class="meta">Telp: {{ $pembayaran->sekolah->phone }}</p>
        @endif
    </div>

    <table class="grid">
        <tr>
            <td>
                <p class="label">No. Invoice</p>
                <p class="value">{{ $invoiceNo }}</p>
            </td>
            <td style="text-align: right;">
                <p class="label">Status</p>
                <span class="badge">DISETUJUI / LUNAS</span>
            </td>
        </tr>
        <tr>
            <td>
                <p class="label">Tanggal Lunas</p>
                <p class="value">{{ $pembayaran->approved_at?->translatedFormat('d F Y H:i') ?? '-' }}</p>
            </td>
            <td style="text-align: right;">
                <p class="label">Periode Tagihan</p>
                <p class="value">{{ $pembayaran->getPeriodeLabel() }}</p>
            </td>
        </tr>
    </table>

    <table class="grid">
        <tr>
            <td>
                <p class="label">Nama Siswa</p>
                <p class="value">{{ $pembayaran->anak->name }}</p>
            </td>
            <td>
                <p class="label">Kelas</p>
                <p class="value">{{ $pembayaran->anak->kelas->name ?? '-' }}</p>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <p class="label">Jenis Biaya</p>
                <p class="value">{{ $pembayaran->biayaBulananSekolah->nama_biaya ?? '-' }}</p>
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Keterangan</th>
                <th style="width: 80px; text-align: center;">Qty</th>
                <th style="width: 110px; text-align: right;">Harga</th>
                <th style="width: 110px; text-align: right;">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Biaya Bulanan</td>
                <td style="text-align: center;">1</td>
                <td class="amount">{{ $pembayaran->getBiayaPerHariFormatted() }}</td>
                <td class="amount">{{ $pembayaran->getSubtotalFormatted() }}</td>
            </tr>
            @php $items = $pembayaran->items; @endphp
            @if($items && $items->count() > 0)
                @foreach($items as $item)
                    <tr>
                        <td>{{ $item->nama_item }}</td>
                        <td style="text-align: center;">1</td>
                        <td class="amount">Rp {{ number_format($item->jumlah, 0, ',', '.') }}</td>
                        <td class="amount">Rp {{ number_format($item->jumlah, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            @endif
            @if($pembayaran->diskon && $pembayaran->nilai_diskon > 0)
                <tr>
                    <td>Diskon ({{ $pembayaran->diskon->nama_diskon }})</td>
                    <td style="text-align: center;">1</td>
                    <td class="amount">-{{ $pembayaran->getNilaiDiskonFormatted() }}</td>
                    <td class="amount">-{{ $pembayaran->getNilaiDiskonFormatted() }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <p style="font-size: 10px; color: #6B6560; margin-bottom: 12px;">Hari Hadir: {{ $pembayaran->hari_hadir }} hari</p>

    <div class="total-box">
        <div class="total-row">
            <span>Subtotal</span>
            <span>{{ $pembayaran->getSubtotalFormatted() }}</span>
        </div>
        @if($items && $items->count() > 0)
            <div class="total-row">
                <span>Total Biaya Lain</span>
                <span>{{ $pembayaran->getTotalBiayaTambahanFormatted() }}</span>
            </div>
        @endif
        @if($pembayaran->nilai_diskon > 0)
            <div class="total-row">
                <span>Diskon</span>
                <span>-{{ $pembayaran->getNilaiDiskonFormatted() }}</span>
            </div>
        @endif
        <div class="total-row grand-total">
            <span>Total Bayar</span>
            <span>{{ $pembayaran->getTotalFormatted() }}</span>
        </div>
    </div>

    @if($pembayaran->approvedBy)
        <p style="margin-top: 24px; font-size: 10px; color: #6B6560;">
            Ditandai lunas oleh: <strong>{{ $pembayaran->approvedBy->name }}</strong>
        </p>
    @endif

    <div class="footer">
        Dokumen ini dibuat otomatis oleh sistem DaycareAI. Invoice berlaku sebagai bukti pembayaran yang telah lunas.
    </div>
</body>
</html>
