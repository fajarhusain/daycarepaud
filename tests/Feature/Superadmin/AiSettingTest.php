<?php

namespace Tests\Feature\Superadmin;

use App\Models\AiSetting;
use App\Models\Lembaga;
use App\Models\Sekolah;
use App\Models\SekolahAiToken;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\SeedsAiTokens;
use Tests\TestCase;

class AiSettingTest extends TestCase
{
    use RefreshDatabase;
    use SeedsAiTokens;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    /**
     * @return array{lembaga: Lembaga, user: User}
     */
    protected function createFixtures(): array
    {
        $lembaga = Lembaga::firstOrFail();

        $user = User::factory()->create([
            'email' => 'superadmin-ai@test.com',
            'password' => Hash::make('password'),
            'lembaga_id' => null,
            'sekolah_id' => null,
        ]);
        $user->assignRole('Superadmin');

        return compact('lembaga', 'user');
    }

    public function test_superadmin_can_view_ai_setting_page_with_provider_dropdown(): void
    {
        $fixtures = $this->createFixtures();

        $response = $this->actingAs($fixtures['user'])
            ->get(route('superadmin.ai-setting.index', ['lembaga_id' => $fixtures['lembaga']->id]));

        $response->assertOk();
        $response->assertSee('Konfigurasi AI Provider');
        $response->assertSee('SumoPod AI');
        $response->assertSee('Custom (OpenAI-compatible)');
    }

    public function test_lembaga_cannot_access_ai_setting(): void
    {
        $lembaga = Lembaga::firstOrFail();
        $user = User::factory()->create([
            'lembaga_id' => $lembaga->id,
            'sekolah_id' => null,
        ]);
        $user->assignRole('Lembaga');

        $this->actingAs($user)
            ->get(route('superadmin.ai-setting.index', ['lembaga_id' => $lembaga->id]))
            ->assertForbidden();
    }

    public function test_superadmin_can_view_ai_setting_page_when_no_settings_exist(): void
    {
        $fixtures = $this->createFixtures();

        AiSetting::where('lembaga_id', $fixtures['lembaga']->id)->delete();

        $response = $this->actingAs($fixtures['user'])
            ->get(route('superadmin.ai-setting.index', ['lembaga_id' => $fixtures['lembaga']->id]));

        $response->assertOk();
        $response->assertSee('AI Belum Dikonfigurasi');
        $response->assertSee('Konfigurasi AI Provider');
    }

    public function test_custom_provider_allows_http_base_url(): void
    {
        $fixtures = $this->createFixtures();

        $response = $this->actingAs($fixtures['user'])
            ->post(route('superadmin.ai-setting.update'), [
                'lembaga_id' => $fixtures['lembaga']->id,
                'slots' => [
                    1 => [
                        'ai_provider' => 'custom',
                        'ai_base_url' => 'http://proxy.example.com/v1',
                        'ai_model' => 'my-model',
                        'ai_api_key' => 'secret-key',
                        'is_enabled' => '1',
                    ],
                ],
            ]);

        $response->assertRedirect(route('superadmin.ai-setting.index', ['lembaga_id' => $fixtures['lembaga']->id, 'tab' => 'provider']));
        $response->assertSessionHas('success');

        $setting = AiSetting::where('lembaga_id', $fixtures['lembaga']->id)->first();
        $this->assertNotNull($setting);
        $this->assertSame('custom', $setting->ai_provider);
        $this->assertSame('http://proxy.example.com/v1', $setting->ai_base_url);

        $slot1 = \App\Models\AiProviderSlot::query()
            ->where('lembaga_id', $fixtures['lembaga']->id)
            ->where('slot', 1)
            ->first();
        $this->assertNotNull($slot1);
        $this->assertSame('custom', $slot1->ai_provider);
        $this->assertSame('http://proxy.example.com/v1', $slot1->ai_base_url);
    }

    public function test_superadmin_can_save_custom_provider_with_base_url(): void
    {
        $fixtures = $this->createFixtures();
        $lembaga = $fixtures['lembaga'];

        $response = $this->actingAs($fixtures['user'])
            ->post(route('superadmin.ai-setting.update'), [
                'lembaga_id' => $lembaga->id,
                'slots' => [
                    1 => [
                        'ai_provider' => 'custom',
                        'ai_base_url' => 'https://llm.internal.example/v1/',
                        'ai_model' => 'my-model',
                        'ai_api_key' => 'secret-key',
                        'is_enabled' => '1',
                    ],
                ],
            ]);

        $response->assertRedirect(route('superadmin.ai-setting.index', ['lembaga_id' => $lembaga->id, 'tab' => 'provider']));
        $response->assertSessionHas('success');

        $setting = AiSetting::where('lembaga_id', $lembaga->id)->first();
        $this->assertNotNull($setting);
        $this->assertSame('custom', $setting->ai_provider);
        $this->assertSame('https://llm.internal.example/v1', $setting->ai_base_url);
    }

    public function test_superadmin_can_save_backup_slots(): void
    {
        $fixtures = $this->createFixtures();
        $lembaga = $fixtures['lembaga'];

        $response = $this->actingAs($fixtures['user'])
            ->post(route('superadmin.ai-setting.update'), [
                'lembaga_id' => $lembaga->id,
                'slots' => [
                    1 => [
                        'ai_provider' => 'openai',
                        'ai_model' => 'gpt-4o-mini',
                        'ai_api_key' => 'primary-key',
                        'is_enabled' => '1',
                    ],
                    2 => [
                        'is_enabled' => '1',
                        'ai_provider' => 'custom',
                        'ai_base_url' => 'https://backup.example.com/v1',
                        'ai_model' => 'oc/big-pickle',
                        'ai_api_key' => 'backup-key',
                    ],
                    3 => [
                        'is_enabled' => '0',
                        'ai_provider' => 'groq',
                        'ai_model' => 'llama-3.3-70b-versatile',
                        'ai_api_key' => 'unused-key',
                    ],
                ],
            ]);

        $response->assertRedirect(route('superadmin.ai-setting.index', ['lembaga_id' => $lembaga->id, 'tab' => 'provider']));
        $response->assertSessionHas('success');

        $slots = \App\Models\AiProviderSlot::query()
            ->where('lembaga_id', $lembaga->id)
            ->orderBy('slot')
            ->get()
            ->keyBy('slot');

        $this->assertTrue($slots->has(1));
        $this->assertTrue($slots->has(2));
        $this->assertTrue($slots->get(2)->is_enabled);
        $this->assertSame('oc/big-pickle', $slots->get(2)->ai_model);
        $this->assertTrue($slots->has(3));
        $this->assertFalse($slots->get(3)->is_enabled);

        $setting = AiSetting::where('lembaga_id', $lembaga->id)->first();
        $this->assertSame(2, $setting->configuredSlotCount());
    }

    public function test_superadmin_can_view_slot_labels_on_ai_setting_page(): void
    {
        $fixtures = $this->createFixtures();

        $response = $this->actingAs($fixtures['user'])
            ->get(route('superadmin.ai-setting.index', ['lembaga_id' => $fixtures['lembaga']->id]));

        $response->assertOk();
        $response->assertSee('Slot 1  Utama');
        $response->assertSee('Slot 2  Backup 1');
        $response->assertSee('Slot 3  Backup 2');
    }

    public function test_superadmin_can_top_up_tokens_for_school(): void
    {
        $fixtures = $this->createFixtures();
        $sekolah = Sekolah::firstOrFail();

        $response = $this->actingAs($fixtures['user'])
            ->post(route('superadmin.ai-setting.tokens.store'), [
                'lembaga_id' => $fixtures['lembaga']->id,
                'sekolah_id' => $sekolah->id,
                'amount' => 25,
                'description' => 'Paket awal',
            ]);

        $response->assertRedirect(route('superadmin.ai-setting.index', [
            'lembaga_id' => $fixtures['lembaga']->id,
            'tab' => 'tokens',
        ]));
        $response->assertSessionHas('success');

        $this->assertSame(25, SekolahAiToken::query()->where('sekolah_id', $sekolah->id)->value('balance'));
    }
}
