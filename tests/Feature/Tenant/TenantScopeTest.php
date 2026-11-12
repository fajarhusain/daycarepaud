<?php

namespace Tests\Feature\Tenant;

use App\Models\Anak;
use App\Models\Lembaga;
use App\Models\Sekolah;
use App\Models\User;
use App\Support\TenantContext;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantScopeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_global_scope_isolates_anak_by_tenant_context(): void
    {
        $lembaga = Lembaga::create(['name' => 'Yayasan A', 'status' => 'active']);
        $sekolahA = Sekolah::create(['lembaga_id' => $lembaga->id, 'name' => 'Sekolah A', 'status' => 'active']);
        $sekolahB = Sekolah::create(['lembaga_id' => $lembaga->id, 'name' => 'Sekolah B', 'status' => 'active']);

        $ortuA = User::factory()->create(['sekolah_id' => null]);
        $ortuA->assignRole('Orang Tua');

        Anak::withoutSekolahScope()->create([
            'user_id' => $ortuA->id,
            'sekolah_id' => $sekolahA->id,
            'name' => 'Anak A',
            'status' => 'approved',
        ]);
        Anak::withoutSekolahScope()->create([
            'user_id' => $ortuA->id,
            'sekolah_id' => $sekolahB->id,
            'name' => 'Anak B',
            'status' => 'approved',
        ]);

        TenantContext::setSekolahId($sekolahA->id);

        $this->assertSame(1, Anak::count());
        $this->assertSame('Anak A', Anak::first()->name);
    }

    public function test_bypass_scope_sees_all_anak_records(): void
    {
        $lembaga = Lembaga::create(['name' => 'Yayasan A', 'status' => 'active']);
        $sekolahA = Sekolah::create(['lembaga_id' => $lembaga->id, 'name' => 'Sekolah A', 'status' => 'active']);
        $sekolahB = Sekolah::create(['lembaga_id' => $lembaga->id, 'name' => 'Sekolah B', 'status' => 'active']);

        $ortu = User::factory()->create(['sekolah_id' => null]);
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

        TenantContext::bypassScope();

        $this->assertSame(2, Anak::count());
    }
}
