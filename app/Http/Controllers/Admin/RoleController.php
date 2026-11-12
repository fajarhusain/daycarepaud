<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    protected array $defaultRoles = ['Superadmin', 'Lembaga', 'Admin Sekolah', 'Wali Kelas', 'Pengajar', 'Orang Tua'];

    protected array $hiddenRoles = ['Superadmin', 'Lembaga'];

    protected array $permissionGroups = [
        'Manajemen Siswa' => [
            ['name' => 'menu.siswa', 'label' => 'Siswa Sekolah'],
            ['name' => 'menu.pendaftaran', 'label' => 'Pendaftaran'],
            ['name' => 'menu.kelola-kelas', 'label' => 'Kelola Kelas'],
            ['name' => 'menu.presensi-siswa', 'label' => 'Presensi Siswa'],
            ['name' => 'menu.kesehatan-siswa', 'label' => 'Kesehatan Siswa'],
        ],
        'Kurikulum' => [
            ['name' => 'menu.matrikulasi', 'label' => 'Matrikulasi'],
            ['name' => 'menu.skala-capaian', 'label' => 'Skala Capaian'],
            ['name' => 'menu.agenda-belajar', 'label' => 'Agenda Belajar'],
            ['name' => 'menu.kegiatan-rutin', 'label' => 'Kegiatan Rutin'],
            ['name' => 'menu.pencapaian-siswa', 'label' => 'Pencapaian Siswa'],
            ['name' => 'menu.monev', 'label' => 'Monev'],
        ],
        'Lembaga & Guru' => [
            ['name' => 'menu.data-pengajar', 'label' => 'Data Guru'],
                ['name' => 'menu.presensi-guru', 'label' => 'Presensi Guru'],
                ['name' => 'menu.pengaturan-presensi-guru', 'label' => 'Pengaturan Presensi Guru'],
            ['name' => 'menu.monev-guru', 'label' => 'Monev Guru'],
            ['name' => 'menu.monev-guru-kriteria', 'label' => 'Kriteria Monev Guru'],
            ['name' => 'menu.sarana', 'label' => 'Sarana'],
            ['name' => 'menu.menu-makanan', 'label' => 'Menu Makanan'],
        ],
        'Akuntansi' => [
            ['name' => 'menu.akun-coa', 'label' => 'Kode Rekening & Akun'],
            ['name' => 'menu.cashflow', 'label' => 'Cashflow'],
            ['name' => 'menu.tabungan-investasi', 'label' => 'Tabungan & Investasi'],
            ['name' => 'menu.jurnal-umum', 'label' => 'Jurnal Umum'],
            ['name' => 'menu.laporan-keuangan', 'label' => 'Laporan Keuangan'],
        ],
        'RKAS' => [
            ['name' => 'menu.sumber-dana', 'label' => 'Sumber Dana'],
            ['name' => 'menu.rkas', 'label' => 'RKAS'],
            ['name' => 'menu.laporan-rkas', 'label' => 'Laporan RKAS'],
        ],
        'Biaya & Pembayaran' => [
            ['name' => 'menu.biaya-harian', 'label' => 'Biaya Bulanan'],
            ['name' => 'menu.diskon', 'label' => 'Diskon'],
            ['name' => 'menu.rekap-pembayaran', 'label' => 'Rekap Pembayaran'],
            ['name' => 'menu.gaji-guru', 'label' => 'Gaji Guru'],
        ],
        'Masukan & Komunikasi' => [
            ['name' => 'menu.kritik-saran', 'label' => 'Kritik & Saran'],
            ['name' => 'menu.chat-orangtua', 'label' => 'Chat Orang Tua'],
        ],
        'Pengaturan' => [
            ['name' => 'menu.role', 'label' => 'Role'],
            ['name' => 'menu.pengguna', 'label' => 'Pengguna'],
            ['name' => 'menu.log-aktivitas', 'label' => 'Log Aktivitas'],
        ],
        'Pengaturan Akuntansi' => [
            ['name' => 'menu.setting-akuntansi', 'label' => 'Setting Akuntansi'],
        ],
        'Pengaturan AI' => [
            ['name' => 'menu.pengaturan-ai', 'label' => 'Pengaturan AI'],
        ],
    ];

    public function index()
    {
        $this->ensurePermissionsExist();

        $roles = $this->scopedRolesQuery()->with('permissions')->get();
        $permissionGroups = $this->permissionGroups;

        return view('admin.role.index', compact('roles', 'permissionGroups'));
    }

    public function store(Request $request)
    {
        $sekolahId = TenantContext::requireSekolahId();

        $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('roles', 'name')->where(fn ($q) => $q->where('sekolah_id', $sekolahId)),
            ],
        ]);

        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'web',
            'sekolah_id' => $sekolahId,
        ]);

        ActivityLogger::log('Role dibuat', null, ['role' => $role->name]);

        return redirect()->route('admin.role.index')
            ->with('success', 'Role "'.e($request->name).'" berhasil ditambahkan.');
    }

    public function update(Request $request, Role $role)
    {
        $this->authorizeTenantRole($role);

        $sekolahId = TenantContext::requireSekolahId();

        $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('roles', 'name')->ignore($role->id)->where(fn ($q) => $q->where('sekolah_id', $sekolahId)),
            ],
        ]);

        $role->update(['name' => $request->name]);

        ActivityLogger::log('Role diperbarui', null, ['role' => $role->name]);

        return redirect()->route('admin.role.index')
            ->with('success', 'Role berhasil diperbarui.');
    }

    public function updatePermissions(Request $request, Role $role)
    {
        $this->authorizeTenantRole($role);

        $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:'.Permission::class.',name'],
        ]);

        $permissions = $request->input('permissions', []);
        $role->syncPermissions($permissions);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        ActivityLogger::log('Permission role diperbarui', null, [
            'role' => $role->name,
            'permissions' => $permissions,
        ]);

        return redirect()->route('admin.role.index')
            ->with('success', 'Akses menu untuk role "'.e($role->name).'" berhasil diperbarui.');
    }

    public function destroy(Role $role)
    {
        $this->authorizeTenantRole($role);

        if (in_array($role->name, $this->defaultRoles)) {
            return back()->withErrors(['role' => 'Role default tidak dapat dihapus.']);
        }

        $roleName = $role->name;
        $role->delete();

        ActivityLogger::log('Role dihapus', null, ['role' => $roleName]);

        return redirect()->route('admin.role.index')
            ->with('success', 'Role "'.$roleName.'" berhasil dihapus.');
    }

    private function ensurePermissionsExist(): void
    {
        foreach ($this->permissionGroups as $permissions) {
            foreach ($permissions as $perm) {
                Permission::firstOrCreate([
                    'name' => $perm['name'],
                    'guard_name' => 'web',
                ]);
            }
        }
    }

    private function scopedRolesQuery()
    {
        $sekolahId = TenantContext::requireSekolahId();

        return Role::query()
            ->whereNotIn('name', $this->hiddenRoles)
            ->where(function ($query) use ($sekolahId) {
                $query->whereNull('sekolah_id')
                    ->orWhere('sekolah_id', $sekolahId);
            });
    }

    private function authorizeTenantRole(Role $role): void
    {
        abort_unless($this->scopedRolesQuery()->whereKey($role->id)->exists(), 403);
    }
}
