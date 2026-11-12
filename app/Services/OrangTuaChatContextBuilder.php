<?php

namespace App\Services;

use App\Models\Anak;
use App\Models\Kegiatan;
use App\Models\KegiatanRutin;
use App\Models\Kesehatan;
use App\Models\MasterKegiatanRutin;
use App\Models\MenuMakanan;
use App\Models\MonevSummary;
use App\Models\Pencapaian;
use App\Models\Presensi;
use App\Models\SekolahAiChatDataAccess;
use App\Models\SekolahAiPersona;
use App\Models\User;
use App\Support\AiPersonaScope;
use App\Support\LabelSkorPencapaian;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class OrangTuaChatContextBuilder
{
    public function __construct(
        protected MonevDataAggregator $monevAggregator,
        protected AiChatDataAccessService $dataAccessService
    ) {}

    public function buildSystemPrompt(User $user): string
    {
        $access = $this->dataAccessService->resolveForSekolah((int) $user->sekolah_id);

        $anaks = Anak::query()
            ->where('user_id', $user->id)
            ->where('sekolah_id', $user->sekolah_id)
            ->where('status', 'approved')
            ->with('kelas')
            ->orderBy('name')
            ->get();

        $sekolahName = $user->sekolah?->name ?? 'PAUD';
        $scheduleBlock = $this->buildScheduleSection($anaks, $access);
        $contextBlocks = [];

        foreach ($anaks as $anak) {
            $contextBlocks[] = $this->buildAnakContext($anak, $access);
        }

        if ($access->access_menu_makanan) {
            $menuBlock = $this->buildMenuContext((int) $user->sekolah_id);
            if ($menuBlock !== '') {
                $contextBlocks[] = $menuBlock;
            }
        }

        $detailContext = $contextBlocks !== []
            ? implode("\n\n---\n\n", $contextBlocks)
            : 'Belum ada data anak aktif yang terdaftar.';

        $detailContext = Str::limit($detailContext, 8000, '…');

        $dataContext = $scheduleBlock !== ''
            ? $scheduleBlock."\n\n---\n\n".$detailContext
            : $detailContext;

        $personaPrompt = SekolahAiPersona::resolveActivePrompt(
            (int) $user->sekolah_id,
            AiPersonaScope::CHAT_ORANGTUA,
            $sekolahName
        );

        $identityLine = $personaPrompt !== null
            ? $personaPrompt
            : "Kamu adalah asisten AI PAUD / daycare yang membantu orang tua memahami perkembangan anak mereka di {$sekolahName}.";

        $styleRules = $personaPrompt !== null
            ? ''
            : "\n- Untuk sapaan singkat (mis. \"halo\"), balas 1-2 kalimat saja, natural dan tidak berlebihan formal.";

        $parentContext = $this->buildParentContext($user);
        $metaContext = $this->buildMetaContext($access);

        return <<<PROMPT
{$identityLine}

ATURAN WAJIB:
- Jawab HANYA seputar anak-anak orang tua ini di PAUD/daycare ini (perkembangan, kegiatan, kehadiran, kesehatan, monev, menu makanan, dll.).
- Tolak sopan pertanyaan di luar topik (politik, cuaca umum, pekerjaan rumah mata pelajaran SD, dll.) dan arahkan kembali ke perkembangan anak di PAUD.
- Gunakan Bahasa Indonesia yang hangat, mudah dipahami orang tua.
- Sapa orang tua dengan "Ayah/Bunda" (format aman). Jangan memanggil "Bu", "Ibu", "Bapak", atau menebak gender dari nama.
- Jawab dalam teks biasa saja. Jangan gunakan format markdown (**, *, #, bullet markdown, link markdown).
- Jika data belum tersedia, jelaskan dengan jujur dan arahkan ke menu aplikasi yang relevan (Monev, Pencapaian, Kehadiran, dll.).
- Jika pengguna menanyakan jenis data yang nonaktif di pengaturan akses data, jelaskan bahwa akses data tersebut belum diaktifkan oleh admin sekolah.
- Agenda belajar dan kegiatan rutin di konteks bisa berstatus jadwal/rencana (belum terlaksana atau belum ada pencapaian per anak)  tetap jawab sebagai rencana kegiatan kelas/anak, jangan anggap belum ada hanya karena belum ditugaskan atau belum dieksekusi.
- Bedakan jadwal/rencana dengan kegiatan yang sudah dilaksanakan bila statusnya tercantum di konteks.
- Bedakan agenda belajar dan kegiatan rutin: agenda belajar = kegiatan pembelajaran terjadwal per hari untuk kelas anak (menu Agenda Belajar); kegiatan rutin = aktivitas harian berulang per anak seperti toilet training atau makan sendiri (menu Kegiatan Rutin). Jika orang tua tanya umum "kegiatan hari ini", jawab keduanya terpisah bila keduanya ada di konteks; jika hanya salah satu yang ada, sebutkan yang tersedia dan jelaskan jenisnya.
- Jangan mengarang data yang tidak ada di konteks di bawah.{$styleRules}

{$parentContext}

{$metaContext}

DATA ANAK & SEKOLAH:
{$dataContext}
PROMPT;
    }

    protected function buildMetaContext(SekolahAiChatDataAccess $access): string
    {
        $lines = [];

        if ($access->include_tanggal) {
            $today = Carbon::today();
            $lines[] = 'Tanggal hari ini: '.$today->translatedFormat('l, d F Y');
        }

        $lines[] = $this->dataAccessService->buildAccessSummary($access);

        return implode("\n", $lines);
    }

    protected function buildParentContext(User $user): string
    {
        $name = trim($user->name) ?: 'Orang tua';

        return <<<BLOCK
ORANG TUA PENGGUNA:
- Nama: {$name}
- Panggilan aman: Ayah/Bunda (gunakan persis format ini saat menyapa, jangan tebak Ayah atau Bunda dari nama)
BLOCK;
    }

    protected function buildAnakContext(Anak $anak, SekolahAiChatDataAccess $access): string
    {
        $lines = [];
        $lines[] = '=== ANAK: '.$anak->displayName().' ===';
        $lines[] = 'Kelas: '.($anak->kelas?->name ?? 'Belum ada kelas');
        $lines[] = 'Usia: '.($anak->dob ? $anak->age : 'Tidak dicatat');

        $now = Carbon::now();

        if ($access->access_monev) {
            $monevBlock = $this->buildMonevContext($anak, $now->year, $now->month);
            if ($monevBlock !== '') {
                $lines[] = $monevBlock;
            }
        }

        if ($access->access_pencapaian) {
            $pencapaianBlock = $this->buildPencapaianContext($anak);
            if ($pencapaianBlock !== '') {
                $lines[] = $pencapaianBlock;
            }
        }

        if ($access->access_presensi) {
            $presensiBlock = $this->buildPresensiContext($anak, $now);
            if ($presensiBlock !== '') {
                $lines[] = $presensiBlock;
            }
        }

        if ($access->access_kesehatan) {
            $kesehatanBlock = $this->buildKesehatanContext($anak);
            if ($kesehatanBlock !== '') {
                $lines[] = $kesehatanBlock;
            }
        }

        return implode("\n", $lines);
    }

    /**
     * @param  Collection<int, Anak>  $anaks
     */
    protected function buildScheduleSection(Collection $anaks, SekolahAiChatDataAccess $access): string
    {
        if ($anaks->isEmpty()) {
            return '';
        }

        if (! $access->access_agenda && ! $access->access_kegiatan_rutin) {
            return '';
        }

        $blocks = [
            '=== JADWAL & RENCANA KEGIATAN ===',
            'Agenda belajar = jadwal pembelajaran kelas per hari. Kegiatan rutin = aktivitas harian berulang per anak (plus template kelas jika ada).',
        ];

        foreach ($anaks as $anak) {
            $childLines = ['-- '.$anak->displayName().' --'];

            if ($access->access_agenda) {
                $agendaBlock = $this->buildAgendaContext($anak, $access);
                if ($agendaBlock !== '') {
                    $childLines[] = $agendaBlock;
                }
            }

            if ($access->access_kegiatan_rutin) {
                $rutinBlock = $this->buildKegiatanRutinContext($anak, $access);
                if ($rutinBlock !== '') {
                    $childLines[] = $rutinBlock;
                }

                $masterBlock = $this->buildMasterKegiatanRutinPlanContext($anak);
                if ($masterBlock !== '') {
                    $childLines[] = $masterBlock;
                }
            }

            if (count($childLines) > 1) {
                $blocks[] = implode("\n", $childLines);
            }
        }

        return count($blocks) > 1 ? implode("\n\n", $blocks) : '';
    }

    protected function buildMonevContext(Anak $anak, int $tahun, int $bulan): string
    {
        $summary = MonevSummary::query()
            ->where('anak_id', $anak->id)
            ->forPeriode($tahun, $bulan)
            ->first();

        if (! $summary) {
            $summary = MonevSummary::query()
                ->where('anak_id', $anak->id)
                ->orderByDesc('tahun')
                ->orderByDesc('bulan')
                ->first();
        }

        if (! $summary) {
            return 'Monev: Belum ada laporan monev yang dipublikasikan.';
        }

        $ringkasan = Str::limit(strip_tags((string) $summary->ringkasan), 1500);

        return 'Monev ('.$summary->periodeLabel()."):\n".$ringkasan;
    }

    protected function buildPencapaianContext(Anak $anak): string
    {
        $items = Pencapaian::query()
            ->where('anak_id', $anak->id)
            ->with(['kegiatan', 'matrikulasi'])
            ->latest()
            ->limit(12)
            ->get();

        if ($items->isEmpty()) {
            return 'Pencapaian terbaru: Belum ada catatan pencapaian.';
        }

        $lines = ['Pencapaian terbaru:'];
        $sekolahId = (int) $anak->sekolah_id;

        foreach ($items as $p) {
            $aspek = $p->matrikulasi
                ? (($p->matrikulasi->aspek ? $p->matrikulasi->aspek.': ' : '').$p->matrikulasi->indicator)
                : '';
            $skor = LabelSkorPencapaian::scoreLabelForAi((string) $p->score, $sekolahId ?: null);
            $kegiatan = $p->kegiatan?->title ?? 'Kegiatan';
            $feedback = trim((string) ($p->feedback ?? ''));
            $line = "- {$kegiatan} | {$aspek} | {$skor}";
            if ($feedback !== '') {
                $line .= ' | Umpan balik: '.Str::limit($feedback, 120);
            }
            $lines[] = $line;
        }

        return implode("\n", $lines);
    }

    protected function buildPresensiContext(Anak $anak, Carbon $now): string
    {
        $start = $now->copy()->startOfMonth();
        $end = $now->copy()->endOfMonth();

        $records = Presensi::query()
            ->where('anak_id', $anak->id)
            ->whereBetween('tanggal', [$start, $end])
            ->get();

        if ($records->isEmpty()) {
            return 'Kehadiran bulan ini: Belum ada data presensi.';
        }

        $hadir = $records->where('hadir', true)->count();
        $total = $records->count();
        $izin = $records->where('status', 'izin')->count();
        $sakit = $records->where('status', 'sakit')->count();
        $alpha = $records->where('status', 'alpha')->count();

        return "Kehadiran bulan ini ({$now->translatedFormat('F Y')}): hadir {$hadir}/{$total}, izin {$izin}, sakit {$sakit}, alpha {$alpha}";
    }

    protected function buildKesehatanContext(Anak $anak): string
    {
        $records = Kesehatan::query()
            ->where('anak_id', $anak->id)
            ->orderByDesc('tanggal_pemeriksaan')
            ->limit(5)
            ->get();

        if ($records->isEmpty()) {
            return 'Kesehatan: Belum ada catatan kesehatan.';
        }

        $lines = ['Catatan kesehatan terbaru:'];
        foreach ($records as $r) {
            $tanggal = $r->tanggal_pemeriksaan?->format('d M Y') ?? '';
            $parts = array_filter([
                $r->berat_badan ? "BB {$r->berat_badan} kg" : null,
                $r->tinggi_badan ? "TB {$r->tinggi_badan} cm" : null,
                $r->alergi ? "Alergi: {$r->alergi}" : null,
            ]);
            $lines[] = '- '.$tanggal.': '.($parts !== [] ? implode(', ', $parts) : 'Pemeriksaan rutin');
        }

        return implode("\n", $lines);
    }

    protected function buildAgendaContext(Anak $anak, SekolahAiChatDataAccess $access): string
    {
        if (! $anak->kelas_id) {
            return 'Agenda belajar: Anak belum terdaftar di kelas.';
        }

        $today = Carbon::today();
        $from = $today->copy()->subDays($access->agenda_days_back);
        $until = $today->copy()->addDays($access->agenda_days_forward);

        $todayItems = Kegiatan::query()
            ->where('sekolah_id', $anak->sekolah_id)
            ->where('kelas_id', $anak->kelas_id)
            ->whereDate('date', $today)
            ->with(['pencapaians' => fn ($q) => $q->where('anak_id', $anak->id)])
            ->orderBy('id')
            ->get();

        $rangeItems = Kegiatan::query()
            ->where('sekolah_id', $anak->sekolah_id)
            ->where('kelas_id', $anak->kelas_id)
            ->whereBetween('date', [$from, $until])
            ->whereDate('date', '!=', $today)
            ->with(['pencapaians' => fn ($q) => $q->where('anak_id', $anak->id)])
            ->get()
            ->sortBy(fn (Kegiatan $k) => abs(Carbon::parse($k->date)->diffInDays($today)))
            ->take(20)
            ->values();

        if ($todayItems->isEmpty() && $rangeItems->isEmpty()) {
            return "Agenda belajar kelas {$anak->kelas?->name} ({$access->agenda_days_back} hari ke belakang, {$access->agenda_days_forward} hari ke depan): Tidak ada jadwal tercatat.";
        }

        $lines = ["Agenda belajar kelas {$anak->kelas?->name} (jadwal/rencana kelas, belum tentu sudah terlaksana per anak):"];

        if ($todayItems->isNotEmpty()) {
            $lines[] = 'Hari ini ('.$today->translatedFormat('d M Y').'):';
            foreach ($todayItems as $k) {
                $lines[] = $this->formatAgendaLine($k, $anak->id);
            }
        }

        if ($rangeItems->isNotEmpty()) {
            $lines[] = "Rentang {$access->agenda_days_back} hari ke belakang / {$access->agenda_days_forward} hari ke depan:";
            foreach ($rangeItems as $k) {
                $lines[] = $this->formatAgendaLine($k, $anak->id);
            }
        }

        return implode("\n", $lines);
    }

    protected function formatAgendaLine(Kegiatan $k, int $anakId): string
    {
        $date = Carbon::parse($k->date)->format('d M');
        $status = $this->kegiatanStatusLabel($k);
        $title = $k->title;
        $desc = trim((string) ($k->description ?? ''));

        $line = "- {$date}: {$title} | status: {$status}";
        if ($desc !== '') {
            $line .= ' | '.Str::limit($desc, 80);
        }

        return $line;
    }

    protected function kegiatanStatusLabel(Kegiatan $k): string
    {
        $hasPencapaian = $k->relationLoaded('pencapaians')
            ? $k->pencapaians->isNotEmpty()
            : $k->pencapaians()->exists();
        $hasPhotos = ! empty($k->photos);

        if ($hasPencapaian || $hasPhotos) {
            return 'sudah dilaksanakan';
        }

        return 'jadwal/rencana';
    }

    protected function buildKegiatanRutinContext(Anak $anak, SekolahAiChatDataAccess $access): string
    {
        $today = Carbon::today();
        $from = $today->copy()->subDays($access->kegiatan_rutin_days_back);
        $until = $today->copy()->addDays($access->kegiatan_rutin_days_forward);

        $todayItems = KegiatanRutin::query()
            ->where('anak_id', $anak->id)
            ->where('sekolah_id', $anak->sekolah_id)
            ->whereDate('tanggal', $today)
            ->orderBy('id')
            ->get();

        $rangeItems = KegiatanRutin::query()
            ->where('anak_id', $anak->id)
            ->where('sekolah_id', $anak->sekolah_id)
            ->whereBetween('tanggal', [$from, $until])
            ->whereDate('tanggal', '!=', $today)
            ->get()
            ->sortBy(fn (KegiatanRutin $r) => abs($r->tanggal->diffInDays($today)))
            ->take(20)
            ->values();

        if ($todayItems->isEmpty() && $rangeItems->isEmpty()) {
            return "Kegiatan rutin harian ({$access->kegiatan_rutin_days_back} hari ke belakang, {$access->kegiatan_rutin_days_forward} hari ke depan): Belum ada catatan harian. Lihat daftar template kegiatan rutin kelas di bawah jika ada.";
        }

        $lines = ['Kegiatan rutin harian (catatan per tanggal, bisa jadwal atau sudah diisi):'];

        if ($todayItems->isNotEmpty()) {
            $lines[] = 'Hari ini ('.$today->translatedFormat('d M Y').'):';
            foreach ($todayItems as $r) {
                $lines[] = $this->formatKegiatanRutinLine($r);
            }
        }

        if ($rangeItems->isNotEmpty()) {
            $lines[] = "Rentang {$access->kegiatan_rutin_days_back} hari ke belakang / {$access->kegiatan_rutin_days_forward} hari ke depan:";
            foreach ($rangeItems as $r) {
                $lines[] = $this->formatKegiatanRutinLine($r);
            }
        }

        return implode("\n", $lines);
    }

    protected function formatKegiatanRutinLine(KegiatanRutin $r): string
    {
        $nama = $r->kegiatan ?? $r->aspek ?? 'Kegiatan rutin';
        $status = filled($r->status_pencapaian) ? $r->status_pencapaian : 'jadwal/rencana';

        return '- '.$r->tanggal->format('d M').': '.$nama.' | status: '.$status;
    }

    protected function buildMasterKegiatanRutinPlanContext(Anak $anak): string
    {
        if (! $anak->kelas_id) {
            return '';
        }

        $masters = MasterKegiatanRutin::query()
            ->where('sekolah_id', $anak->sekolah_id)
            ->whereHas('kelas', fn ($q) => $q->where('kelas.id', $anak->kelas_id))
            ->orderBy('nama_kegiatan')
            ->get();

        if ($masters->isEmpty()) {
            return '';
        }

        $lines = ['Template kegiatan rutin kelas (rencana berulang, belum tentu sudah dicatat per hari):'];
        foreach ($masters as $m) {
            $line = '- '.$m->nama_kegiatan;
            if (filled($m->aspek)) {
                $line .= ' ('.$m->aspek.')';
            }
            $lines[] = $line;
        }

        return implode("\n", $lines);
    }

    protected function buildMenuContext(int $sekolahId): string
    {
        $start = Carbon::today()->startOfWeek();
        $end = Carbon::today()->endOfWeek();

        $menus = MenuMakanan::query()
            ->where('sekolah_id', $sekolahId)
            ->whereBetween('date', [$start, $end])
            ->orderBy('date')
            ->get();

        if ($menus->isEmpty()) {
            return 'Menu makanan minggu ini: Belum diunggah sekolah.';
        }

        $lines = ['Menu makanan minggu ini:'];
        foreach ($menus as $m) {
            $lines[] = '- '.Carbon::parse($m->date)->format('d M').': '.Str::limit((string) $m->menu, 100);
        }

        return implode("\n", $lines);
    }

    /**
     * @return Collection<int, Anak>
     */
    public function approvedAnaks(User $user): Collection
    {
        return Anak::query()
            ->where('user_id', $user->id)
            ->where('sekolah_id', $user->sekolah_id)
            ->where('status', 'approved')
            ->orderBy('name')
            ->get();
    }
}
