<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\DownloadsExcel;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GajiPengajarRequest;
use App\Models\AkuntansiSetting;
use App\Models\GajiPengajar;
use App\Models\KomponenGaji;
use App\Models\Pengajar;
use App\Services\AkuntansiService;
use App\Services\GajiGuruService;
use App\Support\PaginationPerPage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class GajiGuruController extends Controller
{
    use DownloadsExcel;

    public function __construct(
        private GajiGuruService $gajiGuruService,
        private AkuntansiService $akuntansiService,
    ) {}

    private function sekolahId(): ?int
    {
        $id = auth()->user()->sekolah_id;

        return $id !== null ? (int) $id : null;
    }

    /** @return array<string, mixed> */
    private function formOptions(int $sekolahId): array
    {
        $komponenDefaults = KomponenGaji::query()
            ->where('sekolah_id', $sekolahId)
            ->aktif()
            ->orderBy('urutan')
            ->orderBy('nama')
            ->get()
            ->map(fn (KomponenGaji $k) => [
                'nama' => $k->nama,
                'jenis' => $k->jenis,
                'nominal' => (float) $k->nominal_default,
            ])
            ->values()
            ->all();

        $pengajars = Pengajar::where('sekolah_id', $sekolahId)->orderBy('name')->get();

        return compact('komponenDefaults', 'pengajars');
    }

    private function settingGaji(int $sekolahId): ?AkuntansiSetting
    {
        return AkuntansiSetting::query()->where('sekolah_id', $sekolahId)->first();
    }

    /** @return array<string, mixed> */
    private function akunDataFromSetting(int $sekolahId): array
    {
        $setting = $this->settingGaji($sekolahId)
            ?? throw new \RuntimeException('Pengaturan akuntansi belum ada. Buka Pengaturan Akuntansi terlebih dahulu.');

        return $setting->akunUntukGajiGuru();
    }

    private function redirectIfSettingGajiIncomplete(int $sekolahId): ?\Illuminate\Http\RedirectResponse
    {
        $setting = $this->settingGaji($sekolahId);
        if ($setting?->lengkapUntukGajiGuru()) {
            return null;
        }

        return redirect()
            ->route('admin.akuntansi-setting.index')
            ->withErrors(['gaji_guru' => 'Lengkapi Akun Kas (Default Cashflow) dan Akun Beban Gaji sebelum membuat slip gaji.']);
    }

    /** @param  Builder<GajiPengajar>  $query */
    private function applyIndexFilters(Builder $query, Request $request): void
    {
        if ($request->filled('pengajar_id')) {
            $query->where('pengajar_id', (int) $request->input('pengajar_id'));
        }

        if ($request->filled('bulan')) {
            $query->where('periode_bulan', (int) $request->input('bulan'));
        }

        if ($request->filled('tahun')) {
            $query->where('periode_tahun', (int) $request->input('tahun'));
        }

        if ($request->filled('status') && in_array($request->input('status'), ['draft', 'approved'], true)) {
            $query->where('status', $request->input('status'));
        }
    }

    public function index(Request $request)
    {
        $sekolahId = $this->sekolahId();
        abort_if($sekolahId === null, 403);

        $bulan = $request->filled('bulan') ? (int) $request->input('bulan') : (int) now()->format('n');
        $tahun = $request->filled('tahun') ? (int) $request->input('tahun') : (int) now()->format('Y');

        $base = GajiPengajar::query()
            ->where('sekolah_id', $sekolahId)
            ->with(['pengajar', 'approvedBy']);

        $filtered = (clone $base);
        $this->applyIndexFilters($filtered, $request);

        if (! $request->hasAny(['bulan', 'tahun', 'pengajar_id', 'status'])) {
            $filtered->where('periode_bulan', $bulan)->where('periode_tahun', $tahun);
        }

        $gajis = (clone $filtered)
            ->orderByDesc('periode_tahun')
            ->orderByDesc('periode_bulan')
            ->orderByDesc('id')
            ->paginate(PaginationPerPage::resolve($request))
            ->withQueryString();

        $ringkasanQuery = (clone $base)->where('periode_bulan', $bulan)->where('periode_tahun', $tahun);
        $totalBulan = (float) (clone $ringkasanQuery)->sum('total_gaji');
        $jumlahGuru = (clone $ringkasanQuery)->distinct('pengajar_id')->count('pengajar_id');

        $pengajars = Pengajar::where('sekolah_id', $sekolahId)->orderBy('name')->get();

        return view('admin.gaji-guru.index', compact(
            'gajis',
            'pengajars',
            'bulan',
            'tahun',
            'totalBulan',
            'jumlahGuru',
        ));
    }

    public function create()
    {
        $sekolahId = $this->sekolahId();
        abort_if($sekolahId === null, 403);

        if ($redirect = $this->redirectIfSettingGajiIncomplete($sekolahId)) {
            return $redirect;
        }

        return view('admin.gaji-guru.create', $this->formOptions($sekolahId));
    }

    public function store(GajiPengajarRequest $request)
    {
        $sekolahId = $this->sekolahId();
        abort_if($sekolahId === null, 403);

        try {
            $gaji = $this->gajiGuruService->store(
                [
                    'sekolah_id' => $sekolahId,
                    'pengajar_id' => (int) $request->input('pengajar_id'),
                    'periode_bulan' => (int) $request->input('periode_bulan'),
                    'periode_tahun' => (int) $request->input('periode_tahun'),
                    ...$this->akunDataFromSetting($sekolahId),
                    'catatan' => $request->input('catatan'),
                ],
                $request->input('komponen', [])
            );
        } catch (\RuntimeException $e) {
            return redirect()->route('admin.akuntansi-setting.index')->withErrors(['gaji_guru' => $e->getMessage()]);
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['komponen' => $e->getMessage()]);
        }

        if ($request->boolean('approve')) {
            try {
                $this->gajiGuruService->approve($gaji, (int) auth()->id());
            } catch (\RuntimeException $e) {
                return redirect()->route('admin.gaji-guru.show', $gaji)
                    ->withErrors(['approve' => $e->getMessage()]);
            }

            return redirect()->route('admin.gaji-guru.show', $gaji)
                ->with('success', 'Slip gaji disimpan dan disetujui.');
        }

        return redirect()->route('admin.gaji-guru.show', $gaji)
            ->with('success', 'Slip gaji draft berhasil dibuat.');
    }

    public function show(GajiPengajar $gaji_pengajar)
    {
        $sekolahId = $this->sekolahId();
        abort_if($sekolahId === null, 403);
        abort_if((int) $gaji_pengajar->sekolah_id !== $sekolahId, 403);

        $gaji_pengajar->load(['pengajar', 'akunKas', 'akunBeban', 'sumberDana', 'approvedBy', 'jurnal', 'cashflow']);

        return view('admin.gaji-guru.show', ['gaji' => $gaji_pengajar]);
    }

    public function edit(GajiPengajar $gaji_pengajar)
    {
        $sekolahId = $this->sekolahId();
        abort_if($sekolahId === null, 403);
        abort_if((int) $gaji_pengajar->sekolah_id !== $sekolahId, 403);
        abort_if(! $gaji_pengajar->isDraft(), 403, 'Slip yang sudah disetujui tidak dapat diedit.');

        $gaji_pengajar->load('pengajar');

        return view('admin.gaji-guru.edit', array_merge(
            $this->formOptions($sekolahId),
            ['gaji' => $gaji_pengajar]
        ));
    }

    public function update(GajiPengajarRequest $request, GajiPengajar $gaji_pengajar)
    {
        $sekolahId = $this->sekolahId();
        abort_if($sekolahId === null, 403);
        abort_if((int) $gaji_pengajar->sekolah_id !== $sekolahId, 403);

        try {
            $this->gajiGuruService->update(
                $gaji_pengajar,
                [
                    'pengajar_id' => (int) $request->input('pengajar_id'),
                    'periode_bulan' => (int) $request->input('periode_bulan'),
                    'periode_tahun' => (int) $request->input('periode_tahun'),
                    ...$this->akunDataFromSetting($sekolahId),
                    'catatan' => $request->input('catatan'),
                ],
                $request->input('komponen', [])
            );
        } catch (\RuntimeException $e) {
            return redirect()->route('admin.akuntansi-setting.index')->withErrors(['gaji_guru' => $e->getMessage()]);
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['form' => $e->getMessage()]);
        }

        return redirect()->route('admin.gaji-guru.show', $gaji_pengajar)
            ->with('success', 'Slip gaji berhasil diperbarui.');
    }

    public function destroy(GajiPengajar $gaji_pengajar)
    {
        $sekolahId = $this->sekolahId();
        abort_if($sekolahId === null, 403);
        abort_if((int) $gaji_pengajar->sekolah_id !== $sekolahId, 403);

        if (! $gaji_pengajar->isDraft()) {
            return redirect()->route('admin.gaji-guru.show', $gaji_pengajar)
                ->withErrors(['delete' => 'Hanya slip draft yang dapat dihapus. Batalkan persetujuan terlebih dahulu.']);
        }

        $gaji_pengajar->delete();

        return redirect()->route('admin.gaji-guru.index')
            ->with('success', 'Slip gaji draft dihapus.');
    }

    public function approve(GajiPengajar $gaji_pengajar)
    {
        $sekolahId = $this->sekolahId();
        abort_if($sekolahId === null, 403);
        abort_if((int) $gaji_pengajar->sekolah_id !== $sekolahId, 403);

        try {
            $this->gajiGuruService->approve($gaji_pengajar, (int) auth()->id());
        } catch (\RuntimeException $e) {
            return back()->withErrors(['approve' => $e->getMessage()]);
        }

        return redirect()->route('admin.gaji-guru.show', $gaji_pengajar)
            ->with('success', 'Slip gaji disetujui. Cashflow dan jurnal telah dibuat.');
    }

    public function batalkan(GajiPengajar $gaji_pengajar)
    {
        $sekolahId = $this->sekolahId();
        abort_if($sekolahId === null, 403);
        abort_if((int) $gaji_pengajar->sekolah_id !== $sekolahId, 403);

        try {
            $this->gajiGuruService->batalkanApprove($gaji_pengajar);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['batalkan' => $e->getMessage()]);
        }

        return redirect()->route('admin.gaji-guru.show', $gaji_pengajar)
            ->with('success', 'Persetujuan dibatalkan. Slip kembali ke draft.');
    }

    public function export(Request $request)
    {
        $sekolahId = $this->sekolahId();
        abort_if($sekolahId === null, 403);

        $query = GajiPengajar::query()
            ->where('sekolah_id', $sekolahId)
            ->with('pengajar');

        $this->applyIndexFilters($query, $request);

        $rows = $query
            ->orderByDesc('periode_tahun')
            ->orderByDesc('periode_bulan')
            ->get()
            ->map(fn (GajiPengajar $g) => [
                $g->pengajar->name ?? '-',
                $g->periodeLabel(),
                (float) $g->total_gaji,
                $g->status === GajiPengajar::STATUS_APPROVED ? 'Disetujui' : 'Draft',
            ])
            ->all();

        $bulan = $request->filled('bulan') ? (int) $request->input('bulan') : null;
        $tahun = $request->filled('tahun') ? (int) $request->input('tahun') : null;

        return $this->downloadExcel(
            ['Guru', 'Periode', 'Total (Rp)', 'Status'],
            $rows,
            $bulan && $tahun
                ? sprintf('gaji-guru-%02d-%d.xlsx', $bulan, $tahun)
                : 'gaji-guru-'.now()->format('Y-m-d').'.xlsx',
            'Gaji Guru',
            [2],
        );
    }
}
