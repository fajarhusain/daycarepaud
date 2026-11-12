<?php

namespace Tests\Feature\Api\V1;

use App\Models\Anak;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class Phase3AnakTest extends ApiTestCase
{
    public function test_anak_list_only_returns_approved_children_in_the_tenant(): void
    {
        $lembaga = $this->makeLembaga();
        $schoolA = $this->makeSekolah($lembaga, 'Sekolah A');
        $schoolB = $this->makeSekolah($lembaga, 'Sekolah B');

        $parent = User::factory()->create();
        $parent->assignRole('Orang Tua');

        $approved = $this->makeAnakFor($parent, $schoolA, ['name' => 'Anak Disetujui']);
        $pending = $this->makeAnakFor($parent, $schoolA, [
            'name' => 'Anak Menunggu',
            'status' => 'pending',
        ]);
        $otherSchool = $this->makeAnakFor($parent, $schoolB, ['name' => 'Anak Sekolah B']);

        $headers = $this->authHeaders($parent);

        $this->getJson('/api/v1/sekolah/'.$schoolA->id.'/anak', $headers)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $approved->id);

        $this->getJson('/api/v1/sekolah/'.$schoolA->id.'/anak/'.$pending->id, $headers)
            ->assertNotFound()
            ->assertJsonPath('error.code', 'ANAK_TIDAK_DITEMUKAN');

        $this->getJson('/api/v1/sekolah/'.$schoolA->id.'/anak/'.$otherSchool->id, $headers)
            ->assertNotFound()
            ->assertJsonPath('error.code', 'ANAK_TIDAK_DITEMUKAN');
    }

    public function test_orang_tua_can_update_child_profile_fields(): void
    {
        $lembaga = $this->makeLembaga();
        $school = $this->makeSekolah($lembaga);
        $parent = User::factory()->create();
        $parent->assignRole('Orang Tua');
        $anak = $this->makeAnakFor($parent, $school);

        $response = $this->patchJson('/api/v1/sekolah/'.$school->id.'/anak/'.$anak->id, [
            'name' => 'Nama Baru',
            'nickname' => 'Baru',
            'jenis_kelamin' => 'perempuan',
            'alamat' => 'Jl. Baru No. 10',
        ], $this->authHeaders($parent));

        $response->assertOk()
            ->assertJsonPath('message', 'Data anak berhasil diperbarui.')
            ->assertJsonPath('data.name', 'Nama Baru')
            ->assertJsonPath('data.nickname', 'Baru')
            ->assertJsonPath('data.jenis_kelamin', 'perempuan');

        $this->assertDatabaseHas('anaks', [
            'id' => $anak->id,
            'name' => 'Nama Baru',
            'nickname' => 'Baru',
            'jenis_kelamin' => 'Perempuan',
        ]);
    }

    public function test_child_photo_is_returned_as_signed_media(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put(
            'anak/foto.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wl2n1cAAAAASUVORK5CYII='),
        );

        $lembaga = $this->makeLembaga();
        $school = $this->makeSekolah($lembaga);
        $parent = User::factory()->create();
        $parent->assignRole('Orang Tua');
        $anak = $this->makeAnakFor($parent, $school, ['photo' => 'anak/foto.png']);

        $response = $this->getJson(
            '/api/v1/sekolah/'.$school->id.'/anak/'.$anak->id,
            $this->authHeaders($parent),
        )->assertOk();

        $response->assertJsonPath('data.photo.mime_type', 'image/png')
            ->assertJsonPath('data.photo.width', 1);

        $url = $response->json('data.photo.url');
        $this->assertStringContainsString('signature=', $url);

        $this->get($url)->assertOk()->assertHeader('content-type', 'image/png');

        $expiredUrl = preg_replace('/([?&])expires=\d+/', '$1expires=1', $url);
        $this->get($expiredUrl)
            ->assertStatus(410)
            ->assertJsonPath('error.code', 'TAUTAN_KEDALUWARSA');
    }

    public function test_additional_child_registration_creates_pending_child_and_rejects_duplicate(): void
    {
        $lembaga = $this->makeLembaga();
        $school = $this->makeSekolah($lembaga);
        $parent = User::factory()->create();
        $parent->assignRole('Orang Tua');
        $this->makeAnakFor($parent, $school, ['name' => 'Anak Pertama']);

        $headers = $this->authHeaders($parent);
        $payload = [
            'sekolah_id' => $school->id,
            'name' => 'Anak Kedua',
            'dob' => now()->subYears(4)->toDateString(),
            'catatan_ortu' => 'Mohon diproses.',
        ];

        $this->postJson('/api/v1/pendaftaran-anak', $payload, $headers)
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('message', 'Pendaftaran anak berhasil dikirim. Menunggu persetujuan admin sekolah.');

        $this->assertDatabaseHas('anaks', [
            'user_id' => $parent->id,
            'sekolah_id' => $school->id,
            'name' => 'Anak Kedua',
            'status' => 'pending',
        ]);

        $this->postJson('/api/v1/pendaftaran-anak', $payload, $headers)
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'PENDAFTARAN_DUPLIKAT');

        $this->getJson('/api/v1/pendaftaran-anak', $headers)
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->assertSame(2, Anak::withoutSekolahScope()->where('user_id', $parent->id)->count());
    }
}
