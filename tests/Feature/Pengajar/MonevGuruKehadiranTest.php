<?php

namespace Tests\Feature\Pengajar;

use App\Models\AiSetting;
use App\Models\Lembaga;
use App\Models\MonevGuruKriteria;
use App\Models\Pengajar;
use App\Models\PresensiPengajar;
use App\Models\PresensiShift;
use App\Models\Sekolah;
use App\Models\SekolahJamKerja;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\Concerns\SeedsAiTokens;
use Tests\TestCase;

class MonevGuruKehadiranTest extends TestCase
{
    use RefreshDatabase;
    use SeedsAiTokens;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ValidateCsrfToken::class);

        // ponytail: RoleSeeder global pivot tidak cocok teams=true; assign per sekolah.
        Role::firstOrCreate(['name' => 'Admin Sekolah', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Pengajar', 'guard_name' => 'web']);
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

    /**
     * @return array{
     *     lembaga: Lembaga,
     *     sekolah: Sekolah,
     *     admin: User,
     *     pengajar: Pengajar,
     *     kriteriaKehadiran: MonevGuruKriteria,
     *     kriteriaLain: MonevGuruKriteria
     * }
     */
    private function fixtures(): array
    {
        $lembaga = Lembaga::create([
            'name' => 'Yayasan Monev Kehadiran',
            'status' => Lembaga::STATUS_ACTIVE,
        ]);

        $sekolah = Sekolah::create([
            'lembaga_id' => $lembaga->id,
            'name' => 'PAUD Monev Kehadiran',
            'address' => 'Jl. Test',
            'phone' => '021',
            'status' => Sekolah::STATUS_ACTIVE,
        ]);

        $admin = User::factory()->create([
            'email' => 'admin-monev-kehadiran@test.com',
            'password' => Hash::make('password'),
            'lembaga_id' => $lembaga->id,
            'sekolah_id' => $sekolah->id,
        ]);
        $this->assignRoleForSchool($admin, 'Admin Sekolah', $sekolah->id);

        $guruUser = User::factory()->create([
            'email' => 'guru-monev-kehadiran@test.com',
            'sekolah_id' => $sekolah->id,
        ]);
        $this->assignRoleForSchool($guruUser, 'Pengajar', $sekolah->id);

        $shift = PresensiShift::withoutSekolahScope()->create([
            'sekolah_id' => $sekolah->id,
            'name' => 'Reguler',
            'location_coordinate' => '-6.200000,106.816666',
            'radius_m' => 100,
            'is_default' => true,
            'sort_order' => 0,
        ]);

        $pengajar = Pengajar::create([
            'user_id' => $guruUser->id,
            'sekolah_id' => $sekolah->id,
            'presensi_shift_id' => $shift->id,
            'name' => 'Guru Kehadiran',
        ]);

        MonevGuruKriteria::seedDefaultsForSekolah($sekolah->id);

        $kriteriaKehadiran = MonevGuruKriteria::query()
            ->where('sekolah_id', $sekolah->id)
            ->where('nama', 'Kedisiplinan & kehadiran')
            ->firstOrFail();

        $kriteriaLain = MonevGuruKriteria::query()
            ->where('sekolah_id', $sekolah->id)
            ->where('nama', '!=', 'Kedisiplinan & kehadiran')
            ->firstOrFail();

        AiSetting::create([
            'lembaga_id' => $lembaga->id,
            'ai_provider' => 'sumopod',
            'ai_api_key' => 'test-api-key',
            'ai_model' => 'gpt-4o-mini',
        ]);

        $this->seedAiTokens($sekolah, 10, $admin);

        return compact('lembaga', 'sekolah', 'admin', 'pengajar', 'kriteriaKehadiran', 'kriteriaLain');
    }

    private function seedJadwalKerja(Sekolah $sekolah): void
    {
        $shift = PresensiShift::withoutSekolahScope()
            ->where('sekolah_id', $sekolah->id)
            ->where('is_default', true)
            ->firstOrFail();

        foreach (range(1, 7) as $hari) {
            $kerja = $hari <= 5;

            SekolahJamKerja::withoutSekolahScope()->updateOrCreate(
                [
                    'presensi_shift_id' => $shift->id,
                    'hari' => $hari,
                ],
                [
                    'sekolah_id' => $sekolah->id,
                    'is_kerja' => $kerja,
                    'jam_masuk' => $kerja ? '07:00:00' : null,
                    'jam_pulang' => $kerja ? '13:00:00' : null,
                    'toleransi_masuk_menit' => 10,
                    'toleransi_pulang_menit' => 10,
                ],
            );
        }
    }

    public function test_saran_ai_kriteria_kehadiran_menyertakan_rekap_presensi(): void
    {
        $f = $this->fixtures();
        $this->seedJadwalKerja($f['sekolah']);

        foreach (['2026-10-26', '2026-10-27', '2026-10-28'] as $tanggal) {
            PresensiPengajar::withoutSekolahScope()->create([
                'sekolah_id' => $f['sekolah']->id,
                'pengajar_id' => $f['pengajar']->id,
                'tanggal' => $tanggal,
                'hadir' => true,
                'status' => 'hadir',
                'check_in_at' => $tanggal.' 07:05:00',
                'check_out_at' => $tanggal.' 13:00:00',
                'sumber' => PresensiPengajar::SUMBER_SELF,
                'terlambat_masuk' => false,
                'telat_pulang' => false,
            ]);
        }

        Http::fake([
            '*/chat/completions' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => "1. Hadir 3 dari 3 hari kerja (100%); pertahankan ketepatan waktu.\n2. Tidak ada alpa pada periode ini; skor 80 selaras dengan kehadiran penuh.\n3. Lanjutkan disiplin check-in/out agar konsistensi terjaga.",
                    ],
                ]],
            ], 200),
        ]);

        $response = $this->actingAs($f['admin'])->postJson(route('admin.ai.monev-guru-suggestions'), [
            'pengajar_id' => $f['pengajar']->id,
            'kriteria_id' => $f['kriteriaKehadiran']->id,
            'skor' => 80,
            'periode_mulai' => '2026-10-26',
            'periode_selesai' => '2026-10-28',
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['suggestions', 'token_balance', 'rekap' => ['hari_kerja', 'hadir', 'persentase']]);
        $this->assertCount(3, $response->json('suggestions'));
        $this->assertSame(3, $response->json('rekap.hadir'));
        $this->assertSame(3, $response->json('rekap.hari_kerja'));

        Http::assertSent(function ($request) {
            $body = $request->data();
            $content = $body['messages'][0]['content'] ?? '';

            return str_contains($content, 'Data Presensi')
                && str_contains($content, 'Telat masuk')
                && str_contains($content, 'Pulang lebih awal')
                && str_contains($content, 'telat masuk')
                && str_contains($content, 'Skor (1–100) : 80');
        });
    }

    public function test_saran_ai_kriteria_lain_tidak_menyertakan_rekap(): void
    {
        $f = $this->fixtures();
        $this->seedJadwalKerja($f['sekolah']);

        Http::fake([
            '*/chat/completions' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => "1. Perencanaan matang.\n2. Media siap pakai.\n3. Pertahankan persiapan.",
                    ],
                ]],
            ], 200),
        ]);

        $response = $this->actingAs($f['admin'])->postJson(route('admin.ai.monev-guru-suggestions'), [
            'pengajar_id' => $f['pengajar']->id,
            'kriteria_id' => $f['kriteriaLain']->id,
            'skor' => 75,
            'periode_mulai' => '2026-10-26',
            'periode_selesai' => '2026-10-28',
        ]);

        $response->assertOk();
        $response->assertJsonMissingPath('rekap');

        Http::assertSent(function ($request) {
            $content = $request->data()['messages'][0]['content'] ?? '';

            return ! str_contains($content, 'Data Presensi');
        });
    }

    public function test_saran_ai_kehadiran_tanpa_periode_tetap_sukses_tanpa_rekap(): void
    {
        $f = $this->fixtures();

        Http::fake([
            '*/chat/completions' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => "1. Saran satu.\n2. Saran dua.\n3. Saran tiga.",
                    ],
                ]],
            ], 200),
        ]);

        $response = $this->actingAs($f['admin'])->postJson(route('admin.ai.monev-guru-suggestions'), [
            'pengajar_id' => $f['pengajar']->id,
            'kriteria_id' => $f['kriteriaKehadiran']->id,
            'skor' => 70,
        ]);

        $response->assertOk();
        $response->assertJsonMissingPath('rekap');
    }

    public function test_endpoint_kehadiran_khusus_sudah_dihapus(): void
    {
        $f = $this->fixtures();

        $this->actingAs($f['admin'])
            ->postJson('/admin/ai/monev-guru-kehadiran-suggestions', [
                'pengajar_id' => $f['pengajar']->id,
                'kriteria_id' => $f['kriteriaKehadiran']->id,
                'periode_mulai' => '2026-10-26',
                'periode_selesai' => '2026-10-28',
            ])
            ->assertNotFound();
    }
}
