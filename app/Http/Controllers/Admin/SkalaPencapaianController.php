<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\DownloadsExcel;
use App\Http\Controllers\Controller;
use App\Models\Anak;
use App\Models\Pencapaian;
use App\Models\SkalaPencapaian;
use App\Support\PaginationPerPage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SkalaPencapaianController extends Controller
{
    use DownloadsExcel;
    private function sekolahId(): ?int
    {
        $id = auth()->user()->sekolah_id;

        return $id !== null ? (int) $id : null;
    }

    public function index(Request $request)
    {
        $sekolah_id = $this->sekolahId();
        abort_if($sekolah_id === null, 403, 'Akun tidak terikat sekolah.');

        $query = SkalaPencapaian::query()
            ->where('sekolah_id', $sekolah_id)
            ->orderBy('sort_order')
            ->orderBy('code');

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', '%'.$search.'%')
                    ->orWhere('label', 'like', '%'.$search.'%');
            });
        }

        $status = (string) $request->input('status', '');
        if ($status === 'aktif') {
            $query->where('is_active', true);
        } elseif ($status === 'nonaktif') {
            $query->where('is_active', false);
        }

        $skalas = $query->paginate(PaginationPerPage::resolve($request))->withQueryString();

        return view('admin.skala-pencapaian.index', compact('skalas'));
    }

    public function export(Request $request)
    {
        $sekolah_id = $this->sekolahId();
        abort_if($sekolah_id === null, 403);

        $rows = SkalaPencapaian::query()
            ->where('sekolah_id', $sekolah_id)
            ->orderBy('sort_order')
            ->orderBy('code')
            ->get()
            ->map(fn (SkalaPencapaian $s) => [
                $s->code,
                $s->label,
                $s->color ?? '-',
                $s->sort_order,
                $s->is_active ? 'Aktif' : 'Nonaktif',
            ])
            ->all();

        return $this->downloadExcel(
            ['Kode', 'Label', 'Warna', 'Urutan', 'Status'],
            $rows,
            'skala-capaian-'.now()->format('Y-m-d').'.xlsx',
            'Skala Capaian'
        );
    }

    public function store(Request $request)
    {
        $sekolah_id = $this->sekolahId();
        abort_if($sekolah_id === null, 403, 'Akun tidak terikat sekolah.');

        $request->validate($this->rules($sekolah_id));

        SkalaPencapaian::create([
            'sekolah_id' => $sekolah_id,
            'code' => strtoupper($request->code),
            'label' => $request->label,
            'color' => $request->color,
            'sort_order' => (int) $request->input('sort_order', 0),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.skala-pencapaian.index')->with('success', 'Skala capaian berhasil ditambahkan.');
    }

    public function update(Request $request, SkalaPencapaian $skalaPencapaian)
    {
        $sekolah_id = $this->sekolahId();
        abort_if($sekolah_id === null, 403);
        abort_if((int) $skalaPencapaian->sekolah_id !== $sekolah_id, 403);

        $request->validate($this->rules($sekolah_id, $skalaPencapaian->id));

        $skalaPencapaian->update([
            'code' => strtoupper($request->code),
            'label' => $request->label,
            'color' => $request->color,
            'sort_order' => (int) $request->input('sort_order', 0),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.skala-pencapaian.index')->with('success', 'Skala capaian berhasil diperbarui.');
    }

    public function destroy(SkalaPencapaian $skalaPencapaian)
    {
        $sekolah_id = $this->sekolahId();
        abort_if($sekolah_id === null, 403);
        abort_if((int) $skalaPencapaian->sekolah_id !== $sekolah_id, 403);

        $anakIds = Anak::query()->where('sekolah_id', $sekolah_id)->pluck('id');
        $inUse = Pencapaian::query()
            ->whereIn('anak_id', $anakIds)
            ->where('score', $skalaPencapaian->code)
            ->exists();

        if ($inUse) {
            return redirect()->route('admin.skala-pencapaian.index')
                ->withErrors(['delete' => 'Skala masih dipakai di data pencapaian. Nonaktifkan saja atau ubah data pencapaian terlebih dahulu.']);
        }

        $skalaPencapaian->delete();

        return redirect()->route('admin.skala-pencapaian.index')->with('success', 'Skala capaian berhasil dihapus.');
    }

    /** @return array<string, mixed> */
    private function rules(int $sekolahId, ?int $ignoreId = null): array
    {
        $uniqueCode = Rule::unique('skala_pencapaians', 'code')
            ->where('sekolah_id', $sekolahId);

        if ($ignoreId !== null) {
            $uniqueCode->ignore($ignoreId);
        }

        return [
            'code' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9_]+$/', $uniqueCode],
            'label' => 'required|string|max:255',
            'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'sort_order' => 'nullable|integer|min:0|max:65535',
            'is_active' => 'nullable|boolean',
        ];
    }
}
