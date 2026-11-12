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

class AnakDestroyMultiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_destroying_one_anak_does_not_delete_parent_with_other_children(): void
    {
        $fixtures = $this->createFixtures();

        $response = $this->actingAs($fixtures['admin'])->delete(
            route('admin.anak.destroy', $fixtures['anak1'])
        );

        $response->assertRedirect(route('admin.anak.index'));

        $this->assertDatabaseHas('users', ['id' => $fixtures['ortu']->id]);
        $this->assertDatabaseMissing('anaks', ['id' => $fixtures['anak1']->id]);
        $this->assertDatabaseHas('anaks', ['id' => $fixtures['anak2']->id]);
    }

    public function test_destroying_last_anak_deletes_parent_user(): void
    {
        $fixtures = $this->createFixtures();

        $this->actingAs($fixtures['admin'])->delete(
            route('admin.anak.destroy', $fixtures['anak2'])
        );

        $response = $this->actingAs($fixtures['admin'])->delete(
            route('admin.anak.destroy', $fixtures['anak1'])
        );

        $response->assertRedirect(route('admin.anak.index'));
        $this->assertDatabaseMissing('users', ['id' => $fixtures['ortu']->id]);
    }

    /**
     * @return array{admin: User, ortu: User, anak1: Anak, anak2: Anak}
     */
    protected function createFixtures(): array
    {
        $lembaga = Lembaga::first();
        $sekolah = Sekolah::first();

        $admin = User::factory()->create([
            'email' => 'admin-destroy-multi@test.com',
            'password' => Hash::make('password'),
            'lembaga_id' => $lembaga->id,
            'sekolah_id' => $sekolah->id,
        ]);
        $admin->assignRole('Admin Sekolah');

        $pengajar = Pengajar::create([
            'user_id' => $admin->id,
            'sekolah_id' => $sekolah->id,
            'name' => 'Guru Destroy',
        ]);

        Kelas::create([
            'sekolah_id' => $sekolah->id,
            'name' => 'Kelas Destroy',
            'wali_kelas_id' => $pengajar->id,
        ]);

        $ortu = User::factory()->create([
            'email' => 'ortu-destroy-multi@test.com',
            'name' => 'Ortu Destroy',
            'sekolah_id' => $sekolah->id,
        ]);
        $ortu->assignRole('Orang Tua');

        $anak1 = Anak::create([
            'user_id' => $ortu->id,
            'sekolah_id' => $sekolah->id,
            'name' => 'Anak Satu',
            'status' => 'approved',
        ]);

        $anak2 = Anak::create([
            'user_id' => $ortu->id,
            'sekolah_id' => $sekolah->id,
            'name' => 'Anak Dua',
            'status' => 'approved',
        ]);

        return compact('admin', 'ortu', 'anak1', 'anak2');
    }
}
