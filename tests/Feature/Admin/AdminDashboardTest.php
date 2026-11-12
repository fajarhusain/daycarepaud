<?php

namespace Tests\Feature\Admin;

use App\Models\Anak;
use App\Models\Lembaga;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ValidateCsrfToken::class);

        Role::firstOrCreate(['name' => 'Admin Sekolah', 'guard_name' => 'web']);
    }

    private function assignRoleForSchool(User $user, string $role, int $sekolahId): void
    {
        $registrar = app(PermissionRegistrar::class);
        $previous = $registrar->getPermissionsTeamId();

        $registrar->setPermissionsTeamId($sekolahId);
        $user->assignRole($role);
        $registrar->setPermissionsTeamId($previous);
    }

    private function createAdminSekolah(string $schoolName = 'PAUD Bintang'): array
    {
        $lembaga = Lembaga::create(['name' => 'Yayasan Pendidikan']);
        $sekolah = Sekolah::create([
            'lembaga_id' => $lembaga->id,
            'name' => $schoolName,
            'is_active' => true,
            'location_coordinate' => '-6.200000,106.816666',
            'radius_m' => 100,
        ]);

        $admin = User::factory()->create([
            'sekolah_id' => $sekolah->id,
            'lembaga_id' => $lembaga->id,
        ]);

        $this->assignRoleForSchool($admin, 'Admin Sekolah', $sekolah->id);

        return [$admin, $sekolah];
    }

    public function test_admin_can_view_dashboard_with_hero_kpis_and_quick_links(): void
    {
        [$admin, $sekolah] = $this->createAdminSekolah('PAUD Mentari Bangsa');

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('PAUD Mentari Bangsa');
        $response->assertSee('Admin Sekolah');
        $response->assertSee('Siswa aktif');
        $response->assertSee('Kehadiran murid');
        $response->assertSee('Jurnal &amp; Guru', false);
        $response->assertSee('Kegiatan bulan ini');
        $response->assertDontSee('Saldo kas');
        $response->assertSee('Akses cepat');
        $response->assertSee('Data Siswa');
        $response->assertSee('Presensi siswa');
        $response->assertSee('Presensi Siswa');
        $response->assertSee('Presensi Guru');
        $response->assertSee('Agenda Belajar');
        $response->assertSee('Tagihan SPP');
        $response->assertSee('Buku Kas');
        $response->assertSee('stat-card--link', false);
        $response->assertSee('dashboard-hero-action--primary', false);
        $response->assertSee('data-tour="dashboard-welcome"', false);
        $response->assertSee('data-tour="dashboard-stats"', false);
        $response->assertSee('data-tour="dashboard-attention"', false);
        $response->assertSee('data-tour="dashboard-quick-links"', false);
        $response->assertDontSee('Pantau operasional sekolah dari satu tempat');
    }

    public function test_dashboard_shows_critical_attention_when_location_coordinate_is_empty(): void
    {
        $lembaga = Lembaga::create(['name' => 'Yayasan Pendidikan']);
        $sekolah = Sekolah::create([
            'lembaga_id' => $lembaga->id,
            'name' => 'PAUD Belum Ada Lokasi',
            'is_active' => true,
            'location_coordinate' => null,
            'radius_m' => 100,
        ]);

        $admin = User::factory()->create([
            'sekolah_id' => $sekolah->id,
            'lembaga_id' => $lembaga->id,
        ]);

        $this->assignRoleForSchool($admin, 'Admin Sekolah', $sekolah->id);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Lokasi shift presensi belum lengkap');
        $response->assertSee('Atur lokasi');
        $response->assertSee('Segera');
    }

    public function test_dashboard_shows_attention_when_student_registration_is_pending(): void
    {
        [$admin, $sekolah] = $this->createAdminSekolah('PAUD Anak Ceria');

        $parent = User::factory()->create();
        Anak::create([
            'sekolah_id' => $sekolah->id,
            'user_id' => $parent->id,
            'name' => 'Budi Kecil',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('1 pendaftaran perlu disetujui');
        $response->assertSee('Review pendaftaran');
    }
}
