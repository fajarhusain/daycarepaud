<?php

namespace App\Http\Controllers\OrangTua;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\DownloadsPublicPhoto;
use App\Http\Controllers\Concerns\ResolvesPencapaianBundlePhoto;
use App\Models\Anak;
use App\Models\Matrikulasi;
use App\Models\Pencapaian;
use App\Support\FilterAspekPencapaian;
use App\Support\LabelSkorPencapaian;
use App\Support\PaginationPerPage;
use App\Support\TanggalRentang;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class PencapaianController extends Controller
{
    use ResolvesPencapaianBundlePhoto;
    public function index(Request $request)
    {
        $anakList = Anak::where('user_id', auth()->id())->orderBy('name')->get();
        $anakIds = $anakList->pluck('id');

        $query = Pencapaian::query()
            ->with(['anak', 'kegiatan', 'matrikulasi', 'pengajar'])
            ->whereIn('anak_id', $anakIds);

        $filterAnakId = null;
        if ($request->filled('filter_anak_id')) {
            $aid = (int) $request->input('filter_anak_id');
            if ($anakIds->contains($aid)) {
                $query->where('anak_id', $aid);
                $filterAnakId = $aid;
            }
        }

        $range = TanggalRentang::dariSampaiQuery($request, null);
        if ($range !== null) {
            $query->whereDate('created_at', '>=', $range[0])
                ->whereDate('created_at', '<=', $range[1]);
        }

        $filterAspekRaw = (string) $request->input('aspek', '');
        $filterAspek = $filterAspekRaw === '' ? null : $filterAspekRaw;

        $all = $query->orderByDesc('updated_at')->get();
        $groupsAll = $all->groupBy(fn ($p) => $p->anak_id.'_'.$p->kegiatan_id);

        $keysFiltered = $groupsAll->keys()->sortByDesc(function ($k) use ($groupsAll) {
            return $groupsAll[$k]->max('updated_at');
        })->values()->filter(function ($k) use ($groupsAll, $filterAspek) {
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

        $filterTanggalAktif = $range !== null;
        $tanggalDari = $filterTanggalAktif ? $range[0] : '';
        $tanggalSampai = $filterTanggalAktif ? $range[1] : '';

        $sekolahIds = $anakList->pluck('sekolah_id')->unique()->filter();
        $aspekPilihan = $sekolahIds->isNotEmpty()
            ? Matrikulasi::query()
                ->whereIn('sekolah_id', $sekolahIds)
                ->whereNotNull('aspek')
                ->where('aspek', '!=', '')
                ->distinct()
                ->orderBy('aspek')
                ->pluck('aspek')
            : collect();

        $filterAktif = $filterTanggalAktif || $filterAnakId !== null || $filterAspek !== null;

        $skalaLegenda = collect();
        foreach ($sekolahIds as $sid) {
            $skalaLegenda = $skalaLegenda->merge(LabelSkorPencapaian::optionsForSekolah((int) $sid));
        }
        $skalaLegenda = $skalaLegenda->unique('code')->sortBy('sort_order')->values();

        return view('orangtua.pencapaian.index', compact(
            'skalaLegenda',
            'groupedPencapaian',
            'tanggalDari',
            'tanggalSampai',
            'filterAktif',
            'filterTanggalAktif',
            'anakList',
            'filterAnakId',
            'filterAspek',
            'filterAspekRaw',
            'aspekPilihan',
        ));
    }

    public function downloadBundlePhoto(Request $request, \App\Services\PhotoArchiveService $photoArchive)
    {
        return $this->downloadPencapaianBundlePhoto($request, $photoArchive);
    }

    protected function authorizePencapaianBundleAnak(Anak $anak): void
    {
        abort_if((int) $anak->user_id !== (int) auth()->id(), 403);
    }
}
