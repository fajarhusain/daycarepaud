<?php

namespace Tests\Feature\MonevGuru;

use App\Models\AiSetting;
use App\Models\Lembaga;
use App\Models\MonevGuruKriteria;
use App\Models\Pengajar;
use App\Models\Sekolah;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\SeedsAiTokens;
use Tests\TestCase;

class AiMonevGuruSuggestionTest extends TestCase
{
    use RefreshDatabase;
    use SeedsAiTokens;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    /** @return array{sekolah: Sekolah, admin: User, pengajar: Pengajar, kriteria: MonevGuruKriteria} */
    protected function createFixtures(): array
    {
        $lembaga = Lembaga::first();
        $sekolah = Sekolah::first();

        $admin = User::factory()->create([
            'email' => 'admin-monev-guru-ai@test.com',
            'password' => Hash::make('password'),
            'lembaga_id' => $lembaga->id,
            'sekolah_id' => $sekolah->id,
        ]);
        $admin->assignRole('Admin Sekolah');

        $guruUser = User::factory()->create([
            'email' => 'guru-ai-monev@test.com',
            'sekolah_id' => $sekolah->id,
        ]);
        $guruUser->assignRole('Pengajar');

        $pengajar = Pengajar::create([
            'user_id' => $guruUser->id,
            'sekolah_id' => $sekolah->id,
            'name' => 'Guru AI Test',
        ]);

        MonevGuruKriteria::seedDefaultsForSekolah($sekolah->id);
        $kriteria = MonevGuruKriteria::where('sekolah_id', $sekolah->id)->where('is_active', true)->first();

        AiSetting::create([
            'lembaga_id' => $lembaga->id,
            'ai_provider' => 'sumopod',
            'ai_api_key' => 'test-api-key',
            'ai_model' => 'gpt-4o-mini',
        ]);

        $this->seedAiTokens($sekolah, 10, $admin);

        return compact('sekolah', 'admin', 'guruUser', 'pengajar', 'kriteria');
    }

    public function test_admin_can_get_monev_guru_ai_suggestions(): void
    {
        $f = $this->createFixtures();

        Http::fake([
            '*/chat/completions' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => "1. Guru menunjukkan perencanaan yang matang.\n2. Dokumentasi kegiatan lengkap dan teratur.\n3. Perlu meningkatkan variasi metode pembelajaran.",
                    ],
                ]],
            ], 200),
        ]);

        $response = $this->actingAs($f['admin'])->postJson(route('admin.ai.monev-guru-suggestions'), [
            'pengajar_id' => $f['pengajar']->id,
            'kriteria_id' => $f['kriteria']->id,
            'skor' => 75,
            'periode_mulai' => '2026-01-01',
            'periode_selesai' => '2026-06-30',
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['suggestions', 'token_balance']);
        $this->assertCount(3, $response->json('suggestions'));
    }

    public function test_monev_guru_ai_requires_skor(): void
    {
        $f = $this->createFixtures();

        $response = $this->actingAs($f['admin'])->postJson(route('admin.ai.monev-guru-suggestions'), [
            'pengajar_id' => $f['pengajar']->id,
            'kriteria_id' => $f['kriteria']->id,
            'skor' => 150,
        ]);

        $response->assertUnprocessable();
    }

    public function test_pengajar_cannot_access_monev_guru_ai_suggestions(): void
    {
        $f = $this->createFixtures();

        $this->actingAs($f['guruUser'])
            ->postJson(route('admin.ai.monev-guru-suggestions'), [
                'pengajar_id' => $f['pengajar']->id,
                'kriteria_id' => $f['kriteria']->id,
                'skor' => 80,
            ])
            ->assertForbidden();
    }

    public function test_admin_can_get_ringkasan_suggestions_from_penilaian(): void
    {
        $f = $this->createFixtures();

        Http::fake([
            '*/chat/completions' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => '{"catatan":["Ringkasan kinerja guru sangat baik.","Perlu konsistensi dokumentasi.","Komunikasi dengan orang tua efektif."],"rekomendasi":["Ikuti pelatihan kurikulum.","Buat jadwal refleksi mingguan.","Pertahankan inovasi pembelajaran."]}',
                    ],
                ]],
            ], 200),
        ]);

        $response = $this->actingAs($f['admin'])->postJson(route('admin.ai.monev-guru-ringkasan-suggestions'), [
            'pengajar_id' => $f['pengajar']->id,
            'periode_mulai' => '2026-01-01',
            'periode_selesai' => '2026-06-30',
            'items' => [
                [
                    'kriteria_id' => $f['kriteria']->id,
                    'skor' => 85,
                    'catatan' => 'Sangat baik dalam perencanaan.',
                ],
            ],
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['catatan_suggestions', 'rekomendasi_suggestions', 'token_balance']);
        $this->assertCount(3, $response->json('catatan_suggestions'));
        $this->assertCount(3, $response->json('rekomendasi_suggestions'));
    }
}
