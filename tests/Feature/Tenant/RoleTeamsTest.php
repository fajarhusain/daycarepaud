<?php

namespace Tests\Feature\Tenant;

use App\Models\Lembaga;
use App\Models\Sekolah;
use App\Support\TenantContext;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleTeamsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->seed(PermissionSeeder::class);
    }

    public function test_custom_role_scoped_per_sekolah(): void
    {
        $lembaga = Lembaga::create(['name' => 'Yayasan', 'status' => 'active']);
        $sekolahA = Sekolah::create(['lembaga_id' => $lembaga->id, 'name' => 'A', 'status' => 'active']);
        $sekolahB = Sekolah::create(['lembaga_id' => $lembaga->id, 'name' => 'B', 'status' => 'active']);

        Role::create(['name' => 'Bendahara', 'guard_name' => 'web', 'sekolah_id' => $sekolahA->id]);
        Role::create(['name' => 'Bendahara', 'guard_name' => 'web', 'sekolah_id' => $sekolahB->id]);

        TenantContext::setSekolahId($sekolahA->id);

        $this->assertSame(1, Role::where('name', 'Bendahara')->where('sekolah_id', $sekolahA->id)->count());
    }
}
