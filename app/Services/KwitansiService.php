<?php

namespace App\Services;

use App\Models\Cashflow;
use App\Models\PembayaranBulanan;
use App\Support\Terbilang;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class KwitansiService
{
    private const BULAN_ID = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public function jenisForCashflow(Cashflow $cashflow): string
    {
        $cashflow->loadMissing('jurnal.sourceable');

        if ($cashflow->jurnal?->sourceable instanceof PembayaranBulanan) {
            return 'penerimaan';
        }

        return $this->jenisFromCashflow($cashflow);
    }

    public function defaultsFromCashflow(Cashflow $cashflow): array
    {
        $cashflow->loadMissing([
            'sekolah',
            'sumberDana',
            'jurnal.sourceable.anak.user',
            'jurnal.sourceable.biayaBulananSekolah',
            'akunLawan',
        ]);

        $pembayaran = $cashflow->jurnal?->sourceable;
        if ($pembayaran instanceof PembayaranBulanan) {
            $data = $this->defaultsFromPembayaran($pembayaran);
            $data['jenis'] = 'penerimaan';

            return $data;
        }

        $jenis = $this->jenisFromCashflow($cashflow);
        $sekolah = $cashflow->sekolah;
        $tanggal = Carbon::parse($cashflow->date);
        $lokasi = $this->parseAlamat($sekolah?->address);
        $jumlah = (float) $cashflow->amount;

        $base = [
            'jenis' => $jenis,
            'tahun_anggaran' => (string) $tanggal->year,
            'nomor_bukti' => $cashflow->jurnal?->no_jurnal ?? sprintf('KW-%d-%05d', $tanggal->year, $cashflow->id),
            'jumlah_uang' => $jumlah,
            'terbilang' => Terbilang::make($jumlah),
            'nama_ra' => $sekolah?->name ?? '',
            'desa_kecamatan' => $lokasi['desa_kecamatan'],
            'kabupaten' => $lokasi['kabupaten'],
            'provinsi' => $lokasi['provinsi'],
            'untuk_pembayaran' => $cashflow->description ?? '',
            'tempat_tanggal' => $this->tanggalId($tanggal),
            'tanggal_lunas' => $this->tanggalId($tanggal),
            'tanggal_lunas_iso' => $tanggal->format('Y-m-d'),
            'penerima_nama' => '',
            'kepala_ra_nama' => '',
            'bendahara_nama' => '',
            'ppk_nama' => '',
            'ppk_nip' => '',
            'sk_ppk_nomor' => '',
            'sk_ppk_tanggal' => '',
            'sumber_dana' => $cashflow->sumberDana?->nama ?? 'BOP RA',
            'sumber_dana_periode_awal' => $this->bulanId((int) $tanggal->month),
            'sumber_dana_periode_akhir' => $this->bulanId((int) $tanggal->month).' '.$tanggal->year,
        ];

        if ($jenis === 'penerimaan') {
            $base['sudah_terima_dari'] = 'Kuasa Pengguna Anggaran/Pejabat Pembuat Komitmen Satker';
            $base['untuk_pembayaran'] = $this->defaultUntukPenerimaan($cashflow, $tanggal);
            $base['nama_ra'] = $sekolah?->name ?? '';
        } else {
            $base['sudah_terima_dari'] = 'Kepala RA';
            if (empty($base['untuk_pembayaran']) && $cashflow->akunLawan) {
                $base['untuk_pembayaran'] = $cashflow->akunLawan->uraian ?? $cashflow->akunLawan->nama;
            }
            if (! $cashflow->sumberDana) {
                $base['sumber_dana'] = 'BOP RA';
            }
        }

        return $base;
    }

    public function defaultsFromPembayaran(PembayaranBulanan $pembayaran): array
    {
        $pembayaran->loadMissing(['anak.user', 'biayaBulananSekolah']);
        $tanggal = $pembayaran->approved_at ?? now();
        $tanggal = Carbon::parse($tanggal);
        $jumlah = (float) $pembayaran->total_bayar;
        $jenisBiaya = $pembayaran->biayaBulananSekolah?->nama_biaya ?? 'SPP';
        $namaOrangTua = $pembayaran->anak?->user?->name ?? '';
        $namaAnak = $pembayaran->anak?->name ?? '';

        return [
            'jenis' => 'penerimaan',
            'tahun_anggaran' => (string) $pembayaran->periode_tahun,
            'nomor_bukti' => sprintf('KW-SPP-%d%02d-%05d', $pembayaran->periode_tahun, $pembayaran->periode_bulan, $pembayaran->id),
            'jumlah_uang' => $jumlah,
            'terbilang' => Terbilang::make($jumlah),
            'sudah_terima_dari' => $namaOrangTua ?: $namaAnak,
            'untuk_pembayaran' => "Penerimaan {$jenisBiaya} {$namaAnak} periode {$pembayaran->getPeriodeLabel()}",
            'tempat_tanggal' => $this->tanggalId($tanggal),
            'kepala_ra_nama' => '',
            'ppk_nama' => '',
            'ppk_nip' => '',
            'sk_ppk_nomor' => '',
            'sk_ppk_tanggal' => '',
        ];
    }

    public function validationRules(): array
    {
        return [
            'jenis' => 'required|in:penerimaan,pembayaran',
            'tahun_anggaran' => 'nullable|string|max:10',
            'nomor_bukti' => 'nullable|string|max:100',
            'sudah_terima_dari' => 'nullable|string|max:500',
            'jumlah_uang' => 'required|numeric|min:0',
            'terbilang' => 'nullable|string|max:500',
            'untuk_pembayaran' => 'nullable|string|max:1000',
            'nama_ra' => 'nullable|string|max:200',
            'desa_kecamatan' => 'nullable|string|max:200',
            'kabupaten' => 'nullable|string|max:200',
            'provinsi' => 'nullable|string|max:200',
            'sumber_dana' => 'nullable|string|max:500',
            'sumber_dana_periode_awal' => 'nullable|string|max:100',
            'sumber_dana_periode_akhir' => 'nullable|string|max:100',
            'tempat_tanggal' => 'nullable|string|max:200',
            'tanggal_lunas' => 'nullable|string|max:200',
            'penerima_nama' => 'nullable|string|max:200',
            'kepala_ra_nama' => 'nullable|string|max:200',
            'bendahara_nama' => 'nullable|string|max:200',
            'ppk_nama' => 'nullable|string|max:200',
            'ppk_nip' => 'nullable|string|max:50',
            'sk_ppk_nomor' => 'nullable|string|max:100',
            'sk_ppk_tanggal' => 'nullable|string|max:100',
        ];
    }

    public function download(array $data, ?string $jenis = null): Response
    {
        $jenis = $jenis ?? ($data['jenis'] ?? 'pembayaran');
        $view = $jenis === 'penerimaan'
            ? 'kwitansi.penerimaan-pdf'
            : 'kwitansi.pembayaran-pdf';

        $data['jenis'] = $jenis;
        $data['jumlah_uang_formatted'] = 'Rp '.number_format((float) $data['jumlah_uang'], 0, ',', '.');
        $data['terbilang'] = ucfirst($data['terbilang'] ?? '');

        if ($jenis === 'pembayaran') {
            $data['sumber_dana_teks'] = $this->formatSumberDanaTeks($data);
        }

        $pdf = Pdf::loadView($view, ['d' => $data])->setPaper('a4', 'portrait');

        $slug = Str::slug($data['nomor_bukti'] ?? 'kwitansi');
        $label = $jenis === 'penerimaan' ? 'Penerimaan' : 'Pembayaran';

        return $pdf->download("Kuitansi-{$label}-{$slug}.pdf");
    }

    /** ponytail: naive comma-split alamat; upgrade: field terstruktur di profil sekolah */
    private function parseAlamat(?string $address): array
    {
        if (! filled($address)) {
            return ['desa_kecamatan' => '', 'kabupaten' => '', 'provinsi' => ''];
        }

        $parts = array_map('trim', explode(',', $address));

        return [
            'desa_kecamatan' => $parts[0] ?? '',
            'kabupaten' => $parts[1] ?? '',
            'provinsi' => $parts[2] ?? '',
        ];
    }

    private function defaultUntukPenerimaan(Cashflow $cashflow, Carbon $tanggal): string
    {
        if (filled($cashflow->description)) {
            return $cashflow->description;
        }

        return 'Penggunaan Dana Bantuan Operasional Pendidikan Tahun '.$tanggal->year
            .' Berdasarkan SK PPK tentang Penerima Dana BOP RA';
    }

    public function jenisFromCashflow(Cashflow $cashflow): string
    {
        return $cashflow->type === 'in' ? 'penerimaan' : 'pembayaran';
    }

    private function bulanId(int $month): string
    {
        return self::BULAN_ID[$month] ?? '';
    }

    private function tanggalId(Carbon $date): string
    {
        return $date->day.' '.$this->bulanId((int) $date->month).' '.$date->year;
    }

    private function formatSumberDanaTeks(array $data): string
    {
        $nama = trim((string) ($data['sumber_dana'] ?? ''));
        if (str_contains(strtolower($nama), 'periode bulan')) {
            return $nama;
        }

        $dana = $nama !== '' ? (str_starts_with(strtolower($nama), 'dana ') ? $nama : "dana {$nama}") : 'dana BOP RA';
        $awal = trim((string) ($data['sumber_dana_periode_awal'] ?? ''));
        $akhir = trim((string) ($data['sumber_dana_periode_akhir'] ?? ''));

        if ($awal !== '' && $akhir !== '') {
            return "{$dana} Periode bulan {$awal} s.d {$akhir}";
        }

        return $dana;
    }
}
