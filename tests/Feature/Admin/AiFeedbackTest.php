<?php

namespace Tests\Feature\Admin;

use App\Models\AiSetting;
use App\Models\Anak;
use App\Models\Kegiatan;
use App\Models\Kelas;
use App\Models\Lembaga;
use App\Models\Matrikulasi;
use App\Models\Pengajar;
use App\Models\Sekolah;
use App\Models\SekolahAiToken;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\SeedsAiTokens;
use Tests\TestCase;

class AiFeedbackTest extends TestCase
{
    use RefreshDatabase;
    use SeedsAiTokens;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    /**
     * @return array{lembaga: Lembaga, sekolah: Sekolah, sekolah2: Sekolah, admin: User, adminOther: User, anak: Anak, kegiatan: Kegiatan, matrikulasi: Matrikulasi}
     */
    protected function createFixtures(): array
    {
        $lembaga = Lembaga::first();
        $sekolah = Sekolah::first();
        $sekolah2 = Sekolah::skip(1)->first() ?? Sekolah::create([
            'lembaga_id' => $lembaga->id,
            'name' => 'Sekolah Lain Feedback',
            'address' => 'Jl. Lain',
            'phone' => '021-33333333',
        ]);

        $admin = User::factory()->create([
            'email' => 'admin-feedback@test.com',
            'password' => Hash::make('password'),
            'lembaga_id' => $lembaga->id,
            'sekolah_id' => $sekolah->id,
        ]);
        $admin->assignRole('Admin Sekolah');

        $pengajar = Pengajar::create([
            'user_id' => $admin->id,
            'sekolah_id' => $sekolah->id,
            'name' => 'Guru Feedback',
        ]);

        $kelas = Kelas::create([
            'sekolah_id' => $sekolah->id,
            'name' => 'Kelas Feedback',
            'wali_kelas_id' => $pengajar->id,
        ]);

        $anakUser = User::factory()->create(['email' => 'anak-feedback@test.com']);
        $anak = Anak::create([
            'user_id' => $anakUser->id,
            'sekolah_id' => $sekolah->id,
            'kelas_id' => $kelas->id,
            'name' => 'Anak Feedback',
            'status' => 'approved',
        ]);

        $matrikulasi = Matrikulasi::create([
            'sekolah_id' => $sekolah->id,
            'indicator' => 'Menyebut warna',
            'aspek' => 'Kognitif',
            'description' => 'Deskripsi',
        ]);

        $kegiatan = Kegiatan::create([
            'sekolah_id' => $sekolah->id,
            'pengajar_id' => $pengajar->id,
            'kelas_id' => $kelas->id,
            'date' => now(),
            'title' => 'Mengenal Warna',
        ]);
        $kegiatan->matrikulasis()->attach($matrikulasi->id);

        $adminOther = User::factory()->create([
            'email' => 'admin-feedback-other@test.com',
            'password' => Hash::make('password'),
            'lembaga_id' => $lembaga->id,
            'sekolah_id' => $sekolah2->id,
        ]);
        $adminOther->assignRole('Admin Sekolah');

        AiSetting::create([
            'lembaga_id' => $lembaga->id,
            'ai_provider' => 'sumopod',
            'ai_api_key' => encrypt('test-api-key'),
            'ai_model' => 'gpt-4o-mini',
        ]);

        $this->seedAiTokens($sekolah, 5, $admin);

        return compact(
            'lembaga',
            'sekolah',
            'sekolah2',
            'admin',
            'adminOther',
            'anak',
            'kegiatan',
            'matrikulasi'
        );
    }

    public function test_admin_can_request_feedback_suggestions_for_valid_scope(): void
    {
        $f = $this->createFixtures();

        Http::fake([
            'ai.sumopod.com/*' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => json_encode(['Saran satu', 'Saran dua'], JSON_UNESCAPED_UNICODE),
                    ],
                ]],
            ], 200),
        ]);

        $response = $this->actingAs($f['admin'])->postJson(route('admin.ai.feedback-suggestions'), [
            'anak_id' => $f['anak']->id,
            'kegiatan_id' => $f['kegiatan']->id,
            'matrikulasi_id' => $f['matrikulasi']->id,
            'score' => 'BSH',
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['suggestions', 'token_balance']);
        $this->assertSame(4, SekolahAiToken::query()->where('sekolah_id', $f['sekolah']->id)->value('balance'));
    }

    public function test_admin_cannot_request_feedback_for_anak_outside_school(): void
    {
        $f = $this->createFixtures();

        $response = $this->actingAs($f['adminOther'])->postJson(route('admin.ai.feedback-suggestions'), [
            'anak_id' => $f['anak']->id,
            'kegiatan_id' => $f['kegiatan']->id,
            'matrikulasi_id' => $f['matrikulasi']->id,
            'score' => 'BSH',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('error', 'Siswa tidak ditemukan atau tidak termasuk sekolah Anda.');
    }

    public function test_feedback_returns_token_exhausted_when_balance_zero(): void
    {
        $f = $this->createFixtures();

        SekolahAiToken::query()
            ->where('sekolah_id', $f['sekolah']->id)
            ->update(['balance' => 0]);

        $response = $this->actingAs($f['admin'])->postJson(route('admin.ai.feedback-suggestions'), [
            'anak_id' => $f['anak']->id,
            'kegiatan_id' => $f['kegiatan']->id,
            'matrikulasi_id' => $f['matrikulasi']->id,
            'score' => 'BSH',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('token_exhausted', true);
        $response->assertJsonPath('token_balance', 0);
    }
}
