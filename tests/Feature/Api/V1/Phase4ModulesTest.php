<?php

namespace Tests\Feature\Api\V1;

use App\Models\KegiatanRutin;
use App\Models\Kesehatan;
use App\Models\MonevSummary;
use App\Models\Pengumuman;
use App\Models\Presensi;
use App\Models\User;

class Phase4ModulesTest extends ApiTestCase
{
    public function test_kegiatan_rutin_filters_dates_child_and_master(): void
    {
        $lembaga = $this->makeLembaga();
        $school = $this->makeSekolah($lembaga);
        $class = $this->makeKelas($school);
        $parent = User::factory()->create();
        $parent->assignRole('Orang Tua');
        $anak = $this->makeAnakFor($parent, $school, ['kelas_id' => $class->id]);
        $otherAnak = $this->makeAnakFor($parent, $school, [
            'kelas_id' => $class->id,
            'name' => 'Anak Lain',
        ]);
        $pengajar = $this->makePengajar($school);

        $today = now()->toDateString();
        $first = KegiatanRutin::create([
            'sekolah_id' => $school->id,
            'kelas_id' => $class->id,
            'anak_id' => $anak->id,
            'pengajar_id' => $pengajar->id,
            'tanggal' => $today,
            'aspek' => 'Kemandirian',
            'kegiatan' => 'Makan sendiri',
            'status_pencapaian' => 'Lancar',
            'keterangan' => 'Sudah bisa',
        ]);

        KegiatanRutin::create([
            'sekolah_id' => $school->id,
            'kelas_id' => $class->id,
            'anak_id' => $otherAnak->id,
            'pengajar_id' => $pengajar->id,
            'tanggal' => $today,
            'aspek' => 'Kognitif',
            'kegiatan' => 'Menghitung',
            'status_pencapaian' => 'MB',
        ]);

        $headers = $this->authHeaders($parent);
        $base = '/api/v1/sekolah/'.$school->id.'/kegiatan-rutin';

        $this->getJson($base.'?anak_id='.$anak->id.'&tanggal_dari='.$today.'&tanggal_sampai='.$today, $headers)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $first->id)
            ->assertJsonPath('data.0.status_pencapaian', 'Lancar');

        $this->getJson($base.'/'.$first->id, $headers)
            ->assertOk()
            ->assertJsonPath('data.kegiatan', 'Makan sendiri');
    }

    public function test_presensi_returns_list_and_summary_for_requested_period(): void
    {
        $lembaga = $this->makeLembaga();
        $school = $this->makeSekolah($lembaga);
        $class = $this->makeKelas($school);
        $parent = User::factory()->create();
        $parent->assignRole('Orang Tua');
        $anak = $this->makeAnakFor($parent, $school, ['kelas_id' => $class->id]);

        $statuses = ['hadir', 'hadir', 'izin', 'sakit', 'alpha'];

        foreach ($statuses as $index => $status) {
            Presensi::create([
                'sekolah_id' => $school->id,
                'kelas_id' => $class->id,
                'anak_id' => $anak->id,
                'tanggal' => now()->startOfMonth()->addDays($index)->toDateString(),
                'hadir' => $status === 'hadir',
                'status' => $status,
            ]);
        }

        $headers = $this->authHeaders($parent);
        $url = '/api/v1/sekolah/'.$school->id.'/presensi?periode=bulanan&bulan='.now()->month.'&tahun='.now()->year;

        $this->getJson($url, $headers)
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.periode', 'bulanan')
            ->assertJsonPath('meta.ringkasan.hadir', 2)
            ->assertJsonPath('meta.ringkasan.izin', 1)
            ->assertJsonPath('meta.ringkasan.sakit', 1)
            ->assertJsonPath('meta.ringkasan.alpha', 1)
            ->assertJsonPath('meta.ringkasan.total', 5);

        $this->getJson(
            '/api/v1/sekolah/'.$school->id.'/presensi?periode=bulanan&bulan='.now()->month,
            $headers,
        )->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.ringkasan.total', 5);
    }

    public function test_kesehatan_only_returns_children_owned_by_the_parent_in_tenant(): void
    {
        $lembaga = $this->makeLembaga();
        $school = $this->makeSekolah($lembaga);
        $parent = User::factory()->create();
        $parent->assignRole('Orang Tua');
        $anak = $this->makeAnakFor($parent, $school);
        $otherParent = User::factory()->create();
        $otherParent->assignRole('Orang Tua');
        $otherAnak = $this->makeAnakFor($otherParent, $school);

        $mine = Kesehatan::create([
            'anak_id' => $anak->id,
            'tanggal_pemeriksaan' => now()->toDateString(),
            'berat_badan' => 15.2,
            'tinggi_badan' => 98.5,
            'lingkar_kepala' => 49,
            'gigi' => 'Bersih',
        ]);

        $other = Kesehatan::create([
            'anak_id' => $otherAnak->id,
            'tanggal_pemeriksaan' => now()->toDateString(),
            'berat_badan' => 14,
        ]);

        $headers = $this->authHeaders($parent);
        $base = '/api/v1/sekolah/'.$school->id.'/kesehatan';

        $this->getJson($base, $headers)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $mine->id)
            ->assertJsonPath('data.0.berat_badan', 15.2);

        $this->getJson($base.'/'.$other->id, $headers)
            ->assertNotFound()
            ->assertJsonPath('error.code', 'DATA_TIDAK_DITEMUKAN');
    }

    public function test_monev_list_detail_and_pdf_use_summary_id(): void
    {
        $lembaga = $this->makeLembaga();
        $school = $this->makeSekolah($lembaga);
        $parent = User::factory()->create();
        $parent->assignRole('Orang Tua');
        $anak = $this->makeAnakFor($parent, $school);

        $summary = MonevSummary::create([
            'anak_id' => $anak->id,
            'tahun' => now()->year,
            'bulan' => now()->month,
            'ringkasan' => "GAMBARAN_UMUM\nPerkembangan baik.",
            'data_snapshot' => ['total_entri' => 2, 'distribusi_skor' => ['BSH' => 2]],
            'sumber' => 'otomatis',
            'generated_at' => now(),
        ]);

        $headers = $this->authHeaders($parent);
        $base = '/api/v1/sekolah/'.$school->id.'/monev';

        $this->getJson($base, $headers)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $summary->id)
            ->assertJsonPath('data.0.pdf_tersedia', true);

        $this->getJson($base.'/'.$summary->id, $headers)
            ->assertOk()
            ->assertJsonPath('data.id', $summary->id)
            ->assertJsonPath('data.data_snapshot.total_entri', 2);

        $this->getJson($base.'/'.$summary->id.'/pdf', $headers)
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_pengumuman_respects_class_visibility_and_read_status(): void
    {
        $lembaga = $this->makeLembaga();
        $school = $this->makeSekolah($lembaga);
        $class = $this->makeKelas($school, 'Kelas Anak');
        $otherClass = $this->makeKelas($school, 'Kelas Lain');
        $parent = User::factory()->create();
        $parent->assignRole('Orang Tua');
        $this->makeAnakFor($parent, $school, ['kelas_id' => $class->id]);

        $global = Pengumuman::create([
            'sekolah_id' => $school->id,
            'judul' => 'Pengumuman Sekolah',
            'kategori' => 'Informasi',
            'isi' => 'Isi pengumuman sekolah.',
            'mulai_tayang' => now()->subDay()->toDateString(),
            'selesai_tayang' => now()->addDay()->toDateString(),
            'is_active' => true,
        ]);

        $classAnnouncement = Pengumuman::create([
            'sekolah_id' => $school->id,
            'kelas_id' => $class->id,
            'judul' => 'Pengumuman Kelas',
            'kategori' => 'Kelas',
            'isi' => 'Isi pengumuman kelas.',
            'mulai_tayang' => now()->subDay()->toDateString(),
            'selesai_tayang' => now()->addDay()->toDateString(),
            'is_active' => true,
        ]);

        $hidden = Pengumuman::create([
            'sekolah_id' => $school->id,
            'kelas_id' => $otherClass->id,
            'judul' => 'Pengumuman Kelas Lain',
            'kategori' => 'Kelas',
            'isi' => 'Tidak terlihat.',
            'mulai_tayang' => now()->subDay()->toDateString(),
            'selesai_tayang' => now()->addDay()->toDateString(),
            'is_active' => true,
        ]);

        $headers = $this->authHeaders($parent);
        $base = '/api/v1/sekolah/'.$school->id.'/pengumuman';

        $this->getJson($base, $headers)
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['id' => $global->id])
            ->assertJsonFragment(['id' => $classAnnouncement->id]);

        $this->getJson($base.'/'.$hidden->id, $headers)
            ->assertForbidden()
            ->assertJsonPath('error.code', 'PENGUMUMAN_TIDAK_TERSEDIA');

        $this->putJson($base.'/'.$global->id.'/dibaca', [], $headers)
            ->assertOk()
            ->assertJsonPath('message', 'Pengumuman ditandai sudah dibaca.')
            ->assertJsonPath('data.sudah_dibaca', true);

        $this->getJson($base.'?status=belum_dibaca', $headers)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $classAnnouncement->id);
    }

    public function test_read_only_empty_states_are_successful(): void
    {
        $lembaga = $this->makeLembaga();
        $school = $this->makeSekolah($lembaga);
        $parent = User::factory()->create();
        $parent->assignRole('Orang Tua');
        $this->makeAnakFor($parent, $school);

        $headers = $this->authHeaders($parent);

        foreach (['presensi', 'kesehatan', 'monev', 'pengumuman', 'kegiatan-rutin'] as $module) {
            $this->getJson('/api/v1/sekolah/'.$school->id.'/'.$module, $headers)
                ->assertOk()
                ->assertJsonCount(0, 'data');
        }
    }
}
