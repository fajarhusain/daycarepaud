<?php

namespace App\Http\Controllers\OrangTua;

use App\Support\TenantContext;

use App\Http\Controllers\Controller;
use App\Models\Anak;
use App\Models\KegiatanRutin;
use Illuminate\Http\Request;

class KegiatanRutinController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $sekolahId = TenantContext::requireSekolahId();

        $anaks = Anak::where('user_id', $user->id)
            ->where('sekolah_id', $sekolahId)
            ->get();

        $anakIds = $anaks->pluck('id');

        $query = KegiatanRutin::whereIn('anak_id', $anakIds)
            ->where('sekolah_id', $sekolahId)
            ->with(['anak', 'masterKegiatanRutin', 'pengajar']);

        $masters = KegiatanRutin::whereIn('anak_id', $anakIds)
            ->where('sekolah_id', $sekolahId)
            ->select('master_kegiatan_rutin_id', 'kegiatan')
            ->groupBy('master_kegiatan_rutin_id', 'kegiatan')
            ->get();

        $mulai = $request->query('mulai', date('Y-m-01'));
        $sampai = $request->query('sampai', date('Y-m-t'));

        if ($request->filled('anak_id')) {
            $query->where('anak_id', $request->query('anak_id'));
        }

        if ($request->filled('master_id')) {
            $query->where('master_kegiatan_rutin_id', $request->query('master_id'));
        }

        $query->whereBetween('tanggal', [$mulai, $sampai]);

        $kegiatans = $query->latest('tanggal')->get();

        return view('orangtua.kegiatan-rutin.index', compact('kegiatans', 'anaks', 'mulai', 'sampai', 'masters'));
    }
}
