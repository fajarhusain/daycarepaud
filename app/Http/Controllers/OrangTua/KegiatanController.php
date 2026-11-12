<?php

namespace App\Http\Controllers\OrangTua;

use App\Support\TenantContext;

use App\Http\Controllers\Controller;
use App\Models\Anak;
use App\Models\Kegiatan;
use App\Models\Presensi;
use App\Support\KegiatanCalendar;
use Carbon\Carbon;
use Illuminate\Http\Request;

class KegiatanController extends Controller
{
    public function index(Request $request)
    {
        $sekolah_id = TenantContext::requireSekolahId();
        $anaks = Anak::where('user_id', auth()->id())
            ->where('sekolah_id', $sekolah_id)
            ->orderBy('name')
            ->get();

        $anakIds = $anaks->pluck('id')->all();
        $anakId = $request->input('anak_id');
        if ($anakId !== null && $anakId !== '') {
            $anakId = (int) $anakId;
            if (! in_array($anakId, $anakIds, true)) {
                $anakId = null;
            }
        } elseif (count($anakIds) === 1) {
            $anakId = $anakIds[0];
        }

        $semuaSekolah = $request->boolean('semua_sekolah');

        [$year, $month] = KegiatanCalendar::resolveYearMonth($request);
        [$from, $to] = KegiatanCalendar::dateRangeForCalendar($year, $month);

        $query = Kegiatan::query()
            ->where('sekolah_id', $sekolah_id)
            ->with(['pengajar', 'kelas', 'pencapaians.anak', 'pencapaians.matrikulasi'])
            ->whereBetween('date', [$from, $to]);

        $anakKelasIds = $anaks->pluck('kelas_id')->filter()->unique()->toArray();

        if (! $semuaSekolah) {
            if ($anakId) {
                $specificAnak = $anaks->firstWhere('id', $anakId);
                if ($specificAnak && $specificAnak->kelas_id) {
                    $query->where('kelas_id', $specificAnak->kelas_id);
                } else {
                    $query->whereRaw('1 = 0');
                }
            } elseif ($anakKelasIds !== []) {
                $query->whereIn('kelas_id', $anakKelasIds);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if ($request->filled('day')) {
            $day = $request->integer('day');
            $query->whereDay('date', $day);
        }

        $kegiatans = $query->orderBy('date', 'desc')->orderBy('id', 'desc')->get();

        // Fetch attendance records for the selected date range (present only)
        $presents = Presensi::whereIn('anak_id', $anakIds)
            ->whereBetween('tanggal', [$from, $to])
            ->where('hadir', true)
            ->get()
            ->groupBy(function ($p) {
                return Carbon::parse($p->tanggal)->toDateString();
            })
            ->map(fn ($rows) => $rows->pluck('anak_id')->values()->all());

        $limitAnakIds = null;
        if ($semuaSekolah) {
            $limitAnakIds = $anakIds !== [] ? $anakIds : [-1];
        }

        $calendarEvents = $kegiatans->filter(function (Kegiatan $k) use ($anakId, $anaks, $presents) {
            $date = Carbon::parse($k->date);
            $dateStr = $date->toDateString();
            $presentOnDate = $presents[$dateStr] ?? [];

            // If it's a future date (not today), show as plan
            if ($date->isFuture() && ! $date->isToday()) {
                return true;
            }

            if ($anakId) {
                // If specific child selected, only show if marked present
                return in_array($anakId, $presentOnDate);
            } else {
                // If multiple children, show if at least one child in that class was present on that day
                $childrenInClass = $anaks->where('kelas_id', $k->kelas_id);
                foreach ($childrenInClass as $child) {
                    if (in_array($child->id, $presentOnDate)) {
                        return true;
                    }
                }

                return false;
            }
        })->map(function (Kegiatan $k) use ($anakId, $limitAnakIds, $semuaSekolah, $anakIds) {
            $subset = null;
            $limitForEvent = null;

            if ($semuaSekolah) {
                $limitForEvent = $limitAnakIds;
            } elseif ($anakId) {
                $subset = $k->pencapaians->where('anak_id', $anakId)->pluck('id')->values()->all();
            } elseif ($anakIds !== []) {
                $limitForEvent = $anakIds;
            }

            return KegiatanCalendar::toReadonlyEvent($k, $subset, $limitForEvent);
        })->values()->all();

        return view('orangtua.kegiatan.index', compact(
            'calendarEvents',
            'year',
            'month',
            'anaks',
            'anakId',
            'semuaSekolah',
        ));
    }
}
