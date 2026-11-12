<?php

namespace Tests\Feature\Api\V1;

use App\Models\Kegiatan;
use App\Models\KritikSaran;
use App\Models\MenuMakanan;
use App\Models\MenuMakananVote;
use App\Models\Pencapaian;
use App\Models\User;

class Phase3ModulesTest extends ApiTestCase
{
    public function test_pencapaian_uses_cursor_and_filters_by_child_date_and_aspect(): void
    {
        $lembaga = $this->makeLembaga();
        $school = $this->makeSekolah($lembaga);
        $parent = User::factory()->create();
        $parent->assignRole('Orang Tua');
        $anak = $this->makeAnakFor($parent, $school);
        $otherAnak = $this->makeAnakFor($parent, $school, ['name' => 'Anak Lain']);
        $pengajar = $this->makePengajar($school);
        $kognitif = $this->makeMatrikulasi($school, 'Kognitif');
        $motorik = $this->makeMatrikulasi($school, 'Motorik');

        $first = Pencapaian::create([
            'anak_id' => $anak->id,
            'pengajar_id' => $pengajar->id,
            'matrikulasi_id' => $kognitif->id,
            'score' => 'BSH',
            'feedback' => 'Baik',
        ]);

        Pencapaian::create([
            'anak_id' => $anak->id,
            'pengajar_id' => $pengajar->id,
            'matrikulasi_id' => $motorik->id,
            'score' => 'MB',
            'feedback' => 'Mulai berkembang',
        ]);

        Pencapaian::create([
            'anak_id' => $otherAnak->id,
            'pengajar_id' => $pengajar->id,
            'matrikulasi_id' => $kognitif->id,
            'score' => 'BSB',
            'feedback' => 'Anak lain',
        ]);

        $headers = $this->authHeaders($parent);
        $url = '/api/v1/sekolah/'.$school->id.'/pencapaian?anak_id='.$anak->id.'&limit=1';

        $response = $this->getJson($url, $headers)->assertOk();
        $response->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.anak.id', $anak->id)
            ->assertJsonPath('meta.has_more', true);

        $cursor = $response->json('meta.next_cursor');
        $this->assertNotNull($cursor);

        $this->getJson($url.'&cursor='.urlencode($cursor), $headers)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.anak.id', $anak->id);

        $filtered = $this->getJson(
            '/api/v1/sekolah/'.$school->id.'/pencapaian?anak_id='.$anak->id.'&aspek=Motorik',
            $headers,
        )->assertOk();

        $filtered->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.matrikulasi.aspek', 'Motorik');

        $this->getJson('/api/v1/sekolah/'.$school->id.'/pencapaian/'.$first->id, $headers)
            ->assertOk()
            ->assertJsonPath('data.score_label', 'Berkembang Sesuai Harapan');
    }

    public function test_kegiatan_can_be_scoped_to_child_class_or_all_school_classes(): void
    {
        $lembaga = $this->makeLembaga();
        $school = $this->makeSekolah($lembaga);
        $classA = $this->makeKelas($school, 'Kelas A');
        $classB = $this->makeKelas($school, 'Kelas B');
        $parent = User::factory()->create();
        $parent->assignRole('Orang Tua');
        $anak = $this->makeAnakFor($parent, $school, ['kelas_id' => $classA->id]);
        $pengajar = $this->makePengajar($school);

        $eventA = Kegiatan::create([
            'sekolah_id' => $school->id,
            'pengajar_id' => $pengajar->id,
            'kelas_id' => $classA->id,
            'date' => now()->toDateString(),
            'title' => 'Kegiatan Kelas A',
            'description' => 'Deskripsi A',
        ]);

        $eventB = Kegiatan::create([
            'sekolah_id' => $school->id,
            'pengajar_id' => $pengajar->id,
            'kelas_id' => $classB->id,
            'date' => now()->toDateString(),
            'title' => 'Kegiatan Kelas B',
            'description' => 'Deskripsi B',
        ]);

        $headers = $this->authHeaders($parent);
        $base = '/api/v1/sekolah/'.$school->id.'/kegiatan';

        $this->getJson($base.'?anak_id='.$anak->id, $headers)
            ->assertOk()
            ->assertJsonPath('data.meta.anak_id', $anak->id)
            ->assertJsonCount(1, 'data.events')
            ->assertJsonPath('data.events.0.id', $eventA->id);

        $this->getJson($base.'?semua_sekolah=1', $headers)
            ->assertOk()
            ->assertJsonCount(2, 'data.events')
            ->assertJsonFragment(['id' => $eventA->id])
            ->assertJsonFragment(['id' => $eventB->id]);

        $this->getJson($base.'/'.$eventA->id, $headers)
            ->assertOk()
            ->assertJsonPath('data.id', $eventA->id);

        $this->getJson($base.'/'.$eventB->id, $headers)
            ->assertNotFound()
            ->assertJsonPath('error.code', 'DATA_TIDAK_DITEMUKAN');
    }

    public function test_menu_vote_is_idempotent_and_can_be_removed(): void
    {
        $lembaga = $this->makeLembaga();
        $school = $this->makeSekolah($lembaga);
        $parent = User::factory()->create();
        $parent->assignRole('Orang Tua');
        $this->makeAnakFor($parent, $school);

        $menu = MenuMakanan::create([
            'sekolah_id' => $school->id,
            'date' => now()->toDateString(),
            'menu' => 'Nasi dan sayur',
        ]);

        $headers = $this->authHeaders($parent);
        $url = '/api/v1/sekolah/'.$school->id.'/menu-makanan/'.$menu->id.'/vote';

        $this->putJson($url, ['vote_type' => 'like'], $headers)
            ->assertOk()
            ->assertJsonPath('message', 'Terima kasih atas penilaiannya.')
            ->assertJsonPath('data.vote.type', 'like')
            ->assertJsonPath('data.vote.like_count', 1);

        $this->putJson($url, ['vote_type' => 'like'], $headers)->assertOk();

        $this->assertSame(1, MenuMakananVote::where('user_id', $parent->id)->count());

        $this->getJson('/api/v1/sekolah/'.$school->id.'/menu-makanan/'.$menu->id, $headers)
            ->assertOk()
            ->assertJsonPath('data.id', $menu->id)
            ->assertJsonPath('data.vote.type', 'like');

        $this->putJson($url, ['vote_type' => 'bintang'], $headers)
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'VOTE_TIDAK_VALID');

        $this->deleteJson($url, [], $headers)
            ->assertOk()
            ->assertJsonPath('data.vote.type', null);

        $this->assertSame(0, MenuMakananVote::where('user_id', $parent->id)->count());
    }

    public function test_masukan_crud_follows_status_and_tenant_rules(): void
    {
        $lembaga = $this->makeLembaga();
        $school = $this->makeSekolah($lembaga);
        $otherSchool = $this->makeSekolah($lembaga, 'Sekolah Lain');
        $parent = User::factory()->create();
        $parent->assignRole('Orang Tua');
        $this->makeAnakFor($parent, $school);
        $this->makeAnakFor($parent, $otherSchool);
        $this->makeAnakFor($parent, $school, ['name' => 'Anak Tambahan']);

        KritikSaran::create([
            'sekolah_id' => $otherSchool->id,
            'user_id' => $parent->id,
            'message' => 'Masukan untuk sekolah lain.',
            'status' => 'Terkirim',
        ]);

        $headers = $this->authHeaders($parent);
        $base = '/api/v1/sekolah/'.$school->id.'/masukan';

        $created = $this->postJson($base, [
            'message' => 'Mohon informasi kegiatan minggu depan.',
        ], $headers)->assertCreated()
            ->assertJsonPath('message', 'Saran dan kritik berhasil dikirim.')
            ->assertJsonPath('data.status', 'Terkirim');

        $id = $created->json('data.id');

        $this->getJson($base, $headers)
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->patchJson($base.'/'.$id, [
            'message' => 'Mohon informasi kegiatan bulan depan.',
        ], $headers)->assertOk()
            ->assertJsonPath('message', 'Masukan berhasil diperbarui.');

        KritikSaran::query()->whereKey($id)->update(['status' => 'Diproses']);

        $this->patchJson($base.'/'.$id, [
            'message' => 'Mohon informasi kegiatan tahun depan.',
        ], $headers)->assertStatus(409)
            ->assertJsonPath('error.code', 'MASUKAN_SUDAH_DIPROSES');

        $this->deleteJson($base.'/'.$id, [], $headers)->assertStatus(409);

        KritikSaran::query()->whereKey($id)->update(['status' => 'Terkirim']);

        $this->deleteJson($base.'/'.$id, [], $headers)->assertOk()
            ->assertJsonPath('message', 'Masukan berhasil dihapus.');

        $this->assertDatabaseMissing('kritik_sarans', ['id' => $id]);
    }
}
