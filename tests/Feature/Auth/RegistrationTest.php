<?php

namespace Tests\Feature\Auth;

use App\Models\Sekolah;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_screen_can_be_rendered(): void
    {
        $this->seed(RoleSeeder::class);

        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('Daftar Orang Tua', false);
    }

    public function test_pendaftaran_screen_can_be_rendered(): void
    {
        $this->seed(RoleSeeder::class);

        $response = $this->get('/pendaftaran');

        $response->assertStatus(200);
        $response->assertSee('Daftar Orang Tua', false);
    }

    public function test_new_parent_can_submit_pendaftaran(): void
    {
        $this->seed(RoleSeeder::class);
        $sekolah = Sekolah::query()->firstOrFail();

        $response = $this->post('/pendaftaran', [
            'name' => 'Ibu Contoh',
            'email' => 'ibu-baru@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'sekolah_id' => $sekolah->id,
            'anak_name' => 'Anak Contoh',
            'anak_dob' => '2020-06-15',
            'catatan_ortu' => 'Catatan opsional uji.',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertDatabaseHas('users', ['email' => 'ibu-baru@example.com']);
        $this->assertDatabaseHas('anaks', ['name' => 'Anak Contoh', 'sekolah_id' => $sekolah->id]);

        $user = User::where('email', 'ibu-baru@example.com')->first();
        $this->assertTrue($user->hasRole('Orang Tua'));
    }
}
