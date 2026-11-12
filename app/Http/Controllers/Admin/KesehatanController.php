<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\DownloadsExcel;
use App\Http\Controllers\Controller;
use App\Models\Anak;
use App\Models\Kelas;
use App\Models\Kesehatan;
use App\Support\PaginationPerPage;
use Carbon\Carbon;
use Illuminate\Http\Request;

class KesehatanController extends Controller
{
    use DownloadsExcel;
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Anak::where('sekolah_id', $user->sekolah_id)->with(['kelas', 'kesehatans' => function ($q) {
            $q->latest('tanggal_pemeriksaan')->limit(1);
        }]);
        $user->applyScopedKelas($query);

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $pemeriksaan = (string) $request->input('pemeriksaan', '');
        if ($pemeriksaan === 'belum') {
            $query->whereDoesntHave('kesehatans');
        } elseif ($pemeriksaan === 'sudah') {
            $query->whereHas('kesehatans');
        }

        $anaks = $query->orderBy('name')->paginate(PaginationPerPage::resolve($request))->withQueryString();
        $kelas = $user->applyScopedKelas(Kelas::where('sekolah_id', $user->sekolah_id)->orderBy('name'), 'id')->get();

        return view('admin.kesehatan.index', compact('anaks', 'kelas'));
    }

    public function export(Request $request)
    {
        $user = auth()->user();
        $query = Anak::where('sekolah_id', $user->sekolah_id)->with(['kelas', 'kesehatans' => fn ($q) => $q->latest('tanggal_pemeriksaan')->limit(1)]);
        $user->applyScopedKelas($query);

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }
        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $pemeriksaan = (string) $request->input('pemeriksaan', '');
        if ($pemeriksaan === 'belum') {
            $query->whereDoesntHave('kesehatans');
        } elseif ($pemeriksaan === 'sudah') {
            $query->whereHas('kesehatans');
        }

        $rows = $query->orderBy('name')->get()->map(function (Anak $anak) {
            $k = $anak->kesehatans->first();

            return [
                $anak->name,
                $anak->kelas?->name ?? '-',
                $k ? ($k->berat_badan.' / '.$k->tinggi_badan) : '-',
                $k ? trim(($k->gigi ?? '-').' / '.($k->telinga ?? '-').' / '.($k->kuku ?? '-')) : '-',
                $k?->alergi ?? '-',
                $k?->tanggal_pemeriksaan?->format('Y-m-d') ?? '-',
            ];
        })->all();

        return $this->downloadExcel(
            ['Siswa', 'Kelas', 'BB/TB', 'Kebersihan (G/T/K)', 'Alergi', 'Pemeriksaan Terakhir'],
            $rows,
            'kesehatan-siswa-'.now()->format('Y-m-d').'.xlsx',
            'Kesehatan Siswa'
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'anak_id' => 'required|exists:anaks,id',
            'berat_badan' => 'nullable|numeric',
            'tinggi_badan' => 'nullable|numeric',
            'lingkar_kepala' => 'nullable|numeric',
            'gigi' => 'nullable|string',
            'telinga' => 'nullable|string',
            'kuku' => 'nullable|string',
            'alergi' => 'nullable|string',
            'tanggal_pemeriksaan' => 'required|date',
        ]);

        Kesehatan::updateOrCreate(
            ['anak_id' => $data['anak_id'], 'tanggal_pemeriksaan' => $data['tanggal_pemeriksaan']],
            $data
        );

        return back()->with('success', 'Data kesehatan berhasil diperbarui.');
    }

    public function history(Request $request, Anak $anak)
    {
        $user = auth()->user();
        if ($anak->sekolah_id !== $user->sekolah_id) {
            abort(403);
        }

        $histories = Kesehatan::where('anak_id', $anak->id)
            ->orderBy('tanggal_pemeriksaan', 'desc')
            ->get()
            ->map(function ($q) {
                $tanggal = Carbon::parse($q->tanggal_pemeriksaan);

                return [
                    'id' => $q->id,
                    'tanggal_pemeriksaan' => $tanggal->format('Y-m-d'),
                    'tanggal_formatted' => $tanggal->format('d M Y'),
                    'berat_badan' => $q->berat_badan,
                    'tinggi_badan' => $q->tinggi_badan,
                    'lingkar_kepala' => $q->lingkar_kepala,
                    'gigi' => $q->gigi,
                    'telinga' => $q->telinga,
                    'kuku' => $q->kuku,
                    'alergi' => $q->alergi,
                ];
            });

        return response()->json($histories);
    }

    public function destroy(Kesehatan $kesehatan)
    {
        $user = auth()->user();
        if ($kesehatan->anak->sekolah_id !== $user->sekolah_id) {
            abort(403);
        }

        $kesehatan->delete();

        return back()->with('success', 'Riwayat kesehatan berhasil dihapus.');
    }
}
