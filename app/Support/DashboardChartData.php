<?php

namespace App\Support;

use App\Models\Anak;
use App\Models\Kegiatan;
use App\Models\Kelas;
use App\Models\Lembaga;
use App\Models\Pencapaian;
use App\Models\Pengajar;
use App\Models\Presensi;
use App\Models\Sekolah;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DashboardChartData
{
    /** @var list<string> */
    private const CHART_COLORS = ['#1A6B6B', '#587E6C', '#668F74', '#2D8585', '#9E9790'];

    /**
     * @return list<array{id: string, title: string, subtitle?: string, height?: int, option: array<string, mixed>}>
     */
    public static function forUser($user, Request $request, array $context = []): array
    {
        if ($user->hasRole('Superadmin')) {
            return self::forSuperadmin();
        }

        if ($user->hasRole('Lembaga')) {
            return self::forLembaga((int) $user->lembaga_id);
        }

        if ($user->hasRole('Admin Sekolah')) {
            return self::forAdminSekolah((int) $user->sekolah_id);
        }

        if ($user->hasRole('Wali Kelas')) {
            $waliKelasIds = collect($context['waliKelasList'] ?? [])->pluck('id');

            return self::forWaliKelas($waliKelasIds);
        }

        if ($user->hasRole('Pengajar') && ! $user->hasRole('Wali Kelas')) {
            $kelasIds = collect($context['pengajarKelasList'] ?? [])->pluck('id');
            $sekolahId = (int) (Pengajar::where('user_id', $user->id)->value('sekolah_id') ?? 0);

            return self::forPengajar($kelasIds, $sekolahId);
        }

        if ($user->hasRole('Orang Tua')) {
            $anakIds = collect($context['anakIds'] ?? []);
            $filter = $context['presensiFilter'] ?? PresensiPeriodeFilter::resolve($request);

            return self::forOrangTua($anakIds, $filter);
        }

        return [];
    }

    /** @return list<array{id: string, title: string, subtitle?: string, height?: int, option: array<string, mixed>}> */
    public static function forSuperadmin(): array
    {
        $months = self::lastMonths(6);
        $labels = $months->map(fn (Carbon $m) => $m->translatedFormat('M Y'))->all();

        $lembagaSeries = self::countsByCreatedMonth(Lembaga::query(), $months);
        $sekolahSeries = self::countsByCreatedMonth(Sekolah::query(), $months);
        $userSeries = self::countsByCreatedMonth(User::query(), $months);

        $schoolsPerLembaga = Sekolah::query()
            ->selectRaw('lembaga_id, count(*) as total')
            ->groupBy('lembaga_id')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $lembagaNames = Lembaga::query()
            ->whereIn('id', $schoolsPerLembaga->pluck('lembaga_id'))
            ->pluck('name', 'id');

        $barLabels = $schoolsPerLembaga->map(fn ($row) => self::truncateLabel($lembagaNames[$row->lembaga_id] ?? 'Lembaga #'.$row->lembaga_id))->all();
        $barValues = $schoolsPerLembaga->pluck('total')->map(fn ($v) => (int) $v)->all();

        return [
            self::lineChart(
                'superadmin-growth',
                'Pertumbuhan platform',
                'Lembaga, sekolah, dan pengguna baru per bulan',
                $labels,
                [
                    ['name' => 'Lembaga', 'type' => 'line', 'smooth' => true, 'data' => $lembagaSeries],
                    ['name' => 'Sekolah', 'type' => 'line', 'smooth' => true, 'data' => $sekolahSeries],
                    ['name' => 'Pengguna', 'type' => 'line', 'smooth' => true, 'data' => $userSeries],
                ],
            ),
            self::barChart(
                'superadmin-schools-lembaga',
                'Sekolah per lembaga',
                '8 lembaga dengan cabang terbanyak',
                $barLabels,
                [['name' => 'Sekolah', 'type' => 'bar', 'data' => $barValues, 'barMaxWidth' => 36]],
            ),
        ];
    }

    /** @return list<array{id: string, title: string, subtitle?: string, height?: int, option: array<string, mixed>}> */
    public static function forLembaga(int $lembagaId): array
    {
        $sekolahIds = Sekolah::where('lembaga_id', $lembagaId)->pluck('id');
        $months = self::lastMonths(6);
        $labels = $months->map(fn (Carbon $m) => $m->translatedFormat('M Y'))->all();

        $kegiatanSeries = $months->map(function (Carbon $month) use ($sekolahIds) {
            if ($sekolahIds->isEmpty()) {
                return 0;
            }

            return Kegiatan::query()
                ->whereIn('sekolah_id', $sekolahIds)
                ->whereYear('date', $month->year)
                ->whereMonth('date', $month->month)
                ->count();
        })->all();

        $siswaPerSekolah = Anak::query()
            ->selectRaw('sekolah_id, count(*) as total')
            ->whereIn('sekolah_id', $sekolahIds)
            ->where('status', 'approved')
            ->groupBy('sekolah_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $sekolahNames = Sekolah::query()
            ->whereIn('id', $siswaPerSekolah->pluck('sekolah_id'))
            ->pluck('name', 'id');

        $barLabels = $siswaPerSekolah->map(fn ($row) => self::truncateLabel($sekolahNames[$row->sekolah_id] ?? 'Sekolah #'.$row->sekolah_id))->all();
        $barValues = $siswaPerSekolah->pluck('total')->map(fn ($v) => (int) $v)->all();

        return [
            self::lineChart(
                'lembaga-kegiatan',
                'Agenda belajar gabungan',
                'Total jurnal kegiatan seluruh cabang',
                $labels,
                [
                    ['name' => 'Kegiatan', 'type' => 'line', 'smooth' => true, 'areaStyle' => ['opacity' => 0.12], 'data' => $kegiatanSeries],
                ],
            ),
            self::barChart(
                'lembaga-siswa-cabang',
                'Murid per cabang',
                'Siswa aktif per sekolah',
                $barLabels,
                [['name' => 'Murid', 'type' => 'bar', 'data' => $barValues, 'barMaxWidth' => 36]],
            ),
        ];
    }

    /** @return list<array{id: string, title: string, subtitle?: string, height?: int, option: array<string, mixed>}> */
    public static function forAdminSekolah(int $sekolahId): array
    {
        $months = self::lastMonths(6);
        $labels = $months->map(fn (Carbon $m) => $m->locale('id')->translatedFormat('M Y'))->all();

        $kegiatanSeries = $months->map(fn (Carbon $month) => (int) Kegiatan::query()
            ->where('sekolah_id', $sekolahId)
            ->whereYear('date', $month->year)
            ->whereMonth('date', $month->month)
            ->count())->all();

        $muridSeries = $months->map(function (Carbon $month) use ($sekolahId) {
            $end = $month->copy()->endOfMonth();

            return Anak::query()
                ->where('sekolah_id', $sekolahId)
                ->where('status', 'approved')
                ->where('created_at', '<=', $end)
                ->count();
        })->all();

        $kelasRows = Kelas::query()
            ->where('sekolah_id', $sekolahId)
            ->withCount(['anaks' => fn ($q) => $q->where('status', 'approved')])
            ->orderBy('name')
            ->get();

        $barLabels = $kelasRows->map(fn ($k) => self::truncateLabel($k->name))->all();
        $barValues = $kelasRows->pluck('anaks_count')->map(fn ($v) => (int) $v)->all();

        $now = Carbon::now()->locale('id');
        $siswaAktif = Anak::query()
            ->where('sekolah_id', $sekolahId)
            ->where('status', 'approved')
            ->count();

        $presensiLabels = [];
        $presensiPctSeries = [];
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();
        $through = $now->copy()->startOfDay()->min($monthEnd);

        foreach (CarbonPeriod::create($monthStart, $through) as $day) {
            if ($day->isWeekend()) {
                continue;
            }
            $presensiLabels[] = $day->format('d/m');
            $hadirHari = (int) Presensi::query()
                ->where('sekolah_id', $sekolahId)
                ->whereDate('tanggal', $day->toDateString())
                ->where('hadir', true)
                ->distinct()
                ->count('anak_id');
            $presensiPctSeries[] = $siswaAktif > 0
                ? (int) round(min(100, 100 * $hadirHari / $siswaAktif))
                : 0;
        }

        $presensiTrend = self::lineChart(
            'admin-presensi-bulan',
            'Tren kehadiran harian',
            $now->translatedFormat('F Y'),
            $presensiLabels,
            [
                [
                    'name' => 'Kehadiran',
                    'type' => 'line',
                    'smooth' => true,
                    'showSymbol' => count($presensiPctSeries) <= 12,
                    'data' => $presensiPctSeries,
                    'areaStyle' => ['opacity' => 0.14],
                ],
            ],
            168,
        );
        $presensiTrend['option']['legend'] = ['show' => false];
        $presensiTrend['option']['grid']['bottom'] = 28;
        $presensiTrend['option']['yAxis']['min'] = 0;
        $presensiTrend['option']['yAxis']['max'] = 100;
        $presensiTrend['option']['yAxis']['axisLabel']['formatter'] = '{value}%';

        $rangeStart = $months->first()->locale('id')->translatedFormat('M Y');
        $rangeEnd = $months->last()->locale('id')->translatedFormat('M Y');
        $lineSubtitle = "Murid terdaftar (kumulatif) vs jumlah entri kegiatan, {$rangeStart}–{$rangeEnd}";

        $globalTrend = self::lineChart(
            'admin-global-trend',
            'Tren murid dan kegiatan',
            $lineSubtitle,
            $labels,
            [
                ['name' => 'Murid terdaftar', 'type' => 'line', 'smooth' => true, 'data' => $muridSeries, 'yAxisIndex' => 0],
                ['name' => 'Kegiatan', 'type' => 'line', 'smooth' => true, 'data' => $kegiatanSeries, 'yAxisIndex' => 1],
            ],
        );
        $globalTrend['option']['grid']['right'] = '12%';
        $globalTrend['option']['yAxis'] = [
            [
                'type' => 'value',
                'name' => 'Murid',
                'minInterval' => 1,
                'splitLine' => ['lineStyle' => ['color' => '#EDE8DF', 'type' => 'dashed']],
                'axisLabel' => ['color' => '#586B60'],
            ],
            [
                'type' => 'value',
                'name' => 'Kegiatan',
                'minInterval' => 1,
                'splitLine' => ['show' => false],
                'axisLabel' => ['color' => '#586B60'],
            ],
        ];

        return [
            $globalTrend,
            self::barChart(
                'admin-siswa-kelas',
                'Murid per kelas',
                'Semua kelas aktif',
                $barLabels,
                [['name' => 'Murid', 'type' => 'bar', 'data' => $barValues, 'barMaxWidth' => 32]],
            ),
            $presensiTrend,
        ];
    }

    /** @return list<array{id: string, title: string, subtitle?: string, height?: int, option: array<string, mixed>}> */
    public static function forWaliKelas(Collection $kelasIds): array
    {
        if ($kelasIds->isEmpty()) {
            return [];
        }

        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();
        $days = CarbonPeriod::create($start, $end);
        $labels = [];
        $hadirSeries = [];

        foreach ($days as $day) {
            if ($day->isWeekend()) {
                continue;
            }
            $labels[] = $day->format('d/m');
            $hadirSeries[] = Presensi::query()
                ->whereIn('kelas_id', $kelasIds)
                ->whereDate('tanggal', $day->toDateString())
                ->where('hadir', true)
                ->count();
        }

        $kelasRows = Kelas::query()
            ->whereIn('id', $kelasIds)
            ->withCount(['anaks' => fn ($q) => $q->where('status', 'approved')])
            ->orderBy('name')
            ->get();

        $barLabels = $kelasRows->map(fn ($k) => self::truncateLabel($k->name))->all();
        $barValues = $kelasRows->pluck('anaks_count')->map(fn ($v) => (int) $v)->all();

        $evaluasiSeries = [];
        $monthLabels = self::lastMonths(4)->map(fn (Carbon $m) => $m->translatedFormat('M Y'))->all();
        foreach (self::lastMonths(4) as $month) {
            $evaluasiSeries[] = Pencapaian::query()
                ->whereHas('anak', fn ($q) => $q->whereIn('kelas_id', $kelasIds))
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        }

        return [
            self::lineChart(
                'wali-presensi-harian',
                'Kehadiran harian kelas wali',
                'Jumlah entri hadir per hari efektif bulan ini',
                $labels,
                [
                    ['name' => 'Hadir', 'type' => 'line', 'smooth' => true, 'areaStyle' => ['opacity' => 0.15], 'data' => $hadirSeries],
                ],
                260,
            ),
            self::barChart(
                'wali-siswa-kelas',
                'Murid per kelas wali',
                'Kapasitas kelas yang Anda pegang',
                $barLabels,
                [['name' => 'Murid', 'type' => 'bar', 'data' => $barValues, 'barMaxWidth' => 32]],
            ),
            self::lineChart(
                'wali-evaluasi',
                'Evaluasi (monev)',
                'Entri pencapaian 4 bulan terakhir',
                $monthLabels,
                [
                    ['name' => 'Evaluasi', 'type' => 'line', 'smooth' => true, 'data' => $evaluasiSeries],
                ],
                240,
            ),
        ];
    }

    /** @return list<array{id: string, title: string, subtitle?: string, height?: int, option: array<string, mixed>}> */
    public static function forPengajar(Collection $kelasIds, int $sekolahId): array
    {
        $months = self::lastMonths(6);
        $labels = $months->map(fn (Carbon $m) => $m->translatedFormat('M Y'))->all();

        if ($kelasIds->isEmpty()) {
            $kegiatanSeries = array_fill(0, count($labels), 0);
            $evalSeries = array_fill(0, count($labels), 0);
            $barLabels = [];
            $barValues = [];
        } else {
            $kegiatanSeries = $months->map(fn (Carbon $month) => Kegiatan::query()
                ->whereIn('kelas_id', $kelasIds)
                ->whereYear('date', $month->year)
                ->whereMonth('date', $month->month)
                ->count())->all();

            $evalSeries = $months->map(fn (Carbon $month) => Pencapaian::query()
                ->whereHas('anak', fn ($q) => $q->whereIn('kelas_id', $kelasIds))
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count())->all();

            $kelasRows = Kelas::query()
                ->whereIn('id', $kelasIds)
                ->withCount(['anaks' => fn ($q) => $q->where('status', 'approved')])
                ->orderBy('name')
                ->get();

            $barLabels = $kelasRows->map(fn ($k) => self::truncateLabel($k->name))->all();
            $barValues = $kelasRows->pluck('anaks_count')->map(fn ($v) => (int) $v)->all();
        }

        return [
            self::lineChart(
                'pengajar-jurnal',
                'Jurnal & evaluasi',
                'Kegiatan belajar vs entri pencapaian',
                $labels,
                [
                    ['name' => 'Jurnal', 'type' => 'line', 'smooth' => true, 'data' => $kegiatanSeries],
                    ['name' => 'Evaluasi', 'type' => 'line', 'smooth' => true, 'data' => $evalSeries],
                ],
            ),
            self::barChart(
                'pengajar-murid-kelas',
                'Murid di kelas ajar',
                $sekolahId > 0 ? 'Per kelas yang Anda ampu' : '',
                $barLabels,
                [['name' => 'Murid', 'type' => 'bar', 'data' => $barValues, 'barMaxWidth' => 32]],
            ),
        ];
    }

    /**
     * @param  array{from: string, to: string, label?: string}  $filter
     * @return list<array{id: string, title: string, subtitle?: string, height?: int, option: array<string, mixed>}> */
    public static function forOrangTua(Collection $anakIds, array $filter): array
    {
        if ($anakIds->isEmpty()) {
            return [];
        }

        $from = Carbon::parse($filter['from'])->startOfDay();
        $to = Carbon::parse($filter['to'])->endOfDay();

        $weeks = collect();
        $cursor = $from->copy()->startOfWeek(Carbon::MONDAY);
        while ($cursor <= $to) {
            $weekEnd = $cursor->copy()->endOfWeek(Carbon::SUNDAY);
            if ($weekEnd < $from) {
                $cursor->addWeek();

                continue;
            }
            $weeks->push([
                'start' => $cursor->copy()->max($from),
                'end' => $weekEnd->copy()->min($to),
                'label' => $cursor->format('d/m').'–'.$weekEnd->format('d/m'),
            ]);
            $cursor->addWeek();
        }

        if ($weeks->isEmpty()) {
            $weeks->push([
                'start' => $from,
                'end' => $to,
                'label' => $from->format('d/m').'–'.$to->format('d/m'),
            ]);
        }

        $labels = $weeks->pluck('label')->all();
        $hadirSeries = [];
        $tidakSeries = [];

        foreach ($weeks as $week) {
            $hadirSeries[] = Presensi::query()
                ->whereIn('anak_id', $anakIds)
                ->whereBetween('tanggal', [$week['start']->toDateString(), $week['end']->toDateString()])
                ->where('hadir', true)
                ->count();
            $tidakSeries[] = Presensi::query()
                ->whereIn('anak_id', $anakIds)
                ->whereBetween('tanggal', [$week['start']->toDateString(), $week['end']->toDateString()])
                ->where('hadir', false)
                ->count();
        }

        $kegiatanSeries = [];
        $monthLabels = self::lastMonths(4)->map(fn (Carbon $m) => $m->translatedFormat('M Y'))->all();
        foreach (self::lastMonths(4) as $month) {
            $kegiatanSeries[] = Kegiatan::query()
                ->whereHas('pencapaians', fn ($q) => $q->whereIn('anak_id', $anakIds))
                ->whereYear('date', $month->year)
                ->whereMonth('date', $month->month)
                ->count();
        }

        return [
            self::lineChart(
                'ortu-presensi',
                'Kehadiran anak',
                $filter['label'] ?? 'Periode presensi aktif',
                $labels,
                [
                    ['name' => 'Hadir', 'type' => 'line', 'smooth' => true, 'data' => $hadirSeries],
                    ['name' => 'Izin / sakit', 'type' => 'line', 'smooth' => true, 'data' => $tidakSeries],
                ],
            ),
            self::lineChart(
                'ortu-kegiatan',
                'Agenda dengan laporan anak',
                'Kegiatan yang menyertakan evaluasi anak Anda',
                $monthLabels,
                [
                    ['name' => 'Kegiatan', 'type' => 'line', 'smooth' => true, 'areaStyle' => ['opacity' => 0.1], 'data' => $kegiatanSeries],
                ],
                240,
            ),
        ];
    }

    /** @return Collection<int, Carbon> */
    private static function lastMonths(int $count): Collection
    {
        $start = Carbon::now()->startOfMonth()->subMonths($count - 1);

        return collect(range(0, $count - 1))->map(fn (int $i) => $start->copy()->addMonths($i));
    }

    /** @param  Builder<Model>  $query */
    private static function countsByCreatedMonth($query, Collection $months): array
    {
        return $months->map(function (Carbon $month) use ($query) {
            return (clone $query)
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        })->all();
    }

    private static function truncateLabel(string $label, int $max = 14): string
    {
        $label = trim($label);
        if (mb_strlen($label) <= $max) {
            return $label;
        }

        return mb_substr($label, 0, $max - 1).'…';
    }

    /**
     * @param  list<array<string, mixed>>  $series
     * @return array{id: string, title: string, subtitle?: string, height?: int, option: array<string, mixed>}
     */
    private static function lineChart(string $id, string $title, string $subtitle, array $labels, array $series, int $height = 300): array
    {
        return [
            'id' => $id,
            'title' => $title,
            'subtitle' => $subtitle,
            'height' => $height,
            'option' => [
                'color' => self::CHART_COLORS,
                'textStyle' => ['fontFamily' => 'Plus Jakarta Sans, system-ui, sans-serif'],
                'tooltip' => ['trigger' => 'axis'],
                'legend' => ['bottom' => 0, 'textStyle' => ['color' => '#586B60']],
                'grid' => ['left' => '2%', 'right' => '3%', 'top' => 16, 'bottom' => 48, 'containLabel' => true],
                'xAxis' => [
                    'type' => 'category',
                    'boundaryGap' => false,
                    'data' => $labels,
                    'axisLine' => ['lineStyle' => ['color' => '#D5E2D1']],
                    'axisLabel' => ['color' => '#586B60', 'fontSize' => 11],
                ],
                'yAxis' => [
                    'type' => 'value',
                    'minInterval' => 1,
                    'splitLine' => ['lineStyle' => ['color' => '#EDE8DF', 'type' => 'dashed']],
                    'axisLabel' => ['color' => '#586B60'],
                ],
                'series' => $series,
            ],
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $series
     * @return array{id: string, title: string, subtitle?: string, height?: int, option: array<string, mixed>}
     */
    private static function barChart(string $id, string $title, string $subtitle, array $labels, array $series, int $height = 280): array
    {
        return [
            'id' => $id,
            'title' => $title,
            'subtitle' => $subtitle,
            'height' => $height,
            'option' => [
                'color' => self::CHART_COLORS,
                'textStyle' => ['fontFamily' => 'Plus Jakarta Sans, system-ui, sans-serif'],
                'tooltip' => ['trigger' => 'axis'],
                'grid' => ['left' => '2%', 'right' => '3%', 'top' => 16, 'bottom' => 8, 'containLabel' => true],
                'xAxis' => [
                    'type' => 'category',
                    'data' => $labels,
                    'axisLabel' => ['color' => '#586B60', 'fontSize' => 10, 'interval' => 0, 'rotate' => count($labels) > 6 ? 28 : 0],
                    'axisLine' => ['lineStyle' => ['color' => '#D5E2D1']],
                ],
                'yAxis' => [
                    'type' => 'value',
                    'minInterval' => 1,
                    'splitLine' => ['lineStyle' => ['color' => '#EDE8DF', 'type' => 'dashed']],
                    'axisLabel' => ['color' => '#586B60'],
                ],
                'series' => $series,
            ],
        ];
    }

    /**
     * @param  list<array{value: int, name: string}>  $data
     * @return array{id: string, title: string, subtitle?: string, height?: int, option: array<string, mixed>}
     */
    private static function pieChart(string $id, string $title, string $subtitle, array $data, int $height = 260): array
    {
        $compact = $height <= 180;

        return [
            'id' => $id,
            'title' => $title,
            'subtitle' => $subtitle,
            'height' => $height,
            'option' => [
                'color' => ['#1A6B6B', '#E8A838'],
                'textStyle' => ['fontFamily' => 'Plus Jakarta Sans, system-ui, sans-serif'],
                'tooltip' => ['trigger' => 'item'],
                'legend' => [
                    'bottom' => 0,
                    'itemWidth' => $compact ? 10 : 14,
                    'itemHeight' => $compact ? 10 : 14,
                    'textStyle' => ['color' => '#586B60', 'fontSize' => $compact ? 10 : 12],
                ],
                'series' => [
                    [
                        'name' => $title,
                        'type' => 'pie',
                        'radius' => $compact ? ['36%', '56%'] : ['42%', '68%'],
                        'center' => $compact ? ['50%', '44%'] : ['50%', '50%'],
                        'avoidLabelOverlap' => true,
                        'itemStyle' => ['borderRadius' => 6, 'borderColor' => '#fff', 'borderWidth' => 2],
                        'label' => ['show' => ! $compact, 'color' => '#2D3F35', 'fontSize' => 12],
                        'data' => $data,
                    ],
                ],
            ],
        ];
    }
}
