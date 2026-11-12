<?php

namespace App\Http\Controllers\OrangTua;
use App\Http\Controllers\Controller;
use App\Models\Anak;

class KesehatanController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        // Get all children associated with this user
        $anaks = Anak::where('user_id', $user->id)
            ->with(['kesehatans' => function ($q) {
                $q->orderBy('tanggal_pemeriksaan', 'desc');
            }])
            ->get();

        return view('orangtua.kesehatan.index', compact('anaks'));
    }
}
