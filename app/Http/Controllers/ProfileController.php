<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Traits\CanUploadImage;
use App\Models\Anak;
use App\Models\Lembaga;
use App\Models\Pengajar;
use App\Models\Sekolah;
use App\Support\PendidikanTerakhir;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    use CanUploadImage;

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $user->loadMissing(['pengajar:id,user_id,photo,name', 'anaks:id,user_id,photo,name']);
        $sekolah = null;
        $lembaga = null;
        $pengajar = null;
        $anaks = null;

        if ($user->hasRole('Lembaga')) {
            $lembaga = Lembaga::find($user->lembaga_id);
            $activeSekolahId = session('active_sekolah_id');
            if ($activeSekolahId) {
                $sekolah = Sekolah::where('id', $activeSekolahId)
                    ->where('lembaga_id', $user->lembaga_id)
                    ->first();
            }
        }
        if ($user->hasRole('Admin Sekolah')) {
            $sekolah = Sekolah::find($user->sekolah_id);
        }
        if ($user->hasRole('Pengajar') || $user->hasRole('Wali Kelas')) {
            $pengajar = Pengajar::where('user_id', $user->id)->first();
        }
        if ($user->hasRole('Orang Tua')) {
            $anaks = Anak::query()
                ->where('user_id', $user->id)
                ->with(['sekolah:id,name', 'kelas:id,name'])
                ->orderBy('name')
                ->get();
        }

        return view('profile.edit', compact('user', 'sekolah', 'lembaga', 'pengajar', 'anaks'));
    }

    public function editSekolah(Request $request): View
    {
        $user = $request->user();
        $user->loadMissing(['pengajar:id,user_id,photo,name', 'anaks:id,user_id,photo,name']);

        $sekolah = null;
        $lembaga = null;
        $pengajar = null;
        $anaks = null;

        if ($user->hasRole('Admin Sekolah')) {
            $sekolah = Sekolah::find($user->sekolah_id);
        } elseif ($user->hasRole('Lembaga')) {
            $lembaga = Lembaga::find($user->lembaga_id);
            $activeSekolahId = session('active_sekolah_id');
            abort_if(! $activeSekolahId, 403);
            $sekolah = Sekolah::where('id', $activeSekolahId)
                ->where('lembaga_id', $user->lembaga_id)
                ->first();
        } else {
            abort(403);
        }

        abort_if(! $sekolah, 404);

        $profileMode = 'sekolah';

        return view('profile.edit', compact('user', 'sekolah', 'lembaga', 'pengajar', 'anaks', 'profileMode'));
    }

    public function updateSekolah(Request $request): RedirectResponse
    {
        $user = $request->user();
        $sekolah = null;
        if ($user->hasRole('Admin Sekolah')) {
            $sekolah = Sekolah::findOrFail($user->sekolah_id);
        } elseif ($user->hasRole('Lembaga')) {
            $activeSekolahId = session('active_sekolah_id');
            abort_if(! $activeSekolahId, 403);
            $sekolah = Sekolah::where('id', $activeSekolahId)
                ->where('lembaga_id', $user->lembaga_id)
                ->firstOrFail();
        } else {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'npsn' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'location_coordinate' => [
                'nullable',
                'string',
                'max:100',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! filled($value)) {
                        return;
                    }

                    $raw = trim((string) $value);
                    $raw = preg_replace('/\s+/', '', $raw) ?? $raw;
                    $parts = explode(',', $raw);
                    if (count($parts) !== 2) {
                        $fail('Koordinat lokasi harus berformat "lat,lng".');

                        return;
                    }

                    $lat = filter_var($parts[0], FILTER_VALIDATE_FLOAT);
                    $lng = filter_var($parts[1], FILTER_VALIDATE_FLOAT);
                    if ($lat === false || $lng === false) {
                        $fail('Koordinat lokasi harus berupa angka desimal.');

                        return;
                    }

                    if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
                        $fail('Koordinat lokasi di luar rentang yang valid.');
                    }
                },
            ],
            'photo' => 'nullable|image|max:2048',
        ]);

        $data = $request->only('name', 'address', 'phone', 'location_coordinate');
        $data['nisn'] = $request->input('npsn');
        if ($request->hasFile('photo')) {
            if (filled($sekolah->photo)) {
                Storage::disk('public')->delete($sekolah->photo);
            }
            $data['photo'] = $this->uploadImage($request->file('photo'), 'sekolah');
        }

        $sekolah->update($data);

        return Redirect::route('profile.sekolah.edit')->with('status', 'profile-sekolah-updated');
    }

    public function updatePengajar(Request $request): RedirectResponse
    {
        $pengajar = Pengajar::where('user_id', $request->user()->id)->firstOrFail();
        $pendidikanPilihan = PendidikanTerakhir::options();
        if (filled($pengajar->pendidikan) && ! in_array($pengajar->pendidikan, $pendidikanPilihan, true)) {
            $pendidikanPilihan[] = $pengajar->pendidikan;
        }
        $request->validate([
            'nik' => 'nullable|string',
            'alamat' => 'nullable|string',
            'phone' => 'nullable|string',
            'pendidikan' => ['nullable', Rule::in($pendidikanPilihan)],
            'jenis_kelamin' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
        ]);
        $data = $request->only('nik', 'alamat', 'phone', 'pendidikan', 'jenis_kelamin');
        if ($request->hasFile('photo')) {
            if ($pengajar->photo) {
                Storage::disk('public')->delete($pengajar->photo);
            }
            $data['photo'] = $this->uploadImage($request->file('photo'), 'pengajar');
        }
        $pengajar->update($data);

        return Redirect::route('profile.edit')->with('status', 'profile-pengajar-updated');
    }

    public function updateOrangTua(Request $request): RedirectResponse
    {
        $request->validate([
            'parent_name' => 'nullable|string|max:255',
            'nama_bapak' => 'nullable|string|max:255',
            'nik_bapak' => 'nullable|string|max:50',
            'nama_ibu' => 'nullable|string|max:255',
            'nik_ibu' => 'nullable|string|max:50',
        ]);
        Anak::where('user_id', $request->user()->id)->update($request->only('parent_name', 'nama_bapak', 'nik_bapak', 'nama_ibu', 'nik_ibu'));

        return Redirect::route('profile.edit')->with('status', 'profile-orangtua-updated');
    }

    public function updateAnak(Request $request, Anak $anak): RedirectResponse
    {
        abort_if($anak->user_id !== $request->user()->id, 403);

        $request->validate([
            'name' => 'required|string|max:255',
            'nickname' => 'nullable|string|max:50',
            'dob' => 'required|date',
            'nik' => 'nullable|string|max:50',
            'jenis_kelamin' => 'nullable|in:Laki-laki,Perempuan',
            'alamat' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
        ]);

        $data = $request->only('name', 'dob', 'nik', 'jenis_kelamin', 'alamat');
        $data['nickname'] = filled(trim($request->input('nickname', '')))
            ? trim($request->input('nickname'))
            : null;

        if ($request->hasFile('photo')) {
            if ($anak->photo) {
                Storage::disk('public')->delete($anak->photo);
            }
            $data['photo'] = $this->uploadImage($request->file('photo'), 'anak');
        }

        $anak->update($data);

        return Redirect::route('profile.edit')->with('status', 'profile-anak-updated');
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
