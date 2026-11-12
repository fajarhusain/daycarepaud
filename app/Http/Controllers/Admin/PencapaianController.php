<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\DownloadsExcel;
use App\Http\Controllers\Concerns\DownloadsPhotoArchive;
use App\Http\Controllers\Concerns\ResolvesPencapaianBundlePhoto;
use App\Http\Controllers\Controller;
use App\Http\Traits\StoresDocumentationMedia;
use App\Models\Anak;
use App\Models\Kegiatan;
use App\Models\Kelas;
use App\Models\Matrikulasi;
use App\Models\Pencapaian;
use App\Services\AiTokenService;
use App\Services\PhotoArchiveService;
use App\Support\AiTokenFeature;
use App\Support\DocumentationMedia;
use App\Support\FilterAspekPencapaian;
use App\Support\LabelSkorPencapaian;
use App\Support\PaginationPerPage;
use App\Support\TanggalRentang;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PencapaianController extends Controller
{
    use DownloadsExcel;
    use DownloadsPhotoArchive;
    use ResolvesPencapaianBundlePhoto;
    use StoresDocumentationMedia;

    public function __construct(
        protected AiTokenService $tokenService
    ) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        $sekolah_id = $user->sekolah_id;
        $skalas = LabelSkorPencapaian::optionsForSekolah((int) $sekolah_id);

        $range = TanggalRentang::dariSampaiQuery($request, null);
        $tanggalDari = $range ? $range[0] : null;
        $tanggalSampai = $range ? $range[1] : null;

        $anakQuery = Anak::query()->where('sekolah_id', $sekolah_id)->orderBy('name');
        $user->applyScopedKelas($anakQuery);
        if ($this->kelasFilterInScope($request)) {
            $anakQuery->where('kelas_id', (int) $request->input('filter_kelas_id'));
        }
        $anaks = $anakQuery->get();

        $filterAspekRaw = (string) $request->input('aspek', '');
        $filterAspek = $filterAspekRaw === '' ? null : $filterAspekRaw;

        $hariQuery = Pencapaian::query()
            ->with(['anak', 'kegiatan.matrikulasis', 'matrikulasi'])
            ->orderByDesc('updated_at');

        if ($tanggalDari) {
            $hariQuery->whereDate('created_at', '>=', $tanggalDari);
        }
        if ($tanggalSampai) {
            $hariQuery->whereDate('created_at', '<=', $tanggalSampai);
        }

        $hariQuery->whereHas('anak', function ($q) use ($user, $sekolah_id) {
            $q->where('sekolah_id', $sekolah_id);
            $user->applyScopedKelas($q);
        });

        if ($request->filled('filter_anak_id')) {
            $aid = (int) $request->input('filter_anak_id');
            $hariQuery->where('anak_id', $aid);
        }
        if ($this->kelasFilterInScope($request)) {
            $hariQuery->whereHas('anak', fn ($q) => $q->where('kelas_id', (int) $request->input('filter_kelas_id')));
        }

        $hariAll = $hariQuery->get();
        $groupsAll = $hariAll->groupBy(fn ($p) => $p->anak_id.'_'.$p->kegiatan_id);

        $keysFiltered = $groupsAll->keys()->values()->filter(function ($k) use ($groupsAll, $filterAspek) {
            return FilterAspekPencapaian::groupHasMatch($filterAspek, $groupsAll[$k]);
        })->values();

        $perPage = PaginationPerPage::resolve($request);
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $total = $keysFiltered->count();
        $sliceKeys = $keysFiltered->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $pageItems = $sliceKeys->mapWithKeys(fn ($k) => [$k => $groupsAll[$k]]);

        $groupedPencapaian = new LengthAwarePaginator(
            $pageItems,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
                'pageName' => 'page',
            ]
        );

        $editBundles = [];
        foreach ($groupsAll as $k => $rows) {
            $first = $rows->first();
            $nilai = [];
            $catatan = [];
            $photoUrl = null;
            $photoPath = null;
            foreach ($rows as $r) {
                if ($r->matrikulasi_id) {
                    $key = (string) $r->matrikulasi_id;
                    $nilai[$key] = $r->score;
                    $catatan[$key] = $r->feedback ?? '';
                }
                if (filled($r->photo) && ! $photoUrl) {
                    $photoPath = $r->photo;
                    $photoUrl = DocumentationMedia::publicAssetPath($r->photo);
                }
            }
            $editBundles[$k] = [
                'bundle_key' => $k,
                'anak_id' => $first->anak_id,
                'kegiatan_id' => $first->kegiatan_id,
                'nilai' => $nilai,
                'catatan' => $catatan,
                'has_photo' => (bool) $photoUrl,
                'is_video' => $photoPath ? DocumentationMedia::isVideoPath($photoPath) : false,
                'photo_url' => $photoUrl,
                'photo_download_url' => $photoUrl
                    ? route('admin.pencapaian.photos.download-bundle', [
                        'anak_id' => $first->anak_id,
                        'kegiatan_id' => $first->kegiatan_id,
                    ])
                    : null,
            ];
        }

        $kegiatanQuery = Kegiatan::where('sekolah_id', $sekolah_id)->with('matrikulasis')->orderBy('date', 'desc');
        $user->applyScopedKelas($kegiatanQuery);
        if ($this->kelasFilterInScope($request)) {
            $kegiatanQuery->where('kelas_id', (int) $request->input('filter_kelas_id'));
        }
        $kegiatans = $kegiatanQuery->get();

        $aspekPilihan = Matrikulasi::query()
            ->where('sekolah_id', $sekolah_id)
            ->whereNotNull('aspek')
            ->where('aspek', '!=', '')
            ->distinct()
            ->orderBy('aspek')
            ->pluck('aspek');

        $filterAnakId = $request->filled('filter_anak_id') ? (int) $request->input('filter_anak_id') : null;
        $filterKelasId = $this->kelasFilterInScope($request) ? (int) $request->input('filter_kelas_id') : null;
        $availableKelas = $user->applyScopedKelas(Kelas::where('sekolah_id', $sekolah_id)->orderBy('name'), 'id')->get();

        $tokenBalance = $this->tokenService->getBalance((int) $sekolah_id);
        $hasTokens = $tokenBalance > 0;
        $tokenFallbackPencapaian = $this->tokenService->resolveFallback((int) $sekolah_id, AiTokenFeature::PENCAPAIAN);

        return view('admin.pencapaian.index', compact(
            'skalas',
            'groupedPencapaian',
            'editBundles',
            'anaks',
            'kegiatans',
            'availableKelas',
            'tanggalDari',
            'tanggalSampai',
            'filterAnakId',
            'filterKelasId',
            'filterAspek',
            'filterAspekRaw',
            'aspekPilihan',
            'hasTokens',
            'tokenFallbackPencapaian',
        ));
    }

    public function export(Request $request)
    {
        $sekolah_id = auth()->user()->sekolah_id;
        $range = TanggalRentang::dariSampaiQuery($request, null);
        $filterAspekRaw = (string) $request->input('aspek', '');
        $filterAspek = $filterAspekRaw === '' ? null : $filterAspekRaw;

        $hariQuery = Pencapaian::query()
            ->with(['anak', 'kegiatan', 'matrikulasi'])
            ->orderByDesc('updated_at');

        if ($range) {
            $hariQuery->whereDate('created_at', '>=', $range[0])
                ->whereDate('created_at', '<=', $range[1]);
        }
        $user = auth()->user();
        $hariQuery->whereHas('anak', function ($q) use ($user, $sekolah_id) {
            $q->where('sekolah_id', $sekolah_id);
            $user->applyScopedKelas($q);
        });
        if ($request->filled('filter_anak_id')) {
            $hariQuery->where('anak_id', (int) $request->input('filter_anak_id'));
        }
        if ($this->kelasFilterInScope($request)) {
            $hariQuery->whereHas('anak', fn ($q) => $q->where('kelas_id', (int) $request->input('filter_kelas_id')));
        }

        $rows = $hariQuery->get()
            ->filter(fn (Pencapaian $p) => FilterAspekPencapaian::groupHasMatch($filterAspek, collect([$p])))
            ->map(fn (Pencapaian $p) => [
                $p->anak?->name ?? '-',
                $p->kegiatan?->title ?? '-',
                ($p->matrikulasi?->aspek ?? '-').' / '.($p->matrikulasi?->indicator ?? '-'),
                $p->score ?? '-',
                $p->feedback ?? '-',
                $p->created_at?->format('Y-m-d') ?? '-',
            ])
            ->values()
            ->all();

        return $this->downloadExcel(
            ['Siswa', 'Kegiatan', 'Aspek / Indikator', 'Nilai', 'Catatan', 'Tanggal'],
            $rows,
            'pencapaian-siswa-'.now()->format('Y-m-d').'.xlsx',
            'Pencapaian Siswa'
        );
    }

    public function downloadPhotos(Request $request, PhotoArchiveService $photoArchive)
    {
        $records = $this->pencapaianQueryForExport($request)->get()
            ->filter(fn (Pencapaian $p) => FilterAspekPencapaian::groupHasMatch(
                $this->resolveFilterAspek($request),
                collect([$p])
            ));

        $entries = $photoArchive->entriesFromPencapaian($records->values());

        return $this->downloadPhotoArchive($entries, 'foto-pencapaian-'.now()->format('Y-m-d').'.zip');
    }

    public function downloadBundlePhoto(Request $request, PhotoArchiveService $photoArchive)
    {
        return $this->downloadPencapaianBundlePhoto($request, $photoArchive);
    }

    protected function authorizePencapaianBundleAnak(Anak $anak): void
    {
        abort_if($anak->sekolah_id !== auth()->user()->sekolah_id, 403);
        $this->assertKelasInScope($anak->kelas_id);
    }

    protected function pencapaianQueryForExport(Request $request)
    {
        $sekolah_id = auth()->user()->sekolah_id;
        $range = TanggalRentang::dariSampaiQuery($request, null);

        $hariQuery = Pencapaian::query()
            ->with(['anak', 'kegiatan', 'matrikulasi'])
            ->whereNotNull('photo')
            ->orderByDesc('updated_at');

        if ($range) {
            $hariQuery->whereDate('created_at', '>=', $range[0])
                ->whereDate('created_at', '<=', $range[1]);
        }
        $user = auth()->user();
        $hariQuery->whereHas('anak', function ($q) use ($user, $sekolah_id) {
            $q->where('sekolah_id', $sekolah_id);
            $user->applyScopedKelas($q);
        });
        if ($request->filled('filter_anak_id')) {
            $hariQuery->where('anak_id', (int) $request->input('filter_anak_id'));
        }
        if ($this->kelasFilterInScope($request)) {
            $hariQuery->whereHas('anak', fn ($q) => $q->where('kelas_id', (int) $request->input('filter_kelas_id')));
        }

        return $hariQuery;
    }

    protected function resolveFilterAspek(Request $request): ?string
    {
        $filterAspekRaw = (string) $request->input('aspek', '');

        return $filterAspekRaw === '' ? null : $filterAspekRaw;
    }

    public function sync(Request $request)
    {
        $sekolah_id = auth()->user()->sekolah_id;
        $scoreCodes = LabelSkorPencapaian::codesForSekolah((int) $sekolah_id);

        $request->validate([
            'anak_id' => 'required|exists:anaks,id',
            'kegiatan_id' => 'required|exists:kegiatans,id',
            'nilai' => 'required|array',
            'nilai.*' => ['required', 'string', Rule::in($scoreCodes)],
            'catatan' => 'nullable|array',
            'catatan.*' => 'nullable|string|max:2000',
            'photo' => DocumentationMedia::singleFileRules(),
        ]);

        $kegiatan = Kegiatan::query()
            ->where('id', $request->integer('kegiatan_id'))
            ->where('sekolah_id', $sekolah_id)
            ->with('matrikulasis')
            ->firstOrFail();

        // Kegiatan harus sudah dilaksanakan (ditandai dengan adanya foto dokumentasi)
        if (empty($kegiatan->photos)) {
            return back()
                ->withInput()
                ->withErrors(['kegiatan_id' => 'Pencapaian hanya dapat diisi untuk kegiatan yang sudah dilaksanakan (sudah ada foto dokumentasinya).']);
        }

        $matIds = $kegiatan->matrikulasis->pluck('id')->all();
        if ($matIds === []) {
            return back()
                ->withInput()
                ->withErrors(['kegiatan_id' => 'Kegiatan ini belum memiliki indikator matrikulasi. Tambahkan di Jurnal Kegiatan terlebih dahulu.']);
        }

        $anak = Anak::where('id', $request->integer('anak_id'))->where('sekolah_id', $sekolah_id)->firstOrFail();

        $this->assertKelasInScope($anak->kelas_id);
        $this->assertKelasInScope($kegiatan->kelas_id);

        if ($anak->kelas_id !== $kegiatan->kelas_id) {
            return back()
                ->withInput()
                ->withErrors(['kegiatan_id' => 'Data Siswa dan Jurnal Kegiatan harus berada di kelas yang sama.']);
        }

        // Pencapaian hanya bisa diinput 1 kali per kegiatan per anak (blokir jika belum pernah ada & ini bukan edit)
        $existingCount = Pencapaian::query()
            ->where('anak_id', $anak->id)
            ->where('kegiatan_id', $kegiatan->id)
            ->count();
        $isEdit = $request->boolean('_is_edit');
        if (! $isEdit && $existingCount > 0) {
            return back()
                ->withInput()
                ->withErrors(['kegiatan_id' => 'Pencapaian untuk anak dan kegiatan ini sudah pernah diisi. Gunakan tombol Edit untuk mengubahnya.']);
        }

        $nilaiInput = $request->input('nilai', []);
        foreach ($matIds as $mid) {
            $sk = (string) $mid;
            if (! array_key_exists($sk, $nilaiInput) && ! array_key_exists($mid, $nilaiInput)) {
                return back()
                    ->withInput()
                    ->withErrors(['nilai' => 'Setiap aspek matrikulasi wajib diberi nilai.']);
            }
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $existing = Pencapaian::query()
                ->where('anak_id', $anak->id)
                ->where('kegiatan_id', $kegiatan->id)
                ->whereNotNull('photo')
                ->first();
            if ($existing?->photo) {
                Storage::disk('public')->delete($existing->photo);
            }
            $photoPath = $this->storeDocumentationMedia($request->file('photo'), 'pencapaian', 'pencapaian-videos');
        } else {
            $photoPath = Pencapaian::query()
                ->where('anak_id', $anak->id)
                ->where('kegiatan_id', $kegiatan->id)
                ->whereNotNull('photo')
                ->value('photo');
        }

        foreach ($matIds as $mid) {
            $sk = (string) $mid;
            $score = $nilaiInput[$sk] ?? $nilaiInput[$mid];
            $catatan = $request->input("catatan.$sk") ?? $request->input("catatan.$mid");

            Pencapaian::query()->updateOrCreate(
                [
                    'anak_id' => $anak->id,
                    'kegiatan_id' => $kegiatan->id,
                    'matrikulasi_id' => $mid,
                ],
                [
                    'pengajar_id' => $kegiatan->pengajar_id,
                    'score' => $score,
                    'feedback' => $catatan ? (string) $catatan : null,
                    'photo' => $photoPath,
                ]
            );
        }

        Pencapaian::query()
            ->where('anak_id', $anak->id)
            ->where('kegiatan_id', $kegiatan->id)
            ->whereNotIn('matrikulasi_id', $matIds)
            ->whereNotNull('matrikulasi_id')
            ->delete();

        return redirect()
            ->route('admin.pencapaian.index', $this->pencapaianFilterQuery($request))
            ->with('success', 'Pencapaian per aspek berhasil disimpan.');
    }

    public function destroy(Pencapaian $pencapaian)
    {
        $sekolah_id = auth()->user()->sekolah_id;
        $pencapaian->loadMissing('anak');
        abort_if($pencapaian->anak?->sekolah_id !== $sekolah_id, 403);
        $this->assertKelasInScope($pencapaian->anak?->kelas_id);

        if ($pencapaian->photo) {
            $stillUsed = Pencapaian::query()
                ->where('anak_id', $pencapaian->anak_id)
                ->where('kegiatan_id', $pencapaian->kegiatan_id)
                ->where('id', '!=', $pencapaian->id)
                ->where('photo', $pencapaian->photo)
                ->exists();
            if (! $stillUsed) {
                Storage::disk('public')->delete($pencapaian->photo);
            }
        }

        $pencapaian->delete();

        return redirect()
            ->route('admin.pencapaian.index', $this->pencapaianFilterQuery(request()))
            ->with('success', 'Satu baris pencapaian dihapus.');
    }

    public function destroyBundle(Request $request)
    {
        $sekolah_id = auth()->user()->sekolah_id;
        $request->validate([
            'anak_id' => 'required|exists:anaks,id',
            'kegiatan_id' => 'required|exists:kegiatans,id',
        ]);

        $anak = Anak::where('id', $request->integer('anak_id'))->where('sekolah_id', $sekolah_id)->firstOrFail();
        $kegiatan = Kegiatan::where('id', $request->integer('kegiatan_id'))->where('sekolah_id', $sekolah_id)->firstOrFail();
        $this->assertKelasInScope($anak->kelas_id);
        $this->assertKelasInScope($kegiatan->kelas_id);

        $rows = Pencapaian::query()
            ->where('anak_id', $request->integer('anak_id'))
            ->where('kegiatan_id', $request->integer('kegiatan_id'))
            ->get();

        foreach ($rows as $pencapaian) {
            if ($pencapaian->photo) {
                $stillUsed = Pencapaian::query()
                    ->where('photo', $pencapaian->photo)
                    ->where('id', '!=', $pencapaian->id)
                    ->exists();
                if (! $stillUsed) {
                    Storage::disk('public')->delete($pencapaian->photo);
                }
            }
            $pencapaian->delete();
        }

        return redirect()
            ->route('admin.pencapaian.index', $this->pencapaianFilterQuery($request))
            ->with('success', 'Seluruh pencapaian untuk kegiatan ini dihapus.');
    }

    private function kelasFilterInScope(Request $request): bool
    {
        if (! $request->filled('filter_kelas_id')) {
            return false;
        }

        $ids = auth()->user()->scopedKelasIds();
        if ($ids === null) {
            return true;
        }

        return in_array((int) $request->input('filter_kelas_id'), $ids, true);
    }

    private function assertKelasInScope(mixed $kelasId): void
    {
        $ids = auth()->user()->scopedKelasIds();
        if ($ids === null) {
            return;
        }

        abort_unless(in_array((int) $kelasId, $ids, true), 403);
    }

    /** @return array<string, string> */
    private function pencapaianFilterQuery(Request $request): array
    {
        $range = TanggalRentang::dariSampaiQuery($request, null);
        $q = $range ? TanggalRentang::toQueryParams($range[0], $range[1]) : [];
        if ($request->filled('filter_anak_id')) {
            $q['filter_anak_id'] = (string) (int) $request->input('filter_anak_id');
        }
        if ($request->filled('filter_kelas_id')) {
            $q['filter_kelas_id'] = (string) (int) $request->input('filter_kelas_id');
        }
        if ($request->filled('aspek')) {
            $q['aspek'] = (string) $request->input('aspek');
        }

        return $q;
    }
}
