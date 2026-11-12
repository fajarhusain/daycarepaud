<?php

use App\Models\CmsContent;
use App\Support\GuestBrand;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $brand = GuestBrand::name();

        $newKeys = [
            'hero_badge_primary' => 'Platform PAUD & Daycare',
            'hero_badge_secondary' => 'Portal ortu, operasional sekolah & AI',
            'value_empathy_title' => 'Empati',
            'value_empathy_desc' => 'Komunikasi transparan orang tua dan guru.',
            'value_explore_title' => 'Eksplorasi',
            'value_explore_desc' => 'Pencatatan aktivitas dan milestone tumbuh kembang.',
            'value_routine_title' => 'Keteraturan',
            'value_routine_desc' => 'Operasional rapi tanpa pekerjaan administratif berulang.',
            'about_highlight_title' => 'Satu platform terpadu untuk semua',
            'about_highlight_desc' => 'Absensi harian, laporan kesehatan dan makan, monev tumbuh kembang, keuangan, dan komunikasi orang tua  dalam satu sistem yang saling terhubung.',
            'page_tentang_hero_lead' => 'Kenali visi produk, manfaat untuk sekolah dan orang tua, serta cara kami mendukung digitalisasi PAUD yang andal.',
            'about_photo_caption' => 'Tim sekolah & orang tua terhubung lewat satu platform',
            'about_partner_title' => 'Kemitraan orang tua & sekolah',
            'about_partner_desc' => 'Dokumentasi harian, laporan terstruktur, dan komunikasi dua arah  tanpa chat personal yang tercecer.',
            'faq_sidebar_title' => 'Ada pertanyaan yang belum terjawab?',
            'faq_whatsapp_cta' => 'Chat WhatsApp dengan tim kami',
            'testimonial_section_badge' => 'Suara pengguna',
            'testimonial_trust_label' => 'Cerita dari sekolah mitra',
            'section_blog_subtitle' => 'Tips praktis seputar operasional PAUD, komunikasi orang tua, dan digitalisasi sekolah usia dini.',
            'blog_1_title' => 'Tips komunikasi efektif antara guru dan orang tua lewat portal sekolah',
            'blog_2_title' => 'Mengurangi beban administrasi guru tanpa mengorbankan dokumentasi anak',
            'blog_3_title' => 'Transparansi monev tumbuh kembang untuk membangun kepercayaan orang tua',
            'blog_3_teaser' => 'Bagaimana laporan berkala dan portofolio digital membantu pendidik fokus pada anak, bukan spreadsheet.',
            'section_demo_title' => 'Pesan jadwal demo platform',
            'section_demo_lead' => 'Pilih waktu untuk sesi demo online bersama tim kami  kami tunjukkan modul sesuai peran (admin, guru, orang tua).',
            'section_demo_hours' => 'Layanan konsultasi: 08.00 – 16.30 WIB',
            'section_demo_success' => 'Permintaan demo Anda telah kami catat. Tim kami akan menghubungi kontak Anda untuk konfirmasi jadwal.',
            'student_age_cta_label' => 'Lihat modul & peran pengguna',
            'student_age_1_kicker' => 'Bayi & batita',
            'student_age_1_range' => 'Kelompok 1–2 tahun',
            'student_age_1_desc' => 'Rombel bayi, jadwal harian, dan laporan ke pihak orang tua.',
            'student_age_2_kicker' => 'Kelompok bermain',
            'student_age_2_range' => 'Kelompok 3–4 tahun',
            'student_age_2_desc' => 'Kegiatan, presensi, dan pencapaian tercatat per anak.',
            'student_age_3_kicker' => 'Taman kanak-kanak',
            'student_age_3_range' => 'Kelompok 5–6 tahun',
            'student_age_3_desc' => 'Monev, kesiapan belajar, dan arsip portofolio rapi.',
            'student_age_4_kicker' => 'Multi-kelompok',
            'student_age_4_range' => 'Daycare terpadu',
            'student_age_4_desc' => 'Beberapa jenjang dan cabang dalam satu akun lembaga.',
            'gallery_section_badge' => 'Dokumentasi platform',
            'gallery_item_caption' => 'Cuplikan modul',
            'gallery_cta_eyebrow' => 'Butuh penjelasan langsung?',
            'gallery_cta_title' => 'Jadwalkan demo platform',
            'gallery_cta_body' => 'Tim kami memandu Anda melihat dashboard admin, guru, dan portal orang tua sesuai kebutuhan sekolah.',
            'gallery_cta_button' => 'Minta jadwal demo',
            'page_kontak_hero_lead' => 'Hubungi kami untuk demo produk, penawaran implementasi, atau pertanyaan seputar pendaftaran sekolah dan portal orang tua.',
            'kontak_cta_eyebrow' => 'Ingin mendaftarkan sekolah?',
            'kontak_cta_title' => 'Mulai registrasi lembaga Anda',
            'kontak_cta_body' => 'Langkah awal digitalisasi yang didampingi tim kami  dari onboarding hingga portal orang tua aktif.',
            'nav_label_features' => 'Fitur',
            'footer_program_title' => 'Yang bisa Anda kelola',
            'footer_prog_1_title' => 'Kelompok usia & rombel',
            'footer_prog_1_sub' => 'Bayi, KB, TK  data per kelas',
            'footer_prog_2_title' => 'Portal orang tua',
            'footer_prog_2_sub' => 'Presensi, monev, tagihan di HP',
            'footer_prog_3_title' => 'Operasional sekolah',
            'footer_prog_3_sub' => 'Siswa, kegiatan, keuangan PSAK',
            'footer_prog_4_title' => 'Asisten AI',
            'footer_prog_4_sub' => 'Ringkasan monev & dukungan chat',
            'footer_prog_5_title' => 'Multi-cabang',
            'footer_prog_5_sub' => 'Satu lembaga, banyak sekolah',
            'auth_brand_tagline' => 'Sistem operasional & portal orang tua untuk PAUD',
            'auth_highlight_1_title' => 'Portal orang tua real-time',
            'auth_highlight_1_desc' => 'Presensi, kegiatan, monev, dan pembayaran  langsung dari ponsel.',
            'auth_highlight_2_title' => 'Operasional terpusat',
            'auth_highlight_2_desc' => 'Data siswa, kelas, keuangan, dan dokumentasi dalam satu dashboard.',
            'auth_highlight_3_title' => 'Aman & terkelola',
            'auth_highlight_3_desc' => 'Akses per peran dan alur persetujuan pendaftaran yang jelas.',
            'auth_quote' => 'Teknologi yang memudahkan guru dan orang tua  bukan menambah beban di meja kerja.',
        ];

        foreach ($newKeys as $key => $value) {
            $exists = CmsContent::query()
                ->whereNull('sekolah_id')
                ->where('key', $key)
                ->exists();
            if (! $exists) {
                CmsContent::set($key, $value, null);
            }
        }

        $refresh = [
            'hero_title' => 'Kelola operasional PAUD & pantau tumbuh kembang anak lebih praktis',
            'hero_subtitle' => 'Satu platform untuk admin, guru, dan orang tua  presensi, komunikasi, laporan harian, dan dokumentasi tanpa pekerjaan berulang.',
            'about_text' => "{$brand} menghubungkan orang tua dan sekolah secara transparan, menyederhanakan operasional harian admin dan guru, serta mengotomasi dokumentasi dan pelaporan yang repetitif.\n\nBukan sekadar banyak menu  setiap modul dirancang agar kepala sekolah, guru, dan orang tua merasakan manfaat nyata setiap hari.",
            'footer_text' => 'Menghubungkan orang tua dan sekolah, mempermudah operasional PAUD, dan mendukung pertumbuhan lembaga pendidikan usia dini di Indonesia.',
            'section_student_age_title' => "{$brand} mendukung pengelolaan rombel PAUD, daycare, dan jenjang usia dini",
            'section_student_age_text' => 'Struktur kelas fleksibel  dari penitipan bayi hingga taman kanak-kanak  dengan data tumbuh kembang yang rapi untuk guru dan orang tua.',
            'page_galeri_intro' => 'Cuplikan tampilan modul dan alur kerja yang digunakan admin sekolah, guru, dan orang tua setiap hari.',
            'section_gallery_title' => 'Galeri tampilan produk',
            'section_gallery_subtitle' => 'Unggah screenshot dashboard atau dokumentasi kegiatan mitra sekolah Anda.',
            'section_cta_title' => 'Mulai digitalisasi PAUD Anda',
            'section_cta_subtitle' => 'Daftarkan sekolah atau hubungi tim kami untuk demo dan konsultasi implementasi.',
            'contact_form_lead' => 'Ceritakan kebutuhan sekolah atau lembaga Anda. Kami akan merespons via email atau WhatsApp pada jam kerja.',
        ];

        foreach ($refresh as $key => $value) {
            CmsContent::set($key, $value, null);
        }
    }

    public function down(): void
    {
        // ponytail: data CMS tidak di-rollback  hindari kehilangan edit admin pasca-migrate.
    }
};
