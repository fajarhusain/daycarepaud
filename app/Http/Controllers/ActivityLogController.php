<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\DownloadsExcel;
use App\Services\ActivityLogScopeService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    use DownloadsExcel;

    public function index(Request $request, ActivityLogScopeService $scope): View
    {
        $activities = $scope->paginateForUser($request->user(), $request);

        return view('activity-log.index', compact('activities'));
    }

    public function export(Request $request, ActivityLogScopeService $scope)
    {
        $activities = $scope->getForUser($request->user(), $request);

        $rows = $activities->map(fn ($a) => [
            $a->created_at?->format('Y-m-d H:i:s') ?? '-',
            $a->causer?->name ?? '-',
            $a->event ?? '-',
            $scope->subjectLabel($a),
            $scope->changesSummary($a),
            $a->properties['route'] ?? '-',
        ])->all();

        return $this->downloadExcel(
            ['Waktu', 'Pengguna', 'Aksi', 'Objek', 'Perubahan', 'Route'],
            $rows,
            'log-aktivitas-'.now()->format('Y-m-d').'.xlsx',
            'Log Aktivitas'
        );
    }
}
