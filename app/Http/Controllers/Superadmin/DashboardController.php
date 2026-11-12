<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\AiSetting;
use App\Models\Lembaga;
use App\Models\Sekolah;
use App\Models\User;
use App\Support\DashboardChartData;

class DashboardController extends Controller
{
    public function index()
    {
        $data = [
            'totalLembaga' => Lembaga::count(),
            'totalSekolah' => Sekolah::count(),
            'totalUsers' => User::count(),
            'totalAiConfigured' => AiSetting::whereNotNull('ai_api_key')->count(),
            'recentLembagas' => Lembaga::latest()->limit(5)->get(),
        ];

        $data['dashboardCharts'] = DashboardChartData::forSuperadmin();

        return view('superadmin.dashboard', $data);
    }
}
