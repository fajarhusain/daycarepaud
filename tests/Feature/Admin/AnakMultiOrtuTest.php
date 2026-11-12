<?php

namespace Tests\Feature\Admin;

use App\Models\Anak;
use App\Models\Kelas;
use App\Models\Lembaga;
use App\Models\Pengajar;
use App\Models\Sekolah;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AnakMultiOrtuTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_admin_can_add_anak_to_existing_orang_tua(): void
    {
        $fixtures = $this->createFixtures();

        $response = $this->actingAs($fixtures['admin'])->post(route('admin.anak.store'), [
            'parent_mode' => 'existing',
            'parent_user_id' => $fixtures['ortu']->id,
            'name' => 'Anak Kedua Admin',
            'dob' => '2020-08-01',
        ]);

        $response->assertRedirect(route('admin.anak.index'));
        $response->assertSessionHas('success');

        $this->assertEquals(1, User::where('email', $fixtures['ortu']->email)->count());
        $this->assertEquals(2, Anak::where('user_id', $fixtures['ortu']->id)->count());
        $this->assertDatabaseHas('anaks', [
            'user_id' => $fixtures['ortu']->id,
            'name' => 'Anak Kedua Admin',
            'status' => 'approved',
        ]);
    }

    public function test_admin_orang_tua_search_returns_matching_parents(): void
    {
        $fixtures = $this->createFixtures();

        $response = $this->actingAs($fixtures['admin'])->getJson(
            route('admin.orang-tua.search', ['q' => 'Siti'])
        );

        $response->assertOk();
        $response->assertJsonFragment(['email' => $fixtures['ortu']->email]);
    }

    /**
     * @return array{sekolah: Sekolah, admin: User, ortu: User}
     */
    protected function createFixtures(): array
    {
        $lembaga = Lembaga::first();
        $sekolah = Sekolah::first();

        $admin = User::factory()->create([
            'email' => 'admin-multi-ortu@test.com',
            'password' => Hash::make('password'),
            'lembaga_id' => $lembaga->id,
            'sekolah_id' => $sekolah->id,
        ]);
        $admin->assignRole('Admin Sekolah');

        $pengajar = Pengajar::create([
            'user_id' => $admin->id,
            'sekolah_id' => $sekolah->id,
            'name' => 'Guru Admin',
        ]);

        Kelas::create([
            'sekolah_id' => $sekolah->id,
            'name' => 'Kelas Multi Ortu',
            'wali_kelas_id' => $pengajar->id,
        ]);

        $ortu = User::factory()->create([
            'email' => 'siti-multi-ortu@test.com',
            'name' => 'Siti Ortu',
            'sekolah_id' => $sekolah->id,
        ]);
        $ortu->assignRole('Orang Tua');

        Anak::create([
            'user_id' => $ortu->id,
            'sekolah_id' => $sekolah->id,
            'name' => 'Anak Pertama',
            'status' => 'approved',
        ]);

        return compact('sekolah', 'admin', 'ortu');
    }
}
