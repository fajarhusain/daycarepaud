<?php

namespace App\Http\Controllers\Api\V1\OrangTua;

use App\Http\Controllers\Controller;
use App\Models\Anak;
use App\Models\Kegiatan;
use App\Support\KegiatanCalendar;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KegiatanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $sekolahId = $request->user()->sekolah_id;
        $anaks = Anak::query()
            ->where('user_id', $request->user()->id)
            ->where('sekolah_id', $sekolahId)
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
            ->where('sekolah_id', $sekolahId)
            ->with(['pengajar', 'pencapaians.anak', 'pencapaians.matrikulasi'])
            ->whereBetween('date', [$from, $to]);

        if (! $semuaSekolah) {
            if ($anakId) {
                $query->whereHas('pencapaians', fn ($q) => $q->where('anak_id', $anakId));
            } elseif ($anakIds !== []) {
                $query->whereHas('pencapaians', fn ($q) => $q->whereIn('anak_id', $anakIds));
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $kegiatans = $query->orderBy('date')->orderBy('id')->get();

        $limitAnakIds = null;
        if ($semuaSekolah) {
            $limitAnakIds = $anakIds !== [] ? $anakIds : [-1];
        }

        $events = $kegiatans->map(function (Kegiatan $k) use ($anakId, $limitAnakIds, $semuaSekolah, $anakIds) {
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
        })->values();

        return response()->json([
            'meta' => [
                'year' => $year,
                'month' => $month,
                'date_from' => $from,
                'date_to' => $to,
                'anak_id' => $anakId,
                'semua_sekolah' => $semuaSekolah,
            ],
            'anaks' => $anaks->map(fn (Anak $a) => [
                'id' => $a->id,
                'name' => $a->name,
            ]),
            'events' => $events,
        ]);
    }
}
