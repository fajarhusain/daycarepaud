<?php

namespace Tests\Feature\OrangTua;

use App\Models\Anak;
use App\Models\Lembaga;
use App\Models\Sekolah;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiSekolahTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_orang_tua_can_switch_active_sekolah(): void
    {
        $lembaga = Lembaga::create(['name' => 'Yayasan', 'status' => 'active']);
        $sekolahA = Sekolah::create(['lembaga_id' => $lembaga->id, 'name' => 'PAUD A', 'status' => 'active']);
        $sekolahB = Sekolah::create(['lembaga_id' => $lembaga->id, 'name' => 'PAUD B', 'status' => 'active']);

        $ortu = User::factory()->create(['sekolah_id' => null, 'lembaga_id' => null]);
        $ortu->assignRole('Orang Tua');

        Anak::withoutSekolahScope()->create([
            'user_id' => $ortu->id,
            'sekolah_id' => $sekolahA->id,
            'name' => 'Anak A',
            'status' => 'approved',
        ]);
        Anak::withoutSekolahScope()->create([
            'user_id' => $ortu->id,
            'sekolah_id' => $sekolahB->id,
            'name' => 'Anak B',
            'status' => 'approved',
        ]);

        $this->actingAs($ortu)
            ->post(route('orangtua.active-sekolah.update'), ['sekolah_id' => $sekolahB->id])
            ->assertRedirect();

        $this->assertSame($sekolahB->id, (int) session('ortu_active_sekolah_id'));
    }

    public function test_orang_tua_cannot_switch_to_sekolah_without_approved_anak(): void
    {
        $lembaga = Lembaga::create(['name' => 'Yayasan', 'status' => 'active']);
        $sekolahA = Sekolah::create(['lembaga_id' => $lembaga->id, 'name' => 'PAUD A', 'status' => 'active']);
        $sekolahB = Sekolah::create(['lembaga_id' => $lembaga->id, 'name' => 'PAUD B', 'status' => 'active']);

        $ortu = User::factory()->create(['sekolah_id' => null, 'lembaga_id' => null]);
        $ortu->assignRole('Orang Tua');

        Anak::withoutSekolahScope()->create([
            'user_id' => $ortu->id,
            'sekolah_id' => $sekolahA->id,
            'name' => 'Anak A',
            'status' => 'approved',
        ]);

        $this->actingAs($ortu)
            ->post(route('orangtua.active-sekolah.update'), ['sekolah_id' => $sekolahB->id])
            ->assertForbidden();
    }

    public function test_orang_tua_cannot_switch_to_suspended_sekolah(): void
    {
        $lembaga = Lembaga::create(['name' => 'Yayasan', 'status' => 'active']);
        $sekolahA = Sekolah::create(['lembaga_id' => $lembaga->id, 'name' => 'PAUD A', 'status' => 'active']);
        $sekolahB = Sekolah::create(['lembaga_id' => $lembaga->id, 'name' => 'PAUD B', 'status' => 'suspended']);

        $ortu = User::factory()->create(['sekolah_id' => null, 'lembaga_id' => null]);
        $ortu->assignRole('Orang Tua');

        Anak::withoutSekolahScope()->create([
            'user_id' => $ortu->id,
            'sekolah_id' => $sekolahA->id,
            'name' => 'Anak A',
            'status' => 'approved',
        ]);
        Anak::withoutSekolahScope()->create([
            'user_id' => $ortu->id,
            'sekolah_id' => $sekolahB->id,
            'name' => 'Anak B',
            'status' => 'approved',
        ]);

        $this->actingAs($ortu)
            ->post(route('orangtua.active-sekolah.update'), ['sekolah_id' => $sekolahB->id])
            ->assertForbidden();
    }
}
