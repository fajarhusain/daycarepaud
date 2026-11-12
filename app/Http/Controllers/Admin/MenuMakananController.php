<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\CanUploadImage;
use App\Models\MenuMakanan;
use App\Services\PhotoArchiveService;
use App\Support\KegiatanCalendar;
use App\Support\MenuMakananCalendar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MenuMakananController extends Controller
{
    use CanUploadImage;
    use \App\Http\Controllers\Concerns\DownloadsPublicPhoto;

    public function index(Request $request)
    {
        $sekolah_id = auth()->user()->sekolah_id;
        [$year, $month] = KegiatanCalendar::resolveYearMonth($request);
        [$from, $to] = KegiatanCalendar::dateRangeForCalendar($year, $month);

        $menusQuery = MenuMakanan::query()
            ->where('sekolah_id', $sekolah_id)
            ->withCount(['votes as likes_count' => fn ($q) => $q->where('vote_type', 'like')])
            ->withCount(['votes as dislikes_count' => fn ($q) => $q->where('vote_type', 'dislike')])
            ->whereBetween('date', [$from, $to]);

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $menusQuery->where(function ($q) use ($search) {
                $q->where('menu', 'like', '%'.$search.'%')
                    ->orWhere('nutrition_info', 'like', '%'.$search.'%');
            });
        }

        $foto = (string) $request->input('foto', '');
        if ($foto === 'ada') {
            $menusQuery->where(function ($q) {
                $q->whereNotNull('photo')->orWhereNotNull('photo_kegiatan');
            });
        } elseif ($foto === 'tanpa') {
            $menusQuery->whereNull('photo')->whereNull('photo_kegiatan');
        }

        $menus = $menusQuery
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $calendarEvents = $menus->map(fn (MenuMakanan $m) => MenuMakananCalendar::toAdminEvent($m))->values()->all();

        return view('admin.menu_makanan.index', compact('calendarEvents', 'year', 'month'));
    }

    /** @return array<string, mixed> */
    protected function calendarQueryParams(Request $request): array
    {
        return array_filter([
            'year' => $request->input('year'),
            'month' => $request->input('month'),
            'view' => $request->input('view'),
            'day' => $request->input('day'),
            'search' => $request->input('search'),
            'foto' => $request->input('foto'),
        ], fn ($v) => $v !== null && $v !== '');
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'menu' => 'required|string|max:5000',
            'nutrition_info' => 'nullable|string',
            'photo' => 'nullable|image|max:2048', // max 2MB
            'photo_kegiatan' => 'nullable|image|max:2048',
        ]);

        $data = [
            'sekolah_id' => auth()->user()->sekolah_id,
            'date' => $request->date,
            'menu' => $request->menu,
            'nutrition_info' => $request->nutrition_info,
        ];

        if ($request->hasFile('photo')) {
            $data['photo'] = $this->uploadImage($request->file('photo'), 'menu_makanan');
        }
        if ($request->hasFile('photo_kegiatan')) {
            $data['photo_kegiatan'] = $this->uploadImage($request->file('photo_kegiatan'), 'menu_kegiatan');
        }

        MenuMakanan::create($data);

        return redirect()->route('admin.menu-makanan.index', $this->calendarQueryParams($request))->with('success', 'Menu Makanan berhasil ditambahkan.');
    }

    public function update(Request $request, MenuMakanan $menu_makanan)
    {
        abort_if($menu_makanan->sekolah_id !== auth()->user()->sekolah_id, 403);

        $request->validate([
            'date' => 'required|date',
            'menu' => 'required|string|max:5000',
            'nutrition_info' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'photo_kegiatan' => 'nullable|image|max:2048',
        ]);

        $data = [
            'date' => $request->date,
            'menu' => $request->menu,
            'nutrition_info' => $request->nutrition_info,
        ];

        if ($request->hasFile('photo')) {
            // Delete old photo
            if ($menu_makanan->photo) {
                Storage::disk('public')->delete($menu_makanan->photo);
            }
            $data['photo'] = $this->uploadImage($request->file('photo'), 'menu_makanan');
        }
        if ($request->hasFile('photo_kegiatan')) {
            if ($menu_makanan->photo_kegiatan) {
                Storage::disk('public')->delete($menu_makanan->photo_kegiatan);
            }
            $data['photo_kegiatan'] = $this->uploadImage($request->file('photo_kegiatan'), 'menu_kegiatan');
        }

        $menu_makanan->update($data);

        return redirect()->route('admin.menu-makanan.index', $this->calendarQueryParams($request))->with('success', 'Menu Makanan berhasil diperbarui.');
    }

    public function destroy(Request $request, MenuMakanan $menu_makanan)
    {
        abort_if($menu_makanan->sekolah_id !== auth()->user()->sekolah_id, 403);

        if ($menu_makanan->photo) {
            Storage::disk('public')->delete($menu_makanan->photo);
        }
        if ($menu_makanan->photo_kegiatan) {
            Storage::disk('public')->delete($menu_makanan->photo_kegiatan);
        }
        $menu_makanan->delete();

        return redirect()->route('admin.menu-makanan.index', $this->calendarQueryParams($request))->with('success', 'Menu Makanan berhasil dihapus.');
    }

    public function downloadPhoto(Request $request, MenuMakanan $menu_makanan, PhotoArchiveService $photoArchive)
    {
        abort_if($menu_makanan->sekolah_id !== auth()->user()->sekolah_id, 403);

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
