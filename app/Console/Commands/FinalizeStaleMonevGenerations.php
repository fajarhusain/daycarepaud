<?php

namespace App\Console\Commands;

use App\Services\MonevSummaryService;
use Illuminate\Console\Command;

class FinalizeStaleMonevGenerations extends Command
{
    protected $signature = 'monev:finalize-stale {--hours=3 : Jam tanpa aktivitas sebelum dianggap macet}';

    protected $description = 'Menyelesaikan proses generate Monev yang menggantung (stale)';

    public function handle(MonevSummaryService $service): int
    {
        $hours = max(1, (int) $this->option('hours'));
        $finalized = $service->finalizeStaleGenerations($hours);

        $this->info("Menyelesaikan {$finalized} proses generate yang macet.");

        return self::SUCCESS;
    }
}
