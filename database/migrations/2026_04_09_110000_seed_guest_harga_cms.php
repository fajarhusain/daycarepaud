<?php

use App\Models\CmsContent;
use App\Support\GuestBrand;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $brand = GuestBrand::name();

        $defaults = [
            'page_harga_h1' => 'Harga & paket',
            'page_harga_intro' => "Pilih paket yang sesuai skala sekolah Anda. Semua paket termasuk onboarding, pelatihan dasar, dan dukungan implementasi {$brand}.",
            'seo_harga_description' => "Paket harga {$brand} untuk PAUD dan daycare — transparan, fleksibel, dan dapat disesuaikan jumlah siswa serta cabang.",
            'section_pricing_badge' => 'Paket berlangganan',
            'section_pricing_title' => 'Investasi yang menyesuaikan ukuran sekolah',
            'section_pricing_subtitle' => 'Harga final mengikuti jumlah siswa aktif dan modul yang diaktifkan. Hubungi kami untuk simulasi.',
            'pricing_1_name' => 'Starter',
            'pricing_1_price' => 'Hubungi kami',
            'pricing_1_period' => 'bulan',
            'pricing_1_desc' => 'Untuk satu unit sekolah dengan kebutuhan operasional inti.',
            'pricing_1_badge' => '',
            'pricing_1_featured' => '0',
            'pricing_1_features' => "Portal orang tua & presensi\nLaporan harian & dokumentasi\nHingga 80 siswa aktif\nDukungan email & chat",
            'pricing_2_name' => 'Professional',
            'pricing_2_price' => 'Hubungi kami',
            'pricing_2_period' => 'bulan',
            'pricing_2_desc' => 'Paket lengkap untuk sekolah yang ingin otomasi keuangan dan AI.',
            'pricing_2_badge' => 'Paling populer',
            'pricing_2_featured' => '1',
            'pricing_2_features' => "Semua fitur Starter\nKeuangan PSAK & pembayaran online\nAsisten AI untuk guru & ortu\nHingga 250 siswa aktif\nPrioritas dukungan",
            'pricing_3_name' => 'Lembaga',
            'pricing_3_price' => 'Hubungi kami',
            'pricing_3_period' => 'bulan',
            'pricing_3_desc' => 'Multi-cabang dengan kontrol terpusat untuk yayasan atau jaringan.',
            'pricing_3_badge' => '',
            'pricing_3_featured' => '0',
            'pricing_3_features' => "Semua fitur Professional\nMulti-sekolah & multi-cabang\nActivity log lintas unit\nSLA & account manager\nKustomisasi modul",
            'pricing_footnote' => 'Harga belum termasuk PPN. Penawaran resmi dikirim setelah konsultasi kebutuhan sekolah.',
            'harga_cta_eyebrow' => 'Butuh simulasi harga?',
            'harga_cta_title' => 'Diskusikan paket terbaik untuk sekolah Anda',
            'harga_cta_body' => 'Tim kami membantu memetakan modul, jumlah siswa, dan jadwal go-live — tanpa komitmen di awal.',
            'harga_cta_button' => 'Hubungi tim penjualan',
        ];

        foreach ($defaults as $key => $value) {
            if (! CmsContent::query()->whereNull('sekolah_id')->where('key', $key)->exists()) {
                CmsContent::set($key, $value, null);
            }
        }

        $ctaFromGallery = [
            'harga_cta_eyebrow' => 'gallery_cta_eyebrow',
            'harga_cta_title' => 'gallery_cta_title',
            'harga_cta_body' => 'gallery_cta_body',
            'harga_cta_button' => 'gallery_cta_button',
        ];
        foreach ($ctaFromGallery as $newKey => $oldKey) {
            $old = CmsContent::get($oldKey, '');
            if ($old !== '' && ! CmsContent::query()->whereNull('sekolah_id')->where('key', $newKey)->exists()) {
                CmsContent::set($newKey, $old, null);
            }
        }
    }

    public function down(): void
    {
    }
};
