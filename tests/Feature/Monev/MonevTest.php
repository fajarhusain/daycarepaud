<?php

namespace Tests\Feature\Monev;

use App\Jobs\GenerateMonevSummaryJob;
use App\Models\AiSetting;
use App\Models\Anak;
use App\Models\Kelas;
use App\Models\Lembaga;
use App\Models\Matrikulasi;
use App\Models\MonevGeneration;
use App\Models\MonevManualTrigger;
use App\Models\MonevSummary;
use App\Models\Pencapaian;
use App\Models\Pengajar;
use App\Models\Sekolah;
use App\Models\User;
use App\Services\MonevDataAggregator;
use App\Services\MonevSummaryService;
use App\Services\SumopodAIService;
use App\Support\MonevSummaryPresenter;
use Carbon\Carbon;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\SeedsAiTokens;
use Tests\TestCase;

class MonevTest extends TestCase
{
    use RefreshDatabase;
    use SeedsAiTokens;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    /**
     * @return array{lembaga: Lembaga, sekolah: Sekolah, kelas: Kelas, kelas2: Kelas, admin: User, wali: User, pengajar: Pengajar, anak: Anak, anak2: Anak}
     */
    protected function createFixtures(): array
    {
        $lembaga = Lembaga::first();
        $sekolah = Sekolah::first();

        $admin = User::factory()->create([
            'email' => 'admin-monev@test.com',
            'password' => Hash::make('password'),
            'lembaga_id' => $lembaga->id,
            'sekolah_id' => $sekolah->id,
        ]);
        $admin->assignRole('Admin Sekolah');

        $wali = User::factory()->create([
            'email' => 'wali-monev@test.com',
            'password' => Hash::make('password'),
            'sekolah_id' => $sekolah->id,
        ]);
        $wali->assignRole('Wali Kelas');

        $pengajar = Pengajar::create([
            'user_id' => $wali->id,
            'sekolah_id' => $sekolah->id,
            'name' => 'Wali Test',
        ]);

        $kelas = Kelas::create([
            'sekolah_id' => $sekolah->id,
            'name' => 'Kelas A',
            'wali_kelas_id' => $pengajar->id,
        ]);

        $kelas2 = Kelas::create([
            'sekolah_id' => $sekolah->id,
            'name' => 'Kelas B',
            'wali_kelas_id' => null,
        ]);

        $anakUser = User::factory()->create(['email' => 'anak1@test.com']);
        $anak = Anak::create([
            'user_id' => $anakUser->id,
            'sekolah_id' => $sekolah->id,
            'kelas_id' => $kelas->id,
            'name' => 'Anak Satu',
            'status' => 'approved',
        ]);

        $anakUser2 = User::factory()->create(['email' => 'anak2@test.com']);
        $anak2 = Anak::create([
            'user_id' => $anakUser2->id,
            'sekolah_id' => $sekolah->id,
            'kelas_id' => $kelas2->id,
            'name' => 'Anak Dua',
            'status' => 'approved',
        ]);

        AiSetting::create([
            'lembaga_id' => $lembaga->id,
            'ai_provider' => 'sumopod',
            'ai_api_key' => 'test-api-key',
            'ai_model' => 'gpt-4o-mini',
        ]);

        return compact('lembaga', 'sekolah', 'kelas', 'kelas2', 'admin', 'wali', 'pengajar', 'anak', 'anak2');
    }

    public function test_admin_sekolah_can_access_monev_index(): void
    {
        $f = $this->createFixtures();

        $response = $this->actingAs($f['admin'])->get(route('admin.monev.index'));

        $response->assertOk();
        $response->assertSee('Monev Matrikulasi');
        $response->assertSee('Anak Satu');
    }

    public function test_wali_kelas_can_access_monev_index(): void
    {
        $f = $this->createFixtures();

        $response = $this->actingAs($f['wali'])->get(route('adminkelas.monev.index'));

        $response->assertOk();
        $response->assertSee('Anak Satu');
        $response->assertDontSee('Anak Dua');
    }

    public function test_wali_kelas_cannot_view_other_class_student_summary(): void
    {
        $f = $this->createFixtures();

        $summary = MonevSummary::create([
            'anak_id' => $f['anak2']->id,
            'tahun' => now()->year,
            'bulan' => now()->month,
            'ringkasan' => 'Ringkasan rahasia',
            'sumber' => MonevSummary::SUMBER_MANUAL,
            'generated_at' => now(),
        ]);

        $response = $this->actingAs($f['wali'])->get(route('adminkelas.monev.show', [
            'anak' => $f['anak2']->id,
            'tahun' => $summary->tahun,
            'bulan' => $summary->bulan,
        ]));

        $response->assertForbidden();
    }

    public function test_manual_generate_only_once_per_sekolah_per_month(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 15));
        $f = $this->createFixtures();

        $response1 = $this->actingAs($f['admin'])->post(route('admin.monev.generate'));
        $response1->assertRedirect();
        $response1->assertSessionHas('monev_generation_id');

        $this->assertDatabaseHas('monev_generations', [
            'sekolah_id' => $f['sekolah']->id,
            'tahun' => 2026,
            'bulan' => 6,
        ]);

        $this->assertDatabaseHas('monev_manual_triggers', [
            'sekolah_id' => $f['sekolah']->id,
            'tahun' => 2026,
            'bulan' => 6,
        ]);

        $response2 = $this->actingAs($f['admin'])->post(route('admin.monev.generate'));
        $response2->assertRedirect();
        $response2->assertSessionHasErrors('monev');

        Carbon::setTestNow();
    }

    public function test_aggregator_counts_pencapaian_in_month(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 20));
        $f = $this->createFixtures();

        $matrikulasi = Matrikulasi::create([
            'sekolah_id' => $f['sekolah']->id,
            'indicator' => 'Indikator 1',
            'aspek' => 'Kognitif',
            'description' => 'Deskripsi',
        ]);

        Pencapaian::create([
            'anak_id' => $f['anak']->id,
            'matrikulasi_id' => $matrikulasi->id,
            'pengajar_id' => $f['pengajar']->id,
            'score' => 'BSH',
            'feedback' => 'Bagus sekali',
            'created_at' => Carbon::create(2026, 6, 10),
            'updated_at' => Carbon::create(2026, 6, 10),
        ]);

        $aggregator = app(MonevDataAggregator::class);
        $stats = $aggregator->aggregate($f['anak'], 2026, 6);

        $this->assertSame(1, $stats['total_entri']);
        $this->assertArrayHasKey('Kognitif', $stats['per_aspek']);
        $this->assertSame(1, $stats['per_aspek']['Kognitif']['jumlah']);

        Carbon::setTestNow();
    }

    public function test_command_generates_otomatis_summaries_without_ai_when_no_data(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 7, 1));
        $f = $this->createFixtures();

        $this->artisan('monev:generate')
            ->assertSuccessful();

        $this->assertDatabaseHas('monev_summaries', [
            'anak_id' => $f['anak']->id,
            'tahun' => 2026,
            'bulan' => 6,
            'sumber' => MonevSummary::SUMBER_OTOMATIS,
        ]);

        Carbon::setTestNow();
    }

    public function test_generate_for_anak_uses_ai_when_data_exists(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 15));
        $f = $this->createFixtures();

        $matrikulasi = Matrikulasi::create([
            'sekolah_id' => $f['sekolah']->id,
            'indicator' => 'Indikator 1',
            'aspek' => 'Kognitif',
            'description' => 'Deskripsi',
        ]);

        Pencapaian::create([
            'anak_id' => $f['anak']->id,
            'matrikulasi_id' => $matrikulasi->id,
            'pengajar_id' => $f['pengajar']->id,
            'score' => 'MB',
            'feedback' => 'Mulai berkembang',
            'created_at' => Carbon::create(2026, 6, 5),
            'updated_at' => Carbon::create(2026, 6, 5),
        ]);

        $mockAi = $this->createMock(SumopodAIService::class);
        $mockAi->method('generateMonevSummary')->willReturn(<<<'AI'
[GAMBARAN_UMUM]
- Perkembangan baik.

[KEKUATAN]
- Komunikasi lancar.

[PERHATIAN]
- Motorik perlu stimulasi.

[REKOMENDASI]
- Latihan rutin di rumah.

[ASPEK:Kognitif]
- Siswa mulai menunjukkan rasa ingin tahu.
- Mayoritas indikator masih MB namun konsisten.
AI);

        $service = app(MonevSummaryService::class);
        $this->seedAiTokens($f['sekolah'], 1, $f['admin']);

        $summary = $service->generateForAnak(
            $f['anak'],
            2026,
            6,
            MonevSummary::SUMBER_MANUAL,
            $f['admin'],
            $mockAi
        );

        $this->assertStringNotContainsString('[ASPEK:', $summary->ringkasan);
        $this->assertStringContainsString('[GAMBARAN_UMUM]', $summary->ringkasan);
        $this->assertSame(
            ['Siswa mulai menunjukkan rasa ingin tahu.', 'Mayoritas indikator masih MB namun konsisten.'],
            $summary->data_snapshot['per_aspek']['Kognitif']['ringkasan'] ?? null
        );
        $this->assertSame(MonevSummary::SUMBER_MANUAL, $summary->sumber);

        Carbon::setTestNow();
    }

    public function test_index_search_filters_students_by_name(): void
    {
        $f = $this->createFixtures();

        $response = $this->actingAs($f['admin'])->get(route('admin.monev.index', ['search' => 'Satu']));

        $response->assertOk();
        $response->assertSee('Anak Satu');
        $response->assertDontSee('Anak Dua');
    }

    public function test_bulk_reset_deletes_summaries_for_selected_students(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 15));
        $f = $this->createFixtures();

        MonevSummary::create([
            'anak_id' => $f['anak']->id,
            'tahun' => 2026,
            'bulan' => 6,
            'ringkasan' => 'Ringkasan uji',
            'sumber' => MonevSummary::SUMBER_MANUAL,
            'generated_at' => now(),
        ]);

        $response = $this->actingAs($f['admin'])->post(route('admin.monev.bulk-reset'), [
            'anak_ids' => [$f['anak']->id],
            'tahun' => 2026,
            'bulan' => 6,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('monev_summaries', ['anak_id' => $f['anak']->id, 'tahun' => 2026, 'bulan' => 6]);

        Carbon::setTestNow();
    }

    public function test_bulk_generate_starts_background_generation(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 15));
        $f = $this->createFixtures();

        $response = $this->actingAs($f['admin'])->post(route('admin.monev.bulk-generate'), [
            'anak_ids' => [$f['anak']->id],
            'tahun' => 2026,
            'bulan' => 6,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('monev_generation_id');
        $this->assertDatabaseHas('monev_generations', [
            'sekolah_id' => $f['sekolah']->id,
            'tahun' => 2026,
            'bulan' => 6,
            'total' => 1,
        ]);

        Carbon::setTestNow();
    }

    public function test_bulk_generate_rejects_when_insufficient_tokens(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 15));
        $f = $this->createFixtures();

        $matrikulasi = Matrikulasi::create([
            'sekolah_id' => $f['sekolah']->id,
            'indicator' => 'Indikator 1',
            'aspek' => 'Kognitif',
            'description' => 'Deskripsi',
        ]);

        foreach ([$f['anak'], $f['anak2']] as $anak) {
            Pencapaian::create([
                'anak_id' => $anak->id,
                'matrikulasi_id' => $matrikulasi->id,
                'pengajar_id' => $f['pengajar']->id,
                'score' => 'MB',
                'feedback' => 'Mulai berkembang',
                'created_at' => Carbon::create(2026, 6, 5),
                'updated_at' => Carbon::create(2026, 6, 5),
            ]);
        }

        $this->seedAiTokens($f['sekolah'], 1, $f['admin']);

        $response = $this->actingAs($f['admin'])->post(route('admin.monev.bulk-generate'), [
            'anak_ids' => [$f['anak']->id, $f['anak2']->id],
            'tahun' => 2026,
            'bulan' => 6,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('monev');
        $this->assertDatabaseMissing('monev_generations', [
            'sekolah_id' => $f['sekolah']->id,
            'tahun' => 2026,
            'bulan' => 6,
        ]);

        Carbon::setTestNow();
    }

    public function test_orang_tua_can_view_own_child_monev(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 15));
        $f = $this->createFixtures();

        $ortuUser = User::factory()->create([
            'email' => 'ortu-monev@test.com',
            'sekolah_id' => null,
        ]);
        $ortuUser->assignRole('Orang Tua');

        $f['anak']->update(['user_id' => $ortuUser->id]);

        MonevSummary::create([
            'anak_id' => $f['anak']->id,
            'tahun' => 2026,
            'bulan' => 6,
            'ringkasan' => '[GAMBARAN_UMUM]\nPerkembangan baik.',
            'sumber' => MonevSummary::SUMBER_OTOMATIS,
            'generated_at' => now(),
        ]);

        $this->actingAs($ortuUser)->get(route('orangtua.monev.index'))
            ->assertOk()
            ->assertSee('Anak Satu')
            ->assertSee('Perkembangan baik');

        $this->actingAs($ortuUser)->get(route('orangtua.monev.show', ['anak' => $f['anak']->id, 'tahun' => 2026, 'bulan' => 6]))
            ->assertRedirect(route('orangtua.monev.index', [
                'anak_id' => $f['anak']->id,
                'tahun' => 2026,
                'bulan' => 6,
            ]));

        Carbon::setTestNow();
    }

    public function test_orang_tua_cannot_view_other_child_monev(): void
    {
        $f = $this->createFixtures();

        $ortuUser = User::factory()->create([
            'email' => 'ortu-other@test.com',
            'sekolah_id' => null,
        ]);
        $ortuUser->assignRole('Orang Tua');

        MonevSummary::create([
            'anak_id' => $f['anak2']->id,
            'tahun' => 2026,
            'bulan' => 6,
            'ringkasan' => 'Rahasia',
            'sumber' => MonevSummary::SUMBER_OTOMATIS,
            'generated_at' => now(),
        ]);

        $this->actingAs($ortuUser)->get(route('orangtua.monev.show', ['anak' => $f['anak2']->id, 'tahun' => 2026, 'bulan' => 6]))
            ->assertForbidden();

        Carbon::setTestNow();
    }

    public function test_wali_manual_trigger_is_per_kelas(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 15));
        $f = $this->createFixtures();

        $service = app(MonevSummaryService::class);

        $this->assertTrue($service->canManualTriggerForKelas($f['kelas']->id, 2026, 6));

        MonevManualTrigger::create([
            'kelas_id' => $f['kelas']->id,
            'sekolah_id' => null,
            'tahun' => 2026,
            'bulan' => 6,
            'triggered_by_user_id' => $f['wali']->id,
            'triggered_at' => now(),
        ]);

        $this->assertFalse($service->canManualTriggerForKelas($f['kelas']->id, 2026, 6));

        Carbon::setTestNow();
    }

    public function test_bulk_generate_works_after_manual_quota_used(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 15));
        $f = $this->createFixtures();

        $this->actingAs($f['admin'])->post(route('admin.monev.generate'))->assertRedirect();

        $response = $this->actingAs($f['admin'])->post(route('admin.monev.bulk-generate'), [
            'anak_ids' => [$f['anak']->id],
            'tahun' => 2026,
            'bulan' => 6,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('monev_generation_id');

        Carbon::setTestNow();
    }

    public function test_bulk_generate_works_for_non_current_month(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 15));
        $f = $this->createFixtures();

        $response = $this->actingAs($f['admin'])->post(route('admin.monev.bulk-generate'), [
            'anak_ids' => [$f['anak']->id],
            'tahun' => 2026,
            'bulan' => 5,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('monev_generation_id');

        Carbon::setTestNow();
    }

    public function test_bulk_generate_rejects_cross_school_student_ids(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 15));
        $f = $this->createFixtures();

        $otherSekolah = Sekolah::create([
            'lembaga_id' => $f['lembaga']->id,
            'name' => 'Sekolah Lain',
            'address' => 'Alamat',
        ]);

        $otherAnak = Anak::create([
            'user_id' => User::factory()->create()->id,
            'sekolah_id' => $otherSekolah->id,
            'kelas_id' => null,
            'name' => 'Anak Lain Sekolah',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($f['admin'])->post(route('admin.monev.bulk-generate'), [
            'anak_ids' => [$otherAnak->id],
            'tahun' => 2026,
            'bulan' => 6,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('monev');

        Carbon::setTestNow();
    }

    public function test_generation_status_forbidden_for_other_school(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 15));
        $f = $this->createFixtures();

        $otherAdmin = User::factory()->create([
            'email' => 'admin-other@test.com',
            'sekolah_id' => Sekolah::create([
                'lembaga_id' => $f['lembaga']->id,
                'name' => 'Sekolah B',
                'address' => 'Alamat',
            ])->id,
        ]);
        $otherAdmin->assignRole('Admin Sekolah');

        $generation = MonevGeneration::create([
            'sekolah_id' => $f['sekolah']->id,
            'kelas_id' => null,
            'tahun' => 2026,
            'bulan' => 6,
            'sumber' => MonevSummary::SUMBER_MANUAL,
            'total' => 1,
            'status' => MonevGeneration::STATUS_RUNNING,
            'triggered_by_user_id' => $f['admin']->id,
        ]);

        $this->actingAs($otherAdmin)
            ->getJson(route('admin.monev.generation.status', $generation))
            ->assertForbidden();

        Carbon::setTestNow();
    }

    public function test_cron_skips_overwriting_manual_summary(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 7, 1));
        $f = $this->createFixtures();

        MonevSummary::create([
            'anak_id' => $f['anak']->id,
            'tahun' => 2026,
            'bulan' => 6,
            'ringkasan' => 'Ringkasan manual admin',
            'sumber' => MonevSummary::SUMBER_MANUAL,
            'generated_at' => now(),
        ]);

        $this->artisan('monev:generate')->assertSuccessful();

        $this->assertDatabaseHas('monev_summaries', [
            'anak_id' => $f['anak']->id,
            'tahun' => 2026,
            'bulan' => 6,
            'ringkasan' => 'Ringkasan manual admin',
            'sumber' => MonevSummary::SUMBER_MANUAL,
        ]);

        Carbon::setTestNow();
    }

    public function test_generate_job_marks_completed_and_finalizes_generation(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 15));
        $f = $this->createFixtures();

        $generation = MonevGeneration::create([
            'sekolah_id' => $f['sekolah']->id,
            'kelas_id' => null,
            'tahun' => 2026,
            'bulan' => 6,
            'sumber' => MonevSummary::SUMBER_MANUAL,
            'total' => 1,
            'status' => MonevGeneration::STATUS_PENDING,
            'triggered_by_user_id' => $f['admin']->id,
        ]);

        $job = new GenerateMonevSummaryJob(
            $generation->id,
            $f['anak']->id,
            2026,
            6,
            MonevSummary::SUMBER_MANUAL,
            $f['admin']->id
        );

        $job->handle(app(MonevSummaryService::class));

        $generation->refresh();
        $this->assertSame(MonevGeneration::STATUS_COMPLETED, $generation->status);
        $this->assertSame(1, $generation->completed);

        Carbon::setTestNow();
    }

    public function test_finalize_stale_generations_command(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 15));
        $f = $this->createFixtures();

        $generation = MonevGeneration::create([
            'sekolah_id' => $f['sekolah']->id,
            'kelas_id' => null,
            'tahun' => 2026,
            'bulan' => 6,
            'sumber' => MonevSummary::SUMBER_MANUAL,
            'total' => 2,
            'completed' => 0,
            'failed' => 0,
            'skipped' => 0,
            'status' => MonevGeneration::STATUS_RUNNING,
            'triggered_by_user_id' => $f['admin']->id,
        ]);

        MonevGeneration::query()
            ->where('id', $generation->id)
            ->update(['updated_at' => now()->subHours(5)]);

        $this->artisan('monev:finalize-stale')->assertSuccessful();

        $generation->refresh();
        $this->assertTrue($generation->isFinished());
        $this->assertSame(2, $generation->failed);

        Carbon::setTestNow();
    }

    public function test_bulk_generate_blocked_without_ai_configuration(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 15));
        $f = $this->createFixtures();

        AiSetting::query()->delete();

        $response = $this->actingAs($f['admin'])->post(route('admin.monev.bulk-generate'), [
            'anak_ids' => [$f['anak']->id],
            'tahun' => 2026,
            'bulan' => 6,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('monev');

        Carbon::setTestNow();
    }

    public function test_per_aspek_narrative_uses_fallback_when_no_ai_summary(): void
    {
        $points = MonevSummaryPresenter::perAspekNarrativePoints('Kognitif', [
            'jumlah' => 9,
            'skor' => [
                'Mulai Berkembang (MB)' => 6,
                'Berkembang Sesuai Harapan (BSH)' => 3,
            ],
        ]);

        $this->assertGreaterThanOrEqual(3, count($points));
        $this->assertStringContainsString('Kognitif', $points[0]);
        $this->assertStringContainsString('Mulai Berkembang (MB)', $points[1]);
    }

    public function test_split_ringkasan_extracts_aspek_sections(): void
    {
        [$main, $aspek] = MonevSummaryPresenter::splitRingkasanAndAspekSummaries(<<<'TEXT'
[GAMBARAN_UMUM]
- Umum baik.

[ASPEK:Motorik Halus]
- Koordinasi tangan membaik.
- Masih perlu latihan menggunting.

[ASPEK:Kognitif]
- Rasa ingin tahu meningkat.
TEXT);

        $this->assertStringContainsString('[GAMBARAN_UMUM]', $main);
        $this->assertStringNotContainsString('[ASPEK:', $main);
        $this->assertSame(['Koordinasi tangan membaik.', 'Masih perlu latihan menggunting.'], $aspek['Motorik Halus']);
        $this->assertSame(['Rasa ingin tahu meningkat.'], $aspek['Kognitif']);
    }

    public function test_admin_can_export_monev_pdf(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 15));
        $f = $this->createFixtures();

        MonevSummary::create([
            'anak_id' => $f['anak']->id,
            'tahun' => 2026,
            'bulan' => 6,
            'ringkasan' => "[GAMBARAN_UMUM]\n- Perkembangan baik.\n[KEKUATAN]\n- Komunikasi lancar.",
            'data_snapshot' => [
                'total_entri' => 5,
                'distribusi_skor' => ['MB' => 3, 'BSH' => 2],
                'per_aspek' => ['Kognitif' => ['jumlah' => 5, 'skor' => ['MB' => 3, 'BSH' => 2]]],
                'cuplikan_feedback' => ['Bagus sekali'],
                'indikator_tercatat' => ['Indikator 1'],
            ],
            'sumber' => MonevSummary::SUMBER_OTOMATIS,
            'generated_at' => now(),
        ]);

        $response = $this->actingAs($f['admin'])->get(route('admin.monev.export-pdf', [
            'anak' => $f['anak']->id,
            'tahun' => 2026,
            'bulan' => 6,
        ]));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));
        $this->assertStringContainsString('Monev-Anak Satu - Juni.pdf', (string) $response->headers->get('content-disposition'));

        Carbon::setTestNow();
    }

    public function test_wali_kelas_cannot_export_other_class_student_pdf(): void
    {
        $f = $this->createFixtures();

        MonevSummary::create([
            'anak_id' => $f['anak2']->id,
            'tahun' => 2026,
            'bulan' => 6,
            'ringkasan' => 'Ringkasan rahasia',
            'sumber' => MonevSummary::SUMBER_MANUAL,
            'generated_at' => now(),
        ]);

        $this->actingAs($f['wali'])->get(route('adminkelas.monev.export-pdf', [
            'anak' => $f['anak2']->id,
            'tahun' => 2026,
            'bulan' => 6,
        ]))->assertForbidden();
    }

    public function test_orang_tua_can_export_own_child_monev_pdf(): void
    {
        $f = $this->createFixtures();

        $ortuUser = User::factory()->create([
            'email' => 'ortu-pdf@test.com',
            'sekolah_id' => null,
        ]);
        $ortuUser->assignRole('Orang Tua');
        $f['anak']->update(['user_id' => $ortuUser->id]);

        MonevSummary::create([
            'anak_id' => $f['anak']->id,
            'tahun' => 2026,
            'bulan' => 6,
            'ringkasan' => '[GAMBARAN_UMUM]\nPerkembangan baik.',
            'sumber' => MonevSummary::SUMBER_OTOMATIS,
            'generated_at' => now(),
        ]);

        $response = $this->actingAs($ortuUser)->get(route('orangtua.monev.export-pdf', [
            'anak' => $f['anak']->id,
            'tahun' => 2026,
            'bulan' => 6,
        ]));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));
    }
}
