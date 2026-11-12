<?php

namespace App\Http\Controllers\OrangTua;

use App\Support\TenantContext;

use App\Http\Controllers\Controller;
use App\Models\Anak;
use App\Models\Presensi;
use App\Support\PresensiPeriodeFilter;
use Illuminate\Http\Request;

class PresensiController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $sekolahId = TenantContext::requireSekolahId();

        $anaks = Anak::where('user_id', $user->id)
            ->where('sekolah_id', $sekolahId)
            ->where('status', 'approved')
            ->get();
        $anakIds = $anaks->pluck('id');
        $selectedAnakId = null;
        if ($request->filled('anak_id')) {
            $candidate = (int) $request->input('anak_id');
            if ($anakIds->contains($candidate)) {
                $selectedAnakId = $candidate;
            }
        }

        $filter = PresensiPeriodeFilter::resolve($request);

        $presensisQuery = Presensi::whereIn('anak_id', $anakIds)
            ->whereBetween('tanggal', [$filter['from'], $filter['to']])
            ->orderBy('tanggal', 'desc')
            ->with('anak');

        if ($selectedAnakId !== null) {
            $presensisQuery->where('anak_id', $selectedAnakId);
        }

        $presensis = $presensisQuery->get();

        return view('orangtua.presensi.index', compact('anaks', 'presensis', 'filter', 'selectedAnakId'));
    }
}
