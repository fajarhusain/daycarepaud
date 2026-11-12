<?php

namespace App\Http\Controllers;

use App\Models\Anak;
use App\Models\Cashflow;
use App\Models\Kegiatan;
use App\Models\KegiatanRutin;
use App\Models\Kelas;
use App\Models\Kesehatan;
use App\Models\KritikSaran;
use App\Models\MenuMakanan;
use App\Models\MenuMakananVote;
use App\Models\Pencapaian;
use App\Models\Pengajar;
use App\Models\Pengumuman;
use App\Models\PengumumanRead;
use App\Models\Presensi;
use App\Models\Sarana;
use App\Models\Sekolah;
use App\Models\User;
use App\Support\AdminSekolahDashboard;
use App\Support\DashboardChartData;
use App\Support\HariLiburIndonesia;
use App\Support\PresensiPeriodeFilter;
use App\Support\TenantContext;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user->hasRole('Superadmin')) {
            return redirect()->route('superadmin.dashboard');
        }

        if ($user->hasRole('Admin Sekolah') && $request->route()?->getName() === 'dashboard') {
            return redirect()->route('admin.dashboard');
        }

        if ($user->canAccessAdminPanel() && ! $user->hasRole(['Admin Sekolah', 'Lembaga'])) {
            $route = $user->firstAccessibleAdminRoute();
            if ($route) {
                return redirect()->route($route);
            }
        }

        $data = [];

        if ($user->hasRole('Lembaga')) {
            $data['totalSekolah'] = Sekolah::where('lembaga_id', $user->lembaga_id)->count();
            $sekolahIds = Sekolah::where('lembaga_id', $user->lembaga_id)->pluck('id');
            $data['totalAdmin'] = User::role('Admin Sekolah')->whereIn('sekolah_id', $sekolahIds)->count();
            $data['totalKritikSaran'] = KritikSaran::whereIn('sekolah_id', $sekolahIds)->count();
            $data['recentFeedback'] = KritikSaran::whereIn('sekolah_id', $sekolahIds)->latest()->limit(5)->get();
            $data['totalMurid'] = Anak::whereIn('sekolah_id', $sekolahIds)->where('status', 'approved')->count();
            $data['totalGuru'] = Pengajar::whereIn('sekolah_id', $sekolahIds)->count();
            $now = Carbon::now();
            $data['kegiatanBulanIni'] = Kegiatan::whereIn('sekolah_id', $sekolahIds)
                ->whereYear('date', $now->year)
                ->whereMonth('date', $now->month)
                ->count();
        }

        if ($user->hasRole('Admin Sekolah')) {
            $sekolahId = $user->sekolah_id;
            $data['totalAnak'] = Anak::where('sekolah_id', $sekolahId)->where('status', 'approved')->count();
            $data['totalPengajar'] = Pengajar::where('sekolah_id', $sekolahId)->count();
            $data['totalSarana'] = Sarana::where('sekolah_id', $sekolahId)->count();

            $uangMasuk = Cashflow::where('sekolah_id', $sekolahId)->where('type', 'in')->sum('amount');
            $uangKeluar = Cashflow::where('sekolah_id', $sekolahId)->where('type', 'out')->sum('amount');
            $data['saldoKas'] = $uangMasuk - $uangKeluar;

            $data['kegiatanHariIni'] = Kegiatan::where('sekolah_id', $sekolahId)->whereDate('date', Carbon::today())->count();
            $now = Carbon::now();
            $data['kegiatanBulanIni'] = Kegiatan::where('sekolah_id', $sekolahId)
                ->whereYear('date', $now->year)
                ->whereMonth('date', $now->month)
                ->count();
            $data['totalKelas'] = Kelas::where('sekolah_id', $sekolahId)->count();
            $data['adminDashboardUx'] = AdminSekolahDashboard::compose($sekolahId);
        }

        if ($user->hasRole('Wali Kelas')) {
            $pengajar = Pengajar::where('user_id', $user->id)->first();
            if ($pengajar) {
                $waliKelasIds = Kelas::where('wali_kelas_id', $pengajar->id)->pluck('id');
                $data['waliKelasList'] = Kelas::whereIn('id', $waliKelasIds)
                    ->withCount(['anaks' => fn ($q) => $q->where('status', 'approved')])
                    ->orderBy('name')
                    ->get();
                $data['kelasWaliCount'] = $waliKelasIds->count();
                $data['kelasAnakCount'] = Anak::whereIn('kelas_id', $waliKelasIds)->where('status', 'approved')->count();
                $data['presensiHariIniCount'] = Presensi::whereIn('kelas_id', $waliKelasIds)
                    ->whereDate('tanggal', Carbon::today())
                    ->count();
                $data['kesehatanHariIniCount'] = Kesehatan::whereHas('anak', fn ($q) => $q->whereIn('kelas_id', $waliKelasIds))
                    ->whereDate('tanggal_pemeriksaan', Carbon::today())
                    ->count();
                $data['monevHariIniCount'] = Pencapaian::whereHas('anak', fn ($q) => $q->whereIn('kelas_id', $waliKelasIds))
                    ->whereDate('created_at', Carbon::today())
                    ->count();
            }
        }

        if ($user->hasRole('Pengajar')) {
            $pengajar = Pengajar::where('user_id', $user->id)->first();
            if ($pengajar) {
                $sekolahId = $pengajar->sekolah_id;
                $kelasIds = $pengajar->kelas->pluck('id')->toArray();
                $data['kelasAjarCount'] = count($kelasIds);
                $data['pengajarKelasList'] = $pengajar->kelas()
                    ->withCount(['anaks' => fn ($q) => $q->where('status', 'approved')])
                    ->orderBy('name')
                    ->get();

                if (! empty($kelasIds)) {
                    $data['totalAnakSekolah'] = Anak::whereIn('kelas_id', $kelasIds)->where('status', 'approved')->count();
                    $data['dashboardAnakLabel'] = 'Siswa di kelasku';

                    $data['kegiatanSayaHariIni'] = Kegiatan::whereIn('kelas_id', $kelasIds)->whereDate('date', Carbon::today())->count();
                    $data['totalKegiatanSaya'] = Kegiatan::whereIn('kelas_id', $kelasIds)->count();
                    $data['totalEvaluasiSaya'] = Pencapaian::whereHas('anak', fn ($q) => $q->whereIn('kelas_id', $kelasIds))->count();
                } else {
                    $data['totalAnakSekolah'] = Anak::where('sekolah_id', $sekolahId)->where('status', 'approved')->count();
                    $data['dashboardAnakLabel'] = 'Siswa di sekolah';
                    $data['kegiatanSayaHariIni'] = 0;
                    $data['totalKegiatanSaya'] = 0;
                    $data['totalEvaluasiSaya'] = 0;
                }
            }
        }

        if ($user->hasRole('Orang Tua')) {
            $sekolahId = TenantContext::sekolahId();
            $data['ortuSekolahs'] = Sekolah::query()
                ->whereIn('id', $user->approvedSekolahIds())
                ->orderBy('name')
                ->get(['id', 'name']);
            $data['anaks'] = Anak::withoutSekolahScope()
                ->where('user_id', $user->id)
                ->when($sekolahId, fn ($q) => $q->where('sekolah_id', $sekolahId))
                ->with('kelas')
                ->orderBy('name')
                ->get();
            $approvedAnaks = $data['anaks']->where('status', 'approved')->values();
            $data['anakIds'] = $approvedAnaks->pluck('id');

            $data['menuHariIni'] = null;
            $data['myVote'] = null;
            if ($sekolahId) {
                $data['menuHariIni'] = MenuMakanan::where('sekolah_id', $sekolahId)
                    ->whereDate('date', Carbon::today())
                    ->withCount(['votes as likes_count' => fn ($q) => $q->where('vote_type', 'like')])
                    ->withCount(['votes as dislikes_count' => fn ($q) => $q->where('vote_type', 'dislike')])
                    ->first();

                if ($data['menuHariIni']) {
                    $data['myVote'] = MenuMakananVote::where('menu_makanan_id', $data['menuHariIni']->id)
                        ->where('user_id', $user->id)
                        ->first();
                }
            }

            // Agenda belajar + kegiatan rutin hari ini (tanpa pencapaian)
            $feeds = collect();

            if ($sekolahId && $data['anakIds']->isNotEmpty()) {
                $childKelasIds = $approvedAnaks->pluck('kelas_id')->filter()->unique();
                $kegiatansQuery = Kegiatan::query()
                    ->where('sekolah_id', $sekolahId)
                    ->whereIn('kelas_id', $childKelasIds)
                    ->whereDate('date', Carbon::today())
                    ->with(['pengajar', 'pencapaians' => fn ($q) => $q->whereIn('anak_id', $data['anakIds'])->with('matrikulasi')])
                    ->latest('date')
                    ->latest('id');

                $kegiatans = $kegiatansQuery->get();

                // Get attendance for these children today
                $presentAnakIdsToday = Presensi::whereIn('anak_id', $data['anakIds'])
                    ->whereDate('tanggal', Carbon::today())
                    ->where('hadir', true)
                    ->pluck('anak_id')
                    ->toArray();

                foreach ($kegiatans as $keg) {
                    // Check if at least one child of this parent in this class is present today
                    $childrenInClass = $approvedAnaks->where('kelas_id', $keg->kelas_id);
                    $anyPresent = false;
                    foreach ($childrenInClass as $child) {
                        if (in_array($child->id, $presentAnakIdsToday)) {
                            $anyPresent = true;
                            break;
                        }
                    }

                    if ($anyPresent) {
                        $feeds->push([
                            'type' => 'kegiatan',
                            'time' => $keg->created_at,
                            'data' => $keg,
                        ]);
                    }
                }

                $kegiatansRutin = KegiatanRutin::query()
                    ->whereIn('anak_id', $data['anakIds'])
                    ->whereDate('tanggal', Carbon::today())
                    ->with('anak')
                    ->latest('id')
                    ->get();

                foreach ($kegiatansRutin as $kr) {
                    $feeds->push([
                        'type' => 'kegiatan_rutin',
                        'time' => $kr->created_at,
                        'data' => $kr,
                    ]);
                }
            }

            $data['dashboardFeed'] = $feeds->sort(function ($a, $b) {
                $weights = ['kegiatan_rutin' => 0, 'kegiatan' => 1];
                $wa = $weights[$a['type']] ?? 99;
                $wb = $weights[$b['type']] ?? 99;

                if ($wa !== $wb) {
                    return $wa <=> $wb;
                }

                // Secondary sort: time (latest first)
                return $b['time'] <=> $a['time'];
            });

            $data['presensiFilter'] = PresensiPeriodeFilter::resolve($request);
            if ($data['anakIds']->isEmpty()) {
                $data['presensiSummaryPerAnak'] = collect();
            } else {
                $from = Carbon::parse($data['presensiFilter']['from']);
                $to = Carbon::parse($data['presensiFilter']['to']);

                // Hitung hari efektif: tidak termasuk Sabtu, Minggu, dan hari libur nasional Indonesia
                $effectiveDays = HariLiburIndonesia::hitungHariEfektif($from, $to);
                $data['effectiveDaysCount'] = $effectiveDays;

                $data['presensiSummaryPerAnak'] = $data['anaks']->mapWithKeys(function ($anak) use ($from, $to, $effectiveDays) {
                    if (($anak->status ?? '') !== 'approved') {
                        return [$anak->id => [
                            'hadir' => 0,
                            'tidak_hadir' => 0,
                            'efektif' => $effectiveDays,
                            'pending' => true,
                        ]];
                    }

                    $hadir = Presensi::where('anak_id', $anak->id)
                        ->where('hadir', true)
                        ->whereBetween('tanggal', [$from->toDateString(), $to->toDateString()])
                        ->count();

                    $tidakHadir = Presensi::where('anak_id', $anak->id)
                        ->where('hadir', false)
                        ->whereBetween('tanggal', [$from->toDateString(), $to->toDateString()])
                        ->count();

                    return [$anak->id => [
                        'hadir' => $hadir,
                        'tidak_hadir' => $tidakHadir,
                        'efektif' => $effectiveDays,
                    ]];
                });
            }

            $kelasIds = $data['anaks']
                ->where('status', 'approved')
                ->pluck('kelas_id')
                ->filter()
                ->unique()
                ->values()
                ->all();

            $data['pengumumans'] = Pengumuman::where('sekolah_id', $sekolahId)
                ->where('is_active', true)
                ->forKelasIds($kelasIds)
                ->with('kelas')
                ->orderByDesc('mulai_tayang')
                ->orderByDesc('id')
                ->limit(20)
                ->get();

            $data['readPengumumanIds'] = PengumumanRead::query()
                ->where('user_id', $user->id)
                ->whereIn('pengumuman_id', $data['pengumumans']->pluck('id'))
                ->pluck('pengumuman_id')
                ->all();
        }

        $data['dashboardCharts'] = DashboardChartData::forUser($user, $request, $data);

        if ($user->hasRole('Admin Sekolah')) {
            $data['dashboardCharts'] = AdminSekolahDashboard::enrichCharts(
                $data['dashboardCharts'],
                (int) $user->sekolah_id
            );
        }

        return view('dashboard', $data);
    }
}
