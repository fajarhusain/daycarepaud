<?php

namespace App\Http\Controllers\AdminKelas;

use App\Http\Controllers\Controller;
use App\Models\Anak;
use App\Models\Kelas;
use App\Models\Kesehatan;
use App\Models\Pengajar;
use App\Support\PaginationPerPage;
use Carbon\Carbon;
use Illuminate\Http\Request;

class KesehatanController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $pengajar = Pengajar::where('user_id', $user->id)->firstOrFail();
        $kelasIds = Kelas::where('wali_kelas_id', $pengajar->id)->pluck('id')->toArray();

        $query = Anak::whereIn('kelas_id', $kelasIds)->with(['kelas', 'kesehatans' => function ($q) {
            $q->latest('tanggal_pemeriksaan')->limit(1);
        }]);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $anaks = $query->orderBy('name')->paginate(PaginationPerPage::resolve($request))->withQueryString();

        return view('adminkelas.kesehatan.index', compact('anaks'));
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
        $pengajar = Pengajar::where('user_id', $user->id)->firstOrFail();
        $kelasIds = Kelas::where('wali_kelas_id', $pengajar->id)->pluck('id')->toArray();

        if (! in_array($anak->kelas_id, $kelasIds)) {
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

    public function destroy(Request $request, Kesehatan $kesehatan)
    {
        $user = auth()->user();
        $pengajar = Pengajar::where('user_id', $user->id)->firstOrFail();
        $kelasIds = Kelas::where('wali_kelas_id', $pengajar->id)->pluck('id')->toArray();

        if (! in_array($kesehatan->anak->kelas_id, $kelasIds)) {
            abort(403);
        }

        $kesehatan->delete();

        return back()->with('success', 'Riwayat kesehatan berhasil dihapus.');
    }
}
