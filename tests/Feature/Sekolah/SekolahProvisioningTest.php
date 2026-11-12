<?php

namespace Tests\Feature\Sekolah;

use App\Models\Lembaga;
use App\Models\Sekolah;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SekolahProvisioningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_daftar_lembaga_redirects_to_daftar_sekolah(): void
    {
        $this->get('/daftar-lembaga')
            ->assertRedirect('/daftar-sekolah');
    }

    public function test_superadmin_can_provision_active_school_with_admin(): void
    {
        $superadmin = User::factory()->create();
        $superadmin->assignRole('Superadmin');

        $this->actingAs($superadmin)
            ->post(route('superadmin.sekolah.store'), [
                'sekolah_name' => 'PAUD Langsung',
                'sekolah_address' => 'Jl. Test',
                'admin_name' => 'Admin Langsung',
                'admin_email' => 'admin@paud-langsung.test',
            ])
            ->assertRedirect(route('superadmin.sekolah.index'));

        $sekolah = Sekolah::where('name', 'PAUD Langsung')->first();
        $this->assertNotNull($sekolah);
        $this->assertSame(Sekolah::STATUS_ACTIVE, $sekolah->status);

        $admin = User::where('email', 'admin@paud-langsung.test')->first();
        $this->assertNotNull($admin);
        $this->assertTrue($admin->hasRole('Admin Sekolah'));
        $this->assertSame($sekolah->id, $admin->sekolah_id);

        $this->assertNotNull($sekolah->lembaga_id);
        $this->assertSame(Lembaga::STATUS_ACTIVE, $sekolah->lembaga->status);

        $this->post(route('login'), [
            'email' => 'admin@paud-langsung.test',
            'password' => 'password123',
        ])->assertRedirect(route('dashboard'));
    }

    public function test_admin_sekolah_cannot_login_when_school_pending(): void
    {
        $this->post(route('guest.daftar-sekolah.store'), [
            'sekolah_name' => 'PAUD Pending',
            'admin_name' => 'Admin Pending',
            'admin_email' => 'pending@paud.test',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('login'));

        $this->post(route('login'), [
            'email' => 'pending@paud.test',
            'password' => 'password',
        ])->assertRedirect(route('login'));
    }

    public function test_superadmin_can_reject_pending_school(): void
    {
        $this->post(route('guest.daftar-sekolah.store'), [
            'sekolah_name' => 'PAUD Ditolak',
            'admin_name' => 'Admin Ditolak',
            'admin_email' => 'ditolak@paud.test',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $sekolah = Sekolah::where('name', 'PAUD Ditolak')->firstOrFail();
        $superadmin = User::factory()->create();
        $superadmin->assignRole('Superadmin');

        $this->actingAs($superadmin)
            ->post(route('superadmin.sekolah.reject', $sekolah), [
                'rejection_reason' => 'Data tidak lengkap',
            ])
            ->assertRedirect();

        $sekolah->refresh();
        $this->assertSame(Sekolah::STATUS_REJECTED, $sekolah->status);
        $this->assertSame('rejected', $sekolah->lembaga->status);
    }
}
