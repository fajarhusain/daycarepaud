<?php

namespace Tests\Feature\OrangTua;

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

class TambahAnakTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_orang_tua_can_submit_tambah_anak_form(): void
    {
        $fixtures = $this->createOrtuWithApprovedAnak();

        $response = $this->actingAs($fixtures['ortu'])->post(route('orangtua.anak.store'), [
            'sekolah_id' => $fixtures['sekolah']->id,
            'name' => 'Anak Kedua',
            'dob' => '2021-03-10',
            'catatan_ortu' => 'Catatan uji anak kedua.',
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('anaks', [
            'user_id' => $fixtures['ortu']->id,
            'name' => 'Anak Kedua',
            'status' => 'pending',
            'sekolah_id' => $fixtures['sekolah']->id,
        ]);
    }

    public function test_tambah_anak_form_requires_authentication(): void
    {
        $response = $this->get(route('orangtua.anak.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_guest_pendaftaran_shows_tambah_anak_cta(): void
    {
        $response = $this->get(route('guest.pendaftaran'));

        $response->assertStatus(200);
        $response->assertSee('Masuk untuk menambah anak', false);
    }

    /**
     * @return array{sekolah: Sekolah, ortu: User}
     */
    protected function createOrtuWithApprovedAnak(): array
    {
        $lembaga = Lembaga::first();
        $sekolah = Sekolah::first();

        $admin = User::factory()->create([
            'sekolah_id' => $sekolah->id,
            'lembaga_id' => $lembaga->id,
        ]);
        $admin->assignRole('Admin Sekolah');

        $pengajar = Pengajar::create([
            'user_id' => $admin->id,
            'sekolah_id' => $sekolah->id,
            'name' => 'Guru Test',
        ]);

        $kelas = Kelas::create([
            'sekolah_id' => $sekolah->id,
            'name' => 'Kelas Uji',
            'wali_kelas_id' => $pengajar->id,
        ]);

        $ortu = User::factory()->create([
            'email' => 'ortu-tambah-anak@test.com',
            'password' => Hash::make('password'),
            'sekolah_id' => null,
            'name' => 'Budi Ortu',
        ]);
        $ortu->assignRole('Orang Tua');

        Anak::create([
            'user_id' => $ortu->id,
            'sekolah_id' => $sekolah->id,
            'kelas_id' => $kelas->id,
            'name' => 'Anak Pertama',
            'status' => 'approved',
        ]);

        return compact('sekolah', 'ortu');
    }
}
