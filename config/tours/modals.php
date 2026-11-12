<?php

require __DIR__.'/helpers.php';

return [

    'lembaga.sekolah.index' => tour_crud_modal_bundle(
        [
            ['element' => '[data-tour="modal-create-section-form"]', 'title' => 'Data Cabang Sekolah', 'description' => 'Isi nama, alamat, dan informasi cabang baru.'],
            ['element' => '[data-tour="modal-create-submit"]', 'title' => 'Simpan', 'description' => 'Daftarkan cabang sekolah ke sistem.', 'side' => 'top'],
        ],
        'Konfirmasi Hapus',
        'Hapus cabang sekolah secara permanen setelah konfirmasi.',
        [
            0 => ['title' => 'Ubah Data Cabang Sekolah', 'description' => 'Perbarui nama, alamat, dan informasi cabang sekolah yang dipilih.'],
        ],
    ),

    'lembaga.admin-sekolah.index' => tour_crud_modal_bundle(
        [
            ['element' => '[data-tour="modal-create-section-form"]', 'title' => 'Data Admin', 'description' => 'Isi nama dan email admin baru.'],
            ['element' => '[data-tour="modal-create-section-penugasan"]', 'title' => 'Penugasan Sekolah', 'description' => 'Tentukan cabang sekolah yang dikelola admin ini.', 'side' => 'top'],
            ['element' => '[data-tour="modal-create-submit"]', 'title' => 'Simpan', 'description' => 'Buat akun admin setelah data lengkap.', 'side' => 'top'],
        ],
        'Konfirmasi Hapus Admin',
        'Hapus akun admin setelah memastikan tidak ada ketergantungan aktif.',
        [
            0 => ['title' => 'Ubah Data Admin', 'description' => 'Perbarui nama dan email admin.'],
            1 => ['title' => 'Ubah Penugasan Sekolah', 'description' => 'Sesuaikan cabang sekolah yang dikelola admin ini.'],
        ],
    ),

    'admin.anak.index' => tour_crud_modal_bundle(
        [
            ['element' => '[data-tour="modal-create-section-anak"]', 'title' => 'Isi Data Anak (Siswa)', 'description' => 'Lengkapi identitas siswa, kelas, dan foto.'],
            ['element' => '[data-tour="modal-create-section-ortu"]', 'title' => 'Isi Data Orang Tua', 'description' => 'Catat data bapak dan ibu siswa.'],
            ['element' => '[data-tour="modal-create-section-wali"]', 'title' => 'Isi Akun Login Utama (Wali)', 'description' => 'Buat akun baru atau pilih orang tua yang sudah terdaftar.'],
            ['element' => '[data-tour="modal-create-submit"]', 'title' => 'Simpan', 'description' => 'Kirim formulir setelah semua data terisi benar.', 'side' => 'top'],
        ],
        'Konfirmasi Hapus Siswa',
        'Hapus data siswa dari sekolah setelah konfirmasi.',
        [
            0 => ['title' => 'Ubah Data Anak (Siswa)', 'description' => 'Perbarui identitas siswa, kelas, dan foto.'],
            1 => ['title' => 'Ubah Data Orang Tua', 'description' => 'Sesuaikan data bapak dan ibu siswa.'],
            2 => ['title' => 'Ubah Akun Login Utama (Wali)', 'description' => 'Perbarui nama dan email wali untuk login.'],
        ],
    ),

    'admin.kelas.index' => tour_crud_modal_bundle(
        [
            ['element' => '[data-tour="modal-create-section-form"]', 'title' => 'Info Kelas & Wali', 'description' => 'Isi nama kelas dan deskripsi. Wali kelas ditetapkan setelah kelas dibuat.'],
            ['element' => '[data-tour="modal-create-submit"]', 'title' => 'Simpan', 'description' => 'Buat kelas baru setelah data terisi.', 'side' => 'top'],
        ],
        'Konfirmasi Hapus Kelas',
        'Hapus kelas yang tidak lagi digunakan.',
        [
            0 => ['title' => 'Ubah Info Kelas & Wali', 'description' => 'Ubah nama kelas, wali kelas, atau deskripsi.'],
        ],
        extra: tour_modal_detail(
            'Detail Siswa Kelas',
            'Lihat daftar siswa yang terdaftar di kelas ini.',
        ),
    ),

    'admin.matrikulasi.index' => tour_crud_modal_bundle(
        [
            ['element' => '[data-tour="modal-create-section-indikator"]', 'title' => 'Indikator & Aspek', 'description' => 'Tentukan aspek pembelajaran dan indikator capaian.'],
            ['element' => '[data-tour="modal-create-section-detail"]', 'title' => 'Tujuan & Strategi', 'description' => 'Lengkapi tujuan pembelajaran, strategi, dan deskripsi.'],
            ['element' => '[data-tour="modal-create-submit"]', 'title' => 'Simpan', 'description' => 'Simpan indikator matrikulasi baru.', 'side' => 'top'],
        ],
        'Konfirmasi Hapus',
        'Hapus data matrikulasi yang salah atau duplikat.',
        [
            0 => ['title' => 'Ubah Indikator & Aspek', 'description' => 'Perbarui aspek pembelajaran dan indikator capaian.'],
            1 => ['title' => 'Ubah Tujuan & Strategi', 'description' => 'Sesuaikan tujuan pembelajaran, strategi, dan deskripsi.'],
        ],
        extra: tour_modal_detail(
            'Detail Indikator',
            'Baca tujuan, strategi, dan deskripsi lengkap indikator matrikulasi.',
            '[data-tour="modal-detail-content"]',
        ),
    ),

    'admin.skala-pencapaian.index' => tour_crud_modal_bundle(
        [
            ['element' => '[data-tour="modal-create-section-form"]', 'title' => 'Definisi Skala', 'description' => 'Isi kode, label, warna, dan urutan skala capaian.'],
            ['element' => '[data-tour="modal-create-submit"]', 'title' => 'Simpan', 'description' => 'Simpan skala capaian baru.', 'side' => 'top'],
        ],
        'Konfirmasi Hapus Skala',
        'Hapus skala yang tidak lagi dipakai.',
        [
            0 => ['title' => 'Ubah Definisi Skala', 'description' => 'Sesuaikan deskripsi atau level skala pencapaian.'],
        ],
    ),

    'admin.sarana.index' => tour_crud_modal_bundle(
        [
            ['element' => '[data-tour="modal-create-section-form"]', 'title' => 'Data Sarana', 'description' => 'Catat jenis, jumlah, kondisi, dan foto sarana.'],
            ['element' => '[data-tour="modal-create-submit"]', 'title' => 'Simpan', 'description' => 'Tambahkan sarana ke inventaris.', 'side' => 'top'],
        ],
        'Konfirmasi Hapus Sarana',
        'Hapus data sarana dari inventaris.',
        [
            0 => ['title' => 'Ubah Data Sarana', 'description' => 'Perbarui informasi sarana dan prasarana.'],
        ],
    ),

    'admin.pengajar.index' => tour_crud_modal_bundle(
        [
            ['element' => '[data-tour="modal-create-section-data"]', 'title' => 'Data Guru', 'description' => 'Isi identitas, kontak, dan foto guru.'],
            ['element' => '[data-tour="modal-create-section-akun"]', 'title' => 'Penugasan Kelas & Akun', 'description' => 'Tentukan email login dan kelas yang diampu.'],
            ['element' => '[data-tour="modal-create-submit"]', 'title' => 'Simpan', 'description' => 'Daftarkan guru setelah data lengkap.', 'side' => 'top'],
        ],
        'Konfirmasi Hapus Guru',
        'Nonaktifkan atau hapus akun guru.',
        [
            0 => ['title' => 'Ubah Data Guru', 'description' => 'Perbarui identitas, kontak, dan foto guru.'],
            1 => ['title' => 'Ubah Penugasan Kelas & Akun', 'description' => 'Ubah data guru dan penugasan kelas.'],
        ],
    ),

    'admin.menu-makanan.index' => tour_crud_modal_bundle(
        [
            ['element' => '[data-tour="modal-create-section-form"]', 'title' => 'Jadwal Menu', 'description' => 'Isi tanggal, hidangan, info gizi, dan foto menu.'],
            ['element' => '[data-tour="modal-create-submit"]', 'title' => 'Simpan', 'description' => 'Publikasikan menu makan harian.', 'side' => 'top'],
        ],
        'Konfirmasi Hapus Menu',
        'Hapus jadwal menu yang sudah tidak relevan.',
        [
            0 => ['title' => 'Ubah Jadwal Menu', 'description' => 'Perbarui daftar hidangan, gizi, atau foto menu.'],
        ],
    ),

    'admin.cashflow.index' => tour_crud_modal_bundle(
        [
            ['element' => '[data-tour="modal-create-section-form"]', 'title' => 'Data Transaksi', 'description' => 'Isi jenis, nominal, kategori, dan keterangan transaksi.'],
            ['element' => '[data-tour="modal-create-submit"]', 'title' => 'Simpan', 'description' => 'Catat transaksi keuangan.', 'side' => 'top'],
        ],
        'Konfirmasi Hapus Transaksi',
        'Hapus catatan transaksi yang salah input.',
        [
            0 => ['title' => 'Ubah Data Transaksi', 'description' => 'Koreksi nominal, kategori, atau keterangan transaksi.'],
        ],
    ),

    'admin.biaya-bulanan.index' => array_merge(
        tour_modal_create_sections([
            ['element' => '[data-tour="modal-create-section-form"]', 'title' => 'Data Jenis Biaya', 'description' => 'Isi nama biaya, tarif bulanan default, dan keterangan.'],
            ['element' => '[data-tour="modal-create-submit"]', 'title' => 'Simpan', 'description' => 'Simpan jenis biaya baru.', 'side' => 'top'],
        ]),
        [
            [
                'element' => '[data-tour="modal-add-siswa"]',
                'openModal' => 'addSiswa',
                'title' => 'Filter Kelas',
                'description' => 'Saring daftar siswa berdasarkan kelas sebelum memilih.',
                'side' => 'left',
            ],
            [
                'element' => '[data-tour="modal-add-siswa-list"]',
                'openModal' => 'addSiswa',
                'title' => 'Pilih Siswa',
                'description' => 'Centang siswa yang akan dikenakan biaya ini.',
                'side' => 'top',
            ],
            [
                'element' => '[data-tour="modal-add-siswa-submit"]',
                'openModal' => 'addSiswa',
                'title' => 'Simpan',
                'description' => 'Tambahkan siswa terpilih ke daftar biaya bulanan.',
                'side' => 'top',
            ],
        ],
    ),

    'admin.diskon.index' => tour_crud_modal_bundle(
        [
            ['element' => '[data-tour="modal-create-section-form"]', 'title' => 'Data Diskon', 'description' => 'Isi nama, tipe (persentase/nominal), nilai, dan keterangan.'],
            ['element' => '[data-tour="modal-create-submit"]', 'title' => 'Simpan', 'description' => 'Simpan diskon baru.', 'side' => 'top'],
        ],
        'Konfirmasi Hapus Diskon',
        'Hapus diskon yang tidak lagi digunakan.',
        [
            0 => ['title' => 'Ubah Data Diskon', 'description' => 'Perbarui nama, nilai, atau status aktif diskon.'],
        ],
    ),

    'admin.pembayaran-bulanan.index' => [
        [
            'element' => '[data-tour="modal-generate"]',
            'openModal' => 'generate',
            'title' => 'Periode Tagihan',
            'description' => 'Pilih bulan dan tahun tagihan yang akan digenerate.',
            'side' => 'left',
        ],
        [
            'element' => '[data-tour="modal-generate-checklist"]',
            'openModal' => 'generate',
            'title' => 'Pilih Siswa',
            'description' => 'Centang siswa yang akan digenerate. Bisa atur diskon dan biaya lain per baris.',
            'side' => 'top',
        ],
        [
            'element' => '[data-tour="modal-generate-submit"]',
            'openModal' => 'generate',
            'title' => 'Generate',
            'description' => 'Buat tagihan untuk siswa yang dicentang.',
            'side' => 'top',
        ],
    ],

    'admin.pembayaran-bulanan.show' => [
        [
            'element' => '[data-tour="modal-approve"]',
            'openModal' => 'approve',
            'title' => 'Tandai Lunas',
            'description' => 'Konfirmasi pembayaran setelah bukti transfer diverifikasi.',
            'side' => 'left',
        ],
        [
            'element' => '[data-tour="modal-reject"]',
            'openModal' => 'reject',
            'title' => 'Tolak Pembayaran',
            'description' => 'Tolak dengan alasan jika bukti tidak valid atau nominal salah.',
            'side' => 'left',
        ],
    ],

    'orangtua.pembayaran.show' => [
        [
            'element' => '[data-tour="modal-create-section-form"]',
            'openModal' => 'bayar',
            'title' => 'Upload Bukti',
            'description' => 'Unggah foto bukti transfer dan catatan opsional.',
            'side' => 'left',
        ],
        [
            'element' => '[data-tour="modal-create-submit"]',
            'openModal' => 'bayar',
            'title' => 'Kirim',
            'description' => 'Kirim bukti ke sekolah untuk diverifikasi.',
            'side' => 'top',
        ],
    ],

    'admin.kesehatan.index' => array_merge(
        tour_modal_create_sections([
            ['element' => '[data-tour="modal-create-section-form"]', 'title' => 'Data Pemeriksaan', 'description' => 'Isi antropometri, kebersihan, dan catatan kesehatan siswa.'],
            ['element' => '[data-tour="modal-create-submit"]', 'title' => 'Simpan', 'description' => 'Simpan data pemeriksaan kesehatan.', 'side' => 'top'],
        ]),
        tour_modal_history([
            ['element' => '[data-tour="modal-history"]', 'title' => 'Riwayat Kesehatan', 'description' => 'Lihat daftar pemeriksaan kesehatan dan kebersihan siswa.'],
            ['element' => '[data-tour="modal-history-content"]', 'title' => 'Tabel Riwayat', 'description' => 'Setiap baris berisi tanggal, dimensi tubuh, kebersihan, dan alergi.', 'side' => 'top'],
        ]),
    ),

    'admin.kegiatan.index' => tour_crud_modal_bundle(
        [
            ['element' => '[data-tour="modal-create-section-form"]', 'title' => 'Data Jurnal', 'description' => 'Isi judul, tanggal, kelas, dan dokumentasi kegiatan.'],
            ['element' => '[data-tour="modal-create-submit"]', 'title' => 'Simpan', 'description' => 'Publikasikan jurnal kegiatan belajar.', 'side' => 'top'],
        ],
        'Konfirmasi Hapus Kegiatan',
        'Hapus kegiatan yang tercatat salah.',
        [
            0 => ['title' => 'Ubah Data Jurnal', 'description' => 'Ubah detail kegiatan, tanggal, atau lampiran.'],
        ],
    ),

    'admin.pencapaian.index' => array_merge(
        tour_modal_pencapaian_create_sections(includeKelas: true),
        tour_modal_edit_sections([
            ['element' => '[data-tour="modal-edit-section-form"]', 'title' => 'Ubah Data Evaluasi', 'description' => 'Sesuaikan nilai skala, umpan balik, atau dokumentasi evaluasi.'],
            ['element' => '[data-tour="modal-edit-submit"]', 'title' => 'Simpan', 'description' => 'Kirim perubahan evaluasi.', 'side' => 'top'],
        ]),
        tour_modal_delete_only(
            'Konfirmasi Hapus Evaluasi',
            'Hapus evaluasi yang dibuat secara keliru.',
        ),
    ),

    'admin.pendaftaran.index' => [
        [
            'element' => '[data-tour="modal-reject"]',
            'openModal' => 'reject',
            'title' => 'Modal Tolak Pendaftaran',
            'description' => 'Berikan alasan penolakan saat pendaftaran siswa tidak disetujui.',
            'side' => 'left',
        ],
    ],

    'admin.kegiatan-rutin.index' => tour_modal_create_sections([
        ['element' => '[data-tour="modal-create-section-form"]', 'title' => 'Status Pencapaian', 'description' => 'Pilih status capaian untuk setiap kegiatan rutin siswa.'],
        ['element' => '[data-tour="modal-create-submit"]', 'title' => 'Simpan', 'description' => 'Simpan pencapaian kegiatan rutin hari ini.', 'side' => 'top'],
    ]),

    'adminkelas.anak.index' => tour_crud_modal_bundle(
        [
            ['element' => '[data-tour="modal-create-section-siswa"]', 'title' => 'Isi Data Siswa', 'description' => 'Lengkapi identitas siswa, kelas, dan foto.'],
            ['element' => '[data-tour="modal-create-section-ortu"]', 'title' => 'Isi Data Orang Tua & Akun Login', 'description' => 'Catat email login wali dan nama orang tua.'],
            ['element' => '[data-tour="modal-create-submit"]', 'title' => 'Simpan', 'description' => 'Kirim formulir setelah semua data terisi benar.', 'side' => 'top'],
        ],
        'Konfirmasi Hapus Siswa',
        'Hapus data siswa dari kelas.',
        [
            0 => ['title' => 'Ubah Data Siswa', 'description' => 'Perbarui identitas siswa, kelas, dan foto.'],
            1 => ['title' => 'Ubah Data Orang Tua & Akun Login', 'description' => 'Sesuaikan email login wali dan nama orang tua.'],
        ],
    ),

    'adminkelas.kesehatan.index' => array_merge(
        tour_modal_create_sections([
            ['element' => '[data-tour="modal-create-section-form"]', 'title' => 'Data Pemeriksaan', 'description' => 'Isi antropometri, kebersihan, dan catatan kesehatan siswa.'],
            ['element' => '[data-tour="modal-create-submit"]', 'title' => 'Simpan', 'description' => 'Simpan data pemeriksaan kesehatan.', 'side' => 'top'],
        ]),
        tour_modal_history([
            ['element' => '[data-tour="modal-history"]', 'title' => 'Riwayat Kesehatan', 'description' => 'Lihat daftar pemeriksaan kesehatan siswa di kelas Anda.'],
            ['element' => '[data-tour="modal-history-content"]', 'title' => 'Tabel Riwayat', 'description' => 'Setiap baris berisi tanggal, dimensi tubuh, kebersihan, dan alergi.', 'side' => 'top'],
        ]),
    ),

    'pengajar.kegiatan.index' => tour_crud_modal_bundle(
        [
            ['element' => '[data-tour="modal-create-section-form"]', 'title' => 'Data Jurnal', 'description' => 'Isi judul, tanggal, dan dokumentasi kegiatan belajar.'],
            ['element' => '[data-tour="modal-create-submit"]', 'title' => 'Simpan', 'description' => 'Publikasikan jurnal kegiatan.', 'side' => 'top'],
        ],
        'Konfirmasi Hapus Kegiatan',
        'Hapus kegiatan yang tidak relevan.',
        [
            0 => ['title' => 'Ubah Data Jurnal', 'description' => 'Perbarui detail kegiatan yang sudah diinput.'],
        ],
    ),

    'pengajar.pencapaian.index' => array_merge(
        tour_modal_pencapaian_create_sections(includeKelas: false),
        tour_modal_edit_sections([
            ['element' => '[data-tour="modal-edit-section-form"]', 'title' => 'Ubah Data Evaluasi', 'description' => 'Ubah nilai skala, umpan balik, atau dokumentasi evaluasi.'],
            ['element' => '[data-tour="modal-edit-submit"]', 'title' => 'Simpan', 'description' => 'Kirim perubahan evaluasi.', 'side' => 'top'],
        ]),
        tour_modal_delete_only(
            'Konfirmasi Hapus Evaluasi',
            'Hapus evaluasi yang keliru.',
        ),
    ),

    'pengajar.kegiatan-rutin.index' => tour_modal_create_sections([
        ['element' => '[data-tour="modal-create-section-form"]', 'title' => 'Status Pencapaian', 'description' => 'Pilih status capaian untuk setiap kegiatan rutin siswa.'],
        ['element' => '[data-tour="modal-create-submit"]', 'title' => 'Simpan', 'description' => 'Simpan pencapaian kegiatan rutin hari ini.', 'side' => 'top'],
    ]),

    'orangtua.kritik-saran.index' => array_merge(
        tour_modal_create_sections([
            ['element' => '[data-tour="modal-create-section-form"]', 'title' => 'Isi Pesan', 'description' => 'Tulis saran atau kritik untuk sekolah.'],
            ['element' => '[data-tour="modal-create-submit"]', 'title' => 'Kirim', 'description' => 'Kirim masukan ke pihak sekolah.', 'side' => 'top'],
        ]),
        tour_modal_edit_sections_mirror_create(
            [
                ['element' => '[data-tour="modal-create-section-form"]', 'title' => 'Isi Pesan', 'description' => 'Tulis saran atau kritik untuk sekolah.'],
                ['element' => '[data-tour="modal-create-submit"]', 'title' => 'Kirim', 'description' => 'Kirim masukan ke pihak sekolah.', 'side' => 'top'],
            ],
            [
                0 => ['title' => 'Ubah Pesan', 'description' => 'Ubah pesan yang masih berstatus terkirim.'],
                1 => ['title' => 'Simpan', 'description' => 'Kirim perubahan masukan ke pihak sekolah.', 'side' => 'top'],
            ],
        ),
    ),

];
