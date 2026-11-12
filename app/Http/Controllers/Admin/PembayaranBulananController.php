<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\DownloadsExcel;
use App\Http\Controllers\Concerns\DownloadsPublicPhoto;
use App\Http\Controllers\Controller;
use App\Models\Diskon;
use App\Models\Kelas;
use App\Models\PembayaranBulanan;
use App\Models\PembayaranBulananItem;
use App\Services\AkuntansiService;
use App\Services\RekapBiayaService;
use App\Support\PaginationPerPage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PembayaranBulananController extends Controller
{
    use DownloadsExcel;
    use DownloadsPublicPhoto;

    public function __construct(
        private RekapBiayaService $rekapBiayaService,
        private AkuntansiService $akuntansiService
    ) {}

    /** @return list<int>|null null = akses semua kelas sekolah */
    private function waliKelasIds(): ?array
    {
        return auth()->user()->scopedKelasIds();
    }

    private function assertPembayaranAccessible(PembayaranBulanan $pembayaran): void
    {
        abort_if($pembayaran->sekolah_id !== auth()->user()->sekolah_id, 403);

        $kelasIds = $this->waliKelasIds();
        if ($kelasIds === null) {
            return;
        }

        $pembayaran->loadMissing('anak');
        abort_unless(in_array((int) $pembayaran->anak->kelas_id, $kelasIds, true), 403);
    }

    private function scopeToWaliKelas(Builder $query): Builder
    {
        $kelasIds = $this->waliKelasIds();
        if ($kelasIds !== null) {
            $query->whereHas('anak', fn ($q) => $q->whereIn('kelas_id', $kelasIds));
        }

        return $query;
    }

    public function index(Request $request)
    {
        $sekolah_id = auth()->user()->sekolah_id;
        $waliKelasIds = $this->waliKelasIds();

        $bulan = (int) $request->input('bulan', now()->month);
        $tahun = (int) $request->input('tahun', now()->year);
        $kelasId = $request->input('kelas_id');
        $status = $request->input('status');

        if ($kelasId && $waliKelasIds !== null && ! in_array((int) $kelasId, $waliKelasIds, true)) {
            abort(403);
        }

        $query = PembayaranBulanan::where('sekolah_id', $sekolah_id)
            ->where('periode_bulan', $bulan)
            ->where('periode_tahun', $tahun)
            ->with(['anak', 'anak.kelas', 'biayaBulananSekolah', 'diskon', 'approvedBy', 'items']);

        $this->scopeToWaliKelas($query);

        if ($kelasId) {
            $query->whereHas('anak', function ($q) use ($kelasId) {
                $q->where('kelas_id', $kelasId);
            });
        }

        if ($status && in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $status);
        }

        $pembayarans = $query->orderBy('created_at', 'desc')->paginate(PaginationPerPage::resolve($request))->withQueryString();

        $kelasQuery = Kelas::where('sekolah_id', $sekolah_id)->orderBy('name');
        if ($waliKelasIds !== null) {
            $kelasQuery->whereIn('id', $waliKelasIds);
        }
        $kelas = $kelasQuery->get();

        $summaryQuery = PembayaranBulanan::where('sekolah_id', $sekolah_id)
            ->where('periode_bulan', $bulan)
            ->where('periode_tahun', $tahun);
        $this->scopeToWaliKelas($summaryQuery);

        $summary = $summaryQuery
            ->selectRaw('
                COUNT(*) as total_tagihan,
                SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected,
                SUM(total_bayar) as total_nominal,
                SUM(CASE WHEN status = "approved" THEN total_bayar ELSE 0 END) as nominal_approved
            ')
            ->first();

        return view('admin.pembayaran-bulanan.index', compact(
            'pembayarans', 'kelas', 'bulan', 'tahun', 'kelasId', 'status', 'summary'
        ));
    }

    public function export(Request $request)
    {
        $sekolah_id = auth()->user()->sekolah_id;
        $bulan = (int) $request->input('bulan', now()->month);
        $tahun = (int) $request->input('tahun', now()->year);
        $kelasId = $request->input('kelas_id');
        $status = $request->input('status');

        if ($kelasId && $this->waliKelasIds() !== null && ! in_array((int) $kelasId, $this->waliKelasIds(), true)) {
            abort(403);
        }

        $query = PembayaranBulanan::where('sekolah_id', $sekolah_id)
            ->where('periode_bulan', $bulan)
            ->where('periode_tahun', $tahun)
            ->with(['anak', 'anak.kelas', 'biayaBulananSekolah', 'diskon', 'items']);

        $this->scopeToWaliKelas($query);

        if ($kelasId) {
            $query->whereHas('anak', fn ($q) => $q->where('kelas_id', $kelasId));
        }

        if ($status && in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $status);
        }

        $pembayarans = $query->orderBy('created_at', 'desc')->get();

        $rows = $pembayarans->map(function (PembayaranBulanan $p) {
            $biayaLain = $p->items->sum('nominal');
            $diskonLabel = $p->nilai_diskon > 0
                ? ($p->diskon?->nama_diskon.' (-'.number_format($p->nilai_diskon, 0, ',', '.').')')
                : '-';

            return [
                $p->anak?->name ?? '-',
                $p->anak?->kelas?->name ?? '-',
                $p->biayaBulananSekolah?->nama_biaya ?? '-',
                $p->hari_hadir ?? '-',
                (float) $p->biaya_per_hari,
                (float) $biayaLain,
                (float) $p->subtotal,
                $diskonLabel,
                (float) $p->total_bayar,
                ucfirst($p->status),
            ];
        })->all();

        return $this->downloadExcel(
            ['Siswa', 'Kelas', 'Biaya', 'Hadir', 'Biaya/Bln', 'Biaya Lain', 'Subtotal', 'Diskon', 'Total', 'Status'],
            $rows,
            sprintf('rekap-pembayaran-%02d-%d.xlsx', $bulan, $tahun),
            'Rekap Pembayaran',
            [4, 5, 6, 8],
        );
    }

    public function show(PembayaranBulanan $pembayaran)
    {
        $this->assertPembayaranAccessible($pembayaran);

        $pembayaran->load(['anak', 'anak.kelas', 'biayaBulananSekolah', 'diskon', 'approvedBy', 'details.editedBy', 'items']);

        return view('admin.pembayaran-bulanan.show', compact('pembayaran'));
    }

    public function generatePreview(Request $request)
    {
        $sekolahId = auth()->user()->sekolah_id;
        $waliKelasIds = $this->waliKelasIds();
        $bulan = (int) $request->input('bulan', now()->month);
        $tahun = (int) $request->input('tahun', now()->year);

        $diskons = Diskon::where('sekolah_id', $sekolahId)
            ->where('is_aktif', true)
            ->orderBy('nama_diskon')
            ->get();

        $preview = [];
        foreach ($this->rekapBiayaService->getSiswaDenganBiayaBulanan($sekolahId) as $assignment) {
            $anak = $assignment->anak;
            $biaya = $assignment->biayaBulananSekolah;
            if (! $anak || ! $biaya) {
                continue;
            }

            if ($waliKelasIds !== null && ! in_array((int) $anak->kelas_id, $waliKelasIds, true)) {
                continue;
            }

            $biayaBulanan = (float) $assignment->biaya_bulanan;
            $hariHadir = $this->rekapBiayaService->hitungHariHadir($anak->id, $bulan, $tahun);

            $existingForPeriod = PembayaranBulanan::where('anak_id', $anak->id)
                ->where('biaya_bulanan_sekolah_id', $biaya->id)
                ->where('periode_bulan', $bulan)
                ->where('periode_tahun', $tahun)
                ->orderByDesc('id')
                ->get();

            $pending = $existingForPeriod->first(fn (PembayaranBulanan $p) => $p->status === 'pending');
            $existingStatus = 'none';
            if ($pending) {
                $existingStatus = 'pending';
            } elseif ($existingForPeriod->isNotEmpty()) {
                $existingStatus = $existingForPeriod->first()->status;
            }

            $isTagihanTambahan = $pending === null && in_array($existingStatus, ['approved', 'rejected'], true);
            $subtotalPreview = $isTagihanTambahan ? 0.0 : $biayaBulanan;

            $preview[] = [
                'key' => $anak->id.'_'.$biaya->id,
                'anak_id' => $anak->id,
                'anak_name' => $anak->name,
                'kelas_id' => $anak->kelas_id,
                'kelas_name' => $anak->kelas->name ?? '-',
                'biaya_id' => $biaya->id,
                'biaya_name' => $biaya->nama_biaya,
                'hari_hadir' => $isTagihanTambahan ? 0 : $hariHadir,
                'biaya_bulanan' => $isTagihanTambahan ? 0 : $biayaBulanan,
                'subtotal' => $subtotalPreview,
                'existing_status' => $existingStatus,
            ];
        }

        usort($preview, fn ($a, $b) => strcmp($a['anak_name'], $b['anak_name']));

        return response()->json([
            'preview' => $preview,
            'diskons' => $diskons,
            'bulan' => $bulan,
            'tahun' => $tahun,
        ]);
    }

    public function generate(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2000|max:2100',
            'tagihan' => 'required|array|min:1',
            'tagihan.*' => 'required|string|regex:/^\d+_\d+$/',
            'diskon' => 'nullable|array',
            'diskon.*' => 'nullable|exists:diskons,id',
            'diskon_manual' => 'nullable|array',
            'diskon_manual.*.nominal' => 'nullable|numeric|min:0',
            'diskon_manual.*.keterangan' => 'nullable|string|max:255',
            'biaya_tambahan' => 'nullable|array',
        ]);

        $sekolah_id = auth()->user()->sekolah_id;
        $validKeys = $this->validTagihanKeys($sekolah_id);
        $selectedKeys = array_values(array_intersect($request->tagihan, $validKeys));

        if ($selectedKeys === []) {
            return back()->withErrors(['tagihan' => 'Tidak ada tagihan valid yang dipilih.']);
        }

        $diskonPerTagihan = [];

        if ($request->filled('diskon')) {
            foreach ($request->diskon as $key => $diskonId) {
                if ($diskonId && in_array((string) $key, $selectedKeys, true)) {
                    $diskonPerTagihan[(string) $key] = (int) $diskonId;
                }
            }
        }

        $diskonManualPerTagihan = [];
        if ($request->filled('diskon_manual')) {
            foreach ($request->diskon_manual as $key => $row) {
                if (! in_array((string) $key, $selectedKeys, true)) {
                    continue;
                }
                $nominal = (float) ($row['nominal'] ?? 0);
                $keterangan = trim((string) ($row['keterangan'] ?? ''));
                if ($nominal <= 0) {
                    continue;
                }
                if ($keterangan === '') {
                    return back()->withErrors([
                        'diskon_manual' => 'Keterangan diskon manual wajib diisi jika nominal diskon diisi.',
                    ])->withInput();
                }
                $diskonManualPerTagihan[(string) $key] = [
                    'nominal' => $nominal,
                    'keterangan' => $keterangan,
                ];
                unset($diskonPerTagihan[(string) $key]);
            }
        }

        $biayaTambahan = [];
        if ($request->filled('biaya_tambahan')) {
            foreach ($request->biaya_tambahan as $key => $items) {
                if (in_array((string) $key, $selectedKeys, true) && is_array($items)) {
                    $filtered = [];
                    foreach ($items as $item) {
                        $nama = trim($item['nama_item'] ?? '');
                        $jumlah = (float) ($item['jumlah'] ?? 0);
                        if ($nama !== '' && $jumlah > 0) {
                            $filtered[] = ['nama_item' => $nama, 'jumlah' => $jumlah];
                        }
                    }
                    if ($filtered !== []) {
                        $biayaTambahan[(string) $key] = $filtered;
                    }
                }
            }
        }

        $pembayarans = $this->rekapBiayaService->generateTagihan(
            $sekolah_id,
            $request->bulan,
            $request->tahun,
            $diskonPerTagihan,
            $selectedKeys,
            $biayaTambahan,
            $diskonManualPerTagihan
        );

        $setting = \App\Models\AkuntansiSetting::forSekolah($sekolah_id);
        $catatanAkuntansi = $setting->isCash()
            ? ' Jurnal & cashflow baru muncul setelah tagihan ditandai lunas (metode Tunai di Pengaturan Akuntansi).'
            : ' Jurnal tagihan (piutang/pendapatan) dibuat otomatis; cashflow muncul saat lunas.';

        return redirect()
            ->route('admin.pembayaran-bulanan.index', [
                'bulan' => $request->bulan,
                'tahun' => $request->tahun,
            ])
            ->with('success', "Berhasil generate {$pembayarans->count()} tagihan.{$catatanAkuntansi}");
    }

    public function destroy(PembayaranBulanan $pembayaran)
    {
        $this->assertPembayaranAccessible($pembayaran);

        $bulan = $pembayaran->periode_bulan;
        $tahun = $pembayaran->periode_tahun;

        if (! $pembayaran->canBeDeleted()) {
            return redirect()
                ->route('admin.pembayaran-bulanan.index', compact('bulan', 'tahun'))
                ->withErrors(['tagihan' => 'Tagihan lunas tidak bisa dihapus. Buka detail tagihan → Batalkan pelunasan, lalu hapus dari daftar.']);
        }

        DB::transaction(function () use ($pembayaran) {
            $this->akuntansiService->hapusJurnalUntukPembayaranPending($pembayaran);
            $pembayaran->delete();
        });

        return redirect()
            ->route('admin.pembayaran-bulanan.index', compact('bulan', 'tahun'))
            ->with('success', 'Tagihan berhasil dihapus.');
    }

    public function batalkanLunas(PembayaranBulanan $pembayaran)
    {
        $this->assertPembayaranAccessible($pembayaran);

        try {
            $this->akuntansiService->batalkanPelunasanPembayaran($pembayaran);
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.pembayaran-bulanan.show', $pembayaran)
                ->withErrors(['tagihan' => $e->getMessage()]);
        }

        return redirect()
            ->route('admin.pembayaran-bulanan.show', $pembayaran)
            ->with('success', 'Pelunasan dibatalkan. Tagihan kembali Menunggu  bisa dihapus dari daftar jika perlu.');
    }

    /** @return list<string> */
    private function validTagihanKeys(int $sekolahId): array
    {
        $waliKelasIds = $this->waliKelasIds();
        $keys = [];
        foreach ($this->rekapBiayaService->getSiswaDenganBiayaBulanan($sekolahId) as $assignment) {
            if ($assignment->anak && $assignment->biayaBulananSekolah) {
                if ($waliKelasIds !== null && ! in_array((int) $assignment->anak->kelas_id, $waliKelasIds, true)) {
                    continue;
                }
                $keys[] = $assignment->anak_id.'_'.$assignment->biaya_bulanan_sekolah_id;
            }
        }

        return $keys;
    }

    public function updateDiskon(Request $request, PembayaranBulanan $pembayaran)
    {
        $this->assertPembayaranAccessible($pembayaran);

        $request->validate([
            'diskon_mode' => 'required|in:none,master,manual',
            'diskon_id' => 'nullable|required_if:diskon_mode,master|exists:diskons,id',
            'diskon_manual_nominal' => 'nullable|required_if:diskon_mode,manual|numeric|min:1',
            'diskon_manual_keterangan' => 'nullable|required_if:diskon_mode,manual|string|max:255',
        ]);

        if ($request->diskon_mode === 'manual') {
            $nominal = (float) $request->diskon_manual_nominal;
            $maks = (float) $pembayaran->subtotal + $pembayaran->getTotalBiayaTambahan();
            if ($nominal > $maks) {
                return back()->withErrors([
                    'diskon_manual_nominal' => 'Nominal diskon tidak boleh melebihi total tagihan.',
                ])->withInput();
            }

            $pembayaran->update([
                'diskon_id' => null,
                'nilai_diskon' => round($nominal, 2),
                'diskon_keterangan' => trim($request->diskon_manual_keterangan),
            ]);
        } elseif ($request->diskon_mode === 'master') {
            $diskon = Diskon::query()
                ->where('id', $request->diskon_id)
                ->where('sekolah_id', $pembayaran->sekolah_id)
                ->where('is_aktif', true)
                ->first();

            if (! $diskon) {
                return back()->withErrors(['diskon_id' => 'Diskon tidak valid.'])->withInput();
            }

            $pembayaran->update([
                'diskon_id' => $diskon->id,
                'diskon_keterangan' => null,
            ]);
        } else {
            $pembayaran->update([
                'diskon_id' => null,
                'nilai_diskon' => 0,
                'diskon_keterangan' => null,
            ]);
        }

        $this->rekapBiayaService->hitungBiaya($pembayaran);

        return redirect()
            ->route('admin.pembayaran-bulanan.show', $pembayaran)
            ->with('success', 'Diskon berhasil diperbarui.');
    }

    public function approve(Request $request, PembayaranBulanan $pembayaran)
    {
        $this->assertPembayaranAccessible($pembayaran);
        abort_if($pembayaran->isApproved(), 403, 'Tagihan sudah lunas.');

        try {
            // Satu transaksi: jurnal dan status lunas harus berubah bersamaan, supaya klik ganda tidak membuat jurnal dobel.
            DB::transaction(function () use ($pembayaran, $request) {
                $this->akuntansiService->buatJurnalSaatApprove($pembayaran, auth()->id());

                $this->rekapBiayaService->approvePembayaran(
                    $pembayaran,
                    auth()->id(),
                    $request->catatan_admin
                );
            });
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('admin.pembayaran-bulanan.show', $pembayaran)
                ->withErrors(['approve' => $e->getMessage()]);
        }

        return redirect()
            ->route('admin.pembayaran-bulanan.show', $pembayaran)
            ->with('success', 'Pembayaran berhasil ditandai lunas.');
    }

    public function reject(Request $request, PembayaranBulanan $pembayaran)
    {
        $this->assertPembayaranAccessible($pembayaran);

        $request->validate([
            'catatan_admin' => 'required|string',
        ]);

        $this->rekapBiayaService->rejectPembayaran(
            $pembayaran,
            auth()->id(),
            $request->catatan_admin
        );

        return redirect()
            ->route('admin.pembayaran-bulanan.show', $pembayaran)
            ->with('success', 'Pembayaran berhasil ditolak.');
    }

    public function storeItem(Request $request, PembayaranBulanan $pembayaran)
    {
        $this->assertPembayaranAccessible($pembayaran);
        abort_if(! $pembayaran->isPending(), 403, 'Hanya tagihan pending yang bisa diubah.');

        $request->validate([
            'nama_item' => 'required|string|max:255',
            'jumlah' => 'required|numeric|min:1',
        ]);

        $pembayaran->items()->create([
            'nama_item' => $request->nama_item,
            'jumlah' => $request->jumlah,
        ]);

        $this->rekapBiayaService->hitungBiaya($pembayaran);

        return redirect()
            ->route('admin.pembayaran-bulanan.show', $pembayaran)
            ->with('success', 'Biaya tambahan berhasil ditambahkan.');
    }

    public function updateItem(Request $request, PembayaranBulanan $pembayaran, PembayaranBulananItem $item)
    {
        $this->assertPembayaranAccessible($pembayaran);
        abort_if($item->pembayaran_bulanan_id !== $pembayaran->id, 404);
        abort_if(! $pembayaran->isPending(), 403, 'Hanya tagihan pending yang bisa diubah.');

        $request->validate([
            'nama_item' => 'required|string|max:255',
            'jumlah' => 'required|numeric|min:1',
        ]);

        $item->update([
            'nama_item' => $request->nama_item,
            'jumlah' => $request->jumlah,
        ]);

        $this->rekapBiayaService->hitungBiaya($pembayaran);

        return redirect()
            ->route('admin.pembayaran-bulanan.show', $pembayaran)
            ->with('success', 'Biaya tambahan berhasil diperbarui.');
    }

    public function destroyItem(Request $request, PembayaranBulanan $pembayaran, PembayaranBulananItem $item)
    {
        $this->assertPembayaranAccessible($pembayaran);
        abort_if($item->pembayaran_bulanan_id !== $pembayaran->id, 404);
        abort_if(! $pembayaran->isPending(), 403, 'Hanya tagihan pending yang bisa diubah.');

        $item->delete();

        $this->rekapBiayaService->hitungBiaya($pembayaran);

        return redirect()
            ->route('admin.pembayaran-bulanan.show', $pembayaran)
            ->with('success', 'Biaya tambahan berhasil dihapus.');
    }

    public function downloadBukti(PembayaranBulanan $pembayaran, \App\Services\PhotoArchiveService $photoArchive)
    {
        $this->assertPembayaranAccessible($pembayaran);

        return $this->downloadPublicPhoto(
            $photoArchive,
            $pembayaran->bukti_transfer,
            $this->slugPhotoFilename('bukti-transfer-'.$pembayaran->id, $pembayaran->bukti_transfer)
        );
    }
}
