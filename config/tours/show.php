<?php

return [

    'admin.anak.show' => [
        ['element' => '[data-tour="page-header"]', 'title' => 'Profil Siswa', 'description' => 'Ringkasan identitas dan status siswa di sekolah.', 'side' => 'bottom'],
        ['element' => '[data-tour="anak-summary"]', 'title' => 'Informasi Utama', 'description' => 'Data pribadi, kelas, dan orang tua siswa.', 'side' => 'bottom'],
        ['element' => '[data-tour="anak-actions"]', 'title' => 'Aksi Cepat', 'description' => 'Tombol kembali, edit, atau navigasi ke halaman terkait.', 'side' => 'left'],
        ['element' => '[data-tour="anak-detail"]', 'title' => 'Detail Lengkap', 'description' => 'Informasi tambahan dan riwayat terkait siswa.', 'side' => 'top'],
    ],

    'admin.kelas.show' => [
        ['element' => '[data-tour="page-header"]', 'title' => 'Detail Kelas', 'description' => 'Nama kelas, pengajar, dan ringkasan siswa.', 'side' => 'bottom'],
        ['element' => '[data-tour="kelas-summary"]', 'title' => 'Ringkasan Kelas', 'description' => 'Statistik dan informasi utama kelas.', 'side' => 'bottom'],
        ['element' => '[data-tour="kelas-siswa-list"]', 'title' => 'Daftar Siswa', 'description' => 'Siswa yang terdaftar di kelas ini.', 'side' => 'top'],
        ['element' => '[data-tour="kelas-actions"]', 'title' => 'Aksi Kelas', 'description' => 'Kelola siswa atau kembali ke daftar kelas.', 'side' => 'left'],
    ],

    'admin.monev.show' => [
        ['element' => '[data-tour="page-header"]', 'title' => 'Laporan Monev', 'description' => 'Monitoring dan evaluasi perkembangan siswa.', 'side' => 'bottom'],
        ['element' => '[data-tour="monev-summary"]', 'title' => 'Ringkasan Evaluasi', 'description' => 'Skor dan status penilaian terkini.', 'side' => 'bottom'],
        ['element' => '[data-tour="monev-detail"]', 'title' => 'Detail Penilaian', 'description' => 'Rincian aspek yang dinilai beserta catatan.', 'side' => 'top'],
        ['element' => '[data-tour="monev-actions"]', 'title' => 'Unduh & Aksi', 'description' => 'Ekspor PDF atau generate ulang laporan.', 'side' => 'left'],
    ],

    'admin.kritik-saran.show' => [
        ['element' => '[data-tour="page-header"]', 'title' => 'Detail Masukan', 'description' => 'Pesan lengkap dari orang tua atau wali murid.', 'side' => 'bottom'],
        ['element' => '[data-tour="kritik-message"]', 'title' => 'Isi Pesan', 'description' => 'Baca masukan dan lampiran jika ada.', 'side' => 'bottom'],
        ['element' => '[data-tour="kritik-response"]', 'title' => 'Tanggapan Sekolah', 'description' => 'Balas atau perbarui status tanggapan di sini.', 'side' => 'top'],
    ],

    'admin.orangtua-chat.show' => [
        ['element' => '[data-tour="page-header"]', 'title' => 'Percakapan Orang Tua', 'description' => 'Riwayat chat dengan wali murid.', 'side' => 'bottom'],
        ['element' => '[data-tour="chat-thread"]', 'title' => 'Thread Pesan', 'description' => 'Seluruh pesan dalam percakapan ini.', 'side' => 'top'],
        ['element' => '[data-tour="chat-compose"]', 'title' => 'Kirim Balasan', 'description' => 'Tulis dan kirim respons ke orang tua.', 'side' => 'top'],
    ],

    'adminkelas.anak.show' => [
        ['element' => '[data-tour="page-header"]', 'title' => 'Profil Siswa', 'description' => 'Data siswa di kelas yang Anda kelola.', 'side' => 'bottom'],
        ['element' => '[data-tour="anak-summary"]', 'title' => 'Informasi Siswa', 'description' => 'Identitas dan kontak orang tua.', 'side' => 'bottom'],
        ['element' => '[data-tour="anak-detail"]', 'title' => 'Detail Tambahan', 'description' => 'Informasi lengkap terkait siswa.', 'side' => 'top'],
    ],

    'adminkelas.monev.show' => [
        ['element' => '[data-tour="page-header"]', 'title' => 'Laporan Monev', 'description' => 'Evaluasi perkembangan siswa di kelas Anda.', 'side' => 'bottom'],
        ['element' => '[data-tour="monev-summary"]', 'title' => 'Ringkasan', 'description' => 'Status dan skor evaluasi terkini.', 'side' => 'bottom'],
        ['element' => '[data-tour="monev-detail"]', 'title' => 'Detail Penilaian', 'description' => 'Rincian aspek penilaian monev.', 'side' => 'top'],
    ],

    'orangtua.kritik-saran.show' => [
        ['element' => '[data-tour="page-header"]', 'title' => 'Detail Masukan', 'description' => 'Pesan yang Anda kirim ke sekolah.', 'side' => 'bottom'],
        ['element' => '[data-tour="kritik-message"]', 'title' => 'Isi Pesan', 'description' => 'Baca kembali masukan yang terkirim.', 'side' => 'bottom'],
        ['element' => '[data-tour="kritik-response"]', 'title' => 'Tanggapan Sekolah', 'description' => 'Lihat balasan atau status dari pihak sekolah.', 'side' => 'top'],
    ],

    'admin.master-kegiatan-rutin.show' => [
        ['element' => '[data-tour="page-header"]', 'title' => 'Master Kegiatan Rutin', 'description' => 'Template kegiatan rutin yang dijadwalkan.', 'side' => 'bottom'],
        ['element' => '[data-tour="kegiatan-rutin-summary"]', 'title' => 'Informasi Kegiatan', 'description' => 'Nama, jadwal, dan deskripsi kegiatan rutin.', 'side' => 'bottom'],
        ['element' => '[data-tour="kegiatan-rutin-records"]', 'title' => 'Catatan Pelaksanaan', 'description' => 'Riwayat pencatatan per siswa.', 'side' => 'top'],
    ],

    'pengajar.master-kegiatan-rutin.show' => [
        ['element' => '[data-tour="page-header"]', 'title' => 'Master Kegiatan Rutin', 'description' => 'Kelola template kegiatan rutin kelas.', 'side' => 'bottom'],
        ['element' => '[data-tour="kegiatan-rutin-summary"]', 'title' => 'Detail Kegiatan', 'description' => 'Informasi utama kegiatan rutin.', 'side' => 'bottom'],
        ['element' => '[data-tour="kegiatan-rutin-records"]', 'title' => 'Pencatatan Siswa', 'description' => 'Input dan lihat catatan per siswa.', 'side' => 'top'],
    ],

    'admin.pembayaran-bulanan.show' => [
        ['element' => '[data-tour="page-header"]', 'title' => 'Detail Pembayaran', 'description' => 'Rincian tagihan siswa untuk periode ini.', 'side' => 'bottom'],
        ['element' => '[data-tour="pembayaran-rincian"]', 'title' => 'Rincian Perhitungan', 'description' => 'Biaya bulanan + biaya lain − diskon. Edit diskon di sini.', 'side' => 'bottom'],
        ['element' => '[data-tour="pembayaran-bukti"]', 'title' => 'Bukti Transfer', 'description' => 'Foto bukti yang diunggah orang tua (jika ada).', 'side' => 'top'],
        ['element' => '[data-tour="pembayaran-approve-btn"]', 'title' => 'Lunas', 'description' => 'Tandai pembayaran lunas setelah bukti diverifikasi.', 'side' => 'left'],
        ['element' => '[data-tour="pembayaran-reject-btn"]', 'title' => 'Tolak', 'description' => 'Tolak pembayaran dengan catatan alasan.', 'side' => 'left'],
    ],

    'orangtua.pembayaran.show' => [
        ['element' => '[data-tour="page-header"]', 'title' => 'Detail Tagihan', 'description' => 'Rincian tagihan anak untuk periode ini.', 'side' => 'bottom'],
        ['element' => '[data-tour="ortu-pembayaran-rincian"]', 'title' => 'Rincian Biaya', 'description' => 'Breakdown biaya bulanan, biaya lain, hari hadir, diskon, dan total bayar.', 'side' => 'bottom'],
        ['element' => '[data-tour="ortu-pembayaran-bukti"]', 'title' => 'Bukti Transfer', 'description' => 'Bukti yang sudah diunggah sebelumnya.', 'side' => 'top'],
        ['element' => '[data-tour="ortu-pembayaran-bayar-btn"]', 'title' => 'Upload Bukti', 'description' => 'Unggah atau perbarui bukti transfer pembayaran.', 'side' => 'left'],
    ],

];
