<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Sekolah;
use App\Models\User;
use App\Services\AnakRegistrationService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RegisteredUserController extends Controller
{
    public function __construct(
        protected AnakRegistrationService $anakRegistration
    ) {}

    public function create(): View
    {
        $sekolahs = Sekolah::active()->orderBy('name')->get();

        return view('auth.register', compact('sekolahs'));
    }

    public function store(Request $request): RedirectResponse
    {
        $sekolah = Sekolah::active()->findOrFail($request->input('sekolah_id'));

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'sekolah_id' => ['required', 'exists:sekolahs,id'],
            'anak_name' => ['required', 'string', 'max:255'],
            'anak_dob' => ['required', 'date', 'before:today'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'catatan_ortu' => ['nullable', 'string', 'max:1000'],
        ]);

        $existingUser = User::where('email', $request->email)->first();

        if ($existingUser && ! $existingUser->hasRole('Orang Tua')) {
            throw ValidationException::withMessages([
                'email' => 'Email sudah terdaftar dengan peran lain. Gunakan email lain.',
            ]);
        }

        DB::beginTransaction();

        try {
            if ($existingUser) {
                $user = $existingUser;

                $this->anakRegistration->createPendingForParent($user, [
                    'name' => $request->anak_name,
                    'dob' => $request->anak_dob,
                    'catatan_ortu' => $request->catatan_ortu,
                ], (int) $sekolah->id, $request->file('photo'));

                DB::commit();

                return redirect()->route('login')->with(
                    'status',
                    'Akun sudah terdaftar. Pendaftaran anak baru dikirim  silakan masuk untuk melanjutkan.'
                );
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'lembaga_id' => $sekolah->lembaga_id,
                'sekolah_id' => null,
            ]);

            Role::firstOrCreate(
                ['name' => 'Orang Tua', 'guard_name' => 'web']
            );
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            $user->assignRole('Orang Tua');

            event(new Registered($user));

            $this->anakRegistration->createPendingForParent($user, [
                'name' => $request->anak_name,
                'dob' => $request->anak_dob,
                'catatan_ortu' => $request->catatan_ortu,
            ], (int) $sekolah->id, $request->file('photo'));

            DB::commit();
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return back()
                ->withInput()
                ->withErrors([
                    'email' => 'Pendaftaran gagal diproses. Hubungi admin jika masalah berlanjut.',
                ]);
        }

        return redirect()->route('login')->with(
            'status',
            'Pendaftaran berhasil! Akun Anda sedang menunggu persetujuan Admin Sekolah. Anda akan bisa login setelah disetujui.'
        );
    }
}
