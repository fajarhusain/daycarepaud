<?php

namespace Tests\Feature\OrangTua;

use App\Models\AiSetting;
use App\Models\Anak;
use App\Models\Kelas;
use App\Models\Lembaga;
use App\Models\OrangTuaChat;
use App\Models\OrangTuaChatMessage;
use App\Models\Pengajar;
use App\Models\Sekolah;
use App\Models\SekolahAiToken;
use App\Models\User;
use App\Services\OrangTuaChatContextBuilder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\SeedsAiTokens;
use Tests\TestCase;

class ChatTest extends TestCase
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
     * @return array{lembaga: Lembaga, sekolah: Sekolah, ortu: User, anak: Anak, admin: User, sekolah2: Sekolah, ortuOther: User}
     */
    protected function createFixtures(): array
    {
        $lembaga = Lembaga::first();
        $sekolah = Sekolah::first();

        $admin = User::factory()->create([
            'email' => 'admin-chat@test.com',
            'password' => Hash::make('password'),
            'lembaga_id' => $lembaga->id,
            'sekolah_id' => $sekolah->id,
        ]);
        $admin->assignRole('Admin Sekolah');

        $pengajar = Pengajar::create([
            'user_id' => $admin->id,
            'sekolah_id' => $sekolah->id,
            'name' => 'Guru Test',
        ]);

        $kelas = Kelas::create([
            'sekolah_id' => $sekolah->id,
            'name' => 'Kelas Chat',
            'wali_kelas_id' => $pengajar->id,
        ]);

        $ortu = User::factory()->create([
            'email' => 'ortu-chat@test.com',
            'password' => Hash::make('password'),
            'sekolah_id' => $sekolah->id,
            'name' => 'Budi Ortu',
        ]);
        $ortu->assignRole('Orang Tua');

        $anak = Anak::create([
            'user_id' => $ortu->id,
            'sekolah_id' => $sekolah->id,
            'kelas_id' => $kelas->id,
            'name' => 'Anak Chat',
            'status' => 'approved',
        ]);

        AiSetting::create([
            'lembaga_id' => $lembaga->id,
            'ai_provider' => 'sumopod',
            'ai_api_key' => 'test-api-key',
            'ai_model' => 'gpt-4o-mini',
        ]);

        $this->seedAiTokens($sekolah, 10, $admin);

        $sekolah2 = Sekolah::create([
            'lembaga_id' => $lembaga->id,
            'name' => 'PAUD Sekolah Lain',
            'address' => 'Jl. Lain',
            'phone' => '021-00000000',
        ]);

        $ortuOther = User::factory()->create([
            'email' => 'ortu-other-school@test.com',
            'sekolah_id' => $sekolah2->id,
        ]);
        $ortuOther->assignRole('Orang Tua');

        Anak::create([
            'user_id' => $ortuOther->id,
            'sekolah_id' => $sekolah2->id,
            'name' => 'Anak Lain',
            'status' => 'approved',
        ]);

        return compact('lembaga', 'sekolah', 'ortu', 'anak', 'admin', 'sekolah2', 'ortuOther');
    }

    public function test_orang_tua_can_open_chat_page(): void
    {
        $f = $this->createFixtures();

        $this->actingAs($f['ortu'])
            ->get(route('orangtua.chat.index'))
            ->assertOk()
            ->assertSee('Chat AI')
            ->assertSee('Mulai percakapan')
            ->assertSee('aria-label="Kembali"', false)
            ->assertDontSee('Menu Lainnya');
    }

    public function test_orang_tua_can_send_message_and_receive_ai_reply(): void
    {
        $f = $this->createFixtures();

        Http::fake([
            'ai.sumopod.com/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => 'Anak Chat berkembang dengan baik di PAUD.']],
                ],
            ]),
        ]);

        $response = $this->actingAs($f['ortu'])->postJson(route('orangtua.chat.messages.store'), [
            'content' => 'Bagaimana perkembangan anak saya?',
        ]);

        $response->assertOk()
            ->assertJsonCount(2, 'messages');

        $this->assertDatabaseHas('orangtua_chat_messages', [
            'role' => OrangTuaChatMessage::ROLE_USER,
            'content' => 'Bagaimana perkembangan anak saya?',
        ]);

        $this->assertDatabaseHas('orangtua_chat_messages', [
            'role' => OrangTuaChatMessage::ROLE_ASSISTANT,
            'content' => 'Anak Chat berkembang dengan baik di PAUD.',
        ]);
    }

    public function test_chat_returns_token_exhausted_when_balance_zero(): void
    {
        $f = $this->createFixtures();

        SekolahAiToken::query()
            ->where('sekolah_id', $f['sekolah']->id)
            ->update(['balance' => 0]);

        $response = $this->actingAs($f['ortu'])->postJson(route('orangtua.chat.messages.store'), [
            'content' => 'Halo',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('token_exhausted', true);
        $response->assertJsonPath('token_balance', 0);
    }

    public function test_ai_reply_is_stored_without_markdown(): void
    {
        $f = $this->createFixtures();

        Http::fake([
            'ai.sumopod.com/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => "**Anak Chat** berkembang dengan _baik_.\n\n- poin satu\n- poin dua"]],
                ],
            ]),
        ]);

        $this->actingAs($f['ortu'])->postJson(route('orangtua.chat.messages.store'), [
            'content' => 'Bagaimana perkembangan anak saya?',
        ])->assertOk();

        $this->assertDatabaseHas('orangtua_chat_messages', [
            'role' => OrangTuaChatMessage::ROLE_ASSISTANT,
            'content' => "Anak Chat berkembang dengan baik.\n\n- poin satu\n- poin dua",
        ]);
    }

    public function test_chat_system_prompt_uses_ayah_bunda_salutation(): void
    {
        $f = $this->createFixtures();

        $prompt = app(OrangTuaChatContextBuilder::class)->buildSystemPrompt($f['ortu']);

        $this->assertStringContainsString('Ayah/Bunda', $prompt);
        $this->assertStringContainsString($f['ortu']->name, $prompt);
        $this->assertStringContainsString('Jangan gunakan format markdown', $prompt);
    }

    public function test_orang_tua_can_clear_chat_history_with_soft_delete(): void
    {
        $f = $this->createFixtures();

        $chat = OrangTuaChat::create([
            'user_id' => $f['ortu']->id,
            'sekolah_id' => $f['sekolah']->id,
        ]);

        OrangTuaChatMessage::create([
            'orangtua_chat_id' => $chat->id,
            'role' => OrangTuaChatMessage::ROLE_USER,
            'content' => 'Pesan lama',
        ]);

        $this->actingAs($f['ortu'])
            ->deleteJson(route('orangtua.chat.destroy'))
            ->assertOk()
            ->assertJson(['success' => true]);

        $chat->refresh();
        $this->assertNotNull($chat->cleared_at);
        $this->assertDatabaseCount('orangtua_chat_messages', 1);
    }

    public function test_pengajar_cannot_access_orang_tua_chat(): void
    {
        $f = $this->createFixtures();

        $pengajarUser = User::factory()->create([
            'sekolah_id' => $f['sekolah']->id,
        ]);
        $pengajarUser->assignRole('Pengajar');

        $this->actingAs($pengajarUser)
            ->get(route('orangtua.chat.index'))
            ->assertForbidden();
    }

    public function test_chat_returns_422_when_ai_not_configured(): void
    {
        $f = $this->createFixtures();
        AiSetting::query()->delete();

        $this->actingAs($f['ortu'])
            ->postJson(route('orangtua.chat.messages.store'), [
                'content' => 'Halo',
            ])
            ->assertStatus(422)
            ->assertJsonFragment(['error' => 'Pengaturan AI belum dikonfigurasi. Minta admin lembaga untuk mengisi API Key di menu Pengaturan AI.']);
    }
}
