<?php

namespace App\Console\Commands;

use App\Models\Anak;
use App\Models\MonevSummary;
use App\Services\MonevSummaryService;
use Illuminate\Console\Command;

class GenerateMonevSummaries extends Command
{
    protected $signature = 'monev:generate
                            {--year= : Tahun periode (default: bulan sebelumnya)}
                            {--month= : Bulan periode 1-12 (default: bulan sebelumnya)}';

    protected $description = 'Generate ringkasan AI monev matrikulasi per siswa untuk periode bulanan';

    public function handle(MonevSummaryService $service): int
    {
        if ($this->option('year') && $this->option('month')) {
            $tahun = (int) $this->option('year');
            $bulan = (int) $this->option('month');
        } else {
            [$tahun, $bulan] = $service->previousMonthPeriode();
        }

        $this->info("Memproses monev periode {$bulan}/{$tahun}...");

        $anaks = Anak::withoutSekolahScope()
            ->where('status', 'approved')
            ->whereHas('sekolah', fn ($q) => $q->where('status', 'active'))
            ->orderBy('sekolah_id')
            ->orderBy('name')
            ->get();

        if ($anaks->isEmpty()) {
            $this->warn('Tidak ada siswa aktif.');

            return self::SUCCESS;
        }

        $result = $service->generateForAnaks(
            $anaks,
            $tahun,
            $bulan,
            MonevSummary::SUMBER_OTOMATIS
        );

        $this->info("Selesai: {$result['generated']} digenerate, {$result['skipped']} dilewati.");

        foreach ($result['errors'] as $error) {
            $this->error($error);
        }

        return empty($result['errors']) ? self::SUCCESS : self::FAILURE;
    }
}
