<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ActivityLogger;
use App\Support\PaginationPerPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class SuperadminUserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::role('Superadmin')->latest()->paginate(PaginationPerPage::resolve($request))->withQueryString();

        return view('superadmin.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make('password123'),
            'lembaga_id' => null,
            'sekolah_id' => null,
        ]);

        $user->assignRole('Superadmin');

        ActivityLogger::log('Role Superadmin ditetapkan', $user);

        return redirect()->route('superadmin.users.index')
            ->with('success', 'Superadmin berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        abort_unless($user->hasRole('Superadmin'), 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', Rules\Password::defaults()],
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return redirect()->route('superadmin.users.index')
            ->with('success', 'Data Superadmin berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user)
    {
        abort_unless($user->hasRole('Superadmin'), 404);

        if ($request->user()->id === $user->id) {
            return back()->withErrors(['user' => 'Tidak dapat menghapus akun sendiri.']);
        }

        $user->delete();

        return redirect()->route('superadmin.users.index')
            ->with('success', 'Superadmin berhasil dihapus.');
    }
}
