<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\DownloadsExcel;
use App\Http\Controllers\Concerns\DownloadsPublicPhoto;
use App\Http\Controllers\Controller;
use App\Http\Traits\CanUploadImage;
use App\Models\Sarana;
use App\Support\PaginationPerPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SaranaController extends Controller
{
    use CanUploadImage;
    use DownloadsExcel;
    use DownloadsPublicPhoto;

    public function index(Request $request)
    {
        $sekolah_id = auth()->user()->sekolah_id;
        $saranas = Sarana::where('sekolah_id', $sekolah_id)->latest()->paginate(PaginationPerPage::resolve($request))->withQueryString();

        return view('admin.sarana.index', compact('saranas'));
    }

    public function export(Request $request)
    {
        $rows = Sarana::where('sekolah_id', auth()->user()->sekolah_id)
            ->latest()
            ->get()
            ->map(fn (Sarana $s) => [
                $s->name,
                $s->lokasi ?? '-',
                $s->jenis ?? '-',
                $s->quantity,
                $s->condition ?? '-',
            ])
            ->all();

        return $this->downloadExcel(
            ['Nama Sarana', 'Lokasi', 'Jenis', 'Jumlah', 'Kondisi'],
            $rows,
            'sarana-'.now()->format('Y-m-d').'.xlsx',
            'Sarana'
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'condition' => 'nullable|string|max:255',
            'quantity' => 'required|integer|min:1',
            'lokasi' => 'nullable|in:Outdoor,Indoor',
            'jenis' => 'nullable|in:Edukasi,Permainan,Sarpras,ATK',
        ]);

        $data = [
            'sekolah_id' => auth()->user()->sekolah_id,
            'name' => $request->name,
            'condition' => $request->condition,
            'quantity' => $request->quantity,
            'lokasi' => $request->lokasi,
            'jenis' => $request->jenis,
        ];

        if ($request->hasFile('photo')) {
            $data['photo'] = $this->uploadImage($request->file('photo'), 'sarana');
        }

        Sarana::create($data);

        return redirect()->route('admin.sarana.index')->with('success', 'Data Sarana berhasil ditambahkan.');
    }

    public function update(Request $request, Sarana $sarana)
    {
        abort_if($sarana->sekolah_id !== auth()->user()->sekolah_id, 403);

        $request->validate([
            'name' => 'required|string|max:255',
            'condition' => 'nullable|string|max:255',
            'quantity' => 'required|integer|min:1',
            'lokasi' => 'nullable|in:Outdoor,Indoor',
            'jenis' => 'nullable|in:Edukasi,Permainan,Sarpras,ATK',
        ]);

        $dataArr = [
            'name' => $request->name,
            'condition' => $request->condition,
            'quantity' => $request->quantity,
            'lokasi' => $request->lokasi,
            'jenis' => $request->jenis,
        ];

        if ($request->hasFile('photo')) {
            if ($sarana->photo) {
                Storage::disk('public')->delete($sarana->photo);
            }
            $dataArr['photo'] = $this->uploadImage($request->file('photo'), 'sarana');
        }

        $sarana->update($dataArr);

        return redirect()->route('admin.sarana.index')->with('success', 'Data Sarana berhasil diperbarui.');
    }

    public function destroy(Sarana $sarana)
    {
        abort_if($sarana->sekolah_id !== auth()->user()->sekolah_id, 403);
        if ($sarana->photo) {
            Storage::disk('public')->delete($sarana->photo);
        }
        $sarana->delete();

        return redirect()->route('admin.sarana.index')->with('success', 'Data Sarana berhasil dihapus.');
    }

    public function downloadPhoto(Sarana $sarana, \App\Services\PhotoArchiveService $photoArchive)
    {
        abort_if($sarana->sekolah_id !== auth()->user()->sekolah_id, 403);

        return $this->downloadPublicPhoto(
            $photoArchive,
            $sarana->photo,
            $this->slugPhotoFilename($sarana->name ?? 'sarana', $sarana->photo)
        );
    }
}
