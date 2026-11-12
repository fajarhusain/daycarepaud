<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\CanUploadImage;
use App\Models\Kelas;
use App\Models\Pengumuman;
use App\Support\PaginationPerPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PengumumanController extends Controller
{
    use CanUploadImage;

    public function index(Request $request)
    {
        $sekolah_id = auth()->user()->sekolah_id;
        $query = Pengumuman::where('sekolah_id', $sekolah_id)
            ->with('kelas')
            ->latest('mulai_tayang')
            ->latest('id');

        $waliKelasIds = $this->waliKelasIds();
        if ($waliKelasIds !== null) {
            $query->whereIn('kelas_id', $waliKelasIds);
        }

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', '%'.$search.'%')
                    ->orWhere('isi', 'like', '%'.$search.'%')
                    ->orWhere('kategori', 'like', '%'.$search.'%');
            });
        }

        if ($request->filled('kelas_id')) {
            $kelasFilter = (int) $request->input('kelas_id');
            if ($waliKelasIds === null || in_array($kelasFilter, $waliKelasIds, true)) {
                $query->where('kelas_id', $kelasFilter);
            }
        }

        $status = (string) $request->input('status', '');
        $today = today();
        if ($status === 'aktif') {
            $query->where('is_active', true)
                ->whereDate('mulai_tayang', '<=', $today)
                ->whereDate('selesai_tayang', '>=', $today);
        } elseif ($status === 'nonaktif') {
            $query->where('is_active', false);
        } elseif ($status === 'belum_tayang') {
            $query->where('is_active', true)->whereDate('mulai_tayang', '>', $today);
        } elseif ($status === 'berakhir') {
            $query->whereDate('selesai_tayang', '<', $today);
        }

        $pengumumans = $query
            ->paginate(PaginationPerPage::resolve($request))
            ->withQueryString();

        $kelasList = $this->kelasOptions($sekolah_id, $waliKelasIds);

        return view('admin.pengumuman.index', compact('pengumumans', 'kelasList', 'waliKelasIds'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatePengumuman($request);
        $kelasId = $this->resolveKelasId($validated['kelas_id'] ?? null);

        $data = [
            'sekolah_id' => auth()->user()->sekolah_id,
            'kelas_id' => $kelasId,
            'judul' => $validated['judul'],
            'kategori' => $validated['kategori'],
            'isi' => $validated['isi'],
            'mulai_tayang' => $validated['mulai_tayang'],
            'selesai_tayang' => $validated['selesai_tayang'],
            'is_active' => $request->boolean('is_active', true),
        ];

        if ($request->hasFile('gambar')) {
            $data['gambar'] = $this->uploadImage($request->file('gambar'), 'pengumuman');
        }

        Pengumuman::create($data);

        return redirect()->route('admin.pengumuman.index')->with('success', 'Pengumuman berhasil ditambahkan.');
    }

    public function update(Request $request, Pengumuman $pengumuman)
    {
        $this->assertPengumumanAccessible($pengumuman);

        $validated = $this->validatePengumuman($request);
        $kelasId = $this->resolveKelasId($validated['kelas_id'] ?? null);

        $data = [
            'kelas_id' => $kelasId,
            'judul' => $validated['judul'],
            'kategori' => $validated['kategori'],
            'isi' => $validated['isi'],
            'mulai_tayang' => $validated['mulai_tayang'],
            'selesai_tayang' => $validated['selesai_tayang'],
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->hasFile('gambar')) {
            if ($pengumuman->gambar) {
                Storage::disk('public')->delete($pengumuman->gambar);
            }
            $data['gambar'] = $this->uploadImage($request->file('gambar'), 'pengumuman');
        }

        $pengumuman->update($data);

        return redirect()->route('admin.pengumuman.index')->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Pengumuman $pengumuman)
    {
        $this->assertPengumumanAccessible($pengumuman);

        if ($pengumuman->gambar) {
            Storage::disk('public')->delete($pengumuman->gambar);
        }

        $pengumuman->delete();

        return redirect()->route('admin.pengumuman.index')->with('success', 'Pengumuman berhasil dihapus.');
    }

    /** @return list<int>|null */
    private function waliKelasIds(): ?array
    {
        return auth()->user()->scopedKelasIds();
    }

    /** @param  list<int>|null  $waliKelasIds */
    private function kelasOptions(int $sekolahId, ?array $waliKelasIds)
    {
        $query = Kelas::where('sekolah_id', $sekolahId)->orderBy('name');
        if ($waliKelasIds !== null) {
            $query->whereIn('id', $waliKelasIds);
        }

        return $query->get();
    }

    private function resolveKelasId(?int $kelasId): ?int
    {
        $waliKelasIds = $this->waliKelasIds();
        if ($waliKelasIds !== null) {
            abort_if($waliKelasIds === [], 403);
            abort_if($kelasId === null || ! in_array($kelasId, $waliKelasIds, true), 403);

            return $kelasId;
        }

        if ($kelasId === null) {
            return null;
        }

        abort_unless(
            Kelas::where('sekolah_id', auth()->user()->sekolah_id)->whereKey($kelasId)->exists(),
            403
        );

        return $kelasId;
    }

    private function assertPengumumanAccessible(Pengumuman $pengumuman): void
    {
        abort_if($pengumuman->sekolah_id !== auth()->user()->sekolah_id, 403);

        $waliKelasIds = $this->waliKelasIds();
        if ($waliKelasIds === null) {
            return;
        }

        abort_if($pengumuman->kelas_id === null || ! in_array((int) $pengumuman->kelas_id, $waliKelasIds, true), 403);
    }

    private function validatePengumuman(Request $request): array
    {
        $waliKelasIds = $this->waliKelasIds();

        return $request->validate([
            'judul' => 'required|string|max:255',
            'kategori' => 'required|string|max:100',
            'isi' => 'required|string|max:10000',
            'mulai_tayang' => 'required|date',
            'selesai_tayang' => ['required', 'date', 'after_or_equal:mulai_tayang'],
            'gambar' => 'nullable|image|max:2048',
            'is_active' => ['nullable', Rule::in(['0', '1', 0, 1, true, false])],
            'kelas_id' => [
                $waliKelasIds !== null ? 'required' : 'nullable',
                'integer',
                Rule::exists('kelas', 'id')->where(fn ($q) => $q->where('sekolah_id', auth()->user()->sekolah_id)),
            ],
        ]);
    }
}
