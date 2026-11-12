<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\DownloadsExcel;
use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Pengajar;
use App\Services\WaliKelasAssignmentService;
use App\Support\PaginationPerPage;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class KelasController extends Controller
{
    use DownloadsExcel;
    public function index(Request $request)
    {
        $sekolah_id = auth()->user()->sekolah_id;
        abort_if($sekolah_id === null, 403, 'Akun tidak terikat sekolah. Hubungi lembaga.');

        $this->ensureKelasPageRolesExist();

        // User dengan kelas_id = kelas ini seharusnya wali/admin kelas (Sudah dihapus fiturnya)
        $kelasList = Kelas::query()
            ->where('sekolah_id', $sekolah_id)
            ->with(['waliKelas', 'pengajars'])
            ->withCount('anaks')
            ->latest()
            ->paginate(PaginationPerPage::resolve($request))->withQueryString();

        $pengajars = Pengajar::where('sekolah_id', $sekolah_id)->orderBy('name')->get();

        return view('admin.kelas.index', compact('kelasList', 'pengajars'));
    }

    public function export(Request $request)
    {
        $sekolah_id = auth()->user()->sekolah_id;
        abort_if($sekolah_id === null, 403);

        $kelasList = Kelas::query()
            ->where('sekolah_id', $sekolah_id)
            ->with(['waliKelas', 'pengajars'])
            ->withCount('anaks')
            ->latest()
            ->get();

        $rows = $kelasList->map(fn (Kelas $k) => [
            $k->name,
            $k->waliKelas?->name ?? '-',
            $k->description ?? '-',
            $k->anaks_count,
            $k->pengajars->pluck('name')->join(', ') ?: '-',
        ])->all();

        return $this->downloadExcel(
            ['Nama Kelas', 'Wali Kelas', 'Deskripsi', 'Jumlah Siswa', 'Pengajar'],
            $rows,
            'data-kelas-'.now()->format('Y-m-d').'.xlsx',
            'Kelola Kelas'
        );
    }

    public function show(Kelas $kelas)
    {
        $this->authorizeViewKelas($kelas);

        $kelas->load([
            'anaks' => fn ($q) => $q->orderBy('name')->with('user'),
        ]);

        return view('admin.kelas.show', compact('kelas'));
    }

    /** Fragmen HTML untuk modal daftar siswa (AJAX). */
    public function siswaModal(Kelas $kelas)
    {
        $this->authorizeViewKelas($kelas);

        $kelas->load([
            'anaks' => fn ($q) => $q->orderBy('name')->with('user'),
        ]);

        return view('admin.kelas.partials.kelas-siswa-panels', compact('kelas'));
    }

    public function store(Request $request)
    {
        $sekolah_id = auth()->user()->sekolah_id;
        abort_if($sekolah_id === null, 403, 'Akun tidak terikat sekolah. Hubungi lembaga.');

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $newKelas = Kelas::create([
            'sekolah_id' => $sekolah_id,
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.kelas.index')->with('success', 'Kelas berhasil dibuat.');
    }

    public function update(Request $request, $id)
    {
        $sekolah_id = auth()->user()->sekolah_id;
        abort_if($sekolah_id === null, 403, 'Akun tidak terikat sekolah. Hubungi lembaga.');

        $kelas = Kelas::findOrFail($id);
        abort_if((int) $kelas->sekolah_id !== (int) $sekolah_id, 403);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'wali_kelas_id' => [
                'nullable',
                'exists:pengajars,id',
                function ($attribute, $value, $fail) use ($kelas) {
                    if ($value && ! $kelas->pengajars()->where('pengajars.id', $value)->exists()) {
                        $fail('Guru yang terpilih harus terdaftar di kelas ini terlebih dahulu.');
                    }
                },
            ],
        ]);

        $oldWaliId = $kelas->wali_kelas_id;

        $kelas->update([
            'name' => $request->name,
            'description' => $request->description,
            'wali_kelas_id' => $request->wali_kelas_id,
        ]);

        if ($request->wali_kelas_id != $oldWaliId) {
            $waliService = app(WaliKelasAssignmentService::class);
            if ($request->filled('wali_kelas_id')) {
                $waliService->assignByPengajarId((int) $request->wali_kelas_id, $kelas->id);
            }
            if ($oldWaliId) {
                $waliService->removeRoleIfNoLongerWali((int) $oldWaliId);
            }
        }

        return redirect()->route('admin.kelas.index')->with('success', 'Data Kelas berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $sekolah_id = auth()->user()->sekolah_id;
        abort_if($sekolah_id === null, 403, 'Akun tidak terikat sekolah. Hubungi lembaga.');

        $kelas = Kelas::findOrFail($id);
        abort_if((int) $kelas->sekolah_id !== (int) $sekolah_id, 403);

        $oldWaliId = $kelas->wali_kelas_id;
        $kelas->delete();

        if ($oldWaliId) {
            app(WaliKelasAssignmentService::class)->removeRoleIfNoLongerWali((int) $oldWaliId);
        }

        return redirect()->route('admin.kelas.index')->with('success', 'Data Kelas berhasil dihapus.');
    }

    private function authorizeViewKelas(Kelas $kelas): void
    {
        $user = auth()->user();

        if ($user->hasRole('Admin Sekolah')) {
            $sekolah_id = $user->sekolah_id;
            abort_if($sekolah_id === null, 403, 'Akun tidak terikat sekolah. Hubungi lembaga.');
            abort_if((int) $kelas->sekolah_id !== (int) $sekolah_id, 403, 'Kelas ini bukan bagian dari sekolah Anda.');
        } elseif ($user->hasRole('Wali Kelas')) {
            $pengajar = Pengajar::where('user_id', $user->id)->first();
            abort_if(! $pengajar, 403, 'Profil pengajar tidak ditemukan.');
            abort_if((int) $kelas->sekolah_id !== (int) $pengajar->sekolah_id, 403, 'Kelas ini bukan bagian dari sekolah Anda.');
            $mengampu = $pengajar->kelas()->where('kelas.id', $kelas->id)->exists();
            $isWali = (int) $kelas->wali_kelas_id === (int) $pengajar->id;
            abort_unless($mengampu || $isWali, 403, 'Anda tidak memiliki akses ke kelas ini.');
        } else {
            abort(403, 'Anda tidak memiliki akses.');
        }
    }

    private function ensureAdminKelasRoleExists(): void
    {
        Role::firstOrCreate(
            ['name' => 'Wali Kelas', 'guard_name' => 'web'],
        );
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /** Pastikan peran yang dipakai halaman kelas ada (deploy tanpa seeder). */
    private function ensureKelasPageRolesExist(): void
    {
        foreach (['Pengajar', 'Wali Kelas'] as $name) {
            Role::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
            );
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
