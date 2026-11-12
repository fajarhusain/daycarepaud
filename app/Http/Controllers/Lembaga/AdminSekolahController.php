<?php

namespace App\Http\Controllers\Lembaga;

use App\Http\Controllers\Controller;
use App\Models\Sekolah;
use App\Models\User;
use App\Support\ActivityLogger;
use App\Support\PaginationPerPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AdminSekolahController extends Controller
{
    public function index(Request $request)
    {
        $lembaga_id = auth()->user()->lembaga_id;
        $admins = User::role('Admin Sekolah')->where('lembaga_id', $lembaga_id)->with('sekolah')->latest()->paginate(PaginationPerPage::resolve($request))->withQueryString();
        $sekolahs = Sekolah::where('lembaga_id', $lembaga_id)->get();

        return view('lembaga.admin_sekolah.index', compact('admins', 'sekolahs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'sekolah_id' => ['required', 'exists:sekolahs,id'],
        ]);

        // Validate that the chosen sekolah belongs to the current lembaga
        $sekolah = Sekolah::findOrFail($request->sekolah_id);
        abort_if($sekolah->lembaga_id !== auth()->user()->lembaga_id, 403);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make('password123'),
            'lembaga_id' => auth()->user()->lembaga_id,
            'sekolah_id' => $request->sekolah_id,
        ]);

        $user->assignRole('Admin Sekolah');

        ActivityLogger::log('Role Admin Sekolah ditetapkan', $user, ['sekolah_id' => $user->sekolah_id]);

        return redirect()->route('lembaga.admin-sekolah.index')->with('success', 'Admin Sekolah berhasil ditambahkan.');
    }

    public function update(Request $request, User $admin_sekolah)
    {
        abort_if($admin_sekolah->lembaga_id !== auth()->user()->lembaga_id, 403);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$admin_sekolah->id],
            'sekolah_id' => ['required', 'exists:sekolahs,id'],
            'password' => ['nullable', Rules\Password::defaults()],
        ]);

        $sekolah = Sekolah::findOrFail($request->sekolah_id);
        abort_if($sekolah->lembaga_id !== auth()->user()->lembaga_id, 403);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'sekolah_id' => $request->sekolah_id,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $admin_sekolah->update($data);

        return redirect()->route('lembaga.admin-sekolah.index')->with('success', 'Data Admin Sekolah berhasil diperbarui.');
    }

    public function destroy(User $admin_sekolah)
    {
        abort_if($admin_sekolah->lembaga_id !== auth()->user()->lembaga_id, 403);
        $admin_sekolah->delete();

        return redirect()->route('lembaga.admin-sekolah.index')->with('success', 'Admin Sekolah berhasil dihapus.');
    }
}
