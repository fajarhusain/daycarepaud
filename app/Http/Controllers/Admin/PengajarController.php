<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\DownloadsExcel;
use App\Http\Controllers\Controller;
use App\Http\Traits\CanUploadImage;
use App\Models\Kelas;
use App\Models\Pengajar;
use App\Models\PresensiShift;
use App\Models\User;
use App\Support\PaginationPerPage;
use App\Support\PendidikanTerakhir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PengajarController extends Controller
{
    use CanUploadImage;
    use DownloadsExcel;

    public function index(Request $request)
    {
        $sekolah_id = auth()->user()->sekolah_id;
        $pengajars = Pengajar::where('sekolah_id', $sekolah_id)->with(['user', 'kelas', 'presensiShift'])->latest()->paginate(PaginationPerPage::resolve($request))->withQueryString();
        $kelas = Kelas::where('sekolah_id', $sekolah_id)->orderBy('name')->get();
        $presensiShifts = PresensiShift::where('sekolah_id', $sekolah_id)->orderBy('sort_order')->orderBy('id')->get();
        $pendidikanOptions = PendidikanTerakhir::options();
        foreach ($pengajars as $p) {
            if (filled($p->pendidikan) && ! in_array($p->pendidikan, $pendidikanOptions, true)) {
                $pendidikanOptions[] = $p->pendidikan;
            }
        }
        $pendidikanOptions = array_values(array_unique($pendidikanOptions));

        return view('admin.pengajar.index', compact('pengajars', 'kelas', 'pendidikanOptions', 'presensiShifts'));
    }

    public function export(Request $request)
    {
        $sekolah_id = auth()->user()->sekolah_id;
        $pengajars = Pengajar::where('sekolah_id', $sekolah_id)->with(['user', 'kelas'])->latest()->get();

        $rows = $pengajars->map(fn (Pengajar $p) => [
            $p->name,
            $p->user?->email ?? '-',
            $p->jabatan ?? '-',
            $p->kelas->pluck('name')->join(', ') ?: '-',
            $p->nik ?? '-',
            $p->phone ?? '-',
            $p->pendidikan ?? '-',
            $p->jenis_kelamin ?? '-',
        ])->all();

        return $this->downloadExcel(
            ['Nama Guru', 'Email Login', 'Jabatan / Posisi', 'Penempatan Kelas', 'NIK', 'Telepon', 'Pendidikan', 'J/K'],
            $rows,
            'data-guru-'.now()->format('Y-m-d').'.xlsx',
            'Data Guru'
        );
    }

    public function store(Request $request)
    {
        $sekolah_id = auth()->user()->sekolah_id;

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'jabatan' => 'nullable|string|max:255',
            'nik' => 'nullable|string|max:50',
            'alamat' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'pendidikan' => ['nullable', Rule::in(PendidikanTerakhir::options())],
            'jenis_kelamin' => 'nullable|in:Pria,Wanita',
            'kelas_id' => 'nullable|array',
            'kelas_id.*' => 'exists:kelas,id',
            'presensi_shift_id' => [
                'nullable',
                'integer',
                Rule::exists('presensi_shifts', 'id')->where(fn ($q) => $q->where('sekolah_id', $sekolah_id)),
            ],
        ]);

        $defaultShiftId = PresensiShift::where('sekolah_id', $sekolah_id)->where('is_default', true)->value('id')
            ?? PresensiShift::where('sekolah_id', $sekolah_id)->orderBy('id')->value('id');

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make('password123'),
            'sekolah_id' => $sekolah_id,
        ]);
        $user->assignRole('Pengajar');

        $data = [
            'user_id' => $user->id,
            'sekolah_id' => $sekolah_id,
            'presensi_shift_id' => $request->input('presensi_shift_id') ?: $defaultShiftId,
            'name' => $request->name,
            'jabatan' => $request->jabatan,
            'nik' => $request->nik,
            'alamat' => $request->alamat,
            'phone' => $request->phone,
            'pendidikan' => $request->pendidikan,
            'jenis_kelamin' => $request->jenis_kelamin,
        ];

        if ($request->hasFile('photo')) {
            $data['photo'] = $this->uploadImage($request->file('photo'), 'pengajar');
        }

        $pengajar = Pengajar::create($data);

        if ($request->has('kelas_id')) {
            $pengajar->kelas()->sync($request->kelas_id);
        }

        return redirect()->route('admin.pengajar.index')->with('success', 'Data Guru berhasil ditambahkan. Password default: password123');
    }

    public function update(Request $request, Pengajar $pengajar)
    {
        abort_if($pengajar->sekolah_id !== auth()->user()->sekolah_id, 403);

        $pendidikanPilihan = PendidikanTerakhir::options();
        if (filled($pengajar->pendidikan) && ! in_array($pengajar->pendidikan, $pendidikanPilihan, true)) {
            $pendidikanPilihan[] = $pengajar->pendidikan;
        }

        $sekolahId = (int) auth()->user()->sekolah_id;

        $request->validate([
            'name' => 'required|string|max:255',
            'jabatan' => 'nullable|string|max:255',
            'nik' => 'nullable|string|max:50',
            'alamat' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'pendidikan' => ['nullable', Rule::in($pendidikanPilihan)],
            'jenis_kelamin' => 'nullable|in:Pria,Wanita',
            'kelas_id' => 'nullable|array',
            'kelas_id.*' => 'exists:kelas,id',
            'presensi_shift_id' => [
                'required',
                'integer',
                Rule::exists('presensi_shifts', 'id')->where(fn ($q) => $q->where('sekolah_id', $sekolahId)),
            ],
        ]);

        $dataArr = [
            'name' => $request->name,
            'presensi_shift_id' => $request->presensi_shift_id,
            'jabatan' => $request->jabatan,
            'nik' => $request->nik,
            'alamat' => $request->alamat,
            'phone' => $request->phone,
            'pendidikan' => $request->pendidikan,
            'jenis_kelamin' => $request->jenis_kelamin,
        ];

        if ($request->hasFile('photo')) {
            if ($pengajar->photo) {
                Storage::disk('public')->delete($pengajar->photo);
            }
            $dataArr['photo'] = $this->uploadImage($request->file('photo'), 'pengajar');
        }

        $pengajar->update($dataArr);

        $user = $pengajar->user;
        $user->update([
            'name' => $request->name,
        ]);

        $pengajar->kelas()->sync($request->kelas_id ?? []);

        return redirect()->route('admin.pengajar.index')->with('success', 'Data Guru berhasil diperbarui.');
    }

    public function destroy(Pengajar $pengajar)
    {
        abort_if($pengajar->sekolah_id !== auth()->user()->sekolah_id, 403);

        $user = $pengajar->user;
        if ($pengajar->photo) {
            Storage::disk('public')->delete($pengajar->photo);
        }
        $pengajar->delete();

        if ($user) {
            $user->delete();
        }

        return redirect()->route('admin.pengajar.index')->with('success', 'Data Guru dan akun login berhasil dihapus.');
    }
}
