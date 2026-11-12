<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\DownloadsExcel;
use App\Http\Controllers\Concerns\StoresStudentPresensi;
use App\Http\Controllers\Controller;
use App\Models\Anak;
use App\Models\Kelas;
use App\Models\Presensi;
use App\Support\PaginationPerPage;
use App\Support\PresensiPeriodeFilter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PresensiController extends Controller
{
    use DownloadsExcel;
    use StoresStudentPresensi;
    public function index(Request $request)
    {
        $sekolah_id = auth()->user()->sekolah_id;

        $tanggalInput = $request->query('tanggal', now()->format('Y-m-d'));
        try {
            $tanggal = Carbon::parse($tanggalInput)->format('Y-m-d');
        } catch (\Throwable) {
            $tanggal = now()->format('Y-m-d');
        }

        $filterKelasId = $request->query('filter_kelas_id');
        $scopeQuery = $this->buildAnaksQuery($sekolah_id, $filterKelasId);
        $scopeAnakIds = (clone $scopeQuery)->pluck('id');
        $totalSiswa = $scopeAnakIds->count();

        $presensiByAnak = Presensi::where('sekolah_id', $sekolah_id)
            ->whereDate('tanggal', $tanggal)
            ->get()
            ->keyBy('anak_id');

        $hadirCount = Presensi::where('sekolah_id', $sekolah_id)
            ->whereDate('tanggal', $tanggal)
            ->where('hadir', true)
            ->whereIn('anak_id', $scopeAnakIds)
            ->count();

        $anaks = $scopeQuery
            ->paginate(PaginationPerPage::resolve($request))
            ->withQueryString();

        $kelas = auth()->user()->applyScopedKelas(Kelas::where('sekolah_id', $sekolah_id)->orderBy('name'), 'id')->get();

        // Rekap bulanan: hadir count for the month of the selected date
        $startOfMonth = Carbon::parse($tanggal)->startOfMonth()->toDateString();
        $endOfMonth = Carbon::parse($tanggal)->endOfMonth()->toDateString();
        $hadirBulanan = Presensi::where('sekolah_id', $sekolah_id)
            ->whereIn('anak_id', $scopeAnakIds)
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->where('hadir', true)
            ->selectRaw('anak_id, count(*) as total')
            ->groupBy('anak_id')
            ->pluck('total', 'anak_id');

        $statusLabels = Presensi::statusLabels();

        return view('admin.presensi.index', compact('anaks', 'presensiByAnak', 'tanggal', 'hadirCount', 'totalSiswa', 'kelas', 'hadirBulanan', 'filterKelasId', 'statusLabels'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $sekolah_id = $user->sekolah_id;

        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'presensi' => ['nullable', 'array'],
            'presensi.*.status' => ['required', Rule::in(Presensi::statusOptions())],
            'presensi.*.keterangan' => ['nullable', 'string', 'max:500'],
            'filter_kelas_id' => ['nullable', 'integer'],
            'page_anak_ids' => ['nullable', 'array'],
            'page_anak_ids.*' => ['integer', 'exists:anaks,id'],
        ]);

        $anakIds = $this->buildAnaksQuery($sekolah_id, $request->input('filter_kelas_id'))
            ->pluck('id')
            ->all();

        if (! $request->expectsJson()) {
            $pageAnakIds = array_map('intval', $validated['page_anak_ids'] ?? []);
            if ($pageAnakIds !== []) {
                $anakIds = array_values(array_intersect($anakIds, $pageAnakIds));
            }
        }

        return $this->finishPresensiSave(
            $request,
            $sekolah_id,
            $validated['tanggal'],
            $anakIds,
            $validated['presensi'] ?? [],
            'admin.presensi.index'
        );
    }

    public function rekap(Request $request)
    {
        $sekolah_id = auth()->user()->sekolah_id;

        $anaksQuery = $this->buildAnaksQuery($sekolah_id, $request->input('kelas_id'));
        $scopeAnakIds = (clone $anaksQuery)->pluck('id');
        $anaks = $anaksQuery->paginate(PaginationPerPage::resolve($request))->withQueryString();

        $presensiFilter = PresensiPeriodeFilter::resolve($request);
        $hadirPeriode = Presensi::where('sekolah_id', $sekolah_id)
            ->whereBetween('tanggal', [$presensiFilter['from'], $presensiFilter['to']])
            ->where('hadir', true)
            ->whereIn('anak_id', $scopeAnakIds)
            ->selectRaw('anak_id, count(*) as total')
            ->groupBy('anak_id')
            ->pluck('total', 'anak_id');

        $kelas = auth()->user()->applyScopedKelas(Kelas::where('sekolah_id', $sekolah_id)->orderBy('name'), 'id')->get();

        return view('admin.presensi.rekap', compact('anaks', 'hadirPeriode', 'presensiFilter', 'kelas'));
    }

    public function export(Request $request)
    {
        $sekolah_id = auth()->user()->sekolah_id;
        $tanggal = $this->resolveTanggal($request);
        $filterKelasId = $request->query('filter_kelas_id');

        $anaks = $this->buildAnaksQuery($sekolah_id, $filterKelasId)->get();
        $presensiByAnak = Presensi::where('sekolah_id', $sekolah_id)
            ->whereDate('tanggal', $tanggal)
            ->get()
            ->keyBy('anak_id');

        $startOfMonth = Carbon::parse($tanggal)->startOfMonth()->toDateString();
        $endOfMonth = Carbon::parse($tanggal)->endOfMonth()->toDateString();
        $hadirBulanan = Presensi::where('sekolah_id', $sekolah_id)
            ->whereIn('anak_id', $anaks->pluck('id'))
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->where('hadir', true)
            ->selectRaw('anak_id, count(*) as total')
            ->groupBy('anak_id')
            ->pluck('total', 'anak_id');

        $rows = $anaks->map(function (Anak $anak) use ($presensiByAnak, $hadirBulanan) {
            $presensi = $presensiByAnak->get($anak->id);

            return [
                $anak->name,
                $anak->kelas?->name ?? '-',
                $presensi ? Presensi::labelForStatus($presensi->status) : 'Belum dicatat',
                $presensi?->keterangan ?? '-',
                $hadirBulanan->get($anak->id, 0).' hari',
            ];
        })->all();

        return $this->downloadExcel(
            ['Nama Siswa', 'Kelas', 'Status', 'Keterangan', 'Rekap Bulan Ini'],
            $rows,
            'presensi-siswa-'.$tanggal.'.xlsx',
            'Presensi Harian'
        );
    }

    public function exportRekap(Request $request)
    {
        $sekolah_id = auth()->user()->sekolah_id;
        $anaks = $this->buildAnaksQuery($sekolah_id, $request->input('kelas_id'))->get();
        $presensiFilter = PresensiPeriodeFilter::resolve($request);
        $rekapPeriode = Presensi::where('sekolah_id', $sekolah_id)
            ->whereBetween('tanggal', [$presensiFilter['from'], $presensiFilter['to']])
            ->selectRaw('anak_id, status, count(*) as total')
            ->groupBy('anak_id', 'status')
            ->get()
            ->groupBy('anak_id');

        $rows = $anaks->map(function (Anak $anak) use ($rekapPeriode) {
            $stats = $rekapPeriode->get($anak->id, collect());
            $byStatus = $stats->pluck('total', 'status');

            return [
                $anak->name,
                $anak->kelas?->name ?? '-',
                (int) ($byStatus['hadir'] ?? 0),
                (int) ($byStatus['izin'] ?? 0),
                (int) ($byStatus['sakit'] ?? 0),
                (int) ($byStatus['alpha'] ?? 0),
            ];
        })->all();

        return $this->downloadExcel(
            ['Nama Siswa', 'Kelas', 'Hadir', 'Izin', 'Sakit', 'Alpha'],
            $rows,
            'rekap-presensi-siswa-'.$presensiFilter['from'].'-'.$presensiFilter['to'].'.xlsx',
            'Rekap Presensi'
        );
    }

    protected function resolveTanggal(Request $request): string
    {
        $tanggalInput = $request->query('tanggal', now()->format('Y-m-d'));
        try {
            return Carbon::parse($tanggalInput)->format('Y-m-d');
        } catch (\Throwable) {
            return now()->format('Y-m-d');
        }
    }

    protected function buildAnaksQuery(int $sekolah_id, mixed $kelasId)
    {
        $query = Anak::where('sekolah_id', $sekolah_id)
            ->where('status', 'approved')
            ->with(['user', 'kelas'])
            ->orderBy('name');
        auth()->user()->applyScopedKelas($query);
        if ($kelasId) {
            $query->where('kelas_id', $kelasId);
        }

        return $query;
    }
}
