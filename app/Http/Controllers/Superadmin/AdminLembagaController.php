<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Lembaga;
use App\Models\User;
use App\Support\ActivityLogger;
use App\Support\PaginationPerPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AdminLembagaController extends Controller
{
    public function index(Request $request)
    {
        $admins = User::role('Lembaga')->with('lembaga')->latest()->paginate(PaginationPerPage::resolve($request))->withQueryString();
        $lembagas = Lembaga::orderBy('name')->get();

        return view('superadmin.admin_lembaga.index', compact('admins', 'lembagas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'lembaga_id' => ['required', 'exists:lembagas,id'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make('password123'),
            'lembaga_id' => $validated['lembaga_id'],
            'sekolah_id' => null,
        ]);

        $user->assignRole('Lembaga');

        ActivityLogger::log('Role Lembaga ditetapkan', $user, ['lembaga_id' => $user->lembaga_id]);

        return redirect()->route('superadmin.admin-lembaga.index')
            ->with('success', 'Admin Lembaga berhasil ditambahkan.');
    }

    public function update(Request $request, User $admin_lembaga)
    {
        abort_unless($admin_lembaga->hasRole('Lembaga'), 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$admin_lembaga->id],
            'lembaga_id' => ['required', 'exists:lembagas,id'],
            'password' => ['nullable', Rules\Password::defaults()],
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'lembaga_id' => $validated['lembaga_id'],
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($validated['password']);
        }

        $admin_lembaga->update($data);

        return redirect()->route('superadmin.admin-lembaga.index')
            ->with('success', 'Data Admin Lembaga berhasil diperbarui.');
    }

    public function destroy(User $admin_lembaga)
    {
        abort_unless($admin_lembaga->hasRole('Lembaga'), 404);

        $admin_lembaga->delete();

        return redirect()->route('superadmin.admin-lembaga.index')
            ->with('success', 'Admin Lembaga berhasil dihapus.');
    }
}
