<?php

namespace App\Http\Controllers\OrangTua;

use App\Support\TenantContext;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\DownloadsPublicPhoto;
use App\Models\Anak;
use App\Models\MenuMakanan;
use App\Support\PaginationPerPage;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MenuMakananController extends Controller
{
    use DownloadsPublicPhoto;
    public function index(Request $request)
    {
        $user = auth()->user();
        $sekolah_id = TenantContext::requireSekolahId();

        $startDate = $request->input('start_date', now()->startOfWeek(CarbonInterface::MONDAY)->toDateString());
        $endDate = $request->input('end_date', now()->endOfWeek(CarbonInterface::SUNDAY)->toDateString());
        $perPage = PaginationPerPage::resolve($request);

        $menus = MenuMakanan::where('sekolah_id', $sekolah_id)
            ->whereBetween('date', [$startDate, $endDate])
            ->withCount(['votes as likes_count' => fn ($q) => $q->where('vote_type', 'like')])
            ->withCount(['votes as dislikes_count' => fn ($q) => $q->where('vote_type', 'dislike')])
            ->with(['votes' => fn ($q) => $q->where('user_id', $user->id)])
            ->orderBy('date', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return view('orangtua.menu_makanan.index', compact('menus', 'startDate', 'endDate'));
    }

    public function downloadPhoto(Request $request, MenuMakanan $menu_makanan, \App\Services\PhotoArchiveService $photoArchive)
    {
        abort_if($menu_makanan->sekolah_id !== TenantContext::requireSekolahId(), 403);

        $field = $request->validate([
            'field' => ['required', Rule::in(['photo', 'photo_kegiatan'])],
        ])['field'];

        $path = $menu_makanan->{$field};
        $prefix = $field === 'photo_kegiatan' ? 'menu-kegiatan' : 'menu-makanan';

        return $this->downloadPublicPhoto(
            $photoArchive,
            $path,
            $this->slugPhotoFilename($prefix.'-'.$menu_makanan->date?->format('Y-m-d'), $path)
        );
    }
}
