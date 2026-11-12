<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kuitansi Pembayaran {{ $d['nomor_bukti'] ?? '' }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12px;
            color: #000;
            margin: 0;
            padding: 28px 36px;
            line-height: 1.45;
        }
        table { border-collapse: collapse; }
        .meta-out {
            width: 100%;
            margin-bottom: 6px;
        }
        .meta-out td {
            text-align: right;
            font-size: 10px;
            line-height: 1.35;
        }
        .box {
            width: 100%;
            border: 1.5px solid #000;
        }
        .box > tbody > tr > td {
            padding: 4px 22px;
            vertical-align: top;
        }
        .title {
            text-align: center;
            font-weight: bold;
            font-size: 13px;
            text-decoration: underline;
            padding: 16px 22px 10px !important;
            letter-spacing: 0.3px;
        }
        .meta-in {
            text-align: right;
            font-size: 11px;
            padding-bottom: 10px !important;
            line-height: 1.5;
        }
        .field {
            padding-top: 2px !important;
            padding-bottom: 2px !important;
        }
        .sub {
            padding-left: 42px !important;
            padding-top: 1px !important;
            padding-bottom: 1px !important;
        }
        .dots {
            border-bottom: 1px dotted #000;
            display: inline-block;
            min-width: 62%;
            padding-bottom: 1px;
        }
        .dots-wide {
            border-bottom: 1px dotted #000;
            display: block;
            width: 100%;
            min-height: 15px;
            margin-top: 3px;
            padding-bottom: 1px;
        }
        .gap { height: 6px; }
        .sign-gap { height: 18px; }
        .sign-block {
            text-align: center;
            font-size: 11px;
            padding-top: 8px !important;
            padding-bottom: 4px !important;
        }
        .sign-line {
            border-bottom: 1px dotted #000;
            display: inline-block;
            width: 200px;
            height: 50px;
            margin: 6px 0 4px;
        }
        .sign-name {
            font-size: 10px;
        }
        .center-note {
            text-align: center;
            font-size: 11px;
            padding: 10px 22px !important;
        }
        .sign-foot {
            padding-top: 6px !important;
            padding-bottom: 22px !important;
            font-size: 11px;
            vertical-align: bottom;
            text-align: center;
        }
        .foot-line {
            border-bottom: 1px dotted #000;
            display: block;
            width: 200px;
            height: 44px;
            margin: 8px auto 0;
        }
        .foot-name {
            font-size: 10px;
            margin-top: 2px;
        }
    </style>
</head>
<body>
    <table class="meta-out">
        <tr>
            <td>
                Formulir BOP-12<br>
                Dibuat oleh Kepala RA
            </td>
        </tr>
    </table>

    <table class="box">
        <tr>
            <td colspan="2" class="title">KUITANSI/BUKTI PEMBAYARAN</td>
        </tr>
        <tr>
            <td width="52%"></td>
            <td width="48%" class="meta-in">
                Tahun Anggaran : {{ $d['tahun_anggaran'] ?? '........' }}<br>
                Nomor Bukti : {{ $d['nomor_bukti'] ?? '....................' }}
            </td>
        </tr>

        <tr>
            <td colspan="2" class="field">
                Sudah terima dari : {{ $d['sudah_terima_dari'] ?? 'Kepala RA' }}
            </td>
        </tr>
        <tr>
            <td colspan="2" class="sub">
                RA : <span class="dots">{{ $d['nama_ra'] ?? '' }}</span>
            </td>
        </tr>
        <tr>
            <td colspan="2" class="sub">
                Desa/Kecamatan : <span class="dots">{{ $d['desa_kecamatan'] ?? '' }}</span>
            </td>
        </tr>
        <tr>
            <td colspan="2" class="sub">
                Kabupaten : <span class="dots">{{ $d['kabupaten'] ?? '' }}</span>
            </td>
        </tr>
        <tr>
            <td colspan="2" class="sub">
                Provinsi : <span class="dots">{{ $d['provinsi'] ?? '' }}</span>
            </td>
        </tr>

        <tr><td colspan="2" class="gap"></td></tr>

        <tr>
            <td colspan="2" class="field">
                Jumlah uang : <span class="dots">{{ $d['jumlah_uang_formatted'] ?? '' }}</span>
            </td>
        </tr>
        <tr>
            <td colspan="2" class="field">
                Terbilang :
                <span class="dots-wide">{{ $d['terbilang'] ?? '' }}</span>
            </td>
        </tr>
        <tr>
            <td colspan="2" class="field">
                Untuk pembayaran :
                <span class="dots-wide">{{ $d['untuk_pembayaran'] ?? '' }}</span>
            </td>
        </tr>
        <tr>
            <td colspan="2" class="field">
                Sumber dana :
                dana {{ $d['sumber_dana'] ?? 'BOP RA' }} Periode bulan
                <span class="dots" style="min-width:80px;">{{ $d['sumber_dana_periode_awal'] ?? '' }}</span>
                s.d
                <span class="dots" style="min-width:100px;">{{ $d['sumber_dana_periode_akhir'] ?? '' }}</span>
            </td>
        </tr>

        <tr><td colspan="2" class="sign-gap"></td></tr>

        <tr>
            <td width="52%"></td>
            <td width="48%" class="sign-block">
                Penerima Uang<br>
                Tanda tangan<br>
                <span class="sign-line"></span><br>
                <span class="sign-name">(Nama jelas {{ $d['penerima_nama'] ?? '____________________' }})</span>
            </td>
        </tr>

        <tr>
            <td colspan="2" class="center-note">
                Lunas dibayar tanggal {{ $d['tanggal_lunas'] ?? '... ... ...' }}
            </td>
        </tr>

        <tr>
            <td class="sign-foot">
                Kepala RA
                <span class="foot-line"></span>
                @if(!empty($d['kepala_ra_nama']))
                    <span class="foot-name">{{ $d['kepala_ra_nama'] }}</span>
                @endif
            </td>
            <td class="sign-foot">
                Bendahara RA
                <span class="foot-line"></span>
                @if(!empty($d['bendahara_nama']))
                    <span class="foot-name">{{ $d['bendahara_nama'] }}</span>
                @endif
            </td>
        </tr>
    </table>
</body>
</html>
