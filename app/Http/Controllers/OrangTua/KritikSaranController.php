<?php

namespace App\Http\Controllers\OrangTua;

use App\Support\TenantContext;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\DownloadsPublicPhoto;
use App\Http\Traits\CanUploadImage;
use App\Models\KritikSaran;
use App\Support\PaginationPerPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KritikSaranController extends Controller
{
    use CanUploadImage;
    use DownloadsPublicPhoto;

    public function index(Request $request)
    {
        $feedbacks = KritikSaran::query()
            ->where('user_id', auth()->id())
            ->with('sekolah')
            ->latest()
            ->paginate(PaginationPerPage::resolve($request))->withQueryString();

        return view('orangtua.kritik_saran.index', compact('feedbacks'));
    }

    public function show(KritikSaran $kritik_saran)
    {
        abort_if((int) $kritik_saran->user_id !== (int) auth()->id(), 403);

        $kritik_saran->load('sekolah');

        return view('orangtua.kritik_saran.show', compact('kritik_saran'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string|min:10',
            'photo' => 'nullable|image|max:2048',
        ]);

        $data = [
            'sekolah_id' => TenantContext::requireSekolahId(),
            'user_id' => auth()->id(),
            'message' => $request->message,
            'status' => 'Terkirim',
        ];

        if ($request->hasFile('photo')) {
            $data['photo'] = $this->uploadImage($request->file('photo'), 'kritik-saran');
        }

        KritikSaran::create($data);

        return redirect()->route('orangtua.kritik-saran.index')->with('success', 'Kritik atau saran Anda berhasil dikirimkan ke pihak sekolah/yayasan.');
    }

    public function update(Request $request, KritikSaran $kritik_saran)
    {
        abort_if((int) $kritik_saran->user_id !== (int) auth()->id(), 403);
        if ($kritik_saran->status !== 'Terkirim') {
            return back()->with('error', 'Pesan yang sudah diproses atau ditanggapi tidak dapat diubah.');
        }

        $request->validate([
            'message' => 'required|string|min:10',
            'photo' => 'nullable|image|max:2048',
        ]);

        $data = [
            'message' => $request->message,
        ];

        if ($request->hasFile('photo')) {
            if ($kritik_saran->photo) {
                Storage::disk('public')->delete($kritik_saran->photo);
            }
            $data['photo'] = $this->uploadImage($request->file('photo'), 'kritik-saran');
        }

        $kritik_saran->update($data);

        return redirect()->route('orangtua.kritik-saran.index')->with('success', 'Pesan Anda berhasil diperbarui.');
    }

    public function destroy(KritikSaran $kritik_saran)
    {
        abort_if((int) $kritik_saran->user_id !== (int) auth()->id(), 403);
        if ($kritik_saran->status !== 'Terkirim') {
            return back()->with('error', 'Pesan yang sudah diproses atau ditanggapi tidak dapat dihapus.');
        }

        if ($kritik_saran->photo) {
            Storage::disk('public')->delete($kritik_saran->photo);
        }

        $kritik_saran->delete();

        return redirect()->route('orangtua.kritik-saran.index')->with('success', 'Pesan Anda berhasil dihapus.');
    }

    public function downloadPhoto(KritikSaran $kritik_saran, \App\Services\PhotoArchiveService $photoArchive)
    {
        abort_if((int) $kritik_saran->user_id !== (int) auth()->id(), 403);

        return $this->downloadPublicPhoto(
            $photoArchive,
            $kritik_saran->photo,
            $this->slugPhotoFilename('kritik-saran-'.$kritik_saran->id, $kritik_saran->photo)
        );
    }
}
