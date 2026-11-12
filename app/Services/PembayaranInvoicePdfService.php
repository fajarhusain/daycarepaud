<?php

namespace App\Services;

use App\Models\PembayaranBulanan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class PembayaranInvoicePdfService
{
    public function download(PembayaranBulanan $pembayaran): Response
    {
        $pembayaran->loadMissing(['anak.kelas', 'sekolah', 'biayaBulananSekolah', 'diskon', 'approvedBy']);

        $pdf = Pdf::loadView('pembayaran.invoice-pdf', [
            'pembayaran' => $pembayaran,
            'invoiceNo' => $this->invoiceNumber($pembayaran),
        ])->setPaper('a4', 'portrait');

        return $pdf->download($this->filename($pembayaran));
    }

    public function invoiceNumber(PembayaranBulanan $pembayaran): string
    {
        return sprintf(
            'INV-%d%02d-%05d',
            $pembayaran->periode_tahun,
            $pembayaran->periode_bulan,
            $pembayaran->id
        );
    }

    public function filename(PembayaranBulanan $pembayaran): string
    {
        $name = Str::slug($pembayaran->anak->name ?? 'siswa');
        $periode = Str::slug($pembayaran->getPeriodeLabel());

        return "Invoice-{$name}-{$periode}.pdf";
    }
}
