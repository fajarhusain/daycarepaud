<?php

namespace Tests\Feature\MonevGuru;

use App\Models\Lembaga;
use App\Models\MonevGuruEvaluasi;
use App\Models\MonevGuruKriteria;
use App\Models\Pengajar;
use App\Models\Sekolah;
use App\Models\User;
use App\Services\MonevGuruService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MonevGuruTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    /** @return array{sekolah: Sekolah, admin: User, guruUser: User, pengajar: Pengajar, kriterias: \Illuminate\Support\Collection<int, MonevGuruKriteria>} */
    protected function createFixtures(): array
    {
        $lembaga = Lembaga::first();
        $sekolah = Sekolah::first();

        $admin = User::factory()->create([
            'email' => 'admin-monev-guru@test.com',
            'password' => Hash::make('password'),
            'lembaga_id' => $lembaga->id,
            'sekolah_id' => $sekolah->id,
        ]);
        $admin->assignRole('Admin Sekolah');

        $guruUser = User::factory()->create([
            'email' => 'guru-monev@test.com',
            'password' => Hash::make('password'),
            'sekolah_id' => $sekolah->id,
        ]);
        $guruUser->assignRole('Pengajar');

        $pengajar = Pengajar::create([
            'user_id' => $guruUser->id,
            'sekolah_id' => $sekolah->id,
            'name' => 'Guru Monev Test',
        ]);

        MonevGuruKriteria::seedDefaultsForSekolah($sekolah->id);
        $kriterias = MonevGuruKriteria::where('sekolah_id', $sekolah->id)->where('is_active', true)->orderBy('urutan')->get();

        return compact('sekolah', 'admin', 'guruUser', 'pengajar', 'kriterias');
    }

    /** @return array<int, array{kriteria_id: int, skor: int, catatan: string|null}> */
    protected function sampleItems($kriterias, int $defaultSkor = 80): array
    {
        return $kriterias->map(fn (MonevGuruKriteria $k) => [
            'kriteria_id' => $k->id,
            'skor' => $defaultSkor,
            'catatan' => null,
        ])->all();
    }

    public function test_admin_can_create_draft_evaluasi(): void
    {
        $f = $this->createFixtures();

        $response = $this->actingAs($f['admin'])->post(route('admin.monev-guru.store'), [
            'pengajar_id' => $f['pengajar']->id,
            'judul' => 'Monev Test',
            'periode_mulai' => '2026-01-01',
            'periode_selesai' => '2026-06-30',
            'catatan_umum' => 'Baik',
            'rekomendasi' => 'Pertahankan',
            'items' => $this->sampleItems($f['kriterias'], 70),
            'finalize' => 0,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('monev_guru_evaluasis', [
            'pengajar_id' => $f['pengajar']->id,
            'status' => MonevGuruEvaluasi::STATUS_DRAFT,
        ]);
    }

    public function test_admin_can_finalize_evaluasi(): void
    {
        $f = $this->createFixtures();

        $response = $this->actingAs($f['admin'])->post(route('admin.monev-guru.store'), [
            'pengajar_id' => $f['pengajar']->id,
            'judul' => 'Monev Final',
            'periode_mulai' => '2026-01-01',
            'periode_selesai' => '2026-06-30',
            'items' => $this->sampleItems($f['kriterias'], 90),
            'finalize' => 1,
        ]);

        $response->assertRedirect();

        $evaluasi = MonevGuruEvaluasi::where('pengajar_id', $f['pengajar']->id)->first();
        $this->assertNotNull($evaluasi);
        $this->assertSame(MonevGuruEvaluasi::STATUS_FINAL, $evaluasi->status);
        $this->assertSame(90, $evaluasi->skor_keseluruhan);
        $this->assertNotNull($evaluasi->finalized_at);
    }

    public function test_guru_can_view_final_evaluasi_only(): void
    {
        $f = $this->createFixtures();

        $draft = MonevGuruEvaluasi::create([
            'sekolah_id' => $f['sekolah']->id,
            'pengajar_id' => $f['pengajar']->id,
            'evaluator_user_id' => $f['admin']->id,
            'periode_mulai' => '2026-01-01',
            'periode_selesai' => '2026-06-30',
            'status' => MonevGuruEvaluasi::STATUS_DRAFT,
        ]);

        $final = MonevGuruEvaluasi::create([
            'sekolah_id' => $f['sekolah']->id,
            'pengajar_id' => $f['pengajar']->id,
            'evaluator_user_id' => $f['admin']->id,
            'judul' => 'Final Evaluasi',
            'periode_mulai' => '2026-01-01',
            'periode_selesai' => '2026-06-30',
            'skor_keseluruhan' => 85,
            'status' => MonevGuruEvaluasi::STATUS_FINAL,
            'finalized_at' => now(),
        ]);

        $this->actingAs($f['guruUser'])->get(route('pengajar.monev-guru.index'))
            ->assertOk()
            ->assertSee('Final Evaluasi')
            ->assertDontSee('Draft');

        $this->actingAs($f['guruUser'])->get(route('pengajar.monev-guru.show', $final))
            ->assertOk()
            ->assertSee('85');

        $this->actingAs($f['guruUser'])->get(route('pengajar.monev-guru.show', $draft))
            ->assertNotFound();
    }

    public function test_skor_keseluruhan_weighted_average(): void
    {
        $f = $this->createFixtures();
        $service = app(MonevGuruService::class);

        $evaluasi = MonevGuruEvaluasi::create([
            'sekolah_id' => $f['sekolah']->id,
            'pengajar_id' => $f['pengajar']->id,
            'evaluator_user_id' => $f['admin']->id,
            'periode_mulai' => '2026-01-01',
            'periode_selesai' => '2026-06-30',
            'status' => MonevGuruEvaluasi::STATUS_DRAFT,
        ]);

        $items = [];
        foreach ($f['kriterias'] as $k) {
            $skor = $k->nama === 'Pelaksanaan kegiatan intrakurikuler' ? 100 : 50;
            $items[] = ['kriteria_id' => $k->id, 'skor' => $skor, 'catatan' => null];
        }

        $service->syncPenilaianItems($evaluasi, $items);
        $evaluasi->load('items.kriteria');

        $skor = $service->computeSkorKeseluruhan($evaluasi);

        $this->assertGreaterThan(50, $skor);
        $this->assertLessThan(100, $skor);
    }

    public function test_finalize_rejects_invalid_skor(): void
    {
        $f = $this->createFixtures();

        $items = $f['kriterias']->map(fn (MonevGuruKriteria $k, int $i) => [
            'kriteria_id' => $k->id,
            'skor' => $i === 0 ? 150 : 80,
            'catatan' => null,
        ])->all();

        $response = $this->actingAs($f['admin'])->post(route('admin.monev-guru.store'), [
            'pengajar_id' => $f['pengajar']->id,
            'periode_mulai' => '2026-01-01',
            'periode_selesai' => '2026-06-30',
            'items' => $items,
            'finalize' => 1,
        ]);

        $response->assertSessionHasErrors();
    }

    public function test_admin_can_access_monev_guru_index(): void
    {
        $f = $this->createFixtures();

        $this->actingAs($f['admin'])->get(route('admin.monev-guru.index'))
            ->assertOk()
            ->assertSee('Monev Guru');
    }

    public function test_admin_can_delete_draft_evaluasi(): void
    {
        $f = $this->createFixtures();

        $this->actingAs($f['admin'])->post(route('admin.monev-guru.store'), [
            'pengajar_id' => $f['pengajar']->id,
            'judul' => 'Draft Hapus',
            'periode_mulai' => '2026-01-01',
            'periode_selesai' => '2026-06-30',
            'items' => $this->sampleItems($f['kriterias'], 75),
            'finalize' => 0,
        ]);

        $evaluasi = MonevGuruEvaluasi::where('pengajar_id', $f['pengajar']->id)->first();
        $this->assertNotNull($evaluasi);

        $this->actingAs($f['admin'])->delete(route('admin.monev-guru.destroy', $evaluasi))
            ->assertRedirect(route('admin.monev-guru.index'));

        $this->assertDatabaseMissing('monev_guru_evaluasis', ['id' => $evaluasi->id]);
    }

    public function test_admin_cannot_delete_final_evaluasi(): void
    {
        $f = $this->createFixtures();

        $this->actingAs($f['admin'])->post(route('admin.monev-guru.store'), [
            'pengajar_id' => $f['pengajar']->id,
            'judul' => 'Final Hapus',
            'periode_mulai' => '2026-01-01',
            'periode_selesai' => '2026-06-30',
            'items' => $this->sampleItems($f['kriterias'], 80),
            'finalize' => 1,
        ]);

        $evaluasi = MonevGuruEvaluasi::where('pengajar_id', $f['pengajar']->id)->first();
        $this->assertNotNull($evaluasi);
        $this->assertTrue($evaluasi->isFinal());

        $this->actingAs($f['admin'])->delete(route('admin.monev-guru.destroy', $evaluasi))
            ->assertForbidden();

        $this->assertDatabaseHas('monev_guru_evaluasis', ['id' => $evaluasi->id]);
    }
}
