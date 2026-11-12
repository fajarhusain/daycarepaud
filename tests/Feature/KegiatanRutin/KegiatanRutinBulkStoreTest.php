<?php

namespace Tests\Feature\KegiatanRutin;

use App\Models\Anak;
use App\Models\KegiatanRutin;
use App\Models\Kelas;
use App\Models\MasterKegiatanRutin;
use App\Models\Pengajar;
use App\Models\Sekolah;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class KegiatanRutinBulkStoreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    /**
     * @return array{
     *     sekolah: Sekolah,
     *     kelas: Kelas,
     *     pengajarUser: User,
     *     pengajar: Pengajar,
     *     anak1: Anak,
     *     anak2: Anak,
     *     master1: MasterKegiatanRutin,
     *     master2: MasterKegiatanRutin
     * }
     */
    protected function createFixtures(): array
    {
        $sekolah = Sekolah::first();

        $pengajarUser = User::factory()->create([
            'email' => 'guru-rutin@test.com',
            'password' => Hash::make('password'),
            'sekolah_id' => $sekolah->id,
        ]);
        $pengajarUser->assignRole('Pengajar');

        $pengajar = Pengajar::create([
            'user_id' => $pengajarUser->id,
            'sekolah_id' => $sekolah->id,
            'name' => 'Guru Rutin',
        ]);

        $kelas = Kelas::create([
            'sekolah_id' => $sekolah->id,
            'name' => 'Kelas Rutin A',
            'wali_kelas_id' => $pengajar->id,
        ]);

        $anakUser1 = User::factory()->create(['email' => 'siswa-rutin-1@test.com']);
        $anak1 = Anak::create([
            'user_id' => $anakUser1->id,
            'sekolah_id' => $sekolah->id,
            'kelas_id' => $kelas->id,
            'name' => 'Siswa Satu',
            'status' => 'approved',
        ]);

        $anakUser2 = User::factory()->create(['email' => 'siswa-rutin-2@test.com']);
        $anak2 = Anak::create([
            'user_id' => $anakUser2->id,
            'sekolah_id' => $sekolah->id,
            'kelas_id' => $kelas->id,
            'name' => 'Siswa Dua',
            'status' => 'approved',
        ]);

        $master1 = MasterKegiatanRutin::create([
            'sekolah_id' => $sekolah->id,
            'pengajar_id' => $pengajar->id,
            'nama_kegiatan' => 'Mengaji',
            'aspek' => 'Agama',
        ]);
        $master1->kelas()->attach($kelas->id);

        $master2 = MasterKegiatanRutin::create([
            'sekolah_id' => $sekolah->id,
            'pengajar_id' => $pengajar->id,
            'nama_kegiatan' => 'Membaca',
            'aspek' => 'Kognitif',
        ]);
        $master2->kelas()->attach($kelas->id);

        return compact('sekolah', 'kelas', 'pengajarUser', 'pengajar', 'anak1', 'anak2', 'master1', 'master2');
    }

    public function test_bulk_store_saves_multiple_students_and_skips_empty_status(): void
    {
        $f = $this->createFixtures();
        $tanggal = '2026-06-15';

        $response = $this->actingAs($f['pengajarUser'])->post(route('pengajar.kegiatan-rutin.store'), [
            'tanggal' => $tanggal,
            'kelas_id' => $f['kelas']->id,
            'rutin' => [
                $f['anak1']->id => [
                    $f['master1']->id => [
                        'aspek' => 'Agama',
                        'kegiatan' => 'Mengaji',
                        'status_pencapaian' => 'Lancar',
                    ],
                    $f['master2']->id => [
                        'aspek' => 'Kognitif',
                        'kegiatan' => 'Membaca',
                        'status_pencapaian' => '',
                    ],
                ],
                $f['anak2']->id => [
                    $f['master1']->id => [
                        'aspek' => 'Agama',
                        'kegiatan' => 'Mengaji',
                        'status_pencapaian' => 'Sangat Lancar',
                    ],
                    $f['master2']->id => [
                        'aspek' => 'Kognitif',
                        'kegiatan' => 'Membaca',
                        'status_pencapaian' => 'Belum Lancar',
                    ],
                ],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('kegiatan_rutins', [
            'anak_id' => $f['anak1']->id,
            'master_kegiatan_rutin_id' => $f['master1']->id,
            'tanggal' => $tanggal,
            'status_pencapaian' => 'Lancar',
        ]);

        $this->assertDatabaseHas('kegiatan_rutins', [
            'anak_id' => $f['anak2']->id,
            'master_kegiatan_rutin_id' => $f['master2']->id,
            'tanggal' => $tanggal,
            'status_pencapaian' => 'Belum Lancar',
        ]);

        $this->assertDatabaseMissing('kegiatan_rutins', [
            'anak_id' => $f['anak1']->id,
            'master_kegiatan_rutin_id' => $f['master2']->id,
            'tanggal' => $tanggal,
        ]);

        $this->assertSame(3, KegiatanRutin::where('kelas_id', $f['kelas']->id)->where('tanggal', $tanggal)->count());
    }

    public function test_index_shows_bulk_grid(): void
    {
        $f = $this->createFixtures();

        $response = $this->actingAs($f['pengajarUser'])->get(route('pengajar.kegiatan-rutin.index', [
            'kelas_id' => $f['kelas']->id,
            'tanggal' => '2026-06-15',
        ]));

        $response->assertOk();
        $response->assertSee('Simpan Semua');
        $response->assertSee('Salin Kemarin');
        $response->assertSee('Siswa Satu');
        $response->assertSee('Mengaji');
    }
}
