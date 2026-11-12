<?php

namespace App\Http\Controllers\Pengajar;

use App\Http\Controllers\Controller;
use App\Models\MonevGuruEvaluasi;
use App\Models\Pengajar;
use App\Support\PaginationPerPage;
use Illuminate\Http\Request;

class MonevGuruController extends Controller
{
    private function pengajar(): Pengajar
    {
        $pengajar = auth()->user()->pengajar;
        abort_if($pengajar === null, 403, 'Profil pengajar tidak ditemukan.');

        return $pengajar;
    }

    public function index(Request $request)
    {
        $pengajar = $this->pengajar();

        $query = MonevGuruEvaluasi::query()
            ->where('pengajar_id', $pengajar->id)
            ->final()
            ->with('evaluator')
            ->latest('finalized_at')
            ->latest('id');

        if ($request->filled('periode_mulai')) {
            $query->whereDate('periode_selesai', '>=', $request->input('periode_mulai'));
        }

        if ($request->filled('periode_selesai')) {
            $query->whereDate('periode_mulai', '<=', $request->input('periode_selesai'));
        }

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', '%'.$search.'%')
                    ->orWhereHas('evaluator', fn ($eq) => $eq->where('name', 'like', '%'.$search.'%'));
            });
        }

        $evaluasis = $query->paginate(PaginationPerPage::resolve($request))->withQueryString();

        return view('pengajar.monev-guru.index', compact('evaluasis'));
    }

    public function show(MonevGuruEvaluasi $monev_guru_evaluasi)
    {
        $pengajar = $this->pengajar();

        abort_if((int) $monev_guru_evaluasi->pengajar_id !== (int) $pengajar->id, 403);
        abort_unless($monev_guru_evaluasi->isFinal(), 404);

        $monev_guru_evaluasi->load(['evaluator', 'items.kriteria']);

        return view('pengajar.monev-guru.show', ['evaluasi' => $monev_guru_evaluasi]);
    }
}
