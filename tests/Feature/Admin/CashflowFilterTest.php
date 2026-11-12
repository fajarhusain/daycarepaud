<?php

namespace Tests\Feature\Admin;

use App\Models\Akun;
use App\Models\Cashflow;
use App\Models\Lembaga;
use App\Models\Sekolah;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashflowFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_cashflow_index_filters_by_type_and_kelompok(): void
    {
        $sekolah = Sekolah::first();
        $admin = User::where('email', 'admin@example.com')->first();

        $kas = Akun::create([
            'sekolah_id' => $sekolah->id,
            'tipe' => 'sistem',
            'kode' => 'T.KAS',
            'nama' => 'Kas Test',
            'jenis' => 'Assets',
            'saldo_normal' => 'debit',
            'is_aktif' => true,
        ]);

        $bebanA = Akun::create([
            'sekolah_id' => $sekolah->id,
            'tipe' => 'rkas',
            'kode' => 'B.A',
            'nama' => 'Beban A',
            'jenis' => 'Beban',
            'snp' => 'Kelompok Alpha',
            'saldo_normal' => 'debit',
            'is_aktif' => true,
        ]);

        $bebanB = Akun::create([
            'sekolah_id' => $sekolah->id,
            'tipe' => 'rkas',
            'kode' => 'B.B',
            'nama' => 'Beban B',
            'jenis' => 'Beban',
            'snp' => 'Kelompok Beta',
            'saldo_normal' => 'debit',
            'is_aktif' => true,
        ]);

        $date = now()->startOfMonth();

        Cashflow::create([
            'sekolah_id' => $sekolah->id,
            'date' => $date,
            'type' => 'out',
            'amount' => 100_000,
            'description' => 'Out Alpha',
            'akun_id' => $kas->id,
            'akun_lawan_id' => $bebanA->id,
        ]);

        Cashflow::create([
            'sekolah_id' => $sekolah->id,
            'date' => $date,
            'type' => 'in',
            'amount' => 50_000,
            'description' => 'In Beta',
            'akun_id' => $kas->id,
            'akun_lawan_id' => $bebanB->id,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.cashflow.index', [
            'bulan' => $date->month,
            'tahun' => $date->year,
            'type' => 'out',
            'kelompok' => 'Kelompok Alpha',
        ]));

        $response->assertOk();
        $response->assertSee('Out Alpha');
        $response->assertDontSee('In Beta');
    }

    public function test_cashflow_index_without_period_shows_all_months(): void
    {
        $sekolah = Sekolah::first();
        $admin = User::where('email', 'admin@example.com')->first();

        Cashflow::create([
            'sekolah_id' => $sekolah->id,
            'date' => now()->startOfMonth(),
            'type' => 'in',
            'amount' => 10_000,
            'description' => 'Bulan ini',
        ]);

        Cashflow::create([
            'sekolah_id' => $sekolah->id,
            'date' => now()->subMonth()->startOfMonth(),
            'type' => 'in',
            'amount' => 20_000,
            'description' => 'Bulan lalu',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.cashflow.index'));

        $response->assertOk();
        $response->assertSee('Bulan ini');
        $response->assertSee('Bulan lalu');
    }
}
