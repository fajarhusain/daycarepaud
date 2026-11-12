<?php

namespace Tests\Feature\Admin;

use App\Models\Anak;
use App\Models\BiayaBulananSekolah;
use App\Models\BiayaBulananSiswa;
use App\Models\PembayaranBulanan;
use App\Models\Sekolah;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BiayaBulananDestroyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_destroy_deletes_jenis_without_pembayaran(): void
    {
        $sekolah = Sekolah::first();
        $admin = User::where('email', 'admin@example.com')->first();

        $biaya = BiayaBulananSekolah::create([
            'sekolah_id' => $sekolah->id,
            'nama_biaya' => 'SPP Test',
            'nominal_default' => 100_000,
            'is_aktif' => true,
        ]);

        BiayaBulananSiswa::create([
            'sekolah_id' => $sekolah->id,
            'biaya_bulanan_sekolah_id' => $biaya->id,
            'anak_id' => Anak::withoutSekolahScope()->create([
                'user_id' => $admin->id,
                'sekolah_id' => $sekolah->id,
                'name' => 'Anak Test',
                'status' => 'approved',
            ])->id,
            'biaya_bulanan' => 0,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.biaya-bulanan.destroy', $biaya))
            ->assertRedirect(route('admin.biaya-bulanan.index'));

        $this->assertDatabaseMissing('biaya_bulanan_sekolahs', ['id' => $biaya->id]);
        $this->assertDatabaseCount('biaya_bulanan_siswas', 0);
    }

    public function test_destroy_with_pembayaran_deactivates_instead(): void
    {
        $sekolah = Sekolah::first();
        $admin = User::where('email', 'admin@example.com')->first();

        $biaya = BiayaBulananSekolah::create([
            'sekolah_id' => $sekolah->id,
            'nama_biaya' => 'Uang Pangkal',
            'nominal_default' => 500_000,
            'is_aktif' => true,
        ]);

        $anak = Anak::withoutSekolahScope()->create([
            'user_id' => $admin->id,
            'sekolah_id' => $sekolah->id,
            'name' => 'Anak Dua',
            'status' => 'approved',
        ]);

        PembayaranBulanan::create([
            'sekolah_id' => $sekolah->id,
            'anak_id' => $anak->id,
            'biaya_bulanan_sekolah_id' => $biaya->id,
            'periode_bulan' => 1,
            'periode_tahun' => 2026,
            'total_bayar' => 100_000,
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.biaya-bulanan.destroy', $biaya))
            ->assertRedirect(route('admin.biaya-bulanan.index'));

        $biaya->refresh();
        $this->assertFalse($biaya->is_aktif);
        $this->assertDatabaseHas('biaya_bulanan_sekolahs', ['id' => $biaya->id]);
    }
}
