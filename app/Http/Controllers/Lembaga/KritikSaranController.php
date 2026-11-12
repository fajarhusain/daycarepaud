<?php

namespace App\Http\Controllers\Lembaga;

use App\Http\Controllers\Controller;
use App\Models\KritikSaran;
use App\Support\PaginationPerPage;
use Illuminate\Http\Request;

class KritikSaranController extends Controller
{
    public function index(Request $request)
    {
        $lembaga_id = auth()->user()->lembaga_id;

        $feedbacks = KritikSaran::whereHas('sekolah', function ($query) use ($lembaga_id) {
            $query->where('lembaga_id', $lembaga_id);
        })->with(['sekolah', 'user'])->latest()->paginate(PaginationPerPage::resolve($request))->withQueryString();

        return view('lembaga.kritik_saran.index', compact('feedbacks'));
    }
}
