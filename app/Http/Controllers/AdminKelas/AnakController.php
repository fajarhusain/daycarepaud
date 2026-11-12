<?php

namespace App\Http\Controllers\AdminKelas;

use App\Http\Controllers\Controller;
use App\Http\Traits\CanUploadImage;
use App\Models\Anak;
use App\Models\Pengajar;
use App\Models\User;
use App\Support\PaginationPerPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AnakController extends Controller
{
    use CanUploadImage;

    public function index(Request $request)
    {
        $user = auth()->user();
        $pengajar = Pengajar::where('user_id', $user->id)->firstOrFail();
        $kelas = $pengajar->waliKelas()->orderBy('name')->get();
        $kelasIds = $kelas->pluck('id')->toArray();

        $query = Anak::whereIn('kelas_id', $kelasIds)->with('kelas')->orderBy('name');
        if ($request->filled('kelas_id') && in_array($request->kelas_id, $kelasIds)) {
            $query->where('kelas_id', $request->kelas_id);
        }
        $anaks = $query->paginate(PaginationPerPage::resolve($request))->withQueryString();

        return view('adminkelas.anak.index', compact('anaks', 'kelas'));
    }

    public function show(Anak $anak)
    {
        $user = auth()->user();
        $pengajar = Pengajar::where('user_id', $user->id)->firstOrFail();
        $kelasIds = $pengajar->waliKelas()->pluck('id')->toArray();

        abort_unless(in_array($anak->kelas_id, $kelasIds), 403);

        $anak->load([
            'user',
            'kelas',
            'kesehatans' => fn ($q) => $q->orderBy('tanggal_pemeriksaan', 'desc'),
        ]);

        return view('adminkelas.anak.show', compact('anak'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'kelas_id' => 'required|exists:kelas,id',
            'dob' => 'nullable|date',
            'nik' => 'nullable|string|max:20',
            'parent_name' => 'nullable|string|max:255',
        ]);

        $pengajar = Pengajar::where('user_id', auth()->id())->firstOrFail();
        $sekolah_id = $pengajar->sekolah_id;
        $kelasIds = $pengajar->waliKelas()->pluck('id')->toArray();
        abort_unless(in_array($request->kelas_id, $kelasIds), 403);

        $user = User::create([
            'name' => $request->parent_name ?: $request->name.' Parent',
            'email' => $request->email,
            'password' => Hash::make('password123'),
            'sekolah_id' => $sekolah_id,
        ]);
        $user->assignRole('Orang Tua');

        $data = [
            'user_id' => $user->id,
            'sekolah_id' => $sekolah_id,
            'kelas_id' => $request->kelas_id,
            'name' => $request->name,
            'dob' => $request->dob,
            'nik' => $request->nik,
            'alamat' => $request->alamat,
            'jenis_kelamin' => $request->jenis_kelamin,
            'nik_bapak' => $request->nik_bapak,
            'nama_bapak' => $request->nama_bapak,
            'nik_ibu' => $request->nik_ibu,
            'nama_ibu' => $request->nama_ibu,
            'parent_name' => $user->name,
            'status' => 'approved',
        ];

        if ($request->hasFile('photo')) {
            $data['photo'] = $this->uploadImage($request->file('photo'), 'anak');
        }

        Anak::create($data);

        return redirect()->route('adminkelas.anak.index')->with('success', 'Data Siswa berhasil ditambahkan.');
    }

    public function update(Request $request, Anak $anak)
    {
        $pengajar = Pengajar::where('user_id', auth()->id())->firstOrFail();
        $kelasIds = $pengajar->waliKelas()->pluck('id')->toArray();
        abort_unless(in_array($anak->kelas_id, $kelasIds), 403);

        $request->validate([
            'name' => 'required|string|max:255',
            'kelas_id' => 'required|exists:kelas,id',
            'dob' => 'nullable|date',
            'nik' => 'nullable|string|max:20',
        ]);

        abort_unless(in_array($request->kelas_id, $kelasIds), 403);

        $dataArr = [
            'name' => $request->name,
            'dob' => $request->dob,
            'kelas_id' => $request->kelas_id,
            'nik' => $request->nik,
            'alamat' => $request->alamat,
            'jenis_kelamin' => $request->jenis_kelamin,
            'nik_bapak' => $request->nik_bapak,
            'nama_bapak' => $request->nama_bapak,
            'nik_ibu' => $request->nik_ibu,
            'nama_ibu' => $request->nama_ibu,
            'parent_name' => $request->parent_name,
        ];

        if ($request->hasFile('photo')) {
            if ($anak->photo) {
                Storage::disk('public')->delete($anak->photo);
            }
            $dataArr['photo'] = $this->uploadImage($request->file('photo'), 'anak');
        }

        $anak->update($dataArr);

        if ($anak->user) {
            $anak->user->update(['name' => $request->parent_name]);
        }

        return redirect()->route('adminkelas.anak.index')->with('success', 'Data Siswa diperbarui.');
    }

    public function destroy(Anak $anak)
    {
        $pengajar = Pengajar::where('user_id', auth()->id())->firstOrFail();
        $kelasIds = $pengajar->waliKelas()->pluck('id')->toArray();
        abort_unless(in_array($anak->kelas_id, $kelasIds), 403);

        $user = $anak->user;
        if ($anak->photo) {
            Storage::disk('public')->delete($anak->photo);
        }
        $anak->delete();
        if ($user && $user->hasRole('Orang Tua')) {
            $user->delete();
        }

        return redirect()->route('adminkelas.anak.index')->with('success', 'Data Siswa dihapus.');
    }
}
