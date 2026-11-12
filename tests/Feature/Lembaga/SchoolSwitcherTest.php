<?php

namespace Tests\Feature\Lembaga;

use App\Models\Lembaga;
use App\Models\Sekolah;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolSwitcherTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->seed(PermissionSeeder::class);
    }

    public function test_lembaga_can_switch_active_sekolah_and_access_admin(): void
    {
        $lembaga = Lembaga::firstOrFail();
        $sekolah = Sekolah::where('lembaga_id', $lembaga->id)->firstOrFail();

        $user = User::factory()->create([
            'lembaga_id' => $lembaga->id,
            'sekolah_id' => null,
        ]);
        $user->assignRole('Lembaga');

        $this->actingAs($user)
            ->post(route('lembaga.active-sekolah.update'), ['sekolah_id' => $sekolah->id])
            ->assertRedirect();

        $this->actingAs($user)
            ->get(route('admin.anak.index'))
            ->assertOk();
    }

    public function test_lembaga_blocked_from_admin_without_active_sekolah(): void
    {
        $lembaga = Lembaga::firstOrFail();
        $user = User::factory()->create([
            'lembaga_id' => $lembaga->id,
            'sekolah_id' => null,
        ]);
        $user->assignRole('Lembaga');

        $this->actingAs($user)
            ->get(route('admin.anak.index'))
            ->assertForbidden();
    }
}
