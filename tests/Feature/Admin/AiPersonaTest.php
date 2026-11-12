<?php

namespace Tests\Feature\Admin;

use App\Models\AiSetting;
use App\Models\Lembaga;
use App\Models\Pengajar;
use App\Models\Sekolah;
use App\Models\SekolahAiPersona;
use App\Models\SekolahAiToken;
use App\Models\User;
use App\Services\OrangTuaChatContextBuilder;
use App\Support\AiPersonaScope;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\SeedsAiTokens;
use Tests\TestCase;

class AiPersonaTest extends TestCase
{
    use RefreshDatabase;
    use SeedsAiTokens;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    /**
     * @return array{lembaga: Lembaga, sekolah: Sekolah, sekolah2: Sekolah, admin: User, adminOther: User}
     */
    protected function createFixtures(): array
    {
        $lembaga = Lembaga::first();
        $sekolah = Sekolah::first();
        $sekolah2 = Sekolah::skip(1)->first() ?? Sekolah::create([
            'lembaga_id' => $lembaga->id,
            'name' => 'Sekolah Lain',
            'address' => 'Jl. Lain',
            'phone' => '021-22222222',
        ]);

        $admin = User::factory()->create([
            'email' => 'admin-persona@test.com',
            'password' => Hash::make('password'),
            'lembaga_id' => $lembaga->id,
            'sekolah_id' => $sekolah->id,
        ]);
        $admin->assignRole('Admin Sekolah');

        Pengajar::create([
            'user_id' => $admin->id,
            'sekolah_id' => $sekolah->id,
            'name' => 'Admin Persona',
        ]);

        $adminOther = User::factory()->create([
            'email' => 'admin-persona-other@test.com',
            'password' => Hash::make('password'),
            'lembaga_id' => $lembaga->id,
            'sekolah_id' => $sekolah2->id,
        ]);
        $adminOther->assignRole('Admin Sekolah');

        Pengajar::create([
            'user_id' => $adminOther->id,
            'sekolah_id' => $sekolah2->id,
            'name' => 'Admin Other',
        ]);

        AiSetting::create([
            'lembaga_id' => $lembaga->id,
            'ai_provider' => 'sumopod',
            'ai_api_key' => encrypt('test-api-key'),
            'ai_model' => 'gpt-4o-mini',
        ]);

        $this->seedAiTokens($sekolah, 10, $admin);

        return compact('lembaga', 'sekolah', 'sekolah2', 'admin', 'adminOther');
    }

    public function test_admin_sekolah_can_view_persona_page_with_tabs(): void
    {
        $fixtures = $this->createFixtures();

        $response = $this->actingAs($fixtures['admin'])
            ->get(route('admin.ai-persona.index'));

        $response->assertOk();
        $response->assertSee('Pengaturan AI');
        $response->assertSee('Chat Orang Tua');
        $response->assertSee('Monev');
        $response->assertSee('Feedback Pencapaian');
        $response->assertSee($fixtures['sekolah']->name);
    }

    public function test_admin_sekolah_can_save_persona_per_scope(): void
    {
        $fixtures = $this->createFixtures();

        foreach (AiPersonaScope::all() as $scope) {
            $response = $this->actingAs($fixtures['admin'])
                ->post(route('admin.ai-persona.update'), [
                    'scope' => $scope,
                    'name' => 'Persona '.$scope,
                    'role_title' => AiPersonaScope::defaultRoleTitle($scope),
                    'description' => 'Deskripsi '.$scope,
                    'gender' => 'perempuan',
                    'age' => 30,
                    'dialog_language' => 'Bahasa Indonesia',
                    'personality_traits' => 'Ramah dan sabar',
                    'communication_style' => 'Bahasa sederhana',
                    'behavior_guidelines' => 'Jawab singkat',
                    'background' => 'Guru PAUD berpengalaman',
                    'is_active' => '1',
                ]);

            $response->assertRedirect(route('admin.ai-persona.index', ['tab' => $scope]));
        }

        $this->assertDatabaseCount('sekolah_ai_personas', 3);
        $this->assertDatabaseHas('sekolah_ai_personas', [
            'sekolah_id' => $fixtures['sekolah']->id,
            'scope' => AiPersonaScope::MONEV,
            'name' => 'Persona '.AiPersonaScope::MONEV,
            'is_active' => true,
        ]);
    }

    public function test_admin_sekolah_can_generate_persona_per_scope(): void
    {
        $fixtures = $this->createFixtures();

        Http::fake([
            'ai.sumopod.com/*' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => json_encode([
                            'name' => 'Bu Ani',
                            'role_title' => 'Asisten Chat Orang Tua PAUD',
                            'description' => 'Asisten hangat untuk orang tua',
                            'gender' => 'perempuan',
                            'age' => 32,
                            'dialog_language' => 'Bahasa Indonesia',
                            'personality_traits' => 'Ramah dan suportif',
                            'communication_style' => 'Natural dan jelas',
                            'behavior_guidelines' => 'Jawab singkat',
                            'background' => 'Pengajar PAUD',
                        ], JSON_UNESCAPED_UNICODE),
                    ],
                ]],
            ], 200),
        ]);

        $response = $this->actingAs($fixtures['admin'])
            ->postJson(route('admin.ai-persona.generate'), [
                'scope' => AiPersonaScope::CHAT_ORANGTUA,
                'brief' => 'PAUD dengan fokus karakter',
            ]);

        $response->assertOk();
        $response->assertJsonPath('ok', true);
        $response->assertJsonPath('fields.name', 'Bu Ani');

        $this->assertNotNull(
            SekolahAiPersona::forScope($fixtures['sekolah']->id, AiPersonaScope::CHAT_ORANGTUA)?->ai_generated_at
        );
    }

    public function test_generate_fails_when_ai_not_configured(): void
    {
        $fixtures = $this->createFixtures();
        AiSetting::query()->delete();

        $response = $this->actingAs($fixtures['admin'])
            ->postJson(route('admin.ai-persona.generate'), [
                'scope' => AiPersonaScope::CHAT_ORANGTUA,
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('ok', false);
    }

    public function test_generate_returns_token_exhausted_when_balance_zero(): void
    {
        $fixtures = $this->createFixtures();

        SekolahAiToken::query()
            ->where('sekolah_id', $fixtures['sekolah']->id)
            ->update(['balance' => 0]);

        $response = $this->actingAs($fixtures['admin'])
            ->postJson(route('admin.ai-persona.generate'), [
                'scope' => AiPersonaScope::CHAT_ORANGTUA,
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('ok', false);
        $response->assertJsonPath('token_exhausted', true);
        $response->assertJsonPath('token_balance', 0);
    }

    public function test_chat_persona_included_in_system_prompt(): void
    {
        $fixtures = $this->createFixtures();

        SekolahAiPersona::create([
            'sekolah_id' => $fixtures['sekolah']->id,
            'scope' => AiPersonaScope::CHAT_ORANGTUA,
            'name' => 'Bu Ani',
            'role_title' => 'Asisten Chat Orang Tua PAUD',
            'personality_traits' => 'Ramah seperti wali kelas',
            'dialog_language' => 'Bahasa Indonesia',
            'is_active' => true,
        ]);

        $ortu = User::factory()->create([
            'email' => 'ortu-persona-prompt@test.com',
            'sekolah_id' => $fixtures['sekolah']->id,
        ]);
        $ortu->assignRole('Orang Tua');

        $prompt = app(OrangTuaChatContextBuilder::class)->buildSystemPrompt($ortu);

        $this->assertStringContainsString('Bu Ani', $prompt);
        $this->assertStringContainsString('Ramah seperti wali kelas', $prompt);
        $this->assertStringContainsString('IDENTITAS:', $prompt);
        $this->assertStringContainsString('Ayah/Bunda', $prompt);
    }

    public function test_inactive_persona_not_included_in_prompt(): void
    {
        $fixtures = $this->createFixtures();

        SekolahAiPersona::create([
            'sekolah_id' => $fixtures['sekolah']->id,
            'scope' => AiPersonaScope::CHAT_ORANGTUA,
            'name' => 'Bu Ani',
            'personality_traits' => 'Ramah seperti wali kelas',
            'is_active' => false,
        ]);

        $ortu = User::factory()->create([
            'email' => 'ortu-persona-inactive@test.com',
            'sekolah_id' => $fixtures['sekolah']->id,
        ]);
        $ortu->assignRole('Orang Tua');

        $prompt = app(OrangTuaChatContextBuilder::class)->buildSystemPrompt($ortu);

        $this->assertStringNotContainsString('Bu Ani', $prompt);
        $this->assertStringContainsString('asisten AI PAUD', $prompt);
    }

    public function test_monev_persona_builds_prompt_section(): void
    {
        $fixtures = $this->createFixtures();

        $persona = SekolahAiPersona::create([
            'sekolah_id' => $fixtures['sekolah']->id,
            'scope' => AiPersonaScope::MONEV,
            'name' => 'Pak Budi',
            'role_title' => 'Guru PAUD penulis ringkasan monev',
            'personality_traits' => 'Analitis dan hangat',
            'is_active' => true,
        ]);

        $prompt = $persona->buildPromptSection($fixtures['sekolah']->name);

        $this->assertStringContainsString('Pak Budi', $prompt);
        $this->assertStringContainsString('Analitis dan hangat', $prompt);
    }
}
