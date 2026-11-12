<?php

namespace Tests\Feature\Lembaga;

use App\Models\Lembaga;
use App\Models\Sekolah;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_school_registration_pending_until_superadmin_approves(): void
    {
        $this->post(route('guest.daftar-sekolah.store'), [
            'sekolah_name' => 'PAUD Baru',
            'admin_name' => 'Admin Baru',
            'admin_email' => 'admin@paud-baru.test',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('login'));

        $sekolah = Sekolah::where('name', 'PAUD Baru')->first();
        $this->assertNotNull($sekolah);
        $this->assertSame(Sekolah::STATUS_PENDING, $sekolah->status);

        $admin = User::where('email', 'admin@paud-baru.test')->first();
        $this->assertNotNull($admin);
        $this->assertTrue($admin->hasRole('Admin Sekolah'));

        $this->post(route('login'), [
            'email' => 'admin@paud-baru.test',
            'password' => 'password',
        ])->assertRedirect(route('login'));

        $superadmin = User::factory()->create();
        $superadmin->assignRole('Superadmin');

        $this->actingAs($superadmin)
            ->post(route('superadmin.sekolah.approve', $sekolah))
            ->assertRedirect();

        $sekolah->refresh();
        $this->assertSame(Sekolah::STATUS_ACTIVE, $sekolah->status);

        $this->post(route('login'), [
            'email' => 'admin@paud-baru.test',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));
    }

    public function test_legacy_lembaga_registration_still_works_via_old_endpoint(): void
    {
        $this->post(route('guest.daftar-lembaga.store'), [
            'lembaga_name' => 'Yayasan Lama',
            'sekolah_name' => 'PAUD Lama',
            'contact_name' => 'Admin Lama',
            'contact_email' => 'admin@yayasan-lama.test',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('login'));

        $lembaga = Lembaga::where('name', 'Yayasan Lama')->first();
        $this->assertNotNull($lembaga);
        $this->assertSame('pending', $lembaga->status);

        $user = User::where('email', 'admin@yayasan-lama.test')->first();
        $this->assertTrue($user->hasRole('Lembaga'));
    }
}
