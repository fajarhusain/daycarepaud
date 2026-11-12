<?php

namespace App\Services;

use App\Models\SekolahAiPersona;
use App\Support\AiPersonaScope;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SumopodAIService
{
    protected string $apiKey;

    protected string $model;

    protected string $baseUrl;

    public function __construct(string $apiKey, string $model = 'gpt-4o-mini', ?string $baseUrl = null)
    {
        $this->apiKey = $apiKey;
        $this->model = $model;
        $this->baseUrl = rtrim($baseUrl ?? 'https://ai.sumopod.com/v1', '/');
    }

    /**
     * Generate 3 feedback suggestions for a student achievement entry.
     *
     * @param  string  $anakName  Student's name
     * @param  string  $kegiatanTitle  Activity title
     * @param  string  $matrikulasiLabel  Matriculation indicator label (aspek: indicator)
     * @param  string  $scoreLabel  Human-readable scale label (e.g. "Mulai Berkembang (MB)")
     * @return array<string> Exactly 3 suggestion strings
     */
    public function generateFeedbackSuggestions(
        string $anakName,
        string $kegiatanTitle,
        string $matrikulasiLabel,
        string $scoreLabel,
        ?string $personaPrefix = null
    ): array {
        $identity = $personaPrefix !== null && trim($personaPrefix) !== ''
            ? trim($personaPrefix)
            : 'Kamu adalah guru PAUD / TK yang profesional dan penuh kasih sayang.';

        $prompt = <<<PROMPT
{$identity}
Berikan TEPAT 3 saran umpan balik dalam Bahasa Indonesia untuk catatan perkembangan siswa PAUD.

Konteks:
- Nama Siswa : {$anakName}
- Judul Kegiatan : {$kegiatanTitle}
- Aspek / Indikator : {$matrikulasiLabel}
- Skala Capaian : {$scoreLabel}

Arti skala (ikuti tingkat yang dipilih, jangan naik atau turun tingkat):
- Belum Berkembang (BB): perilaku indikator belum tampak. Ajak mulai dari satu langkah kecil.
- Mulai Berkembang (MB): baru muncul sesekali atau masih perlu bantuan. Pakai "mulai" atau "dengan pendampingan", lalu ajak lebih sering.
- Berkembang Sesuai Harapan (BSH): sudah tampil sesuai harapan pada kegiatan ini. Apresiasi perilaku yang teramati, lalu minta dipertahankan.
- Berkembang Sangat Baik (BSB): mandiri dan melampaui harapan. Apresiasi kuat, lalu beri tantangan sedikit lebih jauh.

Aturan isi:
- Ketiga saran merujuk SATU perilaku yang sama: bagian indikator yang selaras dengan judul kegiatan, pada tingkat skala yang dipilih.
- Jika indikator memuat beberapa perilaku (koma atau garis miring), pilih hanya bagian yang terjadi di judul kegiatan. Perilaku lain di indikator tidak ditulis seolah sudah teramati.
- Variasikan rumusan kalimat, bukan topiknya.
- Maksimal 2 kalimat, hangat, sesuai usia PAUD/TK, sebut nama siswa.
- Jawab HANYA JSON valid tanpa markdown atau teks lain:
{"saran":["saran pertama","saran kedua","saran ketiga"]}
PROMPT;

        $response = Http::withToken($this->apiKey)
            ->timeout(30)
            ->post("{$this->baseUrl}/chat/completions", [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 512,
                'temperature' => 0.4,
            ]);

        if (! $response->successful()) {
            $this->throwApiError($response);
        }

        $content = $this->extractCompletionContent($response);

        return $this->resolveFeedbackSuggestions($content);
    }

    protected function throwApiError(Response $response): void
    {
        Log::warning('AI API request failed', [
            'status' => $response->status(),
            'body' => mb_substr($response->body(), 0, 2000),
            'base_url' => $this->baseUrl,
        ]);

        throw new \RuntimeException('AI API error: HTTP '.$response->status());
    }

    protected function extractCompletionContent(Response $response): string
    {
        $body = $response->body();

        if ($this->isSseBody($body)) {
            return $this->extractContentFromSse($body);
        }

        $json = $response->json();
        if (is_array($json)) {
            $content = $this->extractContentFromJson($json);
            if ($content !== '') {
                return $content;
            }
        }

        return trim($body);
    }

    protected function isSseBody(string $body): bool
    {
        return str_starts_with(trim($body), 'data:');
    }

    protected function extractContentFromSse(string $body): string
    {
        $content = '';
        $reasoning = '';

        foreach (preg_split('/\r?\n/', $body) as $line) {
            $line = trim($line);
            if ($line === '' || ! str_starts_with($line, 'data:')) {
                continue;
            }

            $data = trim(substr($line, 5));
            if ($data === '' || $data === '[DONE]') {
                continue;
            }

            $json = json_decode($data, true);
            if (! is_array($json)) {
                continue;
            }

            $choice = $json['choices'][0] ?? [];
            $delta = is_array($choice['delta'] ?? null) ? $choice['delta'] : [];
            $message = is_array($choice['message'] ?? null) ? $choice['message'] : [];

            $content .= $this->stringifyContentPart($delta['content'] ?? null);
            $content .= $this->stringifyContentPart($message['content'] ?? null);
            $content .= $this->stringifyContentPart($choice['text'] ?? null);

            $reasoning .= $this->stringifyContentPart($delta['reasoning'] ?? null);
            $reasoning .= $this->stringifyContentPart($delta['reasoning_content'] ?? null);
            $reasoning .= $this->stringifyContentPart($message['reasoning'] ?? null);
            $reasoning .= $this->stringifyContentPart($message['reasoning_content'] ?? null);
        }

        $content = trim($content);

        return $content !== '' ? $content : trim($reasoning);
    }

    /**
     * @param  array<string, mixed>  $json
     */
    protected function extractContentFromJson(array $json): string
    {
        $choice = $json['choices'][0] ?? [];

        $content = $this->stringifyContentPart(data_get($choice, 'message.content'));
        if ($content !== '') {
            return $content;
        }

        $content = $this->stringifyContentPart($choice['text'] ?? null);
        if ($content !== '') {
            return $content;
        }

        foreach (['message.reasoning', 'message.reasoning_content'] as $path) {
            $reasoning = $this->stringifyContentPart(data_get($choice, $path));
            if ($reasoning !== '') {
                return $reasoning;
            }
        }

        return '';
    }

    protected function stringifyContentPart(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_string($value)) {
            return $value;
        }

        if (! is_array($value)) {
            return '';
        }

        $parts = [];
        foreach ($value as $part) {
            if (is_string($part)) {
                $parts[] = $part;

                continue;
            }

            if (is_array($part)) {
                $parts[] = (string) ($part['text'] ?? $part['content'] ?? '');
            }
        }

        return implode('', array_filter($parts, fn (string $part): bool => $part !== ''));
    }

    /**
     * @return array<string>
     */
    protected function resolveFeedbackSuggestions(string $content): array
    {
        $fallback = 'Terus semangat dan pertahankan perkembangan yang baik ini!';

        $fromJson = $this->parseJsonSuggestions($content, 'saran');
        if ($fromJson !== null) {
            return $this->padSuggestions($fromJson, $fallback);
        }

        $fromNumbered = $this->parseNumberedSuggestions($content);
        if ($fromNumbered !== []) {
            return $this->padSuggestions($fromNumbered, $fallback);
        }

        Log::warning('AI feedback response unparseable', [
            'content' => mb_substr($content, 0, 500),
            'base_url' => $this->baseUrl,
            'model' => $this->model,
        ]);

        throw new \RuntimeException('Format respons AI tidak dikenali.');
    }

    /**
     * @return list<string>|null
     */
    protected function parseJsonSuggestions(string $content, string $key): ?array
    {
        $content = trim($content);
        if ($content === '') {
            return null;
        }

        $json = $content;
        if (preg_match('/```(?:json)?\s*([\s\S]*?)```/i', $content, $matches)) {
            $json = trim($matches[1]);
        } elseif (preg_match('/\{[\s\S]*\}/', $content, $matches)) {
            $json = $matches[0];
        }

        $decoded = json_decode($json, true);
        if (! is_array($decoded)) {
            return null;
        }

        $items = $decoded[$key] ?? $decoded['suggestions'] ?? null;
        if (! is_array($items)) {
            return null;
        }

        $items = array_values(array_filter(array_map('strval', $items)));

        return $items !== [] ? $items : null;
    }

    /**
     * Parse the numbered list from the AI response.
     *
     * @return list<string>
     */
    protected function parseNumberedSuggestions(string $content): array
    {
        $content = trim($content);
        if ($content === '') {
            return [];
        }

        $lines = preg_split('/\r?\n/', $content);
        $suggestions = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/^\d+\.\s*\*{0,2}(.+)\*{0,2}$/', $line, $matches)) {
                $suggestion = trim($matches[1]);
                if ($suggestion !== '') {
                    $suggestions[] = $suggestion;
                }
            }
        }

        if ($suggestions === []) {
            preg_match_all('/\d+\.\s*(.+?)(?=\d+\.|$)/s', $content, $matches);
            $suggestions = array_map('trim', $matches[1] ?? []);
        }

        return array_values(array_filter($suggestions));
    }

    /**
     * Parse the numbered list from the AI response into an array of 3 strings.
     *
     * @return array<string>
     */
    protected function parseSuggestions(string $content): array
    {
        $fallback = 'Terus semangat dan pertahankan perkembangan yang baik ini!';

        return $this->padSuggestions($this->parseNumberedSuggestions($content), $fallback);
    }

    /**
     * Generate 3 catatan penilaian suggestions for monev guru per kriteria.
     *
     * @param  array{hari_kerja: int, hadir: int, terlambat: int, telat_pulang: int, tanpa_check_out: int, persentase: float}|null  $rekap
     * @return array<string>
     */
    public function generateMonevGuruCatatanSuggestions(
        string $guruName,
        string $kriteriaNama,
        string $kriteriaDeskripsi,
        int $skor,
        string $periodeLabel,
        ?string $personaPrefix = null,
        ?array $rekap = null
    ): array {
        $identity = $personaPrefix !== null && trim($personaPrefix) !== ''
            ? trim($personaPrefix)
            : 'Kamu adalah kepala sekolah / supervisor PAUD yang profesional dan objektif.';

        $deskripsi = $kriteriaDeskripsi !== '' ? $kriteriaDeskripsi : '(tidak ada deskripsi tambahan)';

        $presensiBlock = '';
        $presensiInstruksi = '';
        if ($rekap !== null && (int) ($rekap['hari_kerja'] ?? 0) > 0) {
            $hariKerja = (int) $rekap['hari_kerja'];
            $hadir = (int) ($rekap['hadir'] ?? 0);
            $alpa = max(0, $hariKerja - $hadir);
            $terlambat = (int) ($rekap['terlambat'] ?? 0);
            $telatPulang = (int) ($rekap['telat_pulang'] ?? 0);
            $tanpaCheckOut = (int) ($rekap['tanpa_check_out'] ?? 0);
            $persentase = (float) ($rekap['persentase'] ?? 0);

            $presensiBlock = <<<PRESENSI

Data Presensi:
- Hari kerja efektif : {$hariKerja} hari
- Hadir              : {$hadir} hari ({$persentase}%)
- Tidak hadir (alpa) : {$alpa} hari
- Telat masuk        : {$terlambat} kali
- Pulang lebih awal  : {$telatPulang} kali
- Check-in tanpa checkout: {$tanpaCheckOut} kali
PRESENSI;

            $presensiInstruksi = <<<PRESENSI_INS

- Data Presensi di atas WAJIB dipakai sebagai dasar catatan; jangan mengarang angka lain.
- Di antara 3 saran, sebar fakta berikut (angka harus sesuai data):
  1) total hadir vs hari kerja / persentase kehadiran,
  2) alpa (tidak hadir) bila > 0, atau apresiasi jika 0,
  3) telat masuk ({$terlambat} kali) — jika 0 sebutkan tepat waktu, jika >0 kritik konstruktif,
  4) pulang lebih awal ({$telatPulang} kali) dan check-in tanpa checkout ({$tanpaCheckOut} kali) bila relevan.
- Setiap saran harus punya alasan jelas yang menghubungkan skor {$skor} dengan fakta presensi tersebut.
PRESENSI_INS;
        }

        $prompt = <<<PROMPT
{$identity}
Berikan TEPAT 3 saran catatan penilaian dalam Bahasa Indonesia untuk dicatat pada evaluasi kinerja guru PAUD.

Konteks:
- Nama Guru : {$guruName}
- Kriteria Penilaian : {$kriteriaNama}
- Panduan Kriteria : {$deskripsi}
- Skor (1–100) : {$skor}
- Periode Evaluasi : {$periodeLabel}
{$presensiBlock}
Instruksi:
- Setiap saran maks 2 kalimat, profesional, spesifik pada kriteria, dan selaras dengan skor.
- Skor rendah (<60): ton konstruktif dengan area perbaikan jelas. Skor sedang (60–79): apresiasi + saran pengembangan. Skor tinggi (≥80): apresiasi konkret + pertahankan.
- Jangan mengulang saran yang sama.{$presensiInstruksi}
- Jawab HANYA dengan 3 saran, masing-masing diawali dengan nomor (1. 2. 3.) tanpa penjelasan tambahan.

Format jawaban:
1. [Saran pertama]
2. [Saran kedua]
3. [Saran ketiga]
PROMPT;

        $temperature = $rekap !== null ? 0.5 : 0.8;

        $response = Http::withToken($this->apiKey)
            ->timeout(30)
            ->post("{$this->baseUrl}/chat/completions", [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 512,
                'temperature' => $temperature,
            ]);

        if (! $response->successful()) {
            $this->throwApiError($response);
        }

        $content = $this->extractCompletionContent($response);

        return $this->parseSuggestions($content);
    }

    /**
     * @param  array<int, array{nama: string, skor: int, catatan: string, bobot: int}>  $penilaianItems
     * @return array{catatan: array<string>, rekomendasi: array<string>}
     */
    public function generateMonevGuruRingkasanSuggestions(
        string $guruName,
        string $periodeLabel,
        array $penilaianItems,
        ?string $judul = null,
        ?string $personaPrefix = null
    ): array {
        $identity = $personaPrefix !== null && trim($personaPrefix) !== ''
            ? trim($personaPrefix)
            : 'Kamu adalah kepala sekolah / supervisor PAUD yang profesional dan objektif.';

        $judulLine = $judul ? "- Judul Evaluasi : {$judul}\n" : '';

        $lines = [];
        foreach ($penilaianItems as $item) {
            $catatan = trim($item['catatan'] ?? '') !== '' ? $item['catatan'] : '(belum ada catatan)';
            $lines[] = "- {$item['nama']} (bobot {$item['bobot']}%): skor {$item['skor']}/100  {$catatan}";
        }
        $penilaianBlock = implode("\n", $lines);

        $prompt = <<<PROMPT
{$identity}
Berdasarkan penilaian per kriteria berikut, buat saran catatan umum dan rekomendasi tindak lanjut untuk evaluasi kinerja guru PAUD.

Konteks:
- Nama Guru : {$guruName}
{$judulLine}- Periode Evaluasi : {$periodeLabel}

Penilaian per kriteria:
{$penilaianBlock}

Instruksi:
- Buat TEPAT 3 saran untuk "catatan_umum" (ringkasan holistik kinerja guru) dan TEPAT 3 saran untuk "rekomendasi" (tindak lanjut konkret).
- Setiap saran maks 3 kalimat, profesional, merujuk pola skor & catatan di atas.
- Jawab HANYA JSON valid tanpa markdown:
{"catatan":["saran 1","saran 2","saran 3"],"rekomendasi":["saran 1","saran 2","saran 3"]}
PROMPT;

        $response = Http::withToken($this->apiKey)
            ->timeout(45)
            ->post("{$this->baseUrl}/chat/completions", [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 1024,
                'temperature' => 0.7,
            ]);

        if (! $response->successful()) {
            $this->throwApiError($response);
        }

        $content = trim($this->extractCompletionContent($response));

        return $this->parseMonevGuruRingkasanSuggestions($content, $penilaianItems);
    }

    /**
     * @param  array<int, array{nama: string, skor: int, catatan: string, bobot: int}>  $penilaianItems
     * @return array{catatan: array<string>, rekomendasi: array<string>}
     */
    protected function parseMonevGuruRingkasanSuggestions(string $content, array $penilaianItems): array
    {
        $json = $content;
        if (preg_match('/\{[\s\S]*\}/', $content, $m)) {
            $json = $m[0];
        }

        $decoded = json_decode($json, true);
        if (is_array($decoded)) {
            $catatan = array_values(array_filter(array_map('strval', $decoded['catatan'] ?? [])));
            $rekomendasi = array_values(array_filter(array_map('strval', $decoded['rekomendasi'] ?? [])));

            if ($catatan !== [] || $rekomendasi !== []) {
                return [
                    'catatan' => $this->padSuggestions($catatan, 'Guru menunjukkan kinerja yang baik secara keseluruhan.'),
                    'rekomendasi' => $this->padSuggestions($rekomendasi, 'Pertahankan performa dan lakukan refleksi berkala.'),
                ];
            }
        }

        // ponytail: fallback parse numbered blocks if JSON gagal
        $catatan = $this->parseSuggestions($content);

        return [
            'catatan' => $catatan,
            'rekomendasi' => $this->padSuggestions([], 'Lakukan pendampingan dan evaluasi lanjutan sesuai kebutuhan.'),
        ];
    }

    /** @param  array<string>  $items */
    protected function padSuggestions(array $items, string $fallback): array
    {
        $items = array_values(array_filter($items));
        while (count($items) < 3) {
            $items[] = $fallback;
        }

        return array_slice($items, 0, 3);
    }

    /**
     * Generate a monthly monitoring summary for a student's matriculation achievements.
     *
     * @param  array<string, mixed>  $stats  Aggregated data from MonevDataAggregator
     */
    public function generateMonevSummary(
        string $anakName,
        string $kelasName,
        string $periodeLabel,
        array $stats,
        ?string $personaPrefix = null
    ): string {
        $statsJson = json_encode($stats, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $aspekNames = implode(', ', array_keys($stats['per_aspek'] ?? []));
        $aspekInstruction = $aspekNames !== ''
            ? <<<ASPEK

Setelah section REKOMENDASI, tambahkan ringkasan naratif per aspek capaian dengan format persis:
[ASPEK:{nama_aspek}]
- (poin ringkasan 1)
- (poin ringkasan 2)

WAJIB buat section [ASPEK:...] untuk setiap aspek berikut (nama harus sama persis): {$aspekNames}
Setiap aspek 2-4 poin bullet, bahasa hangat seperti guru PAUD, jelaskan makna perkembangan (bukan hanya angka).
ASPEK
            : '';

        $identity = $personaPrefix !== null && trim($personaPrefix) !== ''
            ? trim($personaPrefix)
            : 'Kamu adalah guru PAUD / TK yang profesional.';

        $prompt = <<<PROMPT
{$identity} Buat ringkasan monitoring & evaluasi (monev) perkembangan siswa berdasarkan data pencapaian matrikulasi selama satu bulan.

Konteks Siswa:
- Nama: {$anakName}
- Kelas: {$kelasName}
- Periode: {$periodeLabel}

Data Pencapaian Matrikulasi (agregat):
{$statsJson}

Instruksi:
- Tulis dalam Bahasa Indonesia, profesional namun hangat.
- WAJIB gunakan format section marker persis seperti ini (4 section):
[GAMBARAN_UMUM]
- (poin pertama gambaran umum)
- (poin kedua)
- (poin ketiga)

[KEKUATAN]
- (poin kekuatan 1)
- (poin kekuatan 2)

[PERHATIAN]
- (poin area perhatian 1)
- (poin area perhatian 2)

[REKOMENDASI]
- (poin rekomendasi 1)
- (poin rekomendasi 2)

- Setiap section WAJIB berupa daftar poin (bullet), bukan paragraf. Tiap baris diawali "- ".
- Setiap section 3-5 poin singkat (1-2 kalimat per poin).
- Gunakan data statistik sebagai dasar, jangan hanya menyebut angka mentah.
- Jika total entri 0, jelaskan bahwa belum ada data dan berikan 2-3 saran pemantauan umum dalam bentuk poin.
{$aspekInstruction}
PROMPT;

        $response = Http::withToken($this->apiKey)
            ->timeout(60)
            ->post("{$this->baseUrl}/chat/completions", [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 2048,
                'temperature' => 0.7,
            ]);

        if (! $response->successful()) {
            $this->throwApiError($response);
        }

        $content = trim($this->extractCompletionContent($response));

        if ($content === '') {
            throw new \RuntimeException('AI mengembalikan respons kosong.');
        }

        return $content;
    }

    /**
     * Multi-turn chat completion.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     */
    public function chatCompletion(array $messages, int $maxTokens = 1024): string
    {
        $response = Http::withToken($this->apiKey)
            ->timeout(60)
            ->post("{$this->baseUrl}/chat/completions", [
                'model' => $this->model,
                'messages' => $messages,
                'max_tokens' => $maxTokens,
                'temperature' => 0.7,
            ]);

        if (! $response->successful()) {
            $this->throwApiError($response);
        }

        $content = trim($this->extractCompletionContent($response));

        if ($content === '') {
            throw new \RuntimeException('AI mengembalikan respons kosong.');
        }

        return $content;
    }

    /**
     * Generate structured persona fields for a PAUD school AI function.
     *
     * @return array<string, mixed>
     */
    public function generatePersonaFields(string $sekolahName, string $scope, ?string $brief = null): array
    {
        $briefBlock = filled($brief)
            ? "\nDeskripsi sekolah dari admin:\n{$brief}\n"
            : '';

        $scopeContext = AiPersonaScope::generateContext($scope);
        $defaultRole = AiPersonaScope::defaultRoleTitle($scope);

        $prompt = <<<PROMPT
Buat persona AI untuk PAUD / daycare.

Nama sekolah: {$sekolahName}
Fungsi AI: {$scopeContext}
Judul peran default (referensi): {$defaultRole}
{$briefBlock}
Jawab HANYA dengan JSON valid (tanpa markdown, tanpa penjelasan) dengan key persis:
{
  "name": "nama persona",
  "role_title": "judul peran",
  "description": "deskripsi singkat persona",
  "gender": "perempuan atau laki_laki atau netral",
  "age": 28,
  "dialog_language": "Bahasa Indonesia",
  "personality_traits": "sifat kepribadian",
  "communication_style": "gaya komunikasi",
  "behavior_guidelines": "panduan perilaku AI",
  "background": "latar belakang persona"
}

Gunakan Bahasa Indonesia untuk teks. Field age harus angka 18-80.
Hindari sapaan kaku berulang seperti Bu/Ibu/Bapak/Ibu.
PROMPT;

        $content = $this->chatCompletion([
            ['role' => 'user', 'content' => $prompt],
        ], 1536);

        try {
            return $this->parsePersonaFields($content, $scope);
        } catch (\RuntimeException) {
            $retryPrompt = $prompt."\n\nPENTING: Balas HANYA JSON valid tanpa teks lain.";
            $retryContent = $this->chatCompletion([
                ['role' => 'user', 'content' => $retryPrompt],
            ], 1536);

            return $this->parsePersonaFields($retryContent, $scope);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function parsePersonaFields(string $content, string $scope): array
    {
        $defaults = [
            'name' => AiPersonaScope::defaultName($scope),
            'role_title' => AiPersonaScope::defaultRoleTitle($scope),
            'description' => '',
            'gender' => null,
            'age' => null,
            'dialog_language' => 'Bahasa Indonesia',
            'personality_traits' => '',
            'communication_style' => '',
            'behavior_guidelines' => '',
            'background' => '',
        ];

        $json = trim($content);
        if (preg_match('/```(?:json)?\s*([\s\S]*?)```/i', $json, $matches)) {
            $json = trim($matches[1]);
        }

        $decoded = json_decode($json, true);
        if (! is_array($decoded)) {
            if (preg_match('/\{[\s\S]*\}/', $content, $objectMatch)) {
                $decoded = json_decode($objectMatch[0], true);
            }
        }

        if (! is_array($decoded)) {
            throw new \RuntimeException('AI tidak mengembalikan JSON persona yang valid.');
        }

        $limits = [
            'name' => 120,
            'role_title' => 120,
            'description' => 2000,
            'dialog_language' => 60,
            'personality_traits' => 2000,
            'communication_style' => 2000,
            'behavior_guidelines' => 2000,
            'background' => 2000,
        ];

        $result = [];
        foreach ($defaults as $key => $default) {
            if ($key === 'gender') {
                $gender = strtolower(trim((string) ($decoded['gender'] ?? '')));
                $result['gender'] = in_array($gender, [
                    SekolahAiPersona::GENDER_PEREMPUAN,
                    SekolahAiPersona::GENDER_LAKI_LAKI,
                    SekolahAiPersona::GENDER_NETRAL,
                ], true) ? $gender : null;

                continue;
            }

            if ($key === 'age') {
                $age = $decoded['age'] ?? null;
                $age = is_numeric($age) ? (int) $age : null;
                $result['age'] = ($age !== null && $age >= 18 && $age <= 80) ? $age : null;

                continue;
            }

            $value = trim((string) ($decoded[$key] ?? $default));
            $result[$key] = mb_substr($value, 0, $limits[$key] ?? 2000);
        }

        if ($result['name'] === '') {
            $result['name'] = $defaults['name'];
        }

        return $result;
    }
}
