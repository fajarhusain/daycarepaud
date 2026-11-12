<?php

namespace Tests\Feature\Api\V1;

use App\Models\Anak;
use App\Models\PembayaranBulanan;
use App\Models\Pengumuman;
use App\Models\PengumumanRead;
use App\Models\Sekolah;
use App\Support\ApiResponse;
use Illuminate\Support\Facades\Route;

class TenantContextTest extends ApiTestCase
{
    public function test_context_returns_bootstrap_for_accessible_school(): void
    {
        $lembaga = $this->makeLembaga();
        $sekolah = $this->makeSekolah($lembaga, 'PAUD Konteks');
        $kelas = $this->makeKelas($sekolah);
        $ortu = $this->makeOrtu([$sekolah]);

        $anak = Anak::withoutSekolahScope()
            ->where('user_id', $ortu->id)
            ->firstOrFail();
        $anak->update(['kelas_id' => $kelas->id]);

        $announcementGlobal = Pengumuman::create([
            'sekolah_id' => $sekolah->id,
            'kelas_id' => null,
            'judul' => 'Pengumuman Global',
            'kategori' => 'umum',
            'isi' => 'Isi pengumuman global.',
            'mulai_tayang' => now()->subDay()->toDateString(),
            'selesai_tayang' => now()->addDay()->toDateString(),
            'is_active' => true,
        ]);

        Pengumuman::create([
            'sekolah_id' => $sekolah->id,
            'kelas_id' => $kelas->id,
            'judul' => 'Pengumuman Kelas',
            'kategori' => 'kelas',
            'isi' => 'Isi pengumuman kelas.',
            'mulai_tayang' => now()->subDay()->toDateString(),
            'selesai_tayang' => now()->addDay()->toDateString(),
            'is_active' => true,
        ]);

        PengumumanRead::create([
            'pengumuman_id' => $announcementGlobal->id,
            'user_id' => $ortu->id,
            'read_at' => now(),
        ]);

        PembayaranBulanan::create([
            'sekolah_id' => $sekolah->id,
            'anak_id' => $anak->id,
            'periode_bulan' => 10,
            'periode_tahun' => 2026,
            'total_bayar' => 350000,
            'status' => 'pending',
        ]);

        $this->withHeaders($this->authHeaders($ortu))
            ->getJson('/api/v1/sekolah/'.$sekolah->id.'/context')
            ->assertOk()
            ->assertJsonPath('data.sekolah.id', $sekolah->id)
            ->assertJsonPath('data.anak.0.id', $anak->id)
            ->assertJsonPath('data.anak.0.kelas.id', $kelas->id)
            ->assertJsonPath('data.fitur.chat', true)
            ->assertJsonPath('data.pengumuman_belum_dibaca', 1)
            ->assertJsonPath('data.tagihan_belum_dibayar', 1);
    }

    public function test_context_hides_school_without_access(): void
    {
        $lembaga = $this->makeLembaga();
        $schoolA = $this->makeSekolah($lembaga, 'PAUD A');
        $schoolB = $this->makeSekolah($lembaga, 'PAUD B');
        $ortu = $this->makeOrtu([$schoolA]);

        $this->withHeaders($this->authHeaders($ortu))
            ->getJson('/api/v1/sekolah/'.$schoolB->id.'/context')
            ->assertNotFound()
            ->assertJsonPath('error.code', 'SEKOLAH_TIDAK_DITEMUKAN');
    }

    public function test_context_rejects_suspended_school(): void
    {
        $lembaga = $this->makeLembaga();
        $sekolah = $this->makeSekolah($lembaga, 'PAUD Suspend', Sekolah::STATUS_SUSPENDED);
        $ortu = $this->makeOrtu([$sekolah]);

        $this->withHeaders($this->authHeaders($ortu))
            ->getJson('/api/v1/sekolah/'.$sekolah->id.'/context')
            ->assertForbidden()
            ->assertJsonPath('error.code', 'SEKOLAH_TIDAK_AKTIF');
    }

    public function test_context_requires_authentication(): void
    {
        $sekolah = $this->makeSekolah($this->makeLembaga());

        $this->getJson('/api/v1/sekolah/'.$sekolah->id.'/context')
            ->assertUnauthorized()
            ->assertJsonPath('error.code', 'SESI_BERAKHIR');
    }

    public function test_dynamic_role_registry_controls_tenant_route(): void
    {
        $lembaga = $this->makeLembaga();
        $sekolah = $this->makeSekolah($lembaga, 'PAUD Peran');
        $admin = $this->makeAdmin($sekolah);
        $ortu = $this->makeOrtu([$sekolah]);

        Route::middleware(['api.request-id', 'auth:sanctum', 'api.tenant', 'api.role:admin-sekolah'])
            ->get('/api/v1/sekolah/{sekolah}/_test/admin', fn () => ApiResponse::success(['ok' => true]));

        $this->withHeaders($this->authHeaders($admin))
            ->getJson('/api/v1/sekolah/'.$sekolah->id.'/_test/admin')
            ->assertOk()
            ->assertJsonPath('data.ok', true);

        $this->app['auth']->forgetGuards();

        $this->withHeaders($this->authHeaders($ortu))
            ->getJson('/api/v1/sekolah/'.$sekolah->id.'/_test/admin')
            ->assertForbidden()
            ->assertJsonPath('error.code', 'ROLE_TIDAK_SESUAI');
    }
}
