<?php

namespace App\Support;

use App\Models\Anak;
use App\Models\MonevSummary;

final class MonevSummaryPresenter
{
    /**
     * @return array<int, array{key: string, title: string, icon: string, content: string, points: array<int, string>}>
     */
    public static function sections(MonevSummary $summary): array
    {
        $text = trim($summary->ringkasan);

        if ($text === 'Belum ada data pencapaian matrikulasi pada periode ini.') {
            return [[
                'key' => 'empty',
                'title' => 'Belum Ada Data',
                'icon' => 'info',
                'content' => $text,
                'points' => [$text],
            ]];
        }

        $markers = [
            'gambaran' => ['GAMBARAN_UMUM', 'Gambaran Umum', 'chart'],
            'kekuatan' => ['KEKUATAN', 'Kekuatan yang Menonjol', 'star'],
            'perhatian' => ['PERHATIAN', 'Area Perlu Perhatian', 'alert'],
            'rekomendasi' => ['REKOMENDASI', 'Rekomendasi Bulan Depan', 'lightbulb'],
        ];

        $parsed = [];

        foreach ($markers as $key => [$tag, $title, $icon]) {
            $pattern = '/\['.preg_quote($tag, '/').'\]\s*(.*?)(?=\[(?:GAMBARAN_UMUM|KEKUATAN|PERHATIAN|REKOMENDASI)\]|$)/s';
            if (preg_match($pattern, $text, $matches)) {
                $content = trim($matches[1]);
                if ($content !== '') {
                    $parsed[] = compact('key', 'title', 'icon', 'content') + [
                        'points' => self::contentToPoints($content),
                    ];
                }
            }
        }

        if ($parsed !== []) {
            return $parsed;
        }

        $paragraphs = preg_split("/\n\s*\n/", $text) ?: [];
        $paragraphs = array_values(array_filter(array_map('trim', $paragraphs)));

        $fallbackTitles = [
            ['Gambaran Umum', 'chart'],
            ['Kekuatan yang Menonjol', 'star'],
            ['Area Perlu Perhatian', 'alert'],
            ['Rekomendasi Bulan Depan', 'lightbulb'],
        ];

        foreach ($paragraphs as $i => $paragraph) {
            [$title, $icon] = $fallbackTitles[$i] ?? ['Ringkasan', 'chart'];
            $parsed[] = [
                'key' => 'section_'.$i,
                'title' => $title,
                'icon' => $icon,
                'content' => $paragraph,
                'points' => self::contentToPoints($paragraph),
            ];
        }

        return $parsed ?: [[
            'key' => 'full',
            'title' => 'Ringkasan',
            'icon' => 'chart',
            'content' => $text,
            'points' => self::contentToPoints($text),
        ]];
    }

    /**
     * @return array<int, string>
     */
    public static function contentToPoints(string $content): array
    {
        $content = trim($content);

        if ($content === '') {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', $content) ?: [];
        $bulletPoints = [];

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            if (preg_match('/^[-*•]\s+(.+)$/u', $line, $matches)) {
                $bulletPoints[] = trim($matches[1]);

                continue;
            }

            if (preg_match('/^\d+[.)]\s+(.+)$/u', $line, $matches)) {
                $bulletPoints[] = trim($matches[1]);
            }
        }

        if (count($bulletPoints) >= 1) {
            return $bulletPoints;
        }

        $sentences = preg_split('/(?<=[.!?])\s+/u', $content) ?: [];
        $sentences = array_values(array_filter(array_map('trim', $sentences)));

        if (count($sentences) >= 2) {
            return $sentences;
        }

        return [$content];
    }

    /**
     * @return array<string, int>
     */
    public static function scoreDistribution(MonevSummary $summary): array
    {
        return $summary->data_snapshot['distribusi_skor'] ?? [];
    }

    /**
     * @return array<string, array{jumlah: int, skor: array<string, int>}>
     */
    public static function perAspek(MonevSummary $summary): array
    {
        return $summary->data_snapshot['per_aspek'] ?? [];
    }

    /**
     * @return array<string>
     */
    public static function feedbackSamples(MonevSummary $summary): array
    {
        return $summary->data_snapshot['cuplikan_feedback'] ?? [];
    }

    public static function totalEntri(MonevSummary $summary): int
    {
        return (int) ($summary->data_snapshot['total_entri'] ?? 0);
    }

    /**
     * @param  array<string, int>  $scoreDist
     * @return array<int, array{label: string, count: int, percent: float, color: string}>
     */
    public static function pieChartSegments(array $scoreDist): array
    {
        $total = array_sum($scoreDist);

        if ($total <= 0) {
            return [];
        }

        $colors = ['#1A6B6B', '#2D9B9B', '#4DB6AC', '#80CBC4', '#B2DFDB', '#5C7A7A'];
        $segments = [];
        $i = 0;

        foreach ($scoreDist as $label => $count) {
            $segments[] = [
                'label' => $label,
                'count' => (int) $count,
                'percent' => round(((int) $count / $total) * 100, 1),
                'color' => $colors[$i % count($colors)],
            ];
            $i++;
        }

        return $segments;
    }

    /**
     * @param  array<string, int>  $scoreDist
     */
    public static function pieConicGradient(array $scoreDist): string
    {
        $total = array_sum($scoreDist);

        if ($total <= 0) {
            return '#F0F0F0';
        }

        $colors = ['#1A6B6B', '#2D9B9B', '#4DB6AC', '#80CBC4', '#B2DFDB', '#5C7A7A'];
        $parts = [];
        $cursor = 0.0;
        $i = 0;

        foreach ($scoreDist as $count) {
            $pct = ((int) $count / $total) * 100;
            $end = $cursor + $pct;
            $color = $colors[$i % count($colors)];
            $parts[] = "{$color} {$cursor}% {$end}%";
            $cursor = $end;
            $i++;
        }

        return 'conic-gradient(from -90deg, '.implode(', ', $parts).')';
    }

    /**
     * @return array{0: string, 1: array<string, array<int, string>>}
     */
    public static function splitRingkasanAndAspekSummaries(string $text): array
    {
        $pattern = '/\[ASPEK:([^\]]+)\]\s*(.*?)(?=\[ASPEK:|$)/s';
        $aspekSummaries = [];

        if (preg_match_all($pattern, $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $aspek = trim($match[1]);
                $content = trim($match[2]);
                $points = self::contentToPoints($content);

                if ($aspek !== '' && $points !== []) {
                    $aspekSummaries[$aspek] = $points;
                }
            }

            $text = trim((string) preg_replace($pattern, '', $text));
        }

        return [$text, $aspekSummaries];
    }

    /**
     * Ringkasan naratif per aspek (AI jika tersedia, fallback untuk data lama).
     *
     * @param  array{jumlah: int, skor: array<string, int>, ringkasan?: array<int, string>}  $data
     * @return array<int, string>
     */
    public static function perAspekNarrativePoints(string $aspek, array $data): array
    {
        $stored = $data['ringkasan'] ?? [];

        if (is_array($stored) && $stored !== []) {
            return array_values(array_filter(array_map('trim', $stored)));
        }

        return self::buildFallbackAspekNarrative($aspek, $data);
    }

    /**
     * @param  array{jumlah: int, skor: array<string, int>}  $data
     * @return array<int, string>
     */
    private static function buildFallbackAspekNarrative(string $aspek, array $data): array
    {
        $jumlah = (int) ($data['jumlah'] ?? 0);
        $skor = $data['skor'] ?? [];

        if ($jumlah === 0) {
            return ["Belum ada data pencapaian tercatat pada aspek {$aspek} untuk periode ini."];
        }

        arsort($skor);
        $points = [
            "Pada aspek {$aspek}, guru mencatat {$jumlah} entri pencapaian matrikulasi selama periode ini.",
        ];

        $topLabel = (string) array_key_first($skor);
        $topCount = (int) $skor[$topLabel];
        $topPercent = round(($topCount / $jumlah) * 100);

        if (count($skor) === 1) {
            $points[] = "Seluruh pencapaian berada pada level {$topLabel}, menunjukkan pola perkembangan yang konsisten pada aspek ini.";
        } else {
            $points[] = "Perkembangan didominasi oleh {$topLabel} ({$topPercent}%), menjadi gambaran utama kemajuan siswa pada aspek {$aspek}.";

            $others = [];
            foreach ($skor as $label => $count) {
                if ($label === $topLabel) {
                    continue;
                }

                $percent = round(((int) $count / $jumlah) * 100);
                $others[] = "{$label} ({$percent}%)";
            }

            if ($others !== []) {
                $points[] = 'Distribusi capaian lainnya: '.implode(', ', $others).'.';
            }
        }

        $interpretation = self::interpretDominantScore($topLabel);
        if ($interpretation !== null) {
            $points[] = $interpretation;
        }

        return $points;
    }

    private static function interpretDominantScore(string $label): ?string
    {
        if (str_contains($label, 'Belum Berkembang') || preg_match('/\bBB\b/u', $label)) {
            return 'Perlu perhatian khusus dan intervensi bertahap dengan pendekatan bermain sambil belajar.';
        }

        if (str_contains($label, 'Mulai Berkembang') || preg_match('/\bMB\b/u', $label)) {
            return 'Siswa sedang dalam proses pembiasaan; penguatan rutin di rumah dan sekolah akan membantu capaian lebih stabil.';
        }

        if (str_contains($label, 'Sesuai Harapan') || preg_match('/\bBSH\b/u', $label)) {
            return 'Capaian menunjukkan perkembangan sesuai harapan; pertahankan stimulasi dan variasi kegiatan.';
        }

        if (str_contains($label, 'Sangat Baik') || preg_match('/\bBSB\b/u', $label)) {
            return 'Perkembangan sangat baik; siswa siap mendapat tantangan atau penguatan lanjutan.';
        }

        return null;
    }

    /**
     * @param  array{jumlah: int, skor: array<string, int>}  $data
     * @return array<int, string>
     */
    public static function perAspekSummaryPoints(string $aspek, array $data): array
    {
        $jumlah = (int) ($data['jumlah'] ?? 0);
        $skor = $data['skor'] ?? [];

        if ($jumlah === 0) {
            return ['Belum ada entri pencapaian pada aspek ini.'];
        }

        $points = [
            "Tercatat {$jumlah} entri pencapaian pada aspek {$aspek}.",
        ];

        if ($skor === []) {
            return $points;
        }

        arsort($skor);

        foreach ($skor as $label => $count) {
            $count = (int) $count;
            $percent = round(($count / $jumlah) * 100);
            $points[] = "{$label}: {$count} entri ({$percent}%).";
        }

        return $points;
    }

    public static function pdfFilename(Anak $anak, MonevSummary $summary): string
    {
        $name = trim(preg_replace('/\s+/u', ' ', (string) $anak->name));
        $name = preg_replace('/[\/\\\\:*?"<>|]/u', '', $name) ?: 'Siswa';
        $bulan = IndonesianMonths::NAMES[$summary->bulan] ?? (string) $summary->bulan;

        return "Monev-{$name} - {$bulan}.pdf";
    }
}
