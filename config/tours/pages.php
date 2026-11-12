<?php

require __DIR__.'/helpers.php';

return [

    'dashboard' => [
        ['element' => '[data-tour="dashboard-welcome"]', 'title' => 'Selamat Datang', 'description' => 'Dashboard ringkasan aktivitas sesuai peran akun Anda.', 'side' => 'bottom'],
        ['element' => '[data-tour="dashboard-stats"]', 'title' => 'Statistik Utama', 'description' => 'Angka penting yang perlu dipantau setiap hari.', 'side' => 'bottom'],
        ['element' => '[data-tour="dashboard-recent"]', 'title' => 'Aktivitas Terbaru', 'description' => 'Informasi atau tindakan terkini yang perlu perhatian.', 'side' => 'top'],
        ['element' => '[data-tour="dashboard-quick-links"]', 'title' => 'Akses Cepat', 'description' => 'Shortcut ke menu yang sering digunakan.', 'side' => 'top'],
    ],

    'admin.dashboard' => [
        ['element' => '[data-tour="dashboard-welcome"]', 'title' => 'Selamat Datang', 'description' => 'Dashboard ringkasan operasional sekolah.', 'side' => 'bottom'],
        ['element' => '[data-tour="dashboard-stats"]', 'title' => 'Statistik Utama', 'description' => 'Angka penting yang perlu dipantau setiap hari.', 'side' => 'bottom'],
        ['element' => '[data-tour="dashboard-attention"]', 'title' => 'Perlu Perhatian', 'description' => 'Prioritas tindakan hari ini.', 'side' => 'top'],
        ['element' => '[data-tour="dashboard-quick-links"]', 'title' => 'Akses Cepat', 'description' => 'Pintasan ke modul harian sekolah.', 'side' => 'top'],
        ['element' => '[data-tour="dashboard-recent"]', 'title' => 'Aktivitas Terbaru', 'description' => 'Kegiatan dan informasi terkini.', 'side' => 'top'],
    ],

    'profile.edit' => [
        ['element' => '[data-tour="nav-profile.edit"]', 'title' => 'Menu Profil Admin', 'description' => 'Kelola profil akun login Anda.', 'side' => 'right'],
        ['element' => '[data-tour="page-header"]', 'title' => 'Profil Akun', 'description' => 'Kelola informasi login dan profil pribadi Anda.', 'side' => 'bottom'],
        ['element' => '[data-tour="profile-account"]', 'title' => 'Informasi Akun', 'description' => 'Ubah nama tampilan dan alamat email.', 'side' => 'bottom'],
        ['element' => '[data-tour="profile-role"]', 'title' => 'Profil Peran', 'description' => 'Data tambahan sesuai peran (sekolah, pengajar, orang tua).', 'side' => 'top'],
        ['element' => '[data-tour="profile-security"]', 'title' => 'Keamanan', 'description' => 'Ganti kata sandi untuk menjaga keamanan akun.', 'side' => 'top'],
    ],

    'profile.sekolah.edit' => [
        ['element' => '[data-tour="nav-profile.sekolah.edit"]', 'title' => 'Menu Profil Sekolah', 'description' => 'Kelola identitas sekolah (NPSN, alamat, koordinat, dan logo).', 'side' => 'right'],
        ['element' => '[data-tour="page-header"]', 'title' => 'Profil Sekolah', 'description' => 'Lengkapi informasi sekolah yang ditampilkan di sistem.', 'side' => 'bottom'],
        ['element' => '[data-tour="profile-role"]', 'title' => 'Form Profil Sekolah', 'description' => 'Isi data sekolah lalu simpan perubahan.', 'side' => 'top'],
    ],

    'admin.presensi.rekap' => [
        ['element' => '[data-tour="page-header"]', 'title' => 'Rekap Presensi', 'description' => 'Ringkasan kehadiran siswa per periode.', 'side' => 'bottom'],
        ['element' => '[data-tour="presensi-rekap-filter"]', 'title' => 'Filter Periode', 'description' => 'Pilih rentang tanggal atau kelas untuk rekap.', 'side' => 'bottom'],
        ['element' => '[data-tour="presensi-rekap-table"]', 'title' => 'Tabel Rekap', 'description' => 'Lihat persentase dan detail kehadiran.', 'side' => 'top'],
    ],

    'orangtua.anak.create' => [
        ['element' => '[data-tour="page-header"]', 'title' => 'Tambah Anak', 'description' => 'Daftarkan data anak baru ke akun Anda.', 'side' => 'bottom'],
        ['element' => '[data-tour="anak-create-form"]', 'title' => 'Form Pendaftaran', 'description' => 'Isi identitas anak dan unggah foto jika diperlukan.', 'side' => 'top'],
        ['element' => '[data-tour="anak-create-actions"]', 'title' => 'Simpan Data', 'description' => 'Kirim formulir setelah semua data terisi benar.', 'side' => 'left'],
    ],

    'pengajar.master-kegiatan-rutin.create' => [
        ['element' => '[data-tour="page-header"]', 'title' => 'Buat Kegiatan Rutin', 'description' => 'Definisikan template kegiatan rutin baru.', 'side' => 'bottom'],
        ['element' => '[data-tour="kegiatan-rutin-form"]', 'title' => 'Form Kegiatan', 'description' => 'Isi nama, jadwal, dan deskripsi kegiatan.', 'side' => 'top'],
    ],

    'pengajar.master-kegiatan-rutin.edit' => [
        ['element' => '[data-tour="page-header"]', 'title' => 'Edit Kegiatan Rutin', 'description' => 'Perbarui template kegiatan rutin.', 'side' => 'bottom'],
        ['element' => '[data-tour="kegiatan-rutin-form"]', 'title' => 'Form Edit', 'description' => 'Ubah detail kegiatan lalu simpan perubahan.', 'side' => 'top'],
    ],

    'admin.kegiatan-rutin.index' => [
        ['element' => '[data-tour="nav-admin.master-kegiatan-rutin.index"]', 'title' => 'Menu Kegiatan Rutin', 'description' => 'Kelola pencatatan kegiatan rutin seluruh kelas.', 'side' => 'right'],
        ['element' => '[data-tour="page-header"]', 'title' => 'Kegiatan Rutin', 'description' => 'Pantau dan input kegiatan harian siswa.', 'side' => 'bottom'],
        ['element' => '[data-tour="kegiatan-rutin-list"]', 'title' => 'Grid Pencapaian', 'description' => 'Isi status di grid per siswa dan kegiatan, lalu klik Simpan Semua.', 'side' => 'top'],
    ],

    'pengajar.kegiatan-rutin.index' => [
        ['element' => '[data-tour="nav-pengajar.master-kegiatan-rutin.index"]', 'title' => 'Menu Input Rutin', 'description' => 'Catat pencapaian kegiatan rutin harian siswa.', 'side' => 'right'],
        ['element' => '[data-tour="page-header"]', 'title' => 'Update Pencapaian Rutin', 'description' => 'Input perkembangan harian siswa di kelas Anda.', 'side' => 'bottom'],
        ['element' => '[data-tour="kegiatan-rutin-list"]', 'title' => 'Grid Pencapaian', 'description' => 'Isi status di grid, gunakan Salin Kemarin atau Semua Lancar, lalu Simpan Semua.', 'side' => 'top'],
    ],

];
