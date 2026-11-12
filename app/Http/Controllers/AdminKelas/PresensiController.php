<?php

namespace App\Http\Controllers\AdminKelas;

use App\Http\Controllers\Concerns\StoresStudentPresensi;
use App\Http\Controllers\Controller;
use App\Models\Anak;
use App\Models\Kelas;
use App\Models\Pengajar;
use App\Models\Presensi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PresensiController extends Controller
{
    use StoresStudentPresensi;
    public function index(Request $request)
    {
        $user = auth()->user();
        $pengajar = Pengajar::where('user_id', $user->id)->firstOrFail();
        $sekolah_id = $user->sekolah_id;
        $kelas = Kelas::where('wali_kelas_id', $pengajar->id)->orderBy('name')->get();
        $kelasIds = $kelas->pluck('id')->toArray();

        $tanggalInput = $request->query('tanggal', now()->format('Y-m-d'));
        try {
            $tanggal = Carbon::parse($tanggalInput)->format('Y-m-d');
        } catch (\Throwable) {
            $tanggal = now()->format('Y-m-d');
        }

        $filterKelasId = $request->query('filter_kelas_id');
        $queryAnak = Anak::query()->where('sekolah_id', $sekolah_id)->where('status', 'approved');

        if ($filterKelasId && in_array((int) $filterKelasId, $kelasIds)) {
            $queryAnak->where('kelas_id', $filterKelasId);
        } else {
            $queryAnak->whereIn('kelas_id', $kelasIds);
        }

        $anaks = $queryAnak->with('user')->orderBy('name')->get();

        $presensiByAnak = Presensi::where('sekolah_id', $sekolah_id)
            ->whereIn('anak_id', $anaks->pluck('id'))
            ->whereDate('tanggal', $tanggal)
            ->get()
            ->keyBy('anak_id');

        $hadirCount = $presensiByAnak->where('hadir', true)->count();

        // Rekap bulanan: hadir count for the month of the selected date
        $startOfMonth = Carbon::parse($tanggal)->startOfMonth()->toDateString();
        $endOfMonth = Carbon::parse($tanggal)->endOfMonth()->toDateString();
        $hadirBulanan = Presensi::where('sekolah_id', $sekolah_id)
            ->whereIn('anak_id', $anaks->pluck('id'))
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->where('hadir', true)
            ->selectRaw('anak_id, count(*) as total')
            ->groupBy('anak_id')
            ->pluck('total', 'anak_id');

        $statusLabels = Presensi::statusLabels();

        return view('adminkelas.presensi.index', compact('anaks', 'presensiByAnak', 'tanggal', 'hadirCount', 'kelas', 'filterKelasId', 'hadirBulanan', 'statusLabels'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $pengajar = Pengajar::where('user_id', $user->id)->firstOrFail();
        $sekolah_id = $user->sekolah_id;
        $kelasIds = Kelas::where('wali_kelas_id', $pengajar->id)->pluck('id')->toArray();

        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'presensi' => ['nullable', 'array'],
            'presensi.*.status' => ['required', Rule::in(Presensi::statusOptions())],
            'presensi.*.keterangan' => ['nullable', 'string', 'max:500'],
            'filter_kelas_id' => ['nullable', 'integer'],
        ]);

        $queryAnak = Anak::where('sekolah_id', $sekolah_id)->where('status', 'approved')->whereIn('kelas_id', $kelasIds);
        if ($request->filled('filter_kelas_id')) {
            $queryAnak->where('kelas_id', $request->filter_kelas_id);
        }
        $anakIds = $queryAnak->pluck('id')->all();

        return $this->finishPresensiSave(
            $request,
            $sekolah_id,
            $validated['tanggal'],
            $anakIds,
            $validated['presensi'] ?? [],
            'adminkelas.presensi.index'
        );
    }
}
