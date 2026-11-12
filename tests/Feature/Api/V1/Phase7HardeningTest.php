<?php

namespace Tests\Feature\Api\V1;

use App\Models\KegiatanRutin;
use App\Models\Kesehatan;
use App\Models\KritikSaran;
use App\Models\MonevSummary;
use App\Models\PembayaranBulanan;
use App\Models\Pencapaian;
use App\Models\Presensi;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class Phase7HardeningTest extends ApiTestCase
{
    public function test_missing_bound_model_returns_not_found_envelope(): void
    {
        $lembaga = $this->makeLembaga();
        $school = $this->makeSekolah($lembaga);
        $parent = $this->makeOrtu([$school]);

        $this->getJson(
            '/api/v1/sekolah/'.$school->id.'/anak/999999',
            $this->authHeaders($parent),
        )
            ->assertNotFound()
            ->assertJsonPath('error.code', 'DATA_TIDAK_DITEMUKAN')
            ->assertJsonPath('error.message', 'Data yang Anda cari tidak ditemukan.');
    }

    public function test_legacy_route_without_role_returns_forbidden_envelope(): void
    {
        $lembaga = $this->makeLembaga();
        $school = $this->makeSekolah($lembaga);
        $user = User::factory()->create([
            'lembaga_id' => $school->lembaga_id,
            'sekolah_id' => $school->id,
        ]);

        $this->getJson('/api/v1/orang-tua/anak', $this->authHeaders($user))
            ->assertForbidden()
            ->assertJsonPath('error.code', 'AKSES_DITOLAK');
    }

    public function test_validation_before_and_between_are_in_indonesian(): void
    {
        $lembaga = $this->makeLembaga();
        $school = $this->makeSekolah($lembaga);
        $parent = $this->makeOrtu([$school]);
        $anak = $this->makeAnakFor($parent, $school);
        $headers = $this->authHeaders($parent);

        $this->patchJson(
            '/api/v1/sekolah/'.$school->id.'/anak/'.$anak->id,
            ['dob' => now()->addDay()->toDateString()],
            $headers,
        )
            ->assertUnprocessable()
            ->assertJsonPath('error.code', 'VALIDASI_GAGAL')
            ->assertJsonPath('error.fields.dob.0', 'Tanggal lahir harus sebelum hari ini.');

        $this->getJson(
            '/api/v1/sekolah/'.$school->id.'/tagihan?periode_bulan=13',
            $headers,
        )
            ->assertUnprocessable()
            ->assertJsonPath('error.fields.periode_bulan.0', 'Bulan tagihan harus antara 1 dan 12.');
    }

    public function test_resources_from_another_parent_in_same_tenant_are_not_accessible(): void
    {
        $lembaga = $this->makeLembaga();
        $school = $this->makeSekolah($lembaga);
        $class = $this->makeKelas($school);
        $pengajar = $this->makePengajar($school);
        $matrikulasi = $this->makeMatrikulasi($school);

        $parent = $this->makeOrtu([$school]);
        $otherParent = $this->makeOrtu([$school]);
        $otherAnak = $this->makeAnakFor($otherParent, $school, ['kelas_id' => $class->id]);

        $pencapaian = Pencapaian::create([
            'anak_id' => $otherAnak->id,
            'pengajar_id' => $pengajar->id,
            'matrikulasi_id' => $matrikulasi->id,
            'score' => 'BSH',
            'feedback' => 'Milik orang tua lain.',
        ]);

        $monev = MonevSummary::create([
            'anak_id' => $otherAnak->id,
            'tahun' => now()->year,
            'bulan' => now()->month,
            'ringkasan' => 'Ringkasan orang tua lain.',
            'sumber' => 'otomatis',
            'generated_at' => now(),
        ]);

        $kegiatanRutin = KegiatanRutin::create([
            'sekolah_id' => $school->id,
            'kelas_id' => $class->id,
            'anak_id' => $otherAnak->id,
            'pengajar_id' => $pengajar->id,
            'tanggal' => now()->toDateString(),
            'aspek' => 'Kemandirian',
            'kegiatan' => 'Makan sendiri',
            'status_pencapaian' => 'Lancar',
        ]);

        $kesehatan = Kesehatan::create([
            'anak_id' => $otherAnak->id,
            'tanggal_pemeriksaan' => now()->toDateString(),
            'berat_badan' => 15,
        ]);

        $tagihan = PembayaranBulanan::withoutSekolahScope()->create([
            'sekolah_id' => $school->id,
            'anak_id' => $otherAnak->id,
            'periode_bulan' => 10,
            'periode_tahun' => 2026,
            'total_bayar' => 100000,
            'status' => 'pending',
        ]);

        $masukan = KritikSaran::create([
            'sekolah_id' => $school->id,
            'user_id' => $otherParent->id,
            'message' => 'Masukan milik orang tua lain.',
            'status' => 'Terkirim',
        ]);

        Presensi::create([
            'sekolah_id' => $school->id,
            'anak_id' => $otherAnak->id,
            'tanggal' => now()->toDateString(),
            'hadir' => true,
            'status' => 'hadir',
        ]);

        $headers = $this->authHeaders($parent);
        $base = '/api/v1/sekolah/'.$school->id.'/';

        $this->getJson($base.'anak/'.$otherAnak->id, $headers)
            ->assertNotFound()
            ->assertJsonPath('error.code', 'ANAK_TIDAK_DITEMUKAN');

        $this->getJson($base.'pencapaian/'.$pencapaian->id, $headers)
            ->assertNotFound()
            ->assertJsonPath('error.code', 'DATA_TIDAK_DITEMUKAN');

        $this->getJson($base.'monev/'.$monev->id, $headers)
            ->assertNotFound()
            ->assertJsonPath('error.code', 'LAPORAN_BELUM_TERSEDIA');

        $this->getJson($base.'kegiatan-rutin/'.$kegiatanRutin->id, $headers)
            ->assertNotFound()
            ->assertJsonPath('error.code', 'DATA_TIDAK_DITEMUKAN');

        $this->getJson($base.'kesehatan/'.$kesehatan->id, $headers)
            ->assertNotFound()
            ->assertJsonPath('error.code', 'DATA_TIDAK_DITEMUKAN');

        $this->getJson($base.'tagihan/'.$tagihan->id, $headers)
            ->assertNotFound()
            ->assertJsonPath('error.code', 'DATA_TIDAK_DITEMUKAN');

        $this->getJson($base.'masukan/'.$masukan->id, $headers)
            ->assertNotFound()
            ->assertJsonPath('error.code', 'DATA_TIDAK_DITEMUKAN');

        $this->getJson($base.'presensi', $headers)
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_upload_proof_cannot_target_another_parents_tagihan(): void
    {
        Storage::fake('public');

        $lembaga = $this->makeLembaga();
        $school = $this->makeSekolah($lembaga);
        $parent = $this->makeOrtu([$school]);
        $otherParent = $this->makeOrtu([$school]);
        $otherAnak = $this->makeAnakFor($otherParent, $school);

        $tagihan = PembayaranBulanan::withoutSekolahScope()->create([
            'sekolah_id' => $school->id,
            'anak_id' => $otherAnak->id,
            'periode_bulan' => 10,
            'periode_tahun' => 2026,
            'total_bayar' => 100000,
            'status' => 'pending',
        ]);

        $headers = array_merge($this->authHeaders($parent), [
            'Accept' => 'application/json',
            'Idempotency-Key' => 'cross-tenant-upload',
        ]);

        $this->post(
            '/api/v1/sekolah/'.$school->id.'/tagihan/'.$tagihan->id.'/bukti-pembayaran',
            ['bukti_transfer' => $this->proofFile()],
            $headers,
        )
            ->assertNotFound()
            ->assertJsonPath('error.code', 'DATA_TIDAK_DITEMUKAN');

        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_signed_media_rejects_tampered_expired_and_unsafe_files(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put(
            'anak/foto.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wl2n1cAAAAASUVORK5CYII='),
        );
        Storage::disk('public')->put('dokumen.html', '<html><script>alert(1)</script></html>');

        $valid = URL::temporarySignedRoute('api.v1.media.show', now()->addMinute(), [
            'path' => 'anak/foto.png',
        ]);

        $this->get($valid)
            ->assertOk()
            ->assertHeader('content-type', 'image/png');

        $this->get(str_replace('signature=', 'signature=invalid', $valid))
            ->assertStatus(410)
            ->assertJsonPath('error.code', 'TAUTAN_KEDALUWARSA');

        $expired = URL::temporarySignedRoute('api.v1.media.show', now()->subMinute(), [
            'path' => 'anak/foto.png',
        ]);

        $this->get($expired)
            ->assertStatus(410)
            ->assertJsonPath('error.code', 'TAUTAN_KEDALUWARSA');

        $this->get('/api/v1/media?path=anak/foto.png')
            ->assertStatus(410)
            ->assertJsonPath('error.code', 'TAUTAN_KEDALUWARSA');

        $traversal = URL::temporarySignedRoute('api.v1.media.show', now()->addMinute(), [
            'path' => '../.env',
        ]);

        $this->get($traversal)
            ->assertNotFound()
            ->assertJsonPath('error.code', 'DATA_TIDAK_DITEMUKAN');

        $html = URL::temporarySignedRoute('api.v1.media.show', now()->addMinute(), [
            'path' => 'dokumen.html',
        ]);

        $this->get($html)
            ->assertNotFound()
            ->assertJsonPath('error.code', 'DATA_TIDAK_DITEMUKAN');
    }

    public function test_legacy_responses_send_deprecation_headers(): void
    {
        $this->postJson('/api/v1/auth/login', [])
            ->assertUnprocessable()
            ->assertHeader('Deprecation', 'true')
            ->assertHeader('Sunset');

        $this->getJson('/api/v1/ping')
            ->assertOk()
            ->assertHeaderMissing('Deprecation');
    }

    public function test_unhandled_api_exception_does_not_log_sensitive_message(): void
    {
        Log::spy();

        Route::middleware('api.request-id')
            ->get('/api/v1/_test/private-error', function () {
                throw new \RuntimeException('NIK 123456789 dan email rahasia@example.com');
            });

        $this->getJson('/api/v1/_test/private-error')
            ->assertStatus(500)
            ->assertJsonPath('error.code', 'KESALAHAN_SERVER');

        Log::shouldHaveReceived('error')
            ->withArgs(function (string $message, array $context): bool {
                $encoded = json_encode($context);

                return $message === 'Unhandled API exception.'
                    && is_string($encoded)
                    && ! str_contains($encoded, 'NIK 123456789')
                    && ! str_contains($encoded, 'rahasia@example.com')
                    && ! array_key_exists('message', $context);
            })
            ->atLeast()
            ->once();
    }

    private function proofFile(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            'bukti.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wl2n1cAAAAASUVORK5CYII='),
        );
    }
}
