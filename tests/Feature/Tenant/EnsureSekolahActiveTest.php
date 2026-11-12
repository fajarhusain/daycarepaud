<?php

namespace Tests\Feature\Tenant;

use App\Models\Anak;
use App\Models\Lembaga;
use App\Models\Sekolah;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnsureSekolahActiveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_orang_tua_blocked_when_active_sekolah_is_suspended(): void
    {
        $lembaga = Lembaga::create(['name' => 'Yayasan', 'status' => 'active']);
        $sekolah = Sekolah::create([
            'lembaga_id' => $lembaga->id,
            'name' => 'PAUD Ditangguhkan',
            'status' => 'suspended',
        ]);

        $ortu = User::factory()->create(['sekolah_id' => null]);
        $ortu->assignRole('Orang Tua');

        Anak::withoutSekolahScope()->create([
            'user_id' => $ortu->id,
            'sekolah_id' => $sekolah->id,
            'name' => 'Anak Satu',
            'status' => 'approved',
        ]);

        $this->actingAs($ortu)
            ->withSession(['ortu_active_sekolah_id' => $sekolah->id])
            ->get(route('orangtua.monev.index'))
            ->assertForbidden();
    }

    public function test_admin_sekolah_blocked_when_sekolah_is_suspended(): void
    {
        $lembaga = Lembaga::create(['name' => 'Yayasan', 'status' => 'active']);
        $sekolah = Sekolah::create([
            'lembaga_id' => $lembaga->id,
            'name' => 'PAUD Ditangguhkan',
            'status' => 'suspended',
        ]);

        $admin = User::factory()->create([
            'lembaga_id' => $lembaga->id,
            'sekolah_id' => $sekolah->id,
        ]);
        $admin->assignRole('Admin Sekolah');

        $this->actingAs($admin)
            ->get(route('admin.monev.index'))
            ->assertForbidden();
    }
}
