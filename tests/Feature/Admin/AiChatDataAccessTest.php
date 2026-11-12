<?php

namespace Tests\Feature\Admin;

use App\Models\AiSetting;
use App\Models\Anak;
use App\Models\Kegiatan;
use App\Models\KegiatanRutin;
use App\Models\Kelas;
use App\Models\Lembaga;
use App\Models\Pengajar;
use App\Models\Presensi;
use App\Models\Sekolah;
use App\Models\SekolahAiChatDataAccess;
use App\Models\User;
use App\Services\OrangTuaChatContextBuilder;
use App\Support\AiPersonaScope;
use Carbon\Carbon;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AiChatDataAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    /**
     * @return array{
     *     lembaga: Lembaga,
     *     sekolah: Sekolah,
     *     admin: User,
     *     ortu: User,
     *     anak: Anak,
     *     kelas: Kelas,
     *     pengajar: Pengajar
     * }
     */
    protected function createFixtures(): array
    {
        $lembaga = Lembaga::first();
        $sekolah = Sekolah::first();

        $admin = User::factory()->create([
            'email' => 'admin-data-access@test.com',
            'password' => Hash::make('password'),
            'lembaga_id' => $lembaga->id,
            'sekolah_id' => $sekolah->id,
        ]);
        $admin->assignRole('Admin Sekolah');

        $pengajar = Pengajar::create([
            'user_id' => $admin->id,
            'sekolah_id' => $sekolah->id,
            'name' => 'Guru Data Access',
        ]);

        $kelas = Kelas::create([
            'sekolah_id' => $sekolah->id,
            'name' => 'Kelas Data Access',
            'wali_kelas_id' => $pengajar->id,
        ]);

        $ortu = User::factory()->create([
            'email' => 'ortu-data-access@test.com',
            'sekolah_id' => $sekolah->id,
            'name' => 'Ortu Data Access',
        ]);
        $ortu->assignRole('Orang Tua');

        $anak = Anak::create([
            'user_id' => $ortu->id,
            'sekolah_id' => $sekolah->id,
            'kelas_id' => $kelas->id,
            'name' => 'Anak Data Access',
            'status' => 'approved',
        ]);

        AiSetting::create([
            'lembaga_id' => $lembaga->id,
            'ai_provider' => 'sumopod',
            'ai_api_key' => encrypt('test-api-key'),
            'ai_model' => 'gpt-4o-mini',
        ]);

        return compact('lembaga', 'sekolah', 'admin', 'ortu', 'anak', 'kelas', 'pengajar');
    }

    public function test_admin_can_view_data_access_section_on_chat_tab(): void
    {
        $fixtures = $this->createFixtures();

        $response = $this->actingAs($fixtures['admin'])
            ->get(route('admin.ai-persona.index', ['tab' => AiPersonaScope::CHAT_ORANGTUA]));

        $response->assertOk();
        $response->assertSee('Akses Data Chat');
        $response->assertSee('Simpan Pengaturan Akses Data');
        $response->assertSee('Rentang Agenda Belajar');
    }

    public function test_admin_sekolah_can_save_data_access_settings(): void
    {
        $fixtures = $this->createFixtures();

        $response = $this->actingAs($fixtures['admin'])
            ->post(route('admin.ai-persona.data-access.update'), [
                'access_monev' => '1',
                'access_pencapaian' => '0',
                'access_presensi' => '1',
                'access_kesehatan' => '0',
                'access_agenda' => '1',
                'access_kegiatan_rutin' => '1',
                'access_menu_makanan' => '0',
                'include_tanggal' => '1',
                'agenda_days_back' => 10,
                'agenda_days_forward' => 5,
                'kegiatan_rutin_days_back' => 14,
                'kegiatan_rutin_days_forward' => 3,
            ]);

        $response->assertRedirect(route('admin.ai-persona.index', ['tab' => AiPersonaScope::CHAT_ORANGTUA]));

        $this->assertDatabaseHas('sekolah_ai_chat_data_access', [
            'sekolah_id' => $fixtures['sekolah']->id,
            'access_monev' => true,
            'access_pencapaian' => false,
            'access_presensi' => true,
            'access_kesehatan' => false,
            'access_agenda' => true,
            'access_kegiatan_rutin' => true,
            'access_menu_makanan' => false,
            'include_tanggal' => true,
            'agenda_days_back' => 10,
            'agenda_days_forward' => 5,
            'kegiatan_rutin_days_back' => 14,
            'kegiatan_rutin_days_forward' => 3,
        ]);
    }

    public function test_data_access_validation_rejects_invalid_day_range(): void
    {
        $fixtures = $this->createFixtures();

        $response = $this->actingAs($fixtures['admin'])
            ->from(route('admin.ai-persona.index'))
            ->post(route('admin.ai-persona.data-access.update'), [
                'agenda_days_back' => 31,
                'agenda_days_forward' => 7,
                'kegiatan_rutin_days_back' => 7,
                'kegiatan_rutin_days_forward' => 7,
            ]);

        $response->assertRedirect(route('admin.ai-persona.index'));
        $response->assertSessionHasErrors('agenda_days_back');
    }

    public function test_admin_without_sekolah_cannot_update_data_access(): void
    {
        $fixtures = $this->createFixtures();

        $adminNoSekolah = User::factory()->create([
            'email' => 'admin-no-sekolah@test.com',
            'lembaga_id' => $fixtures['lembaga']->id,
            'sekolah_id' => null,
        ]);
        $adminNoSekolah->assignRole('Admin Sekolah');

        $this->actingAs($adminNoSekolah)
            ->post(route('admin.ai-persona.data-access.update'), [
                'agenda_days_back' => 7,
                'agenda_days_forward' => 7,
                'kegiatan_rutin_days_back' => 7,
                'kegiatan_rutin_days_forward' => 7,
            ])
            ->assertForbidden();
    }

    public function test_context_builder_excludes_presensi_when_disabled(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-15 10:00:00'));

        try {
            $fixtures = $this->createFixtures();

            Presensi::create([
                'sekolah_id' => $fixtures['sekolah']->id,
                'kelas_id' => $fixtures['kelas']->id,
                'anak_id' => $fixtures['anak']->id,
                'tanggal' => Carbon::today(),
                'hadir' => true,
                'status' => 'hadir',
            ]);

            SekolahAiChatDataAccess::create(array_merge(
                SekolahAiChatDataAccess::defaults(),
                [
                    'sekolah_id' => $fixtures['sekolah']->id,
                    'access_presensi' => false,
                ]
            ));

            $prompt = app(OrangTuaChatContextBuilder::class)->buildSystemPrompt($fixtures['ortu']);

            $this->assertStringNotContainsString('Kehadiran bulan ini', $prompt);
            $this->assertStringContainsString('Kehadiran (Presensi): nonaktif', $prompt);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_context_builder_includes_past_agenda_within_days_back(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-15 10:00:00'));

        try {
            $fixtures = $this->createFixtures();

            Kegiatan::create([
                'sekolah_id' => $fixtures['sekolah']->id,
                'pengajar_id' => $fixtures['pengajar']->id,
                'kelas_id' => $fixtures['kelas']->id,
                'date' => Carbon::parse('2026-06-13'),
                'title' => 'Kegiatan Jumat Kemarin',
            ]);

            SekolahAiChatDataAccess::create(array_merge(
                SekolahAiChatDataAccess::defaults(),
                [
                    'sekolah_id' => $fixtures['sekolah']->id,
                    'agenda_days_back' => 7,
                    'agenda_days_forward' => 0,
                ]
            ));

            $prompt = app(OrangTuaChatContextBuilder::class)->buildSystemPrompt($fixtures['ortu']);

            $this->assertStringContainsString('Kegiatan Jumat Kemarin', $prompt);
            $this->assertStringContainsString('jadwal/rencana', $prompt);
            $this->assertStringContainsString('JADWAL & RENCANA KEGIATAN', $prompt);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_context_builder_includes_past_kegiatan_rutin_within_days_back(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-15 10:00:00'));

        try {
            $fixtures = $this->createFixtures();

            KegiatanRutin::create([
                'sekolah_id' => $fixtures['sekolah']->id,
                'kelas_id' => $fixtures['kelas']->id,
                'anak_id' => $fixtures['anak']->id,
                'pengajar_id' => $fixtures['pengajar']->id,
                'tanggal' => Carbon::parse('2026-06-12'),
                'aspek' => 'Kemandirian',
                'kegiatan' => 'Toilet Training',
                'status_pencapaian' => 'Sudah Lancar',
            ]);

            SekolahAiChatDataAccess::create(array_merge(
                SekolahAiChatDataAccess::defaults(),
                [
                    'sekolah_id' => $fixtures['sekolah']->id,
                    'kegiatan_rutin_days_back' => 7,
                    'kegiatan_rutin_days_forward' => 0,
                ]
            ));

            $prompt = app(OrangTuaChatContextBuilder::class)->buildSystemPrompt($fixtures['ortu']);

            $this->assertStringContainsString('Toilet Training', $prompt);
            $this->assertStringContainsString('JADWAL & RENCANA KEGIATAN', $prompt);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_context_builder_includes_today_agenda_as_plan_without_pencapaian(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-15 10:00:00'));

        try {
            $fixtures = $this->createFixtures();

            Kegiatan::create([
                'sekolah_id' => $fixtures['sekolah']->id,
                'pengajar_id' => $fixtures['pengajar']->id,
                'kelas_id' => $fixtures['kelas']->id,
                'date' => Carbon::parse('2026-06-15'),
                'title' => 'mengenalkan jenis transportasi udara',
            ]);

            $prompt = app(OrangTuaChatContextBuilder::class)->buildSystemPrompt($fixtures['ortu']);

            $this->assertStringContainsString('mengenalkan jenis transportasi udara', $prompt);
            $this->assertStringContainsString('Hari ini', $prompt);
            $this->assertStringContainsString('jadwal/rencana', $prompt);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_context_builder_includes_today_date_when_enabled(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-15 10:00:00'));

        try {
            $fixtures = $this->createFixtures();
            Carbon::setLocale('id');

            $prompt = app(OrangTuaChatContextBuilder::class)->buildSystemPrompt($fixtures['ortu']);

            $this->assertStringContainsString('Tanggal hari ini:', $prompt);
            $this->assertStringContainsString('15 Juni 2026', $prompt);
        } finally {
            Carbon::setTestNow();
        }
    }
}
