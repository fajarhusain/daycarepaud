<?php

namespace Tests\Feature\Api\V1;

use App\Models\PembayaranBulanan;
use App\Models\PembayaranBulananItem;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class Phase5PaymentsTest extends ApiTestCase
{
    public function test_tagihan_list_uses_offset_pagination_and_tenant_scope(): void
    {
        $lembaga = $this->makeLembaga();
        $school = $this->makeSekolah($lembaga);
        $otherSchool = $this->makeSekolah($lembaga, 'Sekolah Lain');
        $parent = User::factory()->create();
        $parent->assignRole('Orang Tua');
        $anak = $this->makeAnakFor($parent, $school);
        $otherAnak = $this->makeAnakFor($parent, $school, ['name' => 'Anak Kedua']);

        $otherParent = User::factory()->create();
        $otherParent->assignRole('Orang Tua');
        $otherAnakParent = $this->makeAnakFor($otherParent, $school);

        $first = $this->makeTagihan($school, $anak, [
            'periode_bulan' => 10,
            'periode_tahun' => 2026,
            'subtotal' => 400000,
            'nilai_diskon' => 50000,
            'diskon_keterangan' => 'Diskon saudara',
            'total_bayar' => 350000,
        ]);
        PembayaranBulananItem::create([
            'pembayaran_bulanan_id' => $first->id,
            'nama_item' => 'Seragam',
            'jumlah' => 25000,
        ]);

        $second = $this->makeTagihan($school, $otherAnak, [
            'periode_bulan' => 9,
            'periode_tahun' => 2026,
            'total_bayar' => 300000,
            'status' => 'approved',
        ]);
        $this->makeTagihan($otherSchool, $anak, ['periode_bulan' => 8, 'periode_tahun' => 2026]);
        $this->makeTagihan($school, $otherAnakParent, ['periode_bulan' => 10, 'periode_tahun' => 2026]);

        $headers = $this->authHeaders($parent);
        $url = '/api/v1/sekolah/'.$school->id.'/tagihan?anak_id='.$anak->id.'&per_page=1';

        $this->getJson($url, $headers)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $first->id)
            ->assertJsonPath('data.0.anak.id', $anak->id)
            ->assertJsonPath('data.0.periode.label', 'Oktober 2026')
            ->assertJsonPath('data.0.subtotal', 400000)
            ->assertJsonPath('data.0.nilai_diskon', 50000)
            ->assertJsonPath('data.0.diskon_keterangan', 'Diskon saudara')
            ->assertJsonPath('data.0.biaya_tambahan', 25000)
            ->assertJsonPath('data.0.total_bayar', 350000)
            ->assertJsonPath('data.0.total_bayar_label', 'Rp 350.000')
            ->assertJsonPath('data.0.status', 'pending')
            ->assertJsonPath('data.0.status_label', 'Menunggu')
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('meta.last_page', 1)
            ->assertJsonStructure(['links' => ['first', 'prev', 'next']]);

        $this->getJson('/api/v1/sekolah/'.$school->id.'/tagihan?status=approved', $headers)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $second->id)
            ->assertJsonPath('data.0.status_label', 'Lunas');

        $this->getJson('/api/v1/sekolah/'.$school->id.'/tagihan?anak_id='.$otherAnakParent->id, $headers)
            ->assertNotFound()
            ->assertJsonPath('error.code', 'ANAK_TIDAK_DITEMUKAN');
    }

    public function test_tagihan_list_second_page_and_filters(): void
    {
        $lembaga = $this->makeLembaga();
        $school = $this->makeSekolah($lembaga);
        $parent = User::factory()->create();
        $parent->assignRole('Orang Tua');
        $anak = $this->makeAnakFor($parent, $school);

        foreach ([7, 8, 9] as $month) {
            $this->makeTagihan($school, $anak, [
                'periode_bulan' => $month,
                'periode_tahun' => 2026,
                'status' => 'pending',
            ]);
        }

        $headers = $this->authHeaders($parent);
        $base = '/api/v1/sekolah/'.$school->id.'/tagihan';

        $pageOne = $this->getJson($base.'?per_page=2', $headers)
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonPath('data.0.periode.bulan', 9);

        $this->assertNotNull($pageOne->json('links.next'));
        $this->assertNull($pageOne->json('links.prev'));

        $this->getJson($base.'?per_page=2&page=2', $headers)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('data.0.periode.bulan', 7);

        $this->getJson($base.'?periode_bulan=8&periode_tahun=2026', $headers)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.periode.bulan', 8);

        $this->getJson($base.'?status=tidak-valid', $headers)
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDASI_GAGAL');
    }

    public function test_tagihan_summary_and_detail_are_tenant_safe(): void
    {
        $lembaga = $this->makeLembaga();
        $school = $this->makeSekolah($lembaga);
        $otherSchool = $this->makeSekolah($lembaga, 'Sekolah Lain');
        $parent = User::factory()->create();
        $parent->assignRole('Orang Tua');
        $anak = $this->makeAnakFor($parent, $school);
        $otherAnak = $this->makeAnakFor($parent, $school, ['name' => 'Anak Kedua']);

        $otherParent = User::factory()->create();
        $otherParent->assignRole('Orang Tua');
        $otherAnakParent = $this->makeAnakFor($otherParent, $school);

        $pending = $this->makeTagihan($school, $anak, [
            'periode_bulan' => 10,
            'periode_tahun' => 2026,
            'total_bayar' => 100000,
            'status' => 'pending',
        ]);
        $this->makeTagihan($school, $anak, [
            'periode_bulan' => 9,
            'periode_tahun' => 2026,
            'total_bayar' => 50000,
            'status' => 'approved',
        ]);
        $rejected = $this->makeTagihan($school, $otherAnak, [
            'periode_bulan' => 10,
            'periode_tahun' => 2026,
            'total_bayar' => 20000,
            'status' => 'rejected',
        ]);
        $otherParentBill = $this->makeTagihan($school, $otherAnakParent, [
            'periode_bulan' => 10,
            'periode_tahun' => 2026,
            'total_bayar' => 999999,
            'status' => 'pending',
        ]);
        $this->makeTagihan($otherSchool, $anak, [
            'periode_bulan' => 10,
            'periode_tahun' => 2026,
            'total_bayar' => 777777,
            'status' => 'pending',
        ]);

        $headers = $this->authHeaders($parent);
        $base = '/api/v1/sekolah/'.$school->id.'/tagihan';

        $this->getJson($base.'/ringkasan', $headers)
            ->assertOk()
            ->assertJsonPath('data.total_belum_dibayar', 120000)
            ->assertJsonPath('data.total_belum_dibayar_label', 'Rp 120.000')
            ->assertJsonPath('data.jumlah_tagihan', 2)
            ->assertJsonPath('data.anak.0.id', $anak->id)
            ->assertJsonPath('data.anak.0.total_belum_dibayar', 100000)
            ->assertJsonPath('data.anak.1.id', $otherAnak->id)
            ->assertJsonPath('data.anak.1.total_belum_dibayar', 20000);

        $this->getJson($base.'/ringkasan?anak_id='.$anak->id, $headers)
            ->assertOk()
            ->assertJsonCount(1, 'data.anak')
            ->assertJsonPath('data.total_belum_dibayar', 100000);

        $this->getJson($base.'/'.$pending->id, $headers)
            ->assertOk()
            ->assertJsonPath('data.id', $pending->id)
            ->assertJsonPath('data.invoice_tersedia', false);

        $this->getJson($base.'/'.$otherParentBill->id, $headers)
            ->assertNotFound()
            ->assertJsonPath('error.code', 'DATA_TIDAK_DITEMUKAN');

        $this->getJson($base.'/'.$rejected->id, $headers)
            ->assertOk()
            ->assertJsonPath('data.status', 'rejected')
            ->assertJsonPath('data.status_label', 'Ditolak');
    }

    public function test_invoice_is_only_available_for_approved_tagihan(): void
    {
        $lembaga = $this->makeLembaga();
        $school = $this->makeSekolah($lembaga);
        $parent = User::factory()->create();
        $parent->assignRole('Orang Tua');
        $anak = $this->makeAnakFor($parent, $school);

        $approved = $this->makeTagihan($school, $anak, [
            'periode_bulan' => 10,
            'periode_tahun' => 2026,
            'status' => 'approved',
        ]);
        $pending = $this->makeTagihan($school, $anak, [
            'periode_bulan' => 11,
            'periode_tahun' => 2026,
            'status' => 'pending',
        ]);

        $headers = $this->authHeaders($parent);
        $base = '/api/v1/sekolah/'.$school->id.'/tagihan';

        $this->get($base.'/'.$approved->id.'/invoice', $headers)
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->getJson($base.'/'.$pending->id.'/invoice', $headers)
            ->assertForbidden()
            ->assertJsonPath('error.code', 'INVOICE_BELUM_TERSEDIA');
    }

    public function test_upload_proof_requires_idempotency_and_replays_the_same_response(): void
    {
        Storage::fake('public');

        $lembaga = $this->makeLembaga();
        $school = $this->makeSekolah($lembaga);
        $parent = User::factory()->create();
        $parent->assignRole('Orang Tua');
        $anak = $this->makeAnakFor($parent, $school);
        $tagihan = $this->makeTagihan($school, $anak, ['status' => 'pending']);

        $headers = array_merge($this->authHeaders($parent), ['Accept' => 'application/json']);
        $url = '/api/v1/sekolah/'.$school->id.'/tagihan/'.$tagihan->id.'/bukti-pembayaran';

        $this->post($url, ['bukti_transfer' => $this->proofFile()], $headers)
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDASI_GAGAL')
            ->assertJsonStructure(['error' => ['fields' => ['idempotency_key']]]);

        $this->post($url, [
            'bukti_transfer' => UploadedFile::fake()->createWithContent('bukti.txt', 'bukan gambar'),
        ], array_merge($headers, ['Idempotency-Key' => 'upload-bukti-invalid']))
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'BUKTI_PEMBAYARAN_TIDAK_VALID');

        $key = 'upload-bukti-1';
        $payload = ['bukti_transfer' => $this->proofFile(), 'catatan' => 'Transfer dari BCA'];

        $first = $this->post($url, $payload, array_merge($headers, ['Idempotency-Key' => $key]))
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.status_label', 'Menunggu')
            ->assertJsonPath('message', 'Bukti pembayaran berhasil diunggah. Menunggu verifikasi admin.')
            ->assertJsonPath('data.bukti_transfer.mime_type', 'image/png');

        $tagihan->refresh();
        $this->assertNotNull($tagihan->bukti_transfer);
        Storage::disk('public')->assertExists($tagihan->bukti_transfer);

        $proofUrl = $first->json('data.bukti_transfer.url');
        $this->assertIsString($proofUrl);
        $this->assertStringContainsString('disk=public', $proofUrl);
        $this->get($proofUrl)->assertOk()->assertHeader('content-type', 'image/png');

        $firstBody = $first->json();

        $replay = $this->post($url, $payload, array_merge($headers, ['Idempotency-Key' => $key]))
            ->assertCreated();

        $this->assertSame($firstBody, $replay->json());
        $this->assertSame(
            1,
            PembayaranBulanan::withoutSekolahScope()
                ->whereKey($tagihan->id)
                ->whereNotNull('bukti_transfer')
                ->count()
        );

        $this->post($url, [
            'bukti_transfer' => $this->proofFile(),
            'catatan' => 'Catatan berbeda',
        ], array_merge($headers, ['Idempotency-Key' => $key]))
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'IDEMPOTENCY_KEY_BERBEDA');

        $this->getJson($url, $headers)
            ->assertOk()
            ->assertJsonPath('data.tagihan_id', $tagihan->id)
            ->assertJsonPath('data.bukti_transfer.mime_type', 'image/png');
    }

    public function test_upload_replaces_old_proof_and_reopens_rejected_tagihan(): void
    {
        Storage::fake('public');

        $lembaga = $this->makeLembaga();
        $school = $this->makeSekolah($lembaga);
        $parent = User::factory()->create();
        $parent->assignRole('Orang Tua');
        $anak = $this->makeAnakFor($parent, $school);
        $tagihan = $this->makeTagihan($school, $anak, [
            'status' => 'rejected',
            'bukti_transfer' => 'bukti-transfer/lama.jpg',
            'catatan_admin' => 'Bukti kurang jelas.',
            'approved_at' => now(),
        ]);
        Storage::disk('public')->put('bukti-transfer/lama.jpg', 'old-proof');

        $headers = array_merge($this->authHeaders($parent), ['Accept' => 'application/json']);
        $url = '/api/v1/sekolah/'.$school->id.'/tagihan/'.$tagihan->id.'/bukti-pembayaran';

        $this->post($url, ['bukti_transfer' => $this->proofFile()], array_merge($headers, [
            'Idempotency-Key' => 'upload-bukti-rejected',
        ]))
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending');

        $tagihan->refresh();

        $this->assertSame('pending', $tagihan->status);
        $this->assertNull($tagihan->approved_at);
        $this->assertNotSame('bukti-transfer/lama.jpg', $tagihan->bukti_transfer);
        Storage::disk('public')->assertMissing('bukti-transfer/lama.jpg');
        Storage::disk('public')->assertExists($tagihan->bukti_transfer);

        $tagihan->update(['status' => 'approved']);

        $this->post($url, ['bukti_transfer' => $this->proofFile()], array_merge($headers, [
            'Idempotency-Key' => 'upload-bukti-approved',
        ]))
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'TAGIHAN_SUDAH_LUNAS');
    }

    public function test_proof_endpoint_returns_not_found_when_no_proof_exists(): void
    {
        $lembaga = $this->makeLembaga();
        $school = $this->makeSekolah($lembaga);
        $parent = User::factory()->create();
        $parent->assignRole('Orang Tua');
        $anak = $this->makeAnakFor($parent, $school);
        $tagihan = $this->makeTagihan($school, $anak);

        $this->getJson(
            '/api/v1/sekolah/'.$school->id.'/tagihan/'.$tagihan->id.'/bukti-pembayaran',
            $this->authHeaders($parent),
        )
            ->assertNotFound()
            ->assertJsonPath('error.code', 'DATA_TIDAK_DITEMUKAN');
    }

    public function test_proof_endpoint_reads_a_legacy_public_proof(): void
    {
        Storage::fake('public');

        $lembaga = $this->makeLembaga();
        $school = $this->makeSekolah($lembaga);
        $parent = User::factory()->create();
        $parent->assignRole('Orang Tua');
        $anak = $this->makeAnakFor($parent, $school);
        $tagihan = $this->makeTagihan($school, $anak, [
            'bukti_transfer' => 'bukti-transfer/lama.png',
        ]);

        Storage::disk('public')->put('bukti-transfer/lama.png', base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wl2n1cAAAAASUVORK5CYII='
        ));

        $response = $this->getJson(
            '/api/v1/sekolah/'.$school->id.'/tagihan/'.$tagihan->id.'/bukti-pembayaran',
            $this->authHeaders($parent),
        )->assertOk();

        $proofUrl = $response->json('data.bukti_transfer.url');
        $this->assertIsString($proofUrl);
        $this->assertStringContainsString('disk=public', $proofUrl);
        $this->get($proofUrl)->assertOk()->assertHeader('content-type', 'image/png');
    }

    private function makeTagihan($school, $anak, array $attributes = []): PembayaranBulanan
    {
        return PembayaranBulanan::withoutSekolahScope()->create(array_merge([
            'sekolah_id' => $school->id,
            'anak_id' => $anak->id,
            'periode_bulan' => 10,
            'periode_tahun' => 2026,
            'hari_efektif' => 22,
            'hari_hadir' => 20,
            'biaya_per_hari' => 350000,
            'subtotal' => 350000,
            'nilai_diskon' => 0,
            'total_bayar' => 350000,
            'status' => 'pending',
        ], $attributes));
    }

    private function proofFile(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            'bukti.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wl2n1cAAAAASUVORK5CYII='),
        );
    }
}
