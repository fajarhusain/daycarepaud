<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kuitansi Penerimaan {{ $d['nomor_bukti'] ?? '' }}</title>
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
        table { border-collapse: collapse; width: 100%; }
        .meta-out { margin-bottom: 6px; }
        .meta-out td {
            text-align: right;
            font-size: 10px;
            line-height: 1.35;
        }
        .box { border: 1.5px solid #000; }
        .box > tbody > tr > td {
            padding: 4px 22px;
            vertical-align: top;
        }
        .title {
            text-align: center;
            font-weight: bold;
            font-size: 13px;
            text-decoration: underline;
            padding: 16px 22px 8px !important;
        }
        .nomor {
            text-align: center;
            padding-bottom: 14px !important;
        }
        .field {
            padding-top: 3px !important;
            padding-bottom: 3px !important;
        }
        .dots {
            border-bottom: 1px dotted #000;
            display: inline-block;
            min-width: 65%;
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
        .sign-gap { height: 32px; }
        .sign-right {
            font-size: 11px;
            padding-top: 10px !important;
            padding-bottom: 20px !important;
            text-align: center;
        }
        .sign-left {
            font-size: 11px;
            padding-top: 10px !important;
            padding-bottom: 20px !important;
        }
        .sign-line {
            border-bottom: 1px solid #000;
            display: block;
            width: 200px;
            height: 52px;
            margin-top: 8px;
        }
        .sign-line-right { margin: 8px auto 0; }
        .small { font-size: 10px; }
        .sk-inline {
            display: block;
            margin-top: 4px;
            font-size: 11px;
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
            <td colspan="2" class="title">KUITANSI/BUKTI PENERIMAAN</td>
        </tr>
        <tr>
            <td colspan="2" class="nomor">Nomor : {{ $d['nomor_bukti'] ?? '....................' }}</td>
        </tr>

        <tr>
            <td colspan="2" class="field">
                Sudah terima dari :
                <span class="dots-wide">{{ $d['sudah_terima_dari'] ?? '' }}</span>
            </td>
        </tr>
        <tr>
            <td colspan="2" class="field">
                Jumlah uang : <span class="dots">{{ $d['jumlah_uang_formatted'] ?? '' }}</span>
            </td>
        </tr>
        <tr>
            <td colspan="2" class="field">
                Terbilang : <span class="dots-wide">{{ $d['terbilang'] ?? '' }}</span>
            </td>
        </tr>
        <tr>
            <td colspan="2" class="field">
                Untuk penerimaan :
                <span class="dots-wide">{{ $d['untuk_pembayaran'] ?? '' }}</span>
                <span class="sk-inline">
                    No. {{ $d['sk_ppk_nomor'] ?? '....................' }}
                    Tanggal {{ $d['sk_ppk_tanggal'] ?? '....................' }}
                </span>
            </td>
        </tr>

        <tr><td colspan="2" class="sign-gap"></td></tr>

        <tr>
            <td class="sign-left" width="52%">
                Setuju dibebankan pada mata anggaran berkenaan<br>
                a.n. Kuasa Pengguna Anggaran<br>
                Pejabat Pembuat Komitmen<br>
                <span class="small">Tanda tangan dan stempel</span>
                <span class="sign-line"></span>
                <span class="small">(Nama jelas {{ $d['ppk_nama'] ?? '____________________' }})</span><br>
                NIP. {{ $d['ppk_nip'] ?? '....................' }}
            </td>
            <td class="sign-right" width="48%">
                {{ $d['tempat_tanggal'] ?? '....................' }}<br>
                Kepala RA<br>
                <span class="small">Tanda tangan, stempel di atas materai Rp. 6.000</span>
                <span class="sign-line sign-line-right"></span>
                <span class="small">(Nama jelas {{ $d['kepala_ra_nama'] ?? '____________________' }})</span>
            </td>
        </tr>
    </table>
</body>
</html>
