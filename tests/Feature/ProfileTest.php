<?php

namespace Tests\Feature;

use App\Models\Lembaga;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }

    public function test_profil_sekolah_menampilkan_peta_leaflet(): void
    {
        Role::firstOrCreate(['name' => 'Admin Sekolah', 'guard_name' => 'web']);

        $lembaga = Lembaga::create(['name' => 'Yayasan Pendidikan']);
        $sekolah = Sekolah::create([
            'lembaga_id' => $lembaga->id,
            'name' => 'PAUD Bintang',
            'location_coordinate' => '-6.200000,106.816666',
        ]);
        $admin = User::factory()->create([
            'sekolah_id' => $sekolah->id,
            'lembaga_id' => $lembaga->id,
        ]);

        $registrar = app(PermissionRegistrar::class);
        $previous = $registrar->getPermissionsTeamId();
        $registrar->setPermissionsTeamId($sekolah->id);
        $admin->assignRole('Admin Sekolah');
        $registrar->setPermissionsTeamId($previous);

        $response = $this->actingAs($admin)->get('/profile/sekolah');

        $response->assertOk()
            ->assertSee('id="presensi-setting-map"', false)
            ->assertSee('id="location_coordinate"', false)
            ->assertSee('presensi-setting-map.js', false);
    }
}
