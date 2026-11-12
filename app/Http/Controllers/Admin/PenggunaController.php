<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\DownloadsExcel;
use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\User;
use App\Services\WaliKelasAssignmentService;
use App\Models\Sekolah;
use App\Support\ActivityLogger;
use App\Support\PaginationPerPage;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;

class PenggunaController extends Controller
{
    use DownloadsExcel;

    private const HIDDEN_ROLES = ['Superadmin', 'Lembaga'];

    public function index(Request $request)
    {
        $sekolahId = TenantContext::requireSekolahId();
        $penggunas = User::where('sekolah_id', $sekolahId)
            ->with(['roles', 'kelas', 'pengajar.waliKelas'])
            ->latest()
            ->paginate(PaginationPerPage::resolve($request))->withQueryString();
        $roles = Role::whereNotIn('name', self::HIDDEN_ROLES)
            ->where(function ($query) use ($sekolahId) {
                $query->whereNull('sekolah_id')->orWhere('sekolah_id', $sekolahId);
            })
            ->get();
        $kelas = Kelas::where('sekolah_id', $sekolahId)->orderBy('name')->get();

        return view('admin.pengguna.index', compact('penggunas', 'roles', 'kelas'));
    }

    public function export(Request $request)
    {
        $rows = User::where('sekolah_id', TenantContext::requireSekolahId())
            ->with(['roles', 'kelas'])
            ->latest()
            ->get()
            ->map(fn (User $u) => [
                $u->name,
                $u->email,
                $u->roles->pluck('name')->join(', ') ?: '-',
                $u->created_at?->format('Y-m-d') ?? '-',
            ])
            ->all();

        return $this->downloadExcel(
            ['Nama', 'Email', 'Role', 'Bergabung'],
            $rows,
            'pengguna-'.now()->format('Y-m-d').'.xlsx',
            'Pengguna'
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', Rules\Password::defaults()],
            'role' => ['required', 'string', 'exists:'.Role::class.',name', Rule::notIn(self::HIDDEN_ROLES)],
            'kelas_id' => [
                Rule::requiredIf(fn () => $request->input('role') === 'Wali Kelas'),
                'nullable',
                'integer',
                Rule::exists('kelas', 'id')->where(fn ($query) => $query->where('sekolah_id', TenantContext::requireSekolahId())),
            ],
        ]);

        $sekolahId = TenantContext::requireSekolahId();
        $lembagaId = auth()->user()->lembaga_id ?? Sekolah::find($sekolahId)?->lembaga_id;

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'lembaga_id' => $lembagaId,
            'sekolah_id' => $sekolahId,
            'kelas_id' => $request->input('role') === 'Wali Kelas' ? $request->integer('kelas_id') : null,
        ]);

        $user->assignRole($request->role);
        $this->syncWaliKelasAssignment($user, $request->input('role') === 'Wali Kelas' ? $request->integer('kelas_id') : null);

        ActivityLogger::log('Role ditetapkan ke pengguna', $user, [
            'role' => $request->role,
            'kelas_id' => $user->kelas_id,
        ]);

        return redirect()->route('admin.pengguna.index')
            ->with('success', 'Pengguna "'.e($request->name).'" berhasil ditambahkan.');
    }

    public function update(Request $request, User $pengguna)
    {
        abort_if($pengguna->sekolah_id !== TenantContext::requireSekolahId(), 403);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$pengguna->id],
            'password' => ['nullable', Rules\Password::defaults()],
            'role' => ['required', 'string', 'exists:'.Role::class.',name', Rule::notIn(self::HIDDEN_ROLES)],
            'kelas_id' => [
                Rule::requiredIf(fn () => $request->input('role') === 'Wali Kelas'),
                'nullable',
                'integer',
                Rule::exists('kelas', 'id')->where(fn ($query) => $query->where('sekolah_id', TenantContext::requireSekolahId())),
            ],
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'kelas_id' => $request->input('role') === 'Wali Kelas' ? $request->integer('kelas_id') : null,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $pengguna->update($data);

        // Sync role: hapus semua role lama, assign role baru
        $pengguna->syncRoles([$request->role]);
        $this->syncWaliKelasAssignment($pengguna, $request->input('role') === 'Wali Kelas' ? $request->integer('kelas_id') : null);

        ActivityLogger::log('Role pengguna diperbarui', $pengguna, [
            'role' => $request->role,
            'kelas_id' => $pengguna->kelas_id,
        ]);

        return redirect()->route('admin.pengguna.index')
            ->with('success', 'Data pengguna "'.e($request->name).'" berhasil diperbarui.');
    }

    public function destroy(User $pengguna)
    {
        abort_if($pengguna->sekolah_id !== TenantContext::requireSekolahId(), 403);

        if ($pengguna->id === auth()->id()) {
            return back()->withErrors(['pengguna' => 'Tidak dapat menghapus akun sendiri.']);
        }

        $pengguna->delete();

        return redirect()->route('admin.pengguna.index')
            ->with('success', 'Pengguna "'.e($pengguna->name).'" berhasil dihapus.');
    }

    private function syncWaliKelasAssignment(User $user, ?int $kelasId): void
    {
        $service = app(WaliKelasAssignmentService::class);

        if ($kelasId === null) {
            $service->clear($user);

            return;
        }

        $service->assign($user, $kelasId);
    }
}
