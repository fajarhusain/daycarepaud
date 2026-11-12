<?php

namespace Tests\Feature\Admin;

use App\Models\Akun;
use App\Models\AkuntansiSetting;
use App\Models\GajiPengajar;
use App\Models\Lembaga;
use App\Models\Pengajar;
use App\Models\Sekolah;
use App\Models\User;
use App\Services\GajiGuruService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class GajiGuruTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ValidateCsrfToken::class);

        Role::firstOrCreate(['name' => 'Admin Sekolah', 'guard_name' => 'web']);
    }

    private function assignRoleForSchool(User $user, string $role, int $sekolahId): void
    {
        $registrar = app(PermissionRegistrar::class);
        $previous = $registrar->getPermissionsTeamId();

        $registrar->setPermissionsTeamId($sekolahId);
        $user->assignRole($role);
        $registrar->setPermissionsTeamId($previous);
        $registrar->forgetCachedPermissions();
    }

    /** @return array{admin: User, sekolah: Sekolah, pengajar: Pengajar, kas: Akun, beban: Akun} */
    private function fixtures(): array
    {
        $lembaga = Lembaga::create([
            'name' => 'Yayasan Gaji Test',
            'status' => Lembaga::STATUS_ACTIVE,
        ]);

        $sekolah = Sekolah::create([
            'lembaga_id' => $lembaga->id,
            'name' => 'PAUD Gaji Test',
            'address' => 'Jl. Test',
            'phone' => '021',
            'status' => Sekolah::STATUS_ACTIVE,
        ]);

        $admin = User::factory()->create([
            'email' => 'admin-gaji@test.com',
            'password' => Hash::make('password'),
            'lembaga_id' => $lembaga->id,
            'sekolah_id' => $sekolah->id,
        ]);
        $this->assignRoleForSchool($admin, 'Admin Sekolah', $sekolah->id);

        $guruUser = User::factory()->create([
            'email' => 'guru-gaji@test.com',
            'password' => Hash::make('password'),
            'sekolah_id' => $sekolah->id,
        ]);

        $pengajar = Pengajar::create([
            'user_id' => $guruUser->id,
            'sekolah_id' => $sekolah->id,
            'name' => 'Guru Test',
        ]);

        $kas = Akun::create([
            'sekolah_id' => $sekolah->id,
            'tipe' => 'sistem',
            'kode' => '1101',
            'nama' => 'Kas Test',
            'jenis' => 'Assets',
            'saldo_normal' => 'debit',
            'is_aktif' => true,
        ]);

        $beban = Akun::create([
            'sekolah_id' => $sekolah->id,
            'tipe' => 'sistem',
            'kode' => '5101',
            'nama' => 'Beban Gaji Test',
            'jenis' => 'Beban',
            'saldo_normal' => 'debit',
            'is_aktif' => true,
        ]);

        AkuntansiSetting::create([
            'sekolah_id' => $sekolah->id,
            'metode_pencatatan' => 'cash',
            'akun_kas_id' => $kas->id,
            'akun_untuk_in' => $kas->id,
            'akun_untuk_out' => $beban->id,
            'akun_beban_gaji_id' => $beban->id,
            'akun_kas_gaji_id' => $kas->id,
        ]);

        return compact('admin', 'sekolah', 'pengajar', 'kas', 'beban');
    }

    /** @return array<string, mixed> */
    private function slipPayload(array $f, int $bulan = 10, int $tahun = 2026): array
    {
        return [
            'pengajar_id' => $f['pengajar']->id,
            'periode_bulan' => $bulan,
            'periode_tahun' => $tahun,
            'komponen' => [
                ['nama' => 'Gaji Pokok', 'jenis' => 'tambah', 'nominal' => 1_500_000],
                ['nama' => 'Potongan', 'jenis' => 'potong', 'nominal' => 50_000],
            ],
        ];
    }

    public function test_admin_can_view_gaji_guru_index(): void
    {
        $f = $this->fixtures();

        $this->actingAs($f['admin'])
            ->get(route('admin.gaji-guru.index'))
            ->assertOk()
            ->assertSee('Gaji Guru');
    }

    public function test_admin_can_create_draft_slip(): void
    {
        $f = $this->fixtures();

        $this->actingAs($f['admin'])
            ->post(route('admin.gaji-guru.store'), $this->slipPayload($f))
            ->assertRedirect();

        $this->assertDatabaseHas('gaji_pengajars', [
            'pengajar_id' => $f['pengajar']->id,
            'status' => GajiPengajar::STATUS_DRAFT,
            'total_gaji' => 1_450_000,
        ]);
    }

    public function test_duplicate_slip_per_period_is_rejected(): void
    {
        $f = $this->fixtures();
        $payload = $this->slipPayload($f);

        $this->actingAs($f['admin'])->post(route('admin.gaji-guru.store'), $payload);

        $this->actingAs($f['admin'])
            ->post(route('admin.gaji-guru.store'), $payload)
            ->assertSessionHasErrors('pengajar_id');
    }

    public function test_approve_creates_cashflow_and_jurnal(): void
    {
        $f = $this->fixtures();
        $this->actingAs($f['admin'])->post(route('admin.gaji-guru.store'), $this->slipPayload($f));

        $gaji = GajiPengajar::first();

        $this->actingAs($f['admin'])
            ->patch(route('admin.gaji-guru.approve', $gaji))
            ->assertRedirect(route('admin.gaji-guru.show', $gaji));

        $gaji->refresh();
        $this->assertSame(GajiPengajar::STATUS_APPROVED, $gaji->status);
        $this->assertNotNull($gaji->cashflow_id);
        $this->assertNotNull($gaji->jurnal_id);

        $this->assertDatabaseHas('cashflows', [
            'id' => $gaji->cashflow_id,
            'type' => 'out',
            'amount' => 1_450_000,
        ]);

        $this->assertDatabaseHas('jurnals', ['id' => $gaji->jurnal_id]);
    }

    public function test_batalkan_approve_removes_cashflow_and_jurnal(): void
    {
        $f = $this->fixtures();
        $this->actingAs($f['admin'])->post(route('admin.gaji-guru.store'), $this->slipPayload($f));
        $gaji = GajiPengajar::first();
        $this->actingAs($f['admin'])->patch(route('admin.gaji-guru.approve', $gaji));
        $gaji->refresh();

        $cashflowId = $gaji->cashflow_id;
        $jurnalId = $gaji->jurnal_id;

        $this->actingAs($f['admin'])
            ->patch(route('admin.gaji-guru.batalkan', $gaji))
            ->assertRedirect(route('admin.gaji-guru.show', $gaji));

        $gaji->refresh();
        $this->assertTrue($gaji->isDraft());
        $this->assertNull($gaji->cashflow_id);
        $this->assertDatabaseMissing('cashflows', ['id' => $cashflowId]);
        $this->assertDatabaseMissing('jurnals', ['id' => $jurnalId]);
    }

    public function test_update_draft_slip_succeeds(): void
    {
        $f = $this->fixtures();
        $this->actingAs($f['admin'])->post(route('admin.gaji-guru.store'), $this->slipPayload($f));
        $gaji = GajiPengajar::first();

        $payload = $this->slipPayload($f);
        $payload['komponen'][0]['nominal'] = 2_000_000;

        $this->actingAs($f['admin'])
            ->put(route('admin.gaji-guru.update', $gaji), $payload)
            ->assertRedirect(route('admin.gaji-guru.show', $gaji));

        $this->assertSame(1_950_000.0, (float) $gaji->fresh()->total_gaji);
    }

    public function test_update_and_delete_approved_slip_is_blocked(): void
    {
        $f = $this->fixtures();
        $this->actingAs($f['admin'])->post(route('admin.gaji-guru.store'), $this->slipPayload($f));
        $gaji = GajiPengajar::first();
        $this->actingAs($f['admin'])->patch(route('admin.gaji-guru.approve', $gaji));

        $this->actingAs($f['admin'])
            ->get(route('admin.gaji-guru.edit', $gaji))
            ->assertForbidden();

        $this->actingAs($f['admin'])
            ->delete(route('admin.gaji-guru.destroy', $gaji))
            ->assertRedirect();

        $this->assertDatabaseHas('gaji_pengajars', ['id' => $gaji->id, 'status' => GajiPengajar::STATUS_APPROVED]);
    }

    public function test_gaji_kas_fallbacks_to_akun_kas_when_kas_gaji_null(): void
    {
        $f = $this->fixtures();
        AkuntansiSetting::where('sekolah_id', $f['sekolah']->id)->update(['akun_kas_gaji_id' => null]);

        $setting = AkuntansiSetting::where('sekolah_id', $f['sekolah']->id)->firstOrFail();
        $this->assertTrue($setting->lengkapUntukGajiGuru());
        $this->assertSame($f['kas']->id, $setting->akunUntukGajiGuru()['akun_kas_id']);
    }

    public function test_create_redirects_when_gaji_setting_incomplete(): void
    {
        $f = $this->fixtures();
        AkuntansiSetting::where('sekolah_id', $f['sekolah']->id)->update(['akun_beban_gaji_id' => null]);

        $this->actingAs($f['admin'])
            ->get(route('admin.gaji-guru.create'))
            ->assertRedirect(route('admin.akuntansi-setting.index'));
    }

    public function test_total_calculation_from_komponen(): void
    {
        $service = app(GajiGuruService::class);

        $total = $service->hitungTotal([
            ['nama' => 'A', 'jenis' => 'tambah', 'nominal' => 100],
            ['nama' => 'B', 'jenis' => 'tambah', 'nominal' => 50],
            ['nama' => 'C', 'jenis' => 'potong', 'nominal' => 30],
        ]);

        $this->assertSame(120.0, $total);
    }
}
