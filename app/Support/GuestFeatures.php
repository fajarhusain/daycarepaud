<?php

namespace App\Support;

final class GuestFeatures
{
    /**
     * @return list<array{value: string, label: string}>
     */
    public static function stats(): array
    {
        return [
            ['value' => 'Real-time', 'label' => 'Transparansi ortu–sekolah'],
            ['value' => 'Terpadu', 'label' => 'Satu platform operasional'],
            ['value' => 'Mudah', 'label' => 'Memudahkan admin & guru'],
        ];
    }

    /**
     * @param  array<string, string>|null  $cms
     * @return list<array{icon: string, icon_color: string, value: string, label: string}>
     */
    public static function landingValues(?array $cms = null): array
    {
        $cms = $cms ?? [];
        $defaults = [
            ['icon' => 'icon-kindergarden', 'icon_color' => 'text-destructive-foreground', 'value' => 'Real-time', 'label' => 'Transparansi ortu–sekolah'],
            ['icon' => 'icon-book', 'icon_color' => 'text-secondary-foreground', 'value' => 'Terpadu', 'label' => 'Satu platform operasional'],
            ['icon' => 'icon-blocks', 'icon_color' => 'text-primary-foreground', 'value' => 'Mudah', 'label' => 'Memudahkan admin & guru'],
            ['icon' => 'icon-baby-body', 'icon_color' => 'text-green-foreground', 'value' => 'Aman', 'label' => 'Data sekolah terkelola'],
        ];
        $items = [];
        foreach ($defaults as $i => $row) {
            $n = $i + 1;
            $items[] = [
                'icon' => $row['icon'],
                'icon_color' => $row['icon_color'],
                'value' => trim($cms["stats_{$n}_value"] ?? '') ?: $row['value'],
                'label' => trim($cms["stats_{$n}_label"] ?? '') ?: $row['label'],
            ];
        }

        return $items;
    }

    /**
     * @return list<array{id: string, title: string, tagline: string, desc: string, illustration: string, highlights: list<string>}>
     */
    public static function defaultPillars(): array
    {
        return [
            [
                'id' => 'ortu-sekolah',
                'title' => 'Penghubung Ortu & Sekolah',
                'tagline' => 'Komunikasi dua arah, tanpa jarak',
                'desc' => 'Orang tua selalu tahu perkembangan anak  kehadiran, pencapaian, monev, tagihan  tanpa harus menunggu rapat atau chat manual.',
                'illustration' => 'pillar.ortu',
                'highlights' => [
                    'Portal orang tua: presensi, pencapaian, monev PDF',
                    'Pembayaran & invoice online',
                    'Kritik & saran langsung ke admin',
                    'Chat admin–orang tua terpusat',
                ],
            ],
            [
                'id' => 'operasional',
                'title' => 'Operasional Internal',
                'tagline' => 'Admin & guru bekerja lebih cepat',
                'desc' => 'Satu dashboard untuk mengelola siswa, kelas, kegiatan harian, presensi, menu makanan, hingga keuangan PSAK  tanpa spreadsheet terpisah.',
                'illustration' => 'pillar.operasional',
                'highlights' => [
                    'Siswa, kelas & approval pendaftaran',
                    'Agenda belajar & kegiatan rutin',
                    'Presensi siswa & guru',
                    'Biaya bulanan, jurnal & laporan PSAK',
                ],
            ],
            [
                'id' => 'ai',
                'title' => 'Kemudahan dengan AI',
                'tagline' => 'Otomasi yang menghemat waktu guru',
                'desc' => 'Asisten AI bantu jawab pertanyaan orang tua, generate ringkasan monev, dan sarankan umpan balik pencapaian  berbasis data sekolah yang nyata.',
                'illustration' => 'pillar.ai',
                'highlights' => [
                    'Chat AI orang tua berbasis data anak',
                    'Generate monev otomatis per siswa',
                    'Saran feedback pencapaian untuk guru',
                    'Persona AI dapat dikustom per sekolah',
                ],
            ],
        ];
    }

    /**
     * @param  array<string, string>|null  $cms
     * @return list<array{id: string, title: string, tagline: string, desc: string, illustration: string, highlights: list<string>}>
     */
    public static function pillars(?array $cms = null): array
    {
        $cms = $cms ?? [];
        $pillars = self::defaultPillars();
        foreach ($pillars as $i => &$pillar) {
            $n = $i + 1;
            $title = trim($cms["pillar_{$n}_title"] ?? '');
            $desc = trim($cms["pillar_{$n}_desc"] ?? '');
            if ($title !== '') {
                $pillar['title'] = $title;
            }
            if ($desc !== '') {
                $pillar['desc'] = $desc;
            }
        }
        unset($pillar);

        return $pillars;
    }

    /**
     * @return list<array{step: string, title: string, desc: string}>
     */
    public static function onboardingSteps(): array
    {
        return [
            ['step' => '01', 'title' => 'Setup Sekolah & Tim', 'desc' => 'Daftarkan cabang, kelas, pengajar, dan admin  siap operasional dalam hitungan jam.'],
            ['step' => '02', 'title' => 'Aktifkan Portal Orang Tua', 'desc' => 'Orang tua daftar online, pantau anak, bayar tagihan, dan kirim masukan langsung dari HP.'],
            ['step' => '03', 'title' => 'Manfaatkan AI & Laporan', 'desc' => 'Generate monev PDF, aktifkan chat AI, dan biarkan sistem yang mengurus dokumentasi rutin.'],
        ];
    }

    /**
     * @return list<array{title: string, quote: string, name: string, role: string, rating: int}>
     */
    public static function defaultTestimonials(): array
    {
        $brand = GuestBrand::name();

        return [
            [
                'title' => 'Orang tua lebih tenang',
                'quote' => 'Saya bisa cek kehadiran dan pencapaian anak kapan saja. Tidak perlu menunggu rapat bulanan untuk tahu perkembangannya.',
                'name' => 'Ibu Sari',
                'role' => 'Orang Tua PAUD',
                'rating' => 5,
            ],
            [
                'title' => 'Admin jadi lebih efisien',
                'quote' => 'Pendaftaran, presensi, dan pembayaran dalam satu sistem. Tim kami hemat berjam-jam kerja administratif setiap minggu.',
                'name' => 'Budi Santoso',
                'role' => 'Admin Sekolah',
                'rating' => 5,
            ],
            [
                'title' => 'Mudah kelola multi-cabang',
                'quote' => "Sebagai lembaga dengan tiga cabang, {$brand} membantu kami memantau operasional dan komunikasi orang tua secara terpusat.",
                'name' => 'Dewi Lestari',
                'role' => 'Ketua Lembaga',
                'rating' => 5,
            ],
        ];
    }

    /**
     * @param  array<string, string>|null  $cms
     * @return list<array{title: string, quote: string, name: string, role: string, rating: int}>
     */
    public static function testimonials(?array $cms = null): array
    {
        $cms = $cms ?? [];
        $items = [];
        foreach (self::defaultTestimonials() as $i => $default) {
            $n = $i + 1;
            $items[] = [
                'title' => trim($cms["testimonial_{$n}_title"] ?? '') ?: $default['title'],
                'quote' => trim($cms["testimonial_{$n}_quote"] ?? '') ?: $default['quote'],
                'name' => trim($cms["testimonial_{$n}_name"] ?? '') ?: $default['name'],
                'role' => trim($cms["testimonial_{$n}_role"] ?? '') ?: $default['role'],
                'rating' => 5,
            ];
        }

        return $items;
    }

    /**
     * @param  array<string, string>|null  $cms
     * @return list<array{q: string, a: string}>
     */
    public static function faqs(?array $cms = null): array
    {
        $cms = $cms ?? [];
        $brand = GuestBrand::name();
        $defaults = [
            ['q' => "Apa itu {$brand}?", 'a' => 'Platform terpadu untuk operasional PAUD: data siswa, presensi, komunikasi orang tua, keuangan, dan dokumentasi kegiatan dalam satu sistem.'],
            ['q' => 'Bagaimana cara mendaftarkan sekolah?', 'a' => 'Klik Daftar Sekolah di halaman ini, isi formulir, lalu tim kami akan menghubungi Anda untuk onboarding dan demo.'],
            ['q' => 'Apakah orang tua punya akses aplikasi?', 'a' => 'Ya. Orang tua dapat memantau kehadiran, pencapaian, pembayaran, dan berkomunikasi dengan sekolah melalui portal khusus.'],
        ];
        $items = [];
        foreach ($defaults as $i => $default) {
            $n = $i + 1;
            $items[] = [
                'q' => trim($cms["faq_{$n}_q"] ?? '') ?: $default['q'],
                'a' => trim($cms["faq_{$n}_a"] ?? '') ?: $default['a'],
            ];
        }

        return $items;
    }

    public static function faqTitle(?array $cms = null): string
    {
        $cms = $cms ?? [];

        return trim($cms['faq_title'] ?? '') ?: 'Pertanyaan yang sering diajukan';
    }

    /**
     * Kartu layanan halaman Fitur (gaya services.html): CMS facility + pilar produk.
     *
     * @param  array<string, string>  $cms
     * @return list<array{icon: string, title: string, desc: string}>
     */
    public static function servicePageCards(array $cms): array
    {
        $icons = ['icon-car-toy', 'icon-toys', 'icon-feeder', 'icon-book', 'icon-mat', 'icon-baby-body'];
        $cards = [];

        for ($i = 1; $i <= 4; $i++) {
            $title = trim($cms["facility_{$i}_title"] ?? '');
            if ($title === '') {
                continue;
            }
            $cards[] = [
                'icon' => $icons[$i - 1],
                'title' => $title,
                'desc' => trim($cms["facility_{$i}_desc"] ?? ''),
            ];
        }

        foreach (self::pillars($cms) as $pillar) {
            if (count($cards) >= 6) {
                break;
            }
            $cards[] = [
                'icon' => $icons[count($cards)] ?? 'icon-blocks',
                'title' => $pillar['title'],
                'desc' => $pillar['desc'],
            ];
        }

        return array_slice($cards, 0, 6);
    }
}
