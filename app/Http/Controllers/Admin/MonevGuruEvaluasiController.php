<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\DownloadsExcel;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MonevGuruEvaluasiRequest;
use App\Models\MonevGuruEvaluasi;
use App\Models\MonevGuruKriteria;
use App\Models\MonevGuruPenilaianItem;
use App\Models\Pengajar;
use App\Services\AiTokenService;
use App\Services\MonevGuruPdfService;
use App\Services\MonevGuruService;
use App\Services\MonevSummaryService;
use App\Support\AiTokenFeature;
use App\Support\PaginationPerPage;
use Illuminate\Http\Request;

class MonevGuruEvaluasiController extends Controller
{
    use DownloadsExcel;

    public function __construct(
        protected MonevGuruService $service,
        protected MonevGuruPdfService $pdfService,
        protected AiTokenService $tokenService,
        protected MonevSummaryService $monevSummaryService
    ) {}

    private function sekolahId(): ?int
    {
        $id = auth()->user()->sekolah_id;

        return $id !== null ? (int) $id : null;
    }

    private function assertSekolahEvaluasi(MonevGuruEvaluasi $evaluasi): void
    {
        $sekolahId = $this->sekolahId();
        abort_if($sekolahId === null, 403);
        abort_if((int) $evaluasi->sekolah_id !== $sekolahId, 403);
    }

    /** @return array{hasTokens: bool, tokenFallbackMonev: string, aiReady: bool} */
    private function aiFormContext(): array
    {
        $sekolahId = $this->sekolahId() ?? 0;
        $tokenBalance = $sekolahId > 0 ? $this->tokenService->getBalance($sekolahId) : 0;

        return [
            'hasTokens' => $tokenBalance > 0,
            'tokenFallbackMonev' => $sekolahId > 0
                ? $this->tokenService->resolveFallback($sekolahId, AiTokenFeature::MONEV)
                : '',
            'aiReady' => $this->monevSummaryService->resolveAiServiceForUser(auth()->user()) !== null,
        ];
    }

    public function index(Request $request)
    {
        $sekolahId = $this->sekolahId();
        abort_if($sekolahId === null, 403);

        $query = MonevGuruEvaluasi::query()
            ->forSekolah($sekolahId)
            ->with(['pengajar', 'evaluator'])
            ->latest('periode_selesai')
            ->latest('id');

        if ($request->filled('pengajar_id')) {
            $query->where('pengajar_id', (int) $request->input('pengajar_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('periode_mulai')) {
            $query->whereDate('periode_selesai', '>=', $request->input('periode_mulai'));
        }

        if ($request->filled('periode_selesai')) {
            $query->whereDate('periode_mulai', '<=', $request->input('periode_selesai'));
        }

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', '%'.$search.'%')
                    ->orWhereHas('pengajar', fn ($pq) => $pq->where('name', 'like', '%'.$search.'%'));
            });
        }

        $evaluasis = $query->paginate(PaginationPerPage::resolve($request))->withQueryString();
        $pengajars = Pengajar::where('sekolah_id', $sekolahId)->orderBy('name')->get();

        return view('admin.monev-guru.evaluasi.index', compact('evaluasis', 'pengajars'));
    }

    public function create()
    {
        $sekolahId = $this->sekolahId();
        abort_if($sekolahId === null, 403);

        $pengajars = Pengajar::where('sekolah_id', $sekolahId)->orderBy('name')->get();
        $kriterias = MonevGuruKriteria::query()
            ->where('sekolah_id', $sekolahId)
            ->where('is_active', true)
            ->orderBy('urutan')
            ->get();

        abort_if($kriterias->isEmpty(), 403, 'Belum ada kriteria aktif. Atur kriteria penilaian terlebih dahulu.');

        return view('admin.monev-guru.evaluasi.form', array_merge([
            'evaluasi' => null,
            'pengajars' => $pengajars,
            'kriterias' => $kriterias,
            'itemsByKriteria' => collect(),
        ], $this->aiFormContext()));
    }

    public function store(MonevGuruEvaluasiRequest $request)
    {
        $sekolahId = $this->sekolahId();
        abort_if($sekolahId === null, 403);

        $evaluasi = $this->service->storeEvaluasi(
            [
                'sekolah_id' => $sekolahId,
                'pengajar_id' => (int) $request->input('pengajar_id'),
                'evaluator_user_id' => (int) auth()->id(),
                'judul' => $request->input('judul'),
                'periode_mulai' => $request->input('periode_mulai'),
                'periode_selesai' => $request->input('periode_selesai'),
                'catatan_umum' => $request->input('catatan_umum'),
                'rekomendasi' => $request->input('rekomendasi'),
                'status' => MonevGuruEvaluasi::STATUS_DRAFT,
            ],
            $request->normalizedItems(),
            $request->boolean('finalize')
        );

        $message = $evaluasi->isFinal()
            ? 'Evaluasi guru berhasil difinalisasi.'
            : 'Draft evaluasi guru berhasil disimpan.';

        return redirect()->route('admin.monev-guru.show', $evaluasi)->with('success', $message);
    }

    public function show(MonevGuruEvaluasi $monev_guru_evaluasi)
    {
        $this->assertSekolahEvaluasi($monev_guru_evaluasi);
        $monev_guru_evaluasi->load(['pengajar', 'evaluator', 'items.kriteria']);

        return view('admin.monev-guru.evaluasi.show', ['evaluasi' => $monev_guru_evaluasi]);
    }

    public function edit(MonevGuruEvaluasi $monev_guru_evaluasi)
    {
        $this->assertSekolahEvaluasi($monev_guru_evaluasi);
        abort_if($monev_guru_evaluasi->isFinal(), 403, 'Evaluasi final tidak dapat diubah.');

        $sekolahId = $this->sekolahId();
        $pengajars = Pengajar::where('sekolah_id', $sekolahId)->orderBy('name')->get();
        $kriterias = MonevGuruKriteria::query()
            ->where('sekolah_id', $sekolahId)
            ->where('is_active', true)
            ->orderBy('urutan')
            ->get();

        $monev_guru_evaluasi->load('items');

        return view('admin.monev-guru.evaluasi.form', array_merge([
            'evaluasi' => $monev_guru_evaluasi,
            'pengajars' => $pengajars,
            'kriterias' => $kriterias,
            'itemsByKriteria' => $monev_guru_evaluasi->items->keyBy('monev_guru_kriteria_id'),
        ], $this->aiFormContext()));
    }

    public function update(MonevGuruEvaluasiRequest $request, MonevGuruEvaluasi $monev_guru_evaluasi)
    {
        $this->assertSekolahEvaluasi($monev_guru_evaluasi);

        $evaluasi = $this->service->updateEvaluasi(
            $monev_guru_evaluasi,
            [
                'pengajar_id' => (int) $request->input('pengajar_id'),
                'judul' => $request->input('judul'),
                'periode_mulai' => $request->input('periode_mulai'),
                'periode_selesai' => $request->input('periode_selesai'),
                'catatan_umum' => $request->input('catatan_umum'),
                'rekomendasi' => $request->input('rekomendasi'),
            ],
            $request->normalizedItems(),
            $request->boolean('finalize')
        );

        $message = $evaluasi->isFinal()
            ? 'Evaluasi guru berhasil difinalisasi.'
            : 'Draft evaluasi guru berhasil diperbarui.';

        return redirect()->route('admin.monev-guru.show', $evaluasi)->with('success', $message);
    }

    public function destroy(MonevGuruEvaluasi $monev_guru_evaluasi)
    {
        $this->assertSekolahEvaluasi($monev_guru_evaluasi);
        abort_if($monev_guru_evaluasi->isFinal(), 403, 'Evaluasi final tidak dapat dihapus.');

        MonevGuruPenilaianItem::where('monev_guru_evaluasi_id', $monev_guru_evaluasi->id)->delete();
        $monev_guru_evaluasi->delete();

        return redirect()->route('admin.monev-guru.index')
            ->with('success', 'Evaluasi guru berhasil dihapus.');
    }

    public function finalize(MonevGuruEvaluasi $monev_guru_evaluasi)
    {
        $this->assertSekolahEvaluasi($monev_guru_evaluasi);
        abort_if($monev_guru_evaluasi->isFinal(), 403, 'Evaluasi sudah final.');

        $monev_guru_evaluasi->load('items.kriteria');
        $this->service->finalize($monev_guru_evaluasi);

        return redirect()->route('admin.monev-guru.show', $monev_guru_evaluasi)
            ->with('success', 'Evaluasi guru berhasil difinalisasi.');
    }

    public function pdf(MonevGuruEvaluasi $monev_guru_evaluasi)
    {
        $this->assertSekolahEvaluasi($monev_guru_evaluasi);

        return $this->pdfService->download($monev_guru_evaluasi);
    }

    public function export(Request $request)
    {
        $sekolahId = $this->sekolahId();
        abort_if($sekolahId === null, 403);

        $kriterias = MonevGuruKriteria::query()
            ->where('sekolah_id', $sekolahId)
            ->where('is_active', true)
            ->orderBy('urutan')
            ->get();

        $evaluasis = MonevGuruEvaluasi::query()
            ->forSekolah($sekolahId)
            ->final()
            ->with(['pengajar', 'evaluator', 'items'])
            ->latest('finalized_at')
            ->get();

        $headings = array_merge(
            ['Nama Guru', 'Judul', 'Periode Mulai', 'Periode Selesai', 'Skor Keseluruhan', 'Evaluator', 'Tanggal Final'],
            $kriterias->pluck('nama')->all()
        );

        $rows = $evaluasis->map(function (MonevGuruEvaluasi $evaluasi) use ($kriterias) {
            $itemsByKriteria = $evaluasi->items->keyBy('monev_guru_kriteria_id');
            $kriteriaSkor = $kriterias->map(fn ($k) => $itemsByKriteria->get($k->id)?->skor ?? '-')->all();

            return array_merge([
                $evaluasi->pengajar->name ?? '-',
                $evaluasi->judul ?? '-',
                $evaluasi->periode_mulai->format('Y-m-d'),
                $evaluasi->periode_selesai->format('Y-m-d'),
                $evaluasi->skor_keseluruhan ?? '-',
                $evaluasi->evaluator->name ?? '-',
                $evaluasi->finalized_at?->format('Y-m-d H:i') ?? '-',
            ], $kriteriaSkor);
        })->all();

        return $this->downloadExcel(
            $headings,
            $rows,
            'rekap-monev-guru-'.now()->format('Y-m-d').'.xlsx',
            'Rekap Monev Guru'
        );
    }
}
