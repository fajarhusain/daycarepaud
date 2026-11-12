<?php

namespace Tests\Feature\Admin;

use App\Models\Akun;
use App\Models\AkuntansiSetting;
use App\Models\AkuntansiTabunganAkun;
use App\Models\Cashflow;
use App\Models\Sekolah;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TabunganInvestasiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_settings_and_setor_create_cashflow(): void
    {
        $sekolah = Sekolah::first();
        $admin = User::where('email', 'admin@example.com')->first();

        $kas = Akun::create([
            'sekolah_id' => $sekolah->id,
            'tipe' => 'sistem',
            'kode' => '1101',
            'nama' => 'Kas',
            'jenis' => 'Assets',
            'saldo_normal' => 'debit',
            'is_aktif' => true,
        ]);

        $tabungan = Akun::create([
            'sekolah_id' => $sekolah->id,
            'tipe' => 'sistem',
            'kode' => '1106',
            'nama' => 'Persediaan Tabungan Test',
            'jenis' => 'Assets',
            'kategori_arus_kas' => 'investasi',
            'saldo_normal' => 'debit',
            'is_aktif' => true,
        ]);

        $pendapatan = Akun::create([
            'sekolah_id' => $sekolah->id,
            'tipe' => 'sistem',
            'kode' => '4201',
            'nama' => 'Pendapatan Bunga',
            'jenis' => 'Pendapatan',
            'saldo_normal' => 'kredit',
            'is_aktif' => true,
        ]);

        $beban = Akun::create([
            'sekolah_id' => $sekolah->id,
            'tipe' => 'rkas',
            'kode' => '5101',
            'nama' => 'Beban Gaji',
            'jenis' => 'Beban',
            'saldo_normal' => 'debit',
            'is_aktif' => true,
        ]);

        AkuntansiSetting::create([
            'sekolah_id' => $sekolah->id,
            'metode_pencatatan' => 'cash',
            'akun_kas_id' => $kas->id,
            'akun_untuk_in' => $pendapatan->id,
            'akun_untuk_out' => $beban->id,
        ]);

        $this->actingAs($admin)->put(route('admin.tabungan-investasi.settings'), [
            'akun_id' => [$tabungan->id],
        ])->assertRedirect(route('admin.tabungan-investasi.index'));

        $this->assertDatabaseHas('akuntansi_tabungan_akuns', [
            'sekolah_id' => $sekolah->id,
            'akun_id' => $tabungan->id,
        ]);

        $this->actingAs($admin)->post(route('admin.tabungan-investasi.mutasi'), [
            'aksi' => 'setor',
            'akun_tabungan_id' => $tabungan->id,
            'akun_rekening_id' => $kas->id,
            'amount' => 250_000,
            'date' => now()->toDateString(),
            'description' => 'Setor tabungan test',
        ])->assertRedirect();

        $this->assertDatabaseHas('cashflows', [
            'sekolah_id' => $sekolah->id,
            'type' => 'out',
            'amount' => 250_000,
            'akun_id' => $kas->id,
            'akun_lawan_id' => $tabungan->id,
        ]);

        $this->assertSame(1, Cashflow::where('description', 'Setor tabungan test')->count());
    }
}
