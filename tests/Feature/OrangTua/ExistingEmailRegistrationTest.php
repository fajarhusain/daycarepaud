<?php

namespace Tests\Feature\OrangTua;

use App\Models\Lembaga;
use App\Models\Sekolah;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExistingEmailRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_existing_orang_tua_email_creates_pending_anak_not_new_user(): void
    {
        $lembaga = Lembaga::create(['name' => 'Yayasan', 'status' => 'active']);
        $sekolah = Sekolah::create(['lembaga_id' => $lembaga->id, 'name' => 'PAUD', 'status' => 'active']);

        $ortu = User::factory()->create([
            'email' => 'ortu@example.com',
            'sekolah_id' => null,
        ]);
        $ortu->assignRole('Orang Tua');

        $this->post(route('guest.pendaftaran.store'), [
            'name' => 'Ortu Baru',
            'email' => 'ortu@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'sekolah_id' => $sekolah->id,
            'anak_name' => 'Anak Kedua',
            'anak_dob' => '2020-01-01',
        ])->assertRedirect(route('login'));

        $this->assertSame(1, User::where('email', 'ortu@example.com')->count());
        $this->assertDatabaseHas('anaks', [
            'user_id' => $ortu->id,
            'name' => 'Anak Kedua',
            'sekolah_id' => $sekolah->id,
            'status' => 'pending',
        ]);
    }
}
