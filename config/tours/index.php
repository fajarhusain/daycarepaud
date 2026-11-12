<?php

require __DIR__.'/helpers.php';

return [

    // ─── LEMBAGA ───────────────────────────────────────────────

    'lembaga.sekolah.index' => array_merge(
        [
            ['element' => '[data-tour="nav-lembaga.sekolah.index"]', 'title' => 'Menu Sekolah', 'description' => 'Kelola daftar cabang sekolah di bawah yayasan Anda dari menu ini.', 'side' => 'right'],
            ['element' => '[data-tour="page-header"]', 'title' => 'Data Sekolah', 'description' => 'Halaman ini menampilkan seluruh cabang sekolah yang terdaftar di sistem.', 'side' => 'bottom'],
        ],
        tour_table_actions(
            'lembaga-sekolah',
            'Tambah Cabang',
            'Klik tombol ini untuk mendaftarkan cabang sekolah baru.',
            'Edit',
            'Perbarui nama, alamat, dan informasi cabang sekolah.',
            'Hapus',
            'Hapus cabang sekolah secara permanen setelah konfirmasi.',
        ),
    ),

    'lembaga.admin-sekolah.index' => array_merge(
        [
            ['element' => '[data-tour="nav-lembaga.admin-sekolah.index"]', 'title' => 'Menu Admin Sekolah', 'description' => 'Kelola akun admin sekolah yang mengelola operasional tiap cabang.', 'side' => 'right'],
            ['element' => '[data-tour="page-header"]', 'title' => 'Admin Sekolah', 'description' => 'Daftar administrator yang ditugaskan ke masing-masing cabang sekolah.', 'side' => 'bottom'],
        ],
        tour_table_actions(
            'lembaga-admin',
            'Tambah Admin',
            'Buat akun admin baru dan tentukan cabang sekolah yang dikelolanya.',
            'Edit',
            'Ubah data admin dan penugasan cabang sekolah.',
            'Hapus',
            'Hapus akun admin setelah memastikan tidak ada ketergantungan aktif.',
        ),
    ),

    'lembaga.kritik-saran.index' => [
        ['element' => '[data-tour="nav-lembaga.kritik-saran.index"]', 'title' => 'Menu Kritik & Saran', 'description' => 'Pantau masukan dari orang tua di seluruh cabang sekolah.', 'side' => 'right'],
        ['element' => '[data-tour="page-header"]', 'title' => 'Kritik & Saran', 'description' => 'Semua pesan masukan dari wali murid dikumpulkan di halaman ini.', 'side' => 'bottom'],
        ['element' => '[data-tour="lembaga-kritik-list"]', 'title' => 'Daftar Masukan', 'description' => 'Setiap kartu menampilkan pengirim, isi pesan, dan status tanggapan.', 'side' => 'top'],
    ],

    'lembaga.activity-log.index' => [
        ['element' => '[data-tour="nav-lembaga.activity-log.index"]', 'title' => 'Menu Log Aktivitas', 'description' => 'Pantau riwayat perubahan data di seluruh cabang sekolah.', 'side' => 'right'],
        ['element' => '[data-tour="page-header"]', 'title' => 'Log Aktivitas', 'description' => 'Riwayat perubahan data berdasarkan scope lembaga Anda.', 'side' => 'bottom'],
        ['element' => '[data-tour="activity-log-filter"]', 'title' => 'Filter', 'description' => 'Saring berdasarkan tanggal, event, atau kata kunci.', 'side' => 'bottom'],
    ],

    // ─── SUPERADMIN ────────────────────────────────────────────

    'superadmin.dashboard' => [
        ['element' => '[data-tour="nav-superadmin.dashboard"]', 'title' => 'Menu Dashboard', 'description' => 'Ringkasan kesehatan platform untuk superadmin.', 'side' => 'right'],
        ['element' => '[data-tour="page-header"]', 'title' => 'Dashboard', 'description' => 'Ringkasan total lembaga, sekolah, pengguna, dan konfigurasi AI.', 'side' => 'bottom'],
        ['element' => '[data-tour="superadmin-dashboard-stats"]', 'title' => 'Ringkasan', 'description' => 'Kartu ringkasan metrik utama platform.', 'side' => 'bottom'],
    ],

    'superadmin.lembaga.index' => [
        ['element' => '[data-tour="nav-superadmin.lembaga.index"]', 'title' => 'Menu Lembaga', 'description' => 'Kelola data lembaga/yayasan di platform.', 'side' => 'right'],
        ['element' => '[data-tour="page-header"]', 'title' => 'Kelola Lembaga', 'description' => 'Tambah, edit, dan hapus lembaga.', 'side' => 'bottom'],
    ],

    'superadmin.admin-lembaga.index' => [
        ['element' => '[data-tour="nav-superadmin.admin-lembaga.index"]', 'title' => 'Menu Admin Lembaga', 'description' => 'Kelola akun admin lembaga.', 'side' => 'right'],
        ['element' => '[data-tour="page-header"]', 'title' => 'Admin Lembaga', 'description' => 'Tambah, edit, dan hapus akun admin lembaga.', 'side' => 'bottom'],
    ],

    'superadmin.sekolah.index' => [
        ['element' => '[data-tour="nav-superadmin.sekolah.index"]', 'title' => 'Menu Sekolah', 'description' => 'Monitoring sekolah di seluruh platform.', 'side' => 'right'],
        ['element' => '[data-tour="page-header"]', 'title' => 'Monitoring Sekolah', 'description' => 'Lihat daftar sekolah dan filter berdasarkan lembaga.', 'side' => 'bottom'],
    ],

    'superadmin.cms.index' => [
        ['element' => '[data-tour="nav-superadmin.cms.index"]', 'title' => 'Menu CMS Website', 'description' => 'Kelola konten website (banner, teks, gambar).', 'side' => 'right'],
        ['element' => '[data-tour="page-header"]', 'title' => 'CMS Website', 'description' => 'Perbarui konten website dan simpan perubahan.', 'side' => 'bottom'],
    ],

    'superadmin.users.index' => [
        ['element' => '[data-tour="nav-superadmin.users.index"]', 'title' => 'Menu Superadmin', 'description' => 'Kelola akun superadmin.', 'side' => 'right'],
        ['element' => '[data-tour="page-header"]', 'title' => 'Kelola Superadmin', 'description' => 'Tambah, edit, dan hapus akun superadmin.', 'side' => 'bottom'],
    ],

    'superadmin.activity-log.index' => [
        ['element' => '[data-tour="nav-superadmin.activity-log.index"]', 'title' => 'Menu Log Aktivitas', 'description' => 'Pantau audit log seluruh platform.', 'side' => 'right'],
        ['element' => '[data-tour="page-header"]', 'title' => 'Log Aktivitas', 'description' => 'Riwayat perubahan data di seluruh scope.', 'side' => 'bottom'],
        ['element' => '[data-tour="activity-log-filter"]', 'title' => 'Filter', 'description' => 'Saring berdasarkan tanggal, event, atau kata kunci.', 'side' => 'bottom'],
    ],

    'superadmin.ai-setting.index' => [
        ['element' => '[data-tour="nav-superadmin.ai-setting.index"]', 'title' => 'Menu Pengaturan AI', 'description' => 'Konfigurasi layanan AI dan token per lembaga dari sini.', 'side' => 'right'],
        ['element' => '[data-tour="page-header"]', 'title' => 'Pengaturan AI', 'description' => 'Atur provider, model, API key, dan saldo token per sekolah.', 'side' => 'bottom'],
        ['element' => '[data-tour="superadmin-ai-tabs"]', 'title' => 'Tab Pengaturan', 'description' => 'Pilih tab Provider & API atau Token Sekolah.', 'side' => 'bottom'],
        ['element' => '[data-tour="superadmin-ai-status"]', 'title' => 'Status AI', 'description' => 'Banner ini menunjukkan apakah layanan AI aktif atau belum dikonfigurasi.', 'side' => 'bottom'],
        ['element' => '[data-tour="superadmin-ai-form"]', 'title' => 'Form Konfigurasi', 'description' => 'Isi provider, model, dan API key. Gunakan tombol uji koneksi sebelum menyimpan.', 'side' => 'top'],
    ],

    // ─── ADMIN SEKOLAH ─────────────────────────────────────────

    'admin.anak.index' => array_merge(
        [
            ['element' => '[data-tour="nav-admin.anak.index"]', 'title' => 'Menu Siswa Sekolah', 'description' => 'Kelola data seluruh siswa yang terdaftar di sekolah Anda.', 'side' => 'right'],
            ['element' => '[data-tour="page-header"]', 'title' => 'Siswa Sekolah', 'description' => 'Daftar lengkap siswa beserta data orang tua dan kelasnya.', 'side' => 'bottom'],
            ['element' => '[data-tour="admin-anak-filter"]', 'title' => 'Filter & Pencarian', 'description' => 'Cari siswa berdasarkan nama atau filter berdasarkan kelas.', 'side' => 'bottom'],
        ],
        tour_table_actions(
            'admin-anak',
            'Registrasi Siswa',
            'Klik untuk membuka form pendaftaran siswa baru beserta data orang tua.',
            'Edit',
            'Perbarui profil siswa, kelas, atau data orang tua.',
            'Hapus',
            'Hapus data siswa dari sekolah setelah konfirmasi.',
            'Detail',
            'Buka halaman profil lengkap siswa.',
        ),
    ),

    'admin.pendaftaran.index' => [
        ['element' => '[data-tour="nav-admin.pendaftaran.index"]', 'title' => 'Menu Pendaftaran', 'description' => 'Tinjau dan proses pendaftaran siswa baru dari orang tua.', 'side' => 'right'],
        ['element' => '[data-tour="page-header"]', 'title' => 'Pendaftaran Siswa Baru', 'description' => 'Kelola permohonan pendaftaran yang masuk ke sekolah Anda.', 'side' => 'bottom'],
        ['element' => '[data-tour="admin-pendaftaran-pending"]', 'title' => 'Menunggu Persetujuan', 'description' => 'Pendaftar baru yang perlu Anda setujui atau tolak. Badge menunjukkan jumlah pending.', 'side' => 'top'],
        ['element' => '[data-tour="admin-pendaftaran-action-approve"]', 'title' => 'Setujui', 'description' => 'Terima pendaftaran siswa baru ke sekolah.', 'side' => 'left'],
        ['element' => '[data-tour="admin-pendaftaran-action-reject"]', 'title' => 'Tolak', 'description' => 'Tolak pendaftaran dengan alasan penolakan.', 'side' => 'left'],
        ['element' => '[data-tour="admin-pendaftaran-approved"]', 'title' => 'Sudah Disetujui', 'description' => 'Daftar pendaftar yang telah diterima dan aktif di sistem.', 'side' => 'top'],
    ],

    'admin.kelas.index' => array_merge(
        [
            ['element' => '[data-tour="nav-admin.kelas.index"]', 'title' => 'Menu Kelola Kelas', 'description' => 'Atur kelas, wali kelas, dan penempatan siswa.', 'side' => 'right'],
            ['element' => '[data-tour="page-header"]', 'title' => 'Kelola Kelas', 'description' => 'Manajemen kelas dan penempatan siswa di sekolah.', 'side' => 'bottom'],
        ],
        tour_table_actions(
            'admin-kelas',
            'Buat Kelas',
            'Tambahkan kelas baru beserta wali kelas dan deskripsinya.',
            'Edit',
            'Ubah nama kelas, wali kelas, atau deskripsi.',
            'Hapus',
            'Hapus kelas yang tidak lagi digunakan.',
            'Detail',
            'Lihat daftar siswa yang terdaftar di kelas ini.',
        ),
    ),

    'admin.presensi.index' => [
        ['element' => '[data-tour="nav-admin.presensi.index"]', 'title' => 'Menu Presensi Siswa', 'description' => 'Catat dan pantau kehadiran siswa harian.', 'side' => 'right'],
        ['element' => '[data-tour="page-header"]', 'title' => 'Presensi Siswa', 'description' => 'Input kehadiran siswa per kelas dan tanggal.', 'side' => 'bottom'],
        ['element' => '[data-tour="admin-presensi-filter"]', 'title' => 'Filter Kelas & Tanggal', 'description' => 'Pilih kelas dan tanggal untuk menampilkan daftar siswa yang akan diabsen.', 'side' => 'bottom'],
        ['element' => '[data-tour="admin-presensi-checklist"]', 'title' => 'Checklist Kehadiran', 'description' => 'Centang siswa yang hadir, lalu simpan. Ringkasan statistik ditampilkan di atas.', 'side' => 'top'],
    ],

    'admin.kesehatan.index' => array_merge(
        [
            ['element' => '[data-tour="nav-admin.kesehatan.index"]', 'title' => 'Menu Kesehatan Siswa', 'description' => 'Pantau dan catat data kesehatan seluruh siswa.', 'side' => 'right'],
            ['element' => '[data-tour="page-header"]', 'title' => 'Kesehatan Siswa', 'description' => 'Data berat badan, tinggi badan, alergi, dan kebersihan siswa.', 'side' => 'bottom'],
            ['element' => '[data-tour="admin-kesehatan-filter"]', 'title' => 'Filter Siswa', 'description' => 'Saring kelas, nama, atau status pemeriksaan — lalu Filter, Reset, atau export Excel.', 'side' => 'bottom'],
            ['element' => '[data-tour="admin-kesehatan-action-riwayat"]', 'title' => 'Riwayat', 'description' => 'Lihat riwayat pemeriksaan kesehatan dan kebersihan siswa.', 'side' => 'left'],
            ['element' => '[data-tour="admin-kesehatan-action-input"]', 'title' => 'Input Data', 'description' => 'Catat pemeriksaan kesehatan dan kebersihan siswa.', 'side' => 'left'],
        ],
    ),

    'admin.matrikulasi.index' => array_merge(
        [
            ['element' => '[data-tour="nav-admin.matrikulasi.index"]', 'title' => 'Menu Matrikulasi', 'description' => 'Kelola indikator pembelajaran dan kurikulum sekolah.', 'side' => 'right'],
            ['element' => '[data-tour="page-header"]', 'title' => 'Matrikulasi', 'description' => 'Daftar indikator capaian pembelajaran yang menjadi acuan evaluasi.', 'side' => 'bottom'],
        ],
        tour_table_actions(
            'admin-matrikulasi',
            'Tambah Indikator',
            'Buat indikator matrikulasi baru dengan aspek dan tujuan pembelajaran.',
            'Edit',
            'Perbarui indikator matrikulasi yang sudah tersimpan.',
            'Hapus',
            'Hapus indikator yang salah atau duplikat.',
            'Detail',
            'Baca tujuan, strategi, dan deskripsi lengkap indikator.',
        ),
    ),

    'admin.skala-pencapaian.index' => array_merge(
        [
            ['element' => '[data-tour="nav-admin.skala-pencapaian.index"]', 'title' => 'Menu Skala Capaian', 'description' => 'Atur skala penilaian pencapaian siswa (mis. Berkembang, Cakap).', 'side' => 'right'],
            ['element' => '[data-tour="page-header"]', 'title' => 'Skala Capaian', 'description' => 'Definisi tingkat pencapaian yang dipakai saat evaluasi siswa.', 'side' => 'bottom'],
            ['element' => '[data-tour="admin-skala-filter"]', 'title' => 'Filter & Aksi', 'description' => 'Cari kode/label, saring status, export Excel, atau tambah skala baru.', 'side' => 'bottom'],
        ],
        tour_table_actions(
            'admin-skala',
            'Tambah Skala',
            'Buat level capaian baru dengan kode, label, dan warna.',
            'Edit',
            'Sesuaikan deskripsi atau level skala pencapaian.',
            'Hapus',
            'Hapus skala yang tidak lagi dipakai.',
        ),
    ),

    'admin.kegiatan.index' => [
        ['element' => '[data-tour="nav-admin.kegiatan.index"]', 'title' => 'Menu Agenda Belajar', 'description' => 'Kelola jurnal kegiatan belajar harian di sekolah.', 'side' => 'right'],
        ['element' => '[data-tour="page-header"]', 'title' => 'Agenda Belajar', 'description' => 'Kalender jurnal kegiatan belajar seluruh kelas dan pengajar.', 'side' => 'bottom'],
        ['element' => '[data-tour="admin-kegiatan-filter"]', 'title' => 'Filter Kalender', 'description' => 'Saring jurnal berdasarkan kelas atau pengajar tertentu.', 'side' => 'bottom'],
        ['element' => '[data-tour="admin-kegiatan-add-btn"]', 'title' => 'Buat Jurnal', 'description' => 'Tambahkan jurnal kegiatan baru beserta dokumentasi foto.', 'side' => 'left'],
        ['element' => '[data-tour="admin-kegiatan-calendar"]', 'title' => 'Kalender Jurnal', 'description' => 'Klik tanggal di kalender untuk melihat, edit, atau hapus jurnal.', 'side' => 'top'],
    ],

    'admin.master-kegiatan-rutin.index' => array_merge(
        [
            ['element' => '[data-tour="nav-admin.master-kegiatan-rutin.index"]', 'title' => 'Menu Kegiatan Rutin', 'description' => 'Kelola kegiatan rutin harian seperti makan, tidur, dan toilet training.', 'side' => 'right'],
            ['element' => '[data-tour="page-header"]', 'title' => 'Kegiatan Rutin', 'description' => 'Master kegiatan rutin yang dicatat setiap hari untuk setiap siswa.', 'side' => 'bottom'],
        ],
        tour_table_actions(
            'pg-master-rutin',
            'Tambah Kegiatan',
            'Buat jenis kegiatan rutin baru dan tautkan ke matrikulasi.',
            'Edit',
            'Ubah template kegiatan rutin.',
            'Hapus',
            'Hapus kegiatan rutin yang tidak digunakan.',
            'Detail',
            'Lihat capaian dan catatan siswa per kegiatan.',
        ),
    ),

    'admin.pencapaian.index' => array_merge(
        [
            ['element' => '[data-tour="nav-admin.pencapaian.index"]', 'title' => 'Menu Pencapaian Siswa', 'description' => 'Evaluasi dan catat pencapaian belajar siswa.', 'side' => 'right'],
            ['element' => '[data-tour="page-header"]', 'title' => 'Pencapaian Siswa', 'description' => 'Rekap evaluasi pencapaian per kegiatan dan per siswa.', 'side' => 'bottom'],
            ['element' => '[data-tour="admin-pencapaian-filter"]', 'title' => 'Filter Aspek', 'description' => 'Saring pencapaian berdasarkan aspek pembelajaran tertentu.', 'side' => 'bottom'],
        ],
        tour_table_actions(
            'admin-pencapaian',
            'Buat Evaluasi',
            'Tambahkan penilaian pencapaian baru dengan bantuan AI.',
            'Edit',
            'Sesuaikan nilai atau catatan evaluasi.',
            'Hapus',
            'Hapus evaluasi yang dibuat secara keliru.',
        ),
    ),

    'admin.monev.index' => [
        ['element' => '[data-tour="nav-admin.monev.index"]', 'title' => 'Menu Monev', 'description' => 'Monitoring dan evaluasi perkembangan siswa bulanan.', 'side' => 'right'],
        ['element' => '[data-tour="page-header"]', 'title' => 'Monitoring & Evaluasi', 'description' => 'Generate dan kelola laporan monev perkembangan siswa.', 'side' => 'bottom'],
        ['element' => '[data-tour="admin-monev-filters"]', 'title' => 'Filter Laporan', 'description' => 'Cari siswa, pilih kelas, dan tentukan periode bulan/tahun.', 'side' => 'bottom'],
        ['element' => '[data-tour="admin-monev-table"]', 'title' => 'Daftar Siswa', 'description' => 'Generate laporan monev per siswa atau sekaligus untuk seluruh kelas.', 'side' => 'top'],
    ],

    'admin.pengajar.index' => array_merge(
        [
            ['element' => '[data-tour="nav-admin.pengajar.index"]', 'title' => 'Menu Data Guru', 'description' => 'Kelola akun dan data guru di sekolah.', 'side' => 'right'],
            ['element' => '[data-tour="page-header"]', 'title' => 'Data Guru', 'description' => 'Daftar guru beserta jabatan, kelas, dan kontak.', 'side' => 'bottom'],
        ],
        tour_table_actions(
            'admin-pengajar',
            'Registrasi Guru',
            'Daftarkan guru baru beserta akun login dan penugasan kelas.',
            'Edit',
            'Ubah data guru dan penugasan kelas.',
            'Hapus',
            'Nonaktifkan atau hapus akun guru.',
        ),
    ),

    'admin.presensi-guru.index' => [
        ['element' => '[data-tour="nav-admin.presensi-guru.index"]', 'title' => 'Menu Presensi Guru', 'description' => 'Catat kehadiran pengajar setiap hari.', 'side' => 'right'],
        ['element' => '[data-tour="page-header"]', 'title' => 'Presensi Guru', 'description' => 'Input absensi harian seluruh pengajar di sekolah.', 'side' => 'bottom'],
        ['element' => '[data-tour="admin-presensi-guru-date"]', 'title' => 'Filter Harian', 'description' => 'Pilih tanggal, cari pengajar, atau export rekap.', 'side' => 'bottom'],
        ['element' => '[data-tour="admin-presensi-guru-input-table"]', 'title' => 'Daftar Guru', 'description' => 'Tandai Hadir/Tidak Hadir dan isi keterangan, lalu simpan.', 'side' => 'top'],
    ],

    'admin.sarana.index' => array_merge(
        [
            ['element' => '[data-tour="nav-admin.sarana.index"]', 'title' => 'Menu Sarana', 'description' => 'Inventarisasi sarana dan prasarana sekolah.', 'side' => 'right'],
            ['element' => '[data-tour="page-header"]', 'title' => 'Sarana & Prasarana', 'description' => 'Kelola daftar inventaris fasilitas sekolah.', 'side' => 'bottom'],
        ],
        tour_table_actions(
            'admin-sarana',
            'Tambah Sarana',
            'Catat sarana baru beserta foto, lokasi, dan kondisi.',
            'Edit',
            'Perbarui informasi sarana dan prasarana.',
            'Hapus',
            'Hapus data sarana dari inventaris.',
        ),
    ),

    'admin.menu-makanan.index' => [
        ['element' => '[data-tour="nav-admin.menu-makanan.index"]', 'title' => 'Menu Makanan', 'description' => 'Kelola menu makan harian yang dilihat orang tua.', 'side' => 'right'],
        ['element' => '[data-tour="page-header"]', 'title' => 'Menu Makanan', 'description' => 'Kalender menu makan harian siswa — navigasi per bulan seperti Agenda Belajar.', 'side' => 'bottom'],
        ['element' => '[data-tour="admin-menu-filter"]', 'title' => 'Cari & Filter', 'description' => 'Cari hidangan atau info gizi, saring yang punya foto, lalu reset atau input menu baru.', 'side' => 'bottom'],
        ['element' => '[data-tour="admin-menu-calendar"]', 'title' => 'Kalender Menu', 'description' => 'Klik entri di kalender untuk detail, edit, atau hapus menu harian.', 'side' => 'top'],
    ],

    'admin.akun.index' => [
        ['element' => '[data-tour="nav-admin.akun.index"]', 'title' => 'Menu Kode Rekening & Akun', 'description' => 'Master akun untuk RKAS, cashflow, dan jurnal.', 'side' => 'right'],
        ['element' => '[data-tour="page-header"]', 'title' => 'Kode Rekening & Akun', 'description' => 'Kelola master akun dan kode rekening sekolah.', 'side' => 'bottom'],
        ['element' => '[data-tour="admin-akun-filter-tabs"]', 'title' => 'Filter', 'description' => 'Saring akun berdasarkan kategori.', 'side' => 'bottom'],
        ['element' => '[data-tour="admin-akun-table"]', 'title' => 'Daftar Akun', 'description' => 'Tabel master akun yang digunakan oleh modul akuntansi.', 'side' => 'top'],
    ],

    'admin.cashflow.index' => array_merge(
        [
            ['element' => '[data-tour="nav-admin.cashflow.index"]', 'title' => 'Menu Cashflow', 'description' => 'Catat pemasukan dan pengeluaran keuangan sekolah.', 'side' => 'right'],
            ['element' => '[data-tour="page-header"]', 'title' => 'Cashflow', 'description' => 'Riwayat transaksi kas. Ringkasan agregat ada di Laporan Keuangan.', 'side' => 'bottom'],
            ['element' => '[data-tour="admin-cashflow-table"]', 'title' => 'Riwayat transaksi', 'description' => 'Filter periode dan jenis, lalu catat atau ubah transaksi di sini.', 'side' => 'top'],
        ],
        tour_table_actions(
            'admin-cashflow',
            'Catat Transaksi',
            'Input pemasukan atau pengeluaran kas sekolah.',
            'Edit',
            'Koreksi nominal, kategori, atau keterangan transaksi.',
            'Hapus',
            'Hapus catatan transaksi yang salah input.',
        ),
    ),

    'admin.jurnal.index' => [
        ['element' => '[data-tour="nav-admin.jurnal.index"]', 'title' => 'Menu Jurnal Umum', 'description' => 'Lihat jurnal manual dan jurnal otomatis dari transaksi.', 'side' => 'right'],
        ['element' => '[data-tour="page-header"]', 'title' => 'Jurnal Umum', 'description' => 'Daftar jurnal sekolah Anda.', 'side' => 'bottom'],
        ['element' => '[data-tour="admin-jurnal-table"]', 'title' => 'Daftar Jurnal', 'description' => 'Semua jurnal dan statusnya ditampilkan di tabel ini.', 'side' => 'top'],
    ],

    'admin.sumber-dana.index' => [
        ['element' => '[data-tour="nav-admin.sumber-dana.index"]', 'title' => 'Menu Sumber Dana', 'description' => 'Kelola daftar sumber dana untuk RKAS.', 'side' => 'right'],
        ['element' => '[data-tour="page-header"]', 'title' => 'Sumber Dana', 'description' => 'Tambah dan atur sumber dana sekolah (BOS, Komite, SPP, dll).', 'side' => 'bottom'],
        ['element' => '[data-tour="admin-sumber-dana-table"]', 'title' => 'Daftar Sumber Dana', 'description' => 'Tabel sumber dana aktif/nonaktif.', 'side' => 'top'],
    ],

    'admin.rkas.index' => [
        ['element' => '[data-tour="nav-admin.rkas.index"]', 'title' => 'Menu RKAS', 'description' => 'Kelola RKAS per tahun ajaran.', 'side' => 'right'],
        ['element' => '[data-tour="page-header"]', 'title' => 'RKAS', 'description' => 'Buat, edit, dan sinkronisasi RKAS.', 'side' => 'bottom'],
        ['element' => '[data-tour="admin-rkas-filter"]', 'title' => 'Tahun Ajaran', 'description' => 'Pilih tahun ajaran untuk melihat RKAS.', 'side' => 'bottom'],
        ['element' => '[data-tour="admin-rkas-table"]', 'title' => 'Daftar RKAS', 'description' => 'Daftar periode RKAS dan statusnya.', 'side' => 'top'],
    ],

    'admin.rkas.laporan' => [
        ['element' => '[data-tour="nav-admin.rkas.laporan"]', 'title' => 'Menu Laporan RKAS', 'description' => 'Laporan komparasi RKAS dan cashflow.', 'side' => 'right'],
        ['element' => '[data-tour="page-header"]', 'title' => 'Laporan RKAS', 'description' => 'Pilih periode lalu lihat komparasi RKAS vs realisasi.', 'side' => 'bottom'],
        ['element' => '[data-tour="admin-rkas-laporan-filter"]', 'title' => 'Filter Periode', 'description' => 'Pilih tahun ajaran dan semester.', 'side' => 'bottom'],
        ['element' => '[data-tour="admin-rkas-laporan-table"]', 'title' => 'Tabel Laporan', 'description' => 'Ringkasan komparasi RKAS dan realisasi.', 'side' => 'top'],
    ],

    'admin.biaya-bulanan.index' => [
        ['element' => '[data-tour="nav-admin.biaya-bulanan.index"]', 'title' => 'Menu Biaya Bulanan', 'description' => 'Atur jenis biaya dan biaya bulanan per siswa sebelum generate tagihan.', 'side' => 'right'],
        ['element' => '[data-tour="page-header"]', 'title' => 'Biaya Bulanan Siswa', 'description' => 'Tambahkan siswa ke jenis biaya lalu tentukan tarif bulanan masing-masing.', 'side' => 'bottom'],
        ['element' => '[data-tour="admin-biaya-jenis"]', 'title' => 'Jenis Biaya', 'description' => 'Pilih atau buat jenis biaya seperti SPP Bulanan. Setiap jenis punya tarif default.', 'side' => 'right'],
        ['element' => '[data-tour="admin-biaya-add-jenis-btn"]', 'title' => 'Tambah Jenis Biaya', 'description' => 'Buat jenis biaya baru jika belum ada di daftar.', 'side' => 'left'],
        ['element' => '[data-tour="admin-biaya-siswa"]', 'title' => 'Daftar Siswa', 'description' => 'Siswa yang dikenakan biaya ini. Edit kolom biaya bulanan per baris.', 'side' => 'top'],
        ['element' => '[data-tour="admin-biaya-add-siswa-btn"]', 'title' => 'Tambah Siswa', 'description' => 'Pilih siswa dari daftar yang belum ditambahkan ke jenis biaya ini.', 'side' => 'left'],
        ['element' => '[data-tour="admin-biaya-save-btn"]', 'title' => 'Simpan Biaya Bulanan', 'description' => 'Simpan perubahan tarif bulanan setelah selesai mengedit.', 'side' => 'top'],
    ],

    'admin.diskon.index' => array_merge(
        [
            ['element' => '[data-tour="nav-admin.diskon.index"]', 'title' => 'Menu Diskon', 'description' => 'Kelola diskon yang bisa diterapkan saat generate tagihan.', 'side' => 'right'],
            ['element' => '[data-tour="page-header"]', 'title' => 'Diskon', 'description' => 'Daftar diskon persentase atau nominal untuk tagihan siswa.', 'side' => 'bottom'],
        ],
        tour_table_actions(
            'admin-diskon',
            'Tambah Diskon',
            'Buat diskon baru dengan tipe persentase atau nominal tetap.',
            'Edit',
            'Ubah nama, nilai, atau status aktif diskon.',
            'Hapus',
            'Hapus diskon yang tidak lagi digunakan.',
        ),
    ),

    'admin.pembayaran-bulanan.index' => [
        ['element' => '[data-tour="nav-admin.pembayaran-bulanan.index"]', 'title' => 'Menu Rekap Pembayaran', 'description' => 'Generate dan kelola tagihan bulanan berdasarkan kehadiran siswa.', 'side' => 'right'],
        ['element' => '[data-tour="page-header"]', 'title' => 'Rekap Pembayaran Bulanan', 'description' => 'Total tagihan = biaya bulanan + biaya lain − diskon.', 'side' => 'bottom'],
        ['element' => '[data-tour="admin-pembayaran-stats"]', 'title' => 'Ringkasan Tagihan', 'description' => 'Statistik total, menunggu, lunas, dan nominal yang sudah lunas.', 'side' => 'bottom'],
        ['element' => '[data-tour="admin-pembayaran-filter"]', 'title' => 'Filter', 'description' => 'Saring tagihan berdasarkan bulan, tahun, kelas, atau status.', 'side' => 'bottom'],
        ['element' => '[data-tour="admin-pembayaran-generate-btn"]', 'title' => 'Generate Tagihan', 'description' => 'Buat tagihan bulanan untuk siswa yang sudah ada di Biaya Bulanan.', 'side' => 'left'],
        ['element' => '[data-tour="admin-pembayaran-table"]', 'title' => 'Daftar Tagihan', 'description' => 'Semua tagihan periode terpilih beserta status pembayarannya.', 'side' => 'top'],
        ['element' => '[data-tour="admin-pembayaran-action-edit"]', 'title' => 'Edit', 'description' => 'Buka detail untuk edit diskon atau setujui/tolak pembayaran.', 'side' => 'left'],
        ['element' => '[data-tour="admin-pembayaran-action-delete"]', 'title' => 'Hapus', 'description' => 'Hapus tagihan berstatus Menunggu yang salah generate.', 'side' => 'left'],
    ],

    'admin.kritik-saran.index' => [
        ['element' => '[data-tour="nav-admin.kritik-saran.index"]', 'title' => 'Menu Kritik & Saran', 'description' => 'Baca dan tanggapi masukan dari orang tua.', 'side' => 'right'],
        ['element' => '[data-tour="page-header"]', 'title' => 'Kritik & Saran', 'description' => 'Kotak masukan dari wali murid sekolah Anda.', 'side' => 'bottom'],
        ['element' => '[data-tour="admin-kritik-table"]', 'title' => 'Daftar Masukan', 'description' => 'Lihat pengirim, kelas anak, dan ringkasan pesan.', 'side' => 'top'],
        ['element' => '[data-tour="admin-kritik-action-detail"]', 'title' => 'Detail', 'description' => 'Buka halaman detail untuk membaca dan menanggapi masukan.', 'side' => 'left'],
    ],

    'admin.orangtua-chat.index' => [
        ['element' => '[data-tour="nav-admin.orangtua-chat.index"]', 'title' => 'Menu Chat Orang Tua', 'description' => 'Pantau percakapan AI antara orang tua dan asisten sekolah.', 'side' => 'right'],
        ['element' => '[data-tour="admin-chat-filter"]', 'title' => 'Filter', 'description' => 'Cari ortu/anak, filter kelas, atau status riwayat — lalu Filter atau Reset.', 'side' => 'bottom'],
        ['element' => '[data-tour="page-header"]', 'title' => 'Chat Orang Tua', 'description' => 'Riwayat percakapan chatbot yang digunakan orang tua.', 'side' => 'bottom'],
        ['element' => '[data-tour="admin-chat-table"]', 'title' => 'Riwayat Chat', 'description' => 'Lihat orang tua, anak terkait, jumlah pesan, dan cuplikan terakhir.', 'side' => 'top'],
        ['element' => '[data-tour="admin-chat-action-detail"]', 'title' => 'Detail', 'description' => 'Buka thread percakapan lengkap dengan orang tua.', 'side' => 'left'],
    ],

    'admin.ai-persona.index' => [
        ['element' => '[data-tour="nav-admin.ai-persona.index"]', 'title' => 'Menu Pengaturan AI', 'description' => 'Atur persona, fallback, dan pantau saldo token sekolah.', 'side' => 'right'],
        ['element' => '[data-tour="page-header"]', 'title' => 'Pengaturan AI', 'description' => 'Konfigurasi persona AI, pesan fallback, dan saldo token untuk fitur AI sekolah.', 'side' => 'bottom'],
        ['element' => '[data-tour="admin-ai-token-balance"]', 'title' => 'Saldo Token', 'description' => 'Lihat sisa token AI sekolah. Setiap generate memakai 1 token.', 'side' => 'bottom'],
        ['element' => '[data-tour="admin-ai-persona-tabs"]', 'title' => 'Tab Persona', 'description' => 'Pilih scope persona: chat orang tua, evaluasi pencapaian, atau monev.', 'side' => 'bottom'],
        ['element' => '[data-tour="admin-ai-persona-form"]', 'title' => 'Form Persona', 'description' => 'Tulis instruksi persona atau gunakan tombol Generate AI untuk membuatnya otomatis.', 'side' => 'top'],
    ],

    'admin.role.index' => [
        ['element' => '[data-tour="nav-admin.role.index"]', 'title' => 'Menu Role', 'description' => 'Kelola role dan permission menu.', 'side' => 'right'],
        ['element' => '[data-tour="page-header"]', 'title' => 'Manajemen Role', 'description' => 'Tambah/edit role dan atur izin akses.', 'side' => 'bottom'],
        ['element' => '[data-tour="admin-role-table"]', 'title' => 'Daftar Role', 'description' => 'Tabel role yang tersedia di sekolah.', 'side' => 'top'],
    ],

    'admin.pengguna.index' => [
        ['element' => '[data-tour="nav-admin.pengguna.index"]', 'title' => 'Menu Pengguna', 'description' => 'Kelola akun pengguna internal (admin/pengajar/orang tua jika tersedia).', 'side' => 'right'],
        ['element' => '[data-tour="page-header"]', 'title' => 'Manajemen Pengguna', 'description' => 'Tambah/edit pengguna dan perannya.', 'side' => 'bottom'],
        ['element' => '[data-tour="admin-pengguna-table"]', 'title' => 'Daftar Pengguna', 'description' => 'Tabel pengguna yang terdaftar.', 'side' => 'top'],
    ],

    'admin.akuntansi-setting.index' => [
        ['element' => '[data-tour="nav-admin.akuntansi-setting.index"]', 'title' => 'Menu Pengaturan Akuntansi', 'description' => 'Atur default akun untuk transaksi dan jurnal otomatis.', 'side' => 'right'],
        ['element' => '[data-tour="page-header"]', 'title' => 'Pengaturan Akuntansi', 'description' => 'Konfigurasi default akun dan mapping kebutuhan akuntansi.', 'side' => 'bottom'],
        ['element' => '[data-tour="admin-akuntansi-setting-form"]', 'title' => 'Form Pengaturan', 'description' => 'Ubah nilai lalu simpan perubahan.', 'side' => 'top'],
    ],

    'admin.activity-log.index' => [
        ['element' => '[data-tour="nav-admin.activity-log.index"]', 'title' => 'Menu Log Aktivitas', 'description' => 'Audit log perubahan data di sekolah.', 'side' => 'right'],
        ['element' => '[data-tour="page-header"]', 'title' => 'Log Aktivitas', 'description' => 'Riwayat perubahan data di sekolah Anda.', 'side' => 'bottom'],
        ['element' => '[data-tour="activity-log-filter"]', 'title' => 'Filter', 'description' => 'Saring berdasarkan tanggal, event, atau kata kunci.', 'side' => 'bottom'],
    ],

    // ─── ADMIN KELAS ───────────────────────────────────────────

    'adminkelas.anak.index' => array_merge(
        [
            ['element' => '[data-tour="nav-adminkelas.anak.index"]', 'title' => 'Menu Siswa Kelasku', 'description' => 'Kelola siswa di kelas yang Anda ampu.', 'side' => 'right'],
            ['element' => '[data-tour="page-header"]', 'title' => 'Siswa Kelasku', 'description' => 'Daftar siswa di kelas Anda sebagai wali kelas.', 'side' => 'bottom'],
            ['element' => '[data-tour="ak-anak-filter"]', 'title' => 'Filter Kelas', 'description' => 'Pilih kelas untuk melihat daftar siswanya.', 'side' => 'bottom'],
        ],
        tour_table_actions(
            'ak-anak',
            'Registrasi Siswa',
            'Klik untuk mendaftarkan siswa baru ke kelas Anda.',
            'Edit',
            'Perbarui data siswa di kelas Anda.',
            'Hapus',
            'Hapus data siswa dari kelas.',
            'Detail',
            'Buka halaman profil lengkap siswa.',
        ),
    ),

    'adminkelas.presensi.index' => [
        ['element' => '[data-tour="nav-adminkelas.presensi.index"]', 'title' => 'Menu Presensi Kelasku', 'description' => 'Absensi harian siswa di kelas Anda.', 'side' => 'right'],
        ['element' => '[data-tour="page-header"]', 'title' => 'Presensi Kelasku', 'description' => 'Catat kehadiran siswa di kelas yang Anda kelola.', 'side' => 'bottom'],
        ['element' => '[data-tour="ak-presensi-filter"]', 'title' => 'Filter Tanggal', 'description' => 'Pilih kelas dan tanggal untuk menampilkan daftar absensi.', 'side' => 'bottom'],
        ['element' => '[data-tour="ak-presensi-checklist"]', 'title' => 'Checklist Kehadiran', 'description' => 'Centang siswa yang hadir lalu simpan presensi hari ini.', 'side' => 'top'],
    ],

    'adminkelas.kesehatan.index' => array_merge(
        [
            ['element' => '[data-tour="nav-adminkelas.kesehatan.index"]', 'title' => 'Menu Kesehatan Kelasku', 'description' => 'Pantau kesehatan siswa di kelas Anda.', 'side' => 'right'],
            ['element' => '[data-tour="page-header"]', 'title' => 'Kesehatan Kelasku', 'description' => 'Data kesehatan siswa di kelas yang Anda ampu.', 'side' => 'bottom'],
            ['element' => '[data-tour="ak-kesehatan-filter"]', 'title' => 'Pencarian Siswa', 'description' => 'Cari siswa tertentu di kelas Anda.', 'side' => 'bottom'],
            ['element' => '[data-tour="ak-kesehatan-action-riwayat"]', 'title' => 'Riwayat', 'description' => 'Lihat riwayat pemeriksaan kesehatan siswa di kelas Anda.', 'side' => 'left'],
            ['element' => '[data-tour="ak-kesehatan-action-input"]', 'title' => 'Input Data', 'description' => 'Catat pemeriksaan kesehatan siswa di kelas Anda.', 'side' => 'left'],
        ],
    ),

    'adminkelas.monev.index' => [
        ['element' => '[data-tour="nav-adminkelas.monev.index"]', 'title' => 'Menu Monev', 'description' => 'Generate laporan monev siswa di kelas Anda.', 'side' => 'right'],
        ['element' => '[data-tour="page-header"]', 'title' => 'Monev Kelas', 'description' => 'Monitoring dan evaluasi perkembangan siswa kelas Anda.', 'side' => 'bottom'],
        ['element' => '[data-tour="ak-monev-filters"]', 'title' => 'Filter Periode', 'description' => 'Pilih bulan dan tahun laporan monev yang ingin dibuat.', 'side' => 'bottom'],
        ['element' => '[data-tour="ak-monev-table"]', 'title' => 'Daftar Siswa', 'description' => 'Generate laporan monev per siswa atau sekaligus untuk seluruh kelas.', 'side' => 'top'],
    ],

    // ─── PENGAJAR ──────────────────────────────────────────────

    'pengajar.master-kegiatan-rutin.index' => array_merge(
        [
            ['element' => '[data-tour="nav-pengajar.master-kegiatan-rutin.index"]', 'title' => 'Menu Kegiatan Rutin', 'description' => 'Kelola dan input kegiatan rutin harian siswa.', 'side' => 'right'],
            ['element' => '[data-tour="page-header"]', 'title' => 'Kegiatan Rutin', 'description' => 'Master kegiatan rutin yang Anda catat setiap hari.', 'side' => 'bottom'],
        ],
        tour_table_actions(
            'pg-master-rutin',
            'Tambah Kegiatan',
            'Buat jenis kegiatan rutin baru jika belum ada di daftar.',
            'Edit',
            'Ubah template kegiatan rutin.',
            'Hapus',
            'Hapus kegiatan rutin yang tidak digunakan.',
            'Detail',
            'Input harian atau lihat capaian siswa.',
        ),
    ),

    'pengajar.kegiatan.index' => [
        ['element' => '[data-tour="nav-pengajar.kegiatan.index"]', 'title' => 'Menu Agenda Belajar', 'description' => 'Buat dan kelola jurnal kegiatan belajar harian.', 'side' => 'right'],
        ['element' => '[data-tour="page-header"]', 'title' => 'Agenda Belajar', 'description' => 'Kalender jurnal kegiatan belajar yang Anda ampu.', 'side' => 'bottom'],
        ['element' => '[data-tour="pg-kegiatan-add-btn"]', 'title' => 'Buat Jurnal', 'description' => 'Tambahkan jurnal kegiatan baru dengan dokumentasi foto.', 'side' => 'left'],
        ['element' => '[data-tour="pg-kegiatan-calendar"]', 'title' => 'Kalender Jurnal', 'description' => 'Klik tanggal di kalender untuk melihat, edit, atau hapus jurnal.', 'side' => 'top'],
    ],

    'pengajar.matrikulasi.index' => [
        ['element' => '[data-tour="nav-pengajar.matrikulasi.index"]', 'title' => 'Menu Matrikulasi', 'description' => 'Lihat indikator pembelajaran yang menjadi acuan evaluasi.', 'side' => 'right'],
        ['element' => '[data-tour="page-header"]', 'title' => 'Matrikulasi', 'description' => 'Daftar indikator capaian pembelajaran (baca saja).', 'side' => 'bottom'],
        ['element' => '[data-tour="pg-matrikulasi-table"]', 'title' => 'Tabel Indikator', 'description' => 'Lihat kode, aspek, dan klik Detail untuk membaca tujuan & strategi.', 'side' => 'top'],
    ],

    'pengajar.pencapaian.index' => array_merge(
        [
            ['element' => '[data-tour="nav-pengajar.pencapaian.index"]', 'title' => 'Menu Pencapaian Siswa', 'description' => 'Evaluasi pencapaian belajar siswa di kelas Anda.', 'side' => 'right'],
            ['element' => '[data-tour="page-header"]', 'title' => 'Pencapaian Siswa', 'description' => 'Catat dan tinjau evaluasi pencapaian per kegiatan.', 'side' => 'bottom'],
            ['element' => '[data-tour="pg-pencapaian-filter"]', 'title' => 'Filter Aspek', 'description' => 'Saring evaluasi berdasarkan aspek pembelajaran.', 'side' => 'bottom'],
        ],
        tour_table_actions(
            'pg-pencapaian',
            'Buat Evaluasi',
            'Tambahkan penilaian pencapaian siswa.',
            'Edit',
            'Ubah nilai atau umpan balik evaluasi.',
            'Hapus',
            'Hapus evaluasi yang keliru.',
        ),
    ),

    // ─── ORANG TUA ─────────────────────────────────────────────

    'orangtua.pencapaian.index' => [
        ['element' => '[data-tour="ortu-pencapaian-filter"]', 'title' => 'Pencapaian Anak', 'description' => 'Laporan pencapaian belajar anak dari kegiatan di sekolah. Saring berdasarkan aspek, tanggal, atau pilih anak.', 'side' => 'bottom'],
        ['element' => '[data-tour="ortu-pencapaian-reports"]', 'title' => 'Laporan Pencapaian', 'description' => 'Setiap kartu berisi kegiatan, skala capaian, dan catatan guru.', 'side' => 'top'],
    ],

    'orangtua.chat.index' => [
        ['element' => '[data-tour="ortu-chat-suggestions"]', 'title' => 'Chat Asisten', 'description' => 'Percakapan dengan chatbot sekolah. Klik pertanyaan contoh untuk memulai.', 'side' => 'top'],
        ['element' => '[data-tour="ortu-chat-input"]', 'title' => 'Ketik Pesan', 'description' => 'Tulis pertanyaan Anda di sini lalu kirim. Riwayat tersimpan otomatis.', 'side' => 'top'],
    ],

    'orangtua.monev.index' => [
        ['element' => '[data-tour="ortu-monev-filters"]', 'title' => 'Laporan Monev', 'description' => 'Ringkasan perkembangan anak per bulan. Pilih anak serta bulan dan tahun laporan.', 'side' => 'bottom'],
        ['element' => '[data-tour="ortu-monev-report"]', 'title' => 'Isi Laporan', 'description' => 'Baca analisis perkembangan anak yang dihasilkan sekolah.', 'side' => 'top'],
    ],

    'orangtua.kegiatan.index' => [
        ['element' => '[data-tour="ortu-kegiatan-calendar"]', 'title' => 'Agenda Belajar', 'description' => 'Kalender jurnal kegiatan belajar anak Anda. Klik tanggal yang ada jurnalnya.', 'side' => 'top'],
    ],

    'orangtua.kegiatan-rutin.index' => [
        ['element' => '[data-tour="ortu-rutin-filters"]', 'title' => 'Kegiatan Rutin', 'description' => 'Riwayat kegiatan rutin anak. Pilih anak, jenis kegiatan, dan rentang tanggal.', 'side' => 'bottom'],
        ['element' => '[data-tour="ortu-rutin-results"]', 'title' => 'Hasil Kegiatan', 'description' => 'Klik item untuk melihat detail dan foto dokumentasi.', 'side' => 'top'],
    ],

    'orangtua.pembayaran.index' => [
        ['element' => '[data-tour="ortu-pembayaran-summary"]', 'title' => 'Pembayaran Bulanan', 'description' => 'Ringkasan tagihan anak Anda berdasarkan kehadiran di sekolah.', 'side' => 'bottom'],
        ['element' => '[data-tour="ortu-pembayaran-table"]', 'title' => 'Daftar Tagihan', 'description' => 'Semua tagihan per periode beserta status persetujuan sekolah.', 'side' => 'top'],
        ['element' => '[data-tour="ortu-pembayaran-action-detail"]', 'title' => 'Detail', 'description' => 'Buka rincian tagihan dan unggah bukti transfer.', 'side' => 'left'],
    ],

    'orangtua.presensi.index' => [
        ['element' => '[data-tour="ortu-presensi-period-filter"]', 'title' => 'Kehadiran Anak', 'description' => 'Rekap absensi harian dan mingguan. Pilih bulan atau minggu untuk melihat rekap.', 'side' => 'bottom'],
        ['element' => '[data-tour="ortu-presensi-summary-cards"]', 'title' => 'Ringkasan Kehadiran', 'description' => 'Statistik hadir, izin, dan sakit per anak ditampilkan di sini.', 'side' => 'top'],
    ],

    'orangtua.menu-makanan.index' => [
        ['element' => '[data-tour="ortu-menu-date-filter"]', 'title' => 'Menu Makanan', 'description' => 'Daftar menu makan siswa. Pilih rentang tanggal lalu klik Tampilkan.', 'side' => 'bottom'],
        ['element' => '[data-tour="ortu-menu-list"]', 'title' => 'Daftar Menu', 'description' => 'Setiap hari menampilkan menu, foto, dan tombol suka/tidak suka.', 'side' => 'top'],
    ],

    'orangtua.kritik-saran.index' => array_merge(
        [
            ['element' => '[data-tour="ortu-kritik-feed"]', 'title' => 'Saran & Kritik', 'description' => 'Sampaikan masukan Anda dan lihat tanggapan dari sekolah.', 'side' => 'top'],
        ],
        tour_table_actions(
            'ortu-kritik',
            'Kirim Masukan',
            'Klik untuk menulis kritik, saran, atau pertanyaan baru.',
            'Edit',
            'Ubah pesan yang masih berstatus terkirim.',
            'Hapus',
            'Hapus masukan yang sudah tidak ingin dikirim.',
        ),
    ),

    'orangtua.kesehatan.index' => [
        ['element' => '[data-tour="ortu-kesehatan-metrics"]', 'title' => 'Kesehatan Anak', 'description' => 'Riwayat pemeriksaan kesehatan. Berat badan, tinggi badan, lingkar kepala, dan info alergi terbaru.', 'side' => 'bottom'],
        ['element' => '[data-tour="ortu-kesehatan-timeline"]', 'title' => 'Riwayat Pemeriksaan', 'description' => 'Timeline lengkap pemeriksaan kesehatan dari waktu ke waktu.', 'side' => 'top'],
    ],

];
