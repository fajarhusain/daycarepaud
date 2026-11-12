<?php

namespace App\Http\Controllers\OrangTua;
use App\Http\Controllers\Controller;
use App\Models\MenuMakananVote;
use Illuminate\Http\Request;

class MenuMakananVoteController extends Controller
{
    public function vote(Request $request)
    {
        $request->validate([
            'menu_makanan_id' => 'required|exists:menu_makanans,id',
            'vote_type' => 'required|in:like,dislike',
        ]);

        $user = auth()->user();

        MenuMakananVote::updateOrCreate(
            [
                'user_id' => $user->id,
                'menu_makanan_id' => $request->menu_makanan_id,
            ],
            [
                'vote_type' => $request->vote_type,
            ]
        );

        return back()->with('success', 'Terima kasih atas penilaiannya!');
    }
}
