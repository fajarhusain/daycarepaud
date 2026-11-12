<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\OrangTuaChat;
use App\Services\OrangTuaChatService;
use App\Support\PaginationPerPage;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrangTuaChatController extends Controller
{
    public function __construct(
        protected OrangTuaChatService $chatService
    ) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        $sekolahId = $user->sekolah_id;

        $query = OrangTuaChat::query()
            ->where('sekolah_id', $sekolahId)
            ->with(['user.anaks.kelas'])
            ->withCount('messages')
            ->with(['messages' => fn ($q) => $q->latest()->limit(1)])
            ->orderByDesc('updated_at');

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                })->orWhereHas('user.anaks', fn ($aq) => $aq->where('name', 'like', '%'.$search.'%'));
            });
        }

        if ($request->filled('kelas_id')) {
            $query->whereHas('user.anaks', fn ($aq) => $aq->where('kelas_id', (int) $request->input('kelas_id')));
        }

        $status = (string) $request->input('status', '');
        if ($status === 'aktif') {
            $query->whereNull('cleared_at');
        } elseif ($status === 'dihapus') {
            $query->whereNotNull('cleared_at');
        }

        $chats = $query->paginate(PaginationPerPage::resolve($request))->withQueryString();

        $kelas = $user->applyScopedKelas(
            Kelas::where('sekolah_id', $sekolahId)->orderBy('name'),
            'id'
        )->get();

        return view('admin.orangtua_chat.index', compact('chats', 'kelas'));
    }

    public function show(OrangTuaChat $orangtua_chat): View
    {
        abort_if((int) $orangtua_chat->sekolah_id !== (int) auth()->user()->sekolah_id, 404);

        $orangtua_chat->load(['user.anaks.kelas']);
        $messages = $this->chatService->messagesForAdmin($orangtua_chat);

        return view('admin.orangtua_chat.show', [
            'chat' => $orangtua_chat,
            'messages' => $messages,
        ]);
    }
}
