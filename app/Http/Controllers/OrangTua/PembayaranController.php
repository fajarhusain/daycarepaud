<?php

namespace App\Http\Controllers\OrangTua;

use App\Support\TenantContext;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\DownloadsPublicPhoto;
use App\Models\Anak;
use App\Models\PembayaranBulanan;
use App\Support\PaginationPerPage;
use App\Services\PembayaranInvoicePdfService;
use App\Services\RekapBiayaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PembayaranController extends Controller
{
    use DownloadsPublicPhoto;
    public function __construct(
        private RekapBiayaService $rekapBiayaService,
    ) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        $sekolahId = TenantContext::requireSekolahId();

        $anaks = Anak::where('user_id', $user->id)
            ->where('sekolah_id', $sekolahId)
            ->with('kelas')
            ->get();

        $anakIds = $anaks->pluck('id');
        $selectedAnakId = null;
        if ($request->filled('anak_id')) {
            $candidate = (int) $request->input('anak_id');
            if ($anakIds->contains($candidate)) {
                $selectedAnakId = $candidate;
            }
        }

        $summaryPembayarans = PembayaranBulanan::whereIn('anak_id', $anakIds)
            ->with(['anak', 'biayaBulananSekolah', 'diskon'])
            ->orderByDesc('periode_tahun')
            ->orderByDesc('periode_bulan')
            ->get();

        $pembayaransQuery = PembayaranBulanan::whereIn('anak_id', $anakIds)
            ->with(['anak', 'biayaBulananSekolah', 'diskon'])
            ->orderByDesc('periode_tahun')
            ->orderByDesc('periode_bulan');

        if ($selectedAnakId !== null) {
            $pembayaransQuery->where('anak_id', $selectedAnakId);
        }

        $perPage = PaginationPerPage::resolve($request);
        $pembayarans = $pembayaransQuery
            ->paginate($perPage)
            ->withQueryString();

        return view('orangtua.pembayaran.index', compact('anaks', 'pembayarans', 'summaryPembayarans', 'selectedAnakId'));
    }

    public function show(PembayaranBulanan $pembayaran)
    {
        $user = auth()->user();
        $anakIds = Anak::where('user_id', $user->id)->pluck('id');

        abort_if(! $anakIds->contains($pembayaran->anak_id), 403);

        $pembayaran->load(['anak', 'anak.kelas', 'biayaBulananSekolah', 'diskon', 'approvedBy']);

        return view('orangtua.pembayaran.show', compact('pembayaran'));
    }

    public function exportInvoice(PembayaranBulanan $pembayaran)
    {
        $this->authorizePembayaran($pembayaran);
        abort_unless($pembayaran->isApproved(), 403, 'Invoice hanya tersedia untuk pembayaran yang sudah lunas.');

        return app(PembayaranInvoicePdfService::class)->download($pembayaran);
    }

    public function bayar(Request $request, PembayaranBulanan $pembayaran)
    {
        $this->authorizePembayaran($pembayaran);
        abort_if(! $pembayaran->isPending() && ! $pembayaran->isRejected(), 403, 'Pembayaran ini sudah diproses.');

        $request->validate([
            'bukti_transfer' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'catatan' => 'nullable|string|max:500',
        ]);

        if ($pembayaran->bukti_transfer) {
            Storage::disk('public')->delete($pembayaran->bukti_transfer);
        }

        $path = $request->file('bukti_transfer')->store('bukti-transfer', 'public');

        $pembayaran->update([
            'bukti_transfer' => $path,
            'status' => 'pending',
            'catatan_admin' => $request->catatan,
        ]);

        return redirect()
            ->route('orangtua.pembayaran.show', $pembayaran)
            ->with('success', 'Bukti transfer berhasil diupload. Menunggu approval admin.');
    }

    private function authorizePembayaran(PembayaranBulanan $pembayaran): void
    {
        $anakIds = Anak::where('user_id', auth()->id())->pluck('id');
        abort_if(! $anakIds->contains($pembayaran->anak_id), 403);
    }

    public function downloadBukti(PembayaranBulanan $pembayaran, \App\Services\PhotoArchiveService $photoArchive)
    {
        $this->authorizePembayaran($pembayaran);

        return $this->downloadPublicPhoto(
            $photoArchive,
            $pembayaran->bukti_transfer,
            $this->slugPhotoFilename('bukti-transfer-'.$pembayaran->id, $pembayaran->bukti_transfer)
        );
    }
}
