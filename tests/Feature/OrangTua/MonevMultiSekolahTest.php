<?php

namespace Tests\Feature\OrangTua;

use App\Models\Anak;
use App\Models\Lembaga;
use App\Models\MonevSummary;
use App\Models\Sekolah;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonevMultiSekolahTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_monev_index_only_shows_anak_from_active_sekolah(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 15));
        $fixtures = $this->createOrtuWithAnakInTwoSekolahs();

        $this->actingAs($fixtures['ortu'])
            ->get(route('orangtua.monev.index'))
            ->assertOk()
            ->assertSee('Anak Sekolah A')
            ->assertDontSee('Anak Sekolah B');

        $this->actingAs($fixtures['ortu'])
            ->withSession(['ortu_active_sekolah_id' => $fixtures['sekolahB']->id])
            ->get(route('orangtua.monev.index'))
            ->assertOk()
            ->assertSee('Anak Sekolah B')
            ->assertDontSee('Anak Sekolah A');

        Carbon::setTestNow();
    }

    public function test_monev_index_shows_summary_for_selected_anak_in_active_sekolah(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 15));
        $fixtures = $this->createOrtuWithAnakInTwoSekolahs();

        MonevSummary::create([
            'anak_id' => $fixtures['anakA']->id,
            'tahun' => 2026,
            'bulan' => 6,
            'ringkasan' => '[GAMBARAN_UMUM]\nRingkasan sekolah A.',
            'sumber' => MonevSummary::SUMBER_OTOMATIS,
            'generated_at' => now(),
        ]);

        MonevSummary::create([
            'anak_id' => $fixtures['anakB']->id,
            'tahun' => 2026,
            'bulan' => 6,
            'ringkasan' => '[GAMBARAN_UMUM]\nRingkasan sekolah B.',
            'sumber' => MonevSummary::SUMBER_OTOMATIS,
            'generated_at' => now(),
        ]);

        $this->actingAs($fixtures['ortu'])
            ->get(route('orangtua.monev.index'))
            ->assertOk()
            ->assertSee('Ringkasan sekolah A.')
            ->assertDontSee('Ringkasan sekolah B.');

        Carbon::setTestNow();
    }

    public function test_monev_show_not_found_for_anak_in_other_sekolah(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 15));
        $fixtures = $this->createOrtuWithAnakInTwoSekolahs();

        // Default tenant = sekolah A (anak pertama by id)
        $this->actingAs($fixtures['ortu'])
            ->get(route('orangtua.monev.show', [
                'anak' => $fixtures['anakB']->id,
                'tahun' => 2026,
                'bulan' => 6,
            ]))
            ->assertNotFound();

        Carbon::setTestNow();
    }

    public function test_monev_show_redirects_for_own_anak_in_active_sekolah(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 15));
        $fixtures = $this->createOrtuWithAnakInTwoSekolahs();

        $this->actingAs($fixtures['ortu'])
            ->get(route('orangtua.monev.show', [
                'anak' => $fixtures['anakA']->id,
                'tahun' => 2026,
                'bulan' => 6,
            ]))
            ->assertRedirect(route('orangtua.monev.index', [
                'anak_id' => $fixtures['anakA']->id,
                'tahun' => 2026,
                'bulan' => 6,
            ]));

        Carbon::setTestNow();
    }

    public function test_monev_index_excludes_pending_anak(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 15));
        $fixtures = $this->createOrtuWithAnakInTwoSekolahs();

        Anak::withoutSekolahScope()->create([
            'user_id' => $fixtures['ortu']->id,
            'sekolah_id' => $fixtures['sekolahA']->id,
            'name' => 'Anak Pending',
            'status' => 'pending',
        ]);

        $this->actingAs($fixtures['ortu'])
            ->get(route('orangtua.monev.index'))
            ->assertOk()
            ->assertSee('Anak Sekolah A')
            ->assertDontSee('Anak Pending');

        Carbon::setTestNow();
    }

    public function test_monev_export_pdf_not_found_for_anak_in_other_sekolah(): void
    {
        $fixtures = $this->createOrtuWithAnakInTwoSekolahs();

        MonevSummary::create([
            'anak_id' => $fixtures['anakB']->id,
            'tahun' => 2026,
            'bulan' => 6,
            'ringkasan' => '[GAMBARAN_UMUM]\nRingkasan B.',
            'sumber' => MonevSummary::SUMBER_OTOMATIS,
            'generated_at' => now(),
        ]);

        $this->actingAs($fixtures['ortu'])
            ->get(route('orangtua.monev.export-pdf', [
                'anak' => $fixtures['anakB']->id,
                'tahun' => 2026,
                'bulan' => 6,
            ]))
            ->assertNotFound();
    }

    public function test_monev_export_pdf_works_after_switching_active_sekolah(): void
    {
        $fixtures = $this->createOrtuWithAnakInTwoSekolahs();

        MonevSummary::create([
            'anak_id' => $fixtures['anakB']->id,
            'tahun' => 2026,
            'bulan' => 6,
            'ringkasan' => '[GAMBARAN_UMUM]\nRingkasan B.',
            'sumber' => MonevSummary::SUMBER_OTOMATIS,
            'generated_at' => now(),
        ]);

        $response = $this->actingAs($fixtures['ortu'])
            ->withSession(['ortu_active_sekolah_id' => $fixtures['sekolahB']->id])
            ->get(route('orangtua.monev.export-pdf', [
                'anak' => $fixtures['anakB']->id,
                'tahun' => 2026,
                'bulan' => 6,
            ]));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));
    }

    public function test_orang_tua_without_sekolah_id_can_access_monev_via_session_tenant(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 15));
        $fixtures = $this->createOrtuWithAnakInTwoSekolahs();

        $this->assertNull($fixtures['ortu']->sekolah_id);

        $this->actingAs($fixtures['ortu'])
            ->get(route('orangtua.monev.index'))
            ->assertOk()
            ->assertSee('Anak Sekolah A');

        Carbon::setTestNow();
    }

    /**
     * @return array{
     *     ortu: User,
     *     sekolahA: Sekolah,
     *     sekolahB: Sekolah,
     *     anakA: Anak,
     *     anakB: Anak
     * }
     */
    protected function createOrtuWithAnakInTwoSekolahs(): array
    {
        $lembaga = Lembaga::create(['name' => 'Yayasan Multi', 'status' => 'active']);
        $sekolahA = Sekolah::create([
            'lembaga_id' => $lembaga->id,
            'name' => 'PAUD Alpha',
            'status' => 'active',
        ]);
        $sekolahB = Sekolah::create([
            'lembaga_id' => $lembaga->id,
            'name' => 'PAUD Beta',
            'status' => 'active',
        ]);

        $ortu = User::factory()->create([
            'sekolah_id' => null,
            'lembaga_id' => null,
        ]);
        $ortu->assignRole('Orang Tua');

        $anakA = Anak::withoutSekolahScope()->create([
            'user_id' => $ortu->id,
            'sekolah_id' => $sekolahA->id,
            'name' => 'Anak Sekolah A',
            'status' => 'approved',
        ]);
        $anakB = Anak::withoutSekolahScope()->create([
            'user_id' => $ortu->id,
            'sekolah_id' => $sekolahB->id,
            'name' => 'Anak Sekolah B',
            'status' => 'approved',
        ]);

        return compact('ortu', 'sekolahA', 'sekolahB', 'anakA', 'anakB');
    }
}
