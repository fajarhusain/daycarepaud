<?php

namespace Database\Seeders;

use App\Models\Anak;
use App\Models\Cashflow;
use App\Models\CmsContent;
use App\Models\Kegiatan;
use App\Models\KegiatanRutin;
use App\Models\Kelas;
use App\Models\Kesehatan;
use App\Models\KritikSaran;
use App\Models\Lembaga;
use App\Models\MasterKegiatanRutin;
use App\Models\Matrikulasi;
use App\Models\MenuMakanan;
use App\Models\MenuMakananVote;
use App\Models\Pencapaian;
use App\Models\Pengajar;
use App\Models\Presensi;
use App\Models\PresensiShift;
use App\Models\PresensiPengajar;
use App\Models\Sarana;
use App\Models\Sekolah;
use App\Models\SkalaPencapaian;
use App\Models\User;
use App\Services\AiTokenService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Demo  data contoh untuk website PAUD (local/staging saja).
 *
 * Jalankan setelah ReferenceSeeder (butuh lembaga + sekolah):
 *   php artisan db:seed --class=Database\Seeders\DemoSeeder
 *
 * Atau setup dev lengkap:
 *   php artisan db:seed --class=Database\Seeders\DevSetupSeeder
 *
 * Risiko: menambah user @example.com, anak demo, menimpa CMS.
 *
 * Kredensial akun demo (password: password):
 *
 *   | Role            | Email                  |
 *   |-----------------|------------------------|
 *   | Lembaga         | lembaga@example.com    |
 *   | Admin Sekolah   | admin@example.com      |
 *   | Wali Kelas     | wali@example.com       |
 *   | Pengajar        | pengajar@example.com   |
 *   | Orang Tua       | ortu@example.com       |
 *
 * Data yang di-seed (butuh lembaga + sekolah dari ReferenceSeeder):
 *   - User demo tambahan (wali kelas, dll.)
 *   - 2 pengajar + 2 kelas + pivot kelas_pengajar
 *   - 2 anak (status approved) milik akun orang tua
 *   - 5 indikator matrikulasi (Kognitif, Motorik, Sosial-Emosional, Bahasa, Seni)
 *   - Pencapaian untuk setiap anak di tiap matrikulasi (total 10)
 *   - 5 agenda (kegiatan)  hari ini sampai 4 hari lalu
 *   - 7 master kegiatan rutin (Agama, Kognitif, Motorik)
 *   - Kegiatan rutin harian untuk 2 anak x 7 kegiatan x 3 hari
 *   - Presensi siswa (2 anak x 7 hari terakhir)
 *   - Presensi pengajar (2 pengajar x 7 hari terakhir)
 *   - Kesehatan (1 record per anak)
 *   - Menu makanan (5 hari terakhir + 2 vote)
 *   - Sarana (6 item)
 *   - Cashflow (5 transaksi)
 *   - Kritik & Saran (3 data)
 *   - CMS Content (hero, tentang, kontak, footer)
 *   - SkalaPencapaian default (BB/MB/BSH/BSB)
 *   - 50 token AI untuk sekolah demo
 *
 * Semua operasi idempotent  aman dijalankan berulang kali.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command->error('DemoSeeder diblokir di production.');

            return;
        }

        $lembaga = Lembaga::first();
        $sekolah = Sekolah::first();

        if (! $lembaga || ! $sekolah) {
            $this->command->error('Lembaga atau sekolah tidak ditemukan. Jalankan ReferenceSeeder dulu.');

            return;
        }

        // 1. Tambah user yang belum ada di RoleSeeder
        $users = $this->seedDemoUsers($lembaga, $sekolah);

        // 2. Struktur sekolah  pengajar, kelas, anak
        $schoolData = $this->seedSekolahData($sekolah, $users);

        // 3. Data akademik  skala, matrikulasi, pencapaian, agenda, kegiatan rutin
        $this->seedAkademik($sekolah, $schoolData);

        // 4. Presensi  siswa & pengajar
        $this->seedPresensi($sekolah, $schoolData);

        // 5. Operasional  kesehatan, menu makanan, sarana, cashflow, kritik saran, CMS
        $this->seedOperasional($sekolah, $schoolData, $users);

        // 6. Token AI
        $this->seedAiTokens($sekolah, $users['admin']);

        $this->command->info('DemoSeeder selesai. Semua akun demo siap digunakan.');
    }

    /**
     * @return array<string, User>
     */
    private function seedDemoUsers(Lembaga $lembaga, Sekolah $sekolah): array
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin Sekolah',
                'password' => Hash::make('password'),
                'lembaga_id' => $lembaga->id,
                'sekolah_id' => $sekolah->id,
            ]
        );
        $admin->assignRole('Admin Sekolah');

        // Wali Kelas (wali kelas)
        $wali = User::firstOrCreate(
            ['email' => 'wali@example.com'],
            [
                'name' => 'Wali Kelas Rina',
                'password' => Hash::make('password'),
                'sekolah_id' => $sekolah->id,
            ]
        );
        $wali->assignRole('Wali Kelas');

        // Pengajar (guru biasa)
        $pengajar = User::firstOrCreate(
            ['email' => 'pengajar@example.com'],
            [
                'name' => 'Pengajar Budi',
                'password' => Hash::make('password'),
                'sekolah_id' => $sekolah->id,
            ]
        );
        $pengajar->assignRole('Pengajar');

        // Orang Tua
        $ortu = User::firstOrCreate(
            ['email' => 'ortu@example.com'],
            [
                'name' => 'Orang Tua Siti',
                'password' => Hash::make('password'),
                'sekolah_id' => $sekolah->id,
            ]
        );
        $ortu->assignRole('Orang Tua');

        // Lembaga (sudah di RoleSeeder, cukup referensi)
        $lembagaUser = User::where('email', 'lembaga@example.com')->first();

        return compact('admin', 'wali', 'pengajar', 'ortu', 'lembagaUser');
    }

    /**
     * @return array<string, mixed>
     */
    private function seedSekolahData(Sekolah $sekolah, array $users): array
    {
        $shift = PresensiShift::withoutSekolahScope()->firstOrCreate(
            ['sekolah_id' => $sekolah->id, 'is_default' => true],
            [
                'name' => 'Reguler',
                'location_coordinate' => $sekolah->location_coordinate,
                'radius_m' => $sekolah->radius_m ?? 100,
                'sort_order' => 0,
            ]
        );

        // Record Pengajar untuk wali kelas
        $pengajarWali = Pengajar::firstOrCreate(
            ['user_id' => $users['wali']->id],
            [
                'sekolah_id' => $sekolah->id,
                'presensi_shift_id' => $shift->id,
                'name' => 'Wali Kelas Rina',
                'jabatan' => 'Guru Kelas',
            ]
        );

        // Record Pengajar untuk guru biasa
        $pengajarGuru = Pengajar::firstOrCreate(
            ['user_id' => $users['pengajar']->id],
            [
                'sekolah_id' => $sekolah->id,
                'presensi_shift_id' => $shift->id,
                'name' => 'Pengajar Budi',
                'jabatan' => 'Guru Pendamping',
            ]
        );

        // Kelas Melati  dengan wali kelas
        $kelasMelati = Kelas::firstOrCreate(
            ['sekolah_id' => $sekolah->id, 'name' => 'Kelas Melati'],
            [
                'description' => 'Kelompok bermain usia 3-4 tahun',
                'wali_kelas_id' => $pengajarWali->id,
            ]
        );

        // Kelas Mawar  tanpa wali kelas
        $kelasMawar = Kelas::firstOrCreate(
            ['sekolah_id' => $sekolah->id, 'name' => 'Kelas Mawar'],
            [
                'description' => 'Kelompok bermain usia 4-5 tahun',
                'wali_kelas_id' => null,
            ]
        );

        // Pivot: wali kelas + guru biasa mengajar di Kelas Melati
        $kelasMelati->pengajars()->syncWithoutDetaching([$pengajarWali->id, $pengajarGuru->id]);

        // Anak 1  Adinda, di Kelas Melati
        $anakAdinda = Anak::firstOrCreate(
            ['sekolah_id' => $sekolah->id, 'name' => 'Adinda Putri'],
            [
                'user_id' => $users['ortu']->id,
                'kelas_id' => $kelasMelati->id,
                'nickname' => 'Dinda',
                'dob' => '2021-03-15',
                'status' => 'approved',
                'jenis_kelamin' => 'perempuan',
                'nik' => '3201234503150001',
                'alamat' => 'Jl. Merdeka No. 10, Jakarta',
                'nama_bapak' => 'Bapak Adi',
                'nama_ibu' => 'Ibu Siti',
            ]
        );

        // Anak 2  Bima, di Kelas Mawar
        $anakBima = Anak::firstOrCreate(
            ['sekolah_id' => $sekolah->id, 'name' => 'Bima Sakti'],
            [
                'user_id' => $users['ortu']->id,
                'kelas_id' => $kelasMawar->id,
                'nickname' => 'Bima',
                'dob' => '2020-11-02',
                'status' => 'approved',
                'jenis_kelamin' => 'laki-laki',
                'nik' => '3201234511020002',
                'alamat' => 'Jl. Merdeka No. 10, Jakarta',
                'nama_bapak' => 'Bapak Adi',
                'nama_ibu' => 'Ibu Siti',
            ]
        );

        return compact(
            'pengajarWali', 'pengajarGuru',
            'kelasMelati', 'kelasMawar',
            'anakAdinda', 'anakBima',
        );
    }

    private function seedAkademik(Sekolah $sekolah, array $data): void
    {
        // Skala pencapaian default
        SkalaPencapaian::seedDefaultsForSekolah($sekolah->id);

        // Matrikulasi  indikator PAUD
        $matrikulasiData = [
            ['indicator' => 'Menyebut warna dasar', 'aspek' => 'Kognitif', 'description' => 'Anak mampu menyebutkan minimal 3 warna dasar (merah, kuning, biru)'],
            ['indicator' => 'Melompat dengan satu kaki', 'aspek' => 'Motorik Kasar', 'description' => 'Anak mampu melompat dengan satu kaki sejauh 2-3 langkah'],
            ['indicator' => 'Bermain bergilir dengan teman', 'aspek' => 'Sosial-Emosional', 'description' => 'Anak mampu menunggu giliran saat bermain bersama teman'],
            ['indicator' => 'Menyanyikan lagu anak-anak', 'aspek' => 'Seni', 'description' => 'Anak mampu menyanyikan minimal 2 lagu anak-anak dengan irama yang benar'],
            ['indicator' => 'Mengucapkan kalimat sederhana', 'aspek' => 'Bahasa', 'description' => 'Anak mampu mengucapkan kalimat 3-4 kata dengan jelas'],
        ];

        $matrikulasiIds = [];
        foreach ($matrikulasiData as $row) {
            $matrikulasi = Matrikulasi::firstOrCreate(
                [
                    'sekolah_id' => $sekolah->id,
                    'indicator' => $row['indicator'],
                ],
                [
                    'aspek' => $row['aspek'],
                    'description' => $row['description'],
                ]
            );
            $matrikulasiIds[] = $matrikulasi->id;
        }

        // Pencapaian  2 anak x 5 matrikulasi = 10 record
        $skorVariasi = ['BSH', 'MB', 'BSB', 'BB', 'BSH'];
        $feedbackList = [
            'Sudah mulai menunjukkan perkembangan yang baik, terus semangat!',
            'Masih perlu pendampingan lebih lanjut dalam kegiatan ini.',
            'Hasil sangat memuaskan, kemampuan di atas rata-rata teman sekelas.',
            'Belum terlihat perkembangan yang signifikan, perlu stimulasi tambahan.',
            'Perkembangan sesuai dengan target usia, pertahankan!',
        ];

        $bulanBerjalan = Carbon::now()->startOfMonth();
        $pengajarId = $data['pengajarGuru']->id;

        foreach ([$data['anakAdinda'], $data['anakBima']] as $anak) {
            foreach ($matrikulasiIds as $i => $matrikulasiId) {
                $idx = $i % count($skorVariasi);

                Pencapaian::firstOrCreate(
                    [
                        'anak_id' => $anak->id,
                        'matrikulasi_id' => $matrikulasiId,
                    ],
                    [
                        'pengajar_id' => $pengajarId,
                        'score' => $skorVariasi[$idx],
                        'feedback' => $feedbackList[$idx],
                        'created_at' => $bulanBerjalan->copy()->addDays($i),
                        'updated_at' => $bulanBerjalan->copy()->addDays($i),
                    ]
                );
            }
        }

        // ── Agenda (Kegiatan) ─────────────────────────────
        $agendaData = [
            [
                'title' => 'Mengenal Warna',
                'date' => Carbon::today(),
                'description' => 'Kegiatan belajar mengenal warna dasar melalui permainan balok warna.',
                'kelas_id' => $data['kelasMelati']->id,
                'matrikulasi_idx' => [0, 1],
            ],
            [
                'title' => 'Bernyanyi & Menari',
                'date' => Carbon::today()->subDay(),
                'description' => 'Kegiatan bernyanyi lagu anak-anak dan menari bersama untuk melatih motorik kasar.',
                'kelas_id' => $data['kelasMelati']->id,
                'matrikulasi_idx' => [2, 3],
            ],
            [
                'title' => 'Bercerita Gambar',
                'date' => Carbon::today()->subDays(2),
                'description' => 'Anak diminta menceritakan isi gambar yang diberikan untuk melatih kemampuan bahasa.',
                'kelas_id' => $data['kelasMawar']->id,
                'matrikulasi_idx' => [4],
            ],
            [
                'title' => 'Bermain Peran',
                'date' => Carbon::today()->subDays(3),
                'description' => 'Anak bermain peran sebagai dokter dan pasien untuk melatih sosial-emosional.',
                'kelas_id' => $data['kelasMelati']->id,
                'matrikulasi_idx' => [2, 3],
            ],
            [
                'title' => 'Menggambar Bebas',
                'date' => Carbon::today()->subDays(4),
                'description' => 'Kegiatan menggambar bebas menggunakan krayon untuk mengekspresikan kreativitas anak.',
                'kelas_id' => $data['kelasMawar']->id,
                'matrikulasi_idx' => [3, 4],
            ],
        ];

        foreach ($agendaData as $row) {
            $kegiatan = Kegiatan::firstOrCreate(
                [
                    'sekolah_id' => $sekolah->id,
                    'title' => $row['title'],
                    'date' => $row['date'],
                ],
                [
                    'pengajar_id' => $pengajarId,
                    'kelas_id' => $row['kelas_id'],
                    'description' => $row['description'],
                ]
            );

            $attachIds = array_map(fn ($idx) => $matrikulasiIds[$idx], $row['matrikulasi_idx']);
            $kegiatan->matrikulasis()->syncWithoutDetaching($attachIds);
        }

        // ── Master Kegiatan Rutin ───────────────────────
        $masterRutinData = [
            ['nama_kegiatan' => 'Mengaji / Iqro', 'aspek' => 'Agama'],
            ['nama_kegiatan' => 'Membaca Huruf Hijaiyah', 'aspek' => 'Agama'],
            ['nama_kegiatan' => 'Berhitung 1-10', 'aspek' => 'Kognitif'],
            ['nama_kegiatan' => 'Menulis Huruf A-Z', 'aspek' => 'Kognitif'],
            ['nama_kegiatan' => 'Olahraga Pagi', 'aspek' => 'Motorik Kasar'],
            ['nama_kegiatan' => 'Menggunting Kertas', 'aspek' => 'Motorik Halus'],
            ['nama_kegiatan' => 'Berdoa Sebelum Makan', 'aspek' => 'Agama'],
        ];

        $masterRutinIds = [];
        foreach ($masterRutinData as $row) {
            $master = MasterKegiatanRutin::firstOrCreate(
                [
                    'sekolah_id' => $sekolah->id,
                    'nama_kegiatan' => $row['nama_kegiatan'],
                ],
                [
                    'pengajar_id' => $pengajarId,
                    'aspek' => $row['aspek'],
                ]
            );
            // Hubungkan ke semua kelas
            $master->kelas()->syncWithoutDetaching([
                $data['kelasMelati']->id,
                $data['kelasMawar']->id,
            ]);
            $masterRutinIds[] = $master->id;
        }

        // ── Kegiatan Rutin (data harian) ────────────────
        $statusList = ['Sudah Lancar', 'Lancar', 'Belum Lancar', 'Sangat Lancar'];
        $anakList = [$data['anakAdinda'], $data['anakBima']];

        // Seed 3 hari terakhir untuk kedua anak
        for ($dayOffset = 0; $dayOffset < 3; $dayOffset++) {
            $tanggal = Carbon::today()->subDays($dayOffset);

            foreach ($anakList as $anak) {
                foreach ($masterRutinIds as $i => $masterId) {
                    $master = MasterKegiatanRutin::find($masterId);

                    KegiatanRutin::firstOrCreate(
                        [
                            'sekolah_id' => $sekolah->id,
                            'kelas_id' => $anak->kelas_id,
                            'anak_id' => $anak->id,
                            'master_kegiatan_rutin_id' => $masterId,
                            'tanggal' => $tanggal,
                        ],
                        [
                            'pengajar_id' => $pengajarId,
                            'aspek' => $master->aspek,
                            'kegiatan' => $master->nama_kegiatan,
                            'status_pencapaian' => $statusList[($i + $dayOffset) % count($statusList)],
                        ]
                    );
                }
            }
        }
    }

    private function seedPresensi(Sekolah $sekolah, array $data): void
    {
        $anakList = [$data['anakAdinda'], $data['anakBima']];
        $pengajarList = [$data['pengajarWali'], $data['pengajarGuru']];

        // Pola kehadiran per hari offset (0 = hari ini): [anakAdinda, anakBima]
        // true = hadir, false = tidak hadir
        $polaHadirAnak = [
            [true, true],   // hari ini
            [true, true],   // kemarin
            [false, true],  // 2 hari lalu (Adinda izin)
            [true, false],  // 3 hari lalu (Bima sakit)
            [true, true],   // 4 hari lalu
            [true, true],   // 5 hari lalu
            [false, true],  // 6 hari lalu (Adinda alpa)
        ];
        $statusTidakHadir = ['Izin', 'Sakit', 'Alpa'];

        for ($dayOffset = 0; $dayOffset < 7; $dayOffset++) {
            $tanggal = Carbon::today()->subDays($dayOffset);

            if ($tanggal->isWeekend()) {
                continue;
            }

            // Presensi siswa
            foreach ($anakList as $ai => $anak) {
                $hadir = $polaHadirAnak[$dayOffset][$ai];

                Presensi::firstOrCreate(
                    [
                        'sekolah_id' => $sekolah->id,
                        'anak_id' => $anak->id,
                        'tanggal' => $tanggal,
                    ],
                    [
                        'kelas_id' => $anak->kelas_id,
                        'hadir' => $hadir,
                        'status' => $hadir ? 'Hadir' : $statusTidakHadir[$dayOffset % count($statusTidakHadir)],
                    ]
                );
            }

            // Presensi pengajar
            foreach ($pengajarList as $pi => $pengajar) {
                // Semua hadir kecuali 1 record izin di offset 4
                $hadir = ! ($dayOffset === 4 && $pi === 1);

                PresensiPengajar::firstOrCreate(
                    [
                        'sekolah_id' => $sekolah->id,
                        'pengajar_id' => $pengajar->id,
                        'tanggal' => $tanggal,
                    ],
                    [
                        'hadir' => $hadir,
                        'status' => $hadir ? 'Hadir' : 'Izin',
                    ]
                );
            }
        }
    }

    private function seedOperasional(Sekolah $sekolah, array $data, array $users): void
    {
        // ── Kesehatan ───────────────────────────────────
        $kesehatanData = [
            $data['anakAdinda']->id => [
                'berat_badan' => 14.5,
                'tinggi_badan' => 98.0,
                'lingkar_kepala' => 49.0,
                'gigi' => 'Baik, tidak ada lubang',
                'telinga' => 'Normal',
                'kuku' => 'Bersih, terawat',
                'alergi' => 'Tidak ada',
            ],
            $data['anakBima']->id => [
                'berat_badan' => 16.2,
                'tinggi_badan' => 103.0,
                'lingkar_kepala' => 50.5,
                'gigi' => 'Baik, 1 gigi berlubang kecil',
                'telinga' => 'Normal',
                'kuku' => 'Bersih',
                'alergi' => 'Susu sapi (ringan)',
            ],
        ];

        foreach ($kesehatanData as $anakId => $fields) {
            Kesehatan::firstOrCreate(
                [
                    'anak_id' => $anakId,
                    'tanggal_pemeriksaan' => Carbon::now()->startOfMonth()->addDays(5),
                ],
                $fields
            );
        }

        // ── Menu Makanan ────────────────────────────────
        $menuData = [
            ['menu' => 'Nasi Putih, Ayam Goreng, Sayur Bayam, Buah Pepaya', 'offset' => 0],
            ['menu' => 'Nasi Kuning, Telur Dadar, Sop Wortel, Buah Semangka', 'offset' => 1],
            ['menu' => 'Nasi Putih, Ikan Bakar, Tumis Kangkung, Buah Pisang', 'offset' => 2],
            ['menu' => 'Mie Goreng, Bakso, Sayur Sop, Buah Jeruk', 'offset' => 3],
            ['menu' => 'Nasi Uduk, Tempe Orek, Sayur Asem, Buah Apel', 'offset' => 4],
        ];

        $menuIds = [];
        foreach ($menuData as $row) {
            $menu = MenuMakanan::firstOrCreate(
                [
                    'sekolah_id' => $sekolah->id,
                    'date' => Carbon::today()->subDays($row['offset']),
                ],
                [
                    'menu' => $row['menu'],
                    'nutrition_info' => 'Karbohidrat, Protein, Serat, Vitamin',
                ]
            );
            $menuIds[] = $menu->id;
        }

        // Vote dari orang tua untuk 2 menu terbaru
        $ortuUser = $users['ortu'];
        foreach (array_slice($menuIds, 0, 2) as $i => $menuId) {
            MenuMakananVote::firstOrCreate(
                [
                    'menu_makanan_id' => $menuId,
                    'user_id' => $ortuUser->id,
                ],
                [
                    'vote_type' => $i === 0 ? 'like' : 'like',
                ]
            );
        }

        // ── Sarana ──────────────────────────────────────
        $saranaData = [
            ['name' => 'Meja Belajar Anak', 'condition' => 'Baik', 'quantity' => 20],
            ['name' => 'Kursi Anak', 'condition' => 'Baik', 'quantity' => 20],
            ['name' => 'Papan Tulis Whiteboard', 'condition' => 'Baik', 'quantity' => 3],
            ['name' => 'Lemari Mainan Edukatif', 'condition' => 'Cukup', 'quantity' => 4],
            ['name' => 'Alat Peraga Edukatif', 'condition' => 'Baik', 'quantity' => 15],
            ['name' => 'Karpet Kelas', 'condition' => 'Baik', 'quantity' => 6],
        ];

        foreach ($saranaData as $row) {
            Sarana::firstOrCreate(
                [
                    'sekolah_id' => $sekolah->id,
                    'name' => $row['name'],
                ],
                [
                    'condition' => $row['condition'],
                    'quantity' => $row['quantity'],
                ]
            );
        }

        // ── Cashflow ────────────────────────────────────
        $cashflowData = [
            ['type' => 'in',  'amount' => 5000000, 'description' => 'SPP bulanan dari orang tua siswa', 'offset' => 20],
            ['type' => 'in',  'amount' => 3500000, 'description' => 'SPP bulanan dari orang tua siswa kelas Mawar', 'offset' => 15],
            ['type' => 'out', 'amount' => 1200000, 'description' => 'Pembelian alat tulis dan ATK', 'offset' => 10],
            ['type' => 'out', 'amount' => 800000,  'description' => 'Biaya operasional listrik dan air', 'offset' => 5],
            ['type' => 'in',  'amount' => 500000,  'description' => 'Donasi dari yayasan untuk kegiatan', 'offset' => 2],
        ];

        foreach ($cashflowData as $row) {
            Cashflow::firstOrCreate(
                [
                    'sekolah_id' => $sekolah->id,
                    'description' => $row['description'],
                ],
                [
                    'type' => $row['type'],
                    'amount' => $row['amount'],
                    'date' => Carbon::today()->subDays($row['offset']),
                ]
            );
        }

        // ── Kritik & Saran ──────────────────────────────
        $kritikSaranData = [
            ['message' => 'Anak saya sangat senang belajar di PAUD Bintang Kecil. Guru-gurunya sangat sabar dan perhatian.', 'status' => 'selesai', 'umpan_balik' => 'Terima kasih atas apresiasinya! Kami akan terus menjaga kualitas pendidikan.'],
            ['message' => 'Mohon ditambahkan waktu bermain outdoor karena anak-anak perlu aktivitas fisik lebih.', 'status' => 'diproses', 'umpan_balik' => null],
            ['message' => 'Menu makanan sangat variatif dan bergizi. Anak saya jadi lebih lahap makan.', 'status' => 'pending', 'umpan_balik' => null],
        ];

        foreach ($kritikSaranData as $row) {
            KritikSaran::firstOrCreate(
                [
                    'sekolah_id' => $sekolah->id,
                    'user_id' => $users['ortu']->id,
                    'message' => $row['message'],
                ],
                [
                    'status' => $row['status'],
                    'umpan_balik' => $row['umpan_balik'],
                ]
            );
        }

        // ── CMS Content ─────────────────────────────────
        $cmsData = [
            'hero_title' => 'Selamat Datang di PAUD Bintang Kecil! 🌈',
            'hero_subtitle' => 'Tempat terbaik untuk tumbuh, belajar, dan bermain bersama teman-teman baru!',
            'about_title' => 'Tentang PAUD Bintang Kecil',
            'about_text' => 'PAUD Bintang Kecil adalah lembaga pendidikan anak usia dini yang berdiri sejak tahun 2020. Kami mengutamakan perkembangan optimal anak melalui pendidikan yang menyenangkan, aman, dan penuh kasih sayang.',
            'kontak_alamat' => 'Jl. Taman Lembayung No.47, Sendangguwo, Kec. Tembalang, Kota Semarang, Jawa Tengah (50273)',
            'kontak_telepon' => '085117494221',
            'kontak_email' => 'admin@firstudio.id',
            'kontak_jam' => 'Senin-Jumat: 07.00-16.00 WIB',
            'footer_text' => 'Tumbuh bersama, bahagia bersama. 💛',
        ];

        foreach ($cmsData as $key => $value) {
            CmsContent::set($key, $value);
        }
    }

    private function seedAiTokens(Sekolah $sekolah, User $admin): void
    {
        $service = app(AiTokenService::class);
        $balance = $service->getBalance($sekolah->id);

        if ($balance === 0) {
            $service->topUp($sekolah->id, 50, $admin, 'Top-up token awal untuk demo');
        }
    }
}
