<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Anak;
use App\Models\MonevSummary;
use App\Services\MonevPdfService;
use App\Services\MonevSummaryService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

trait HandlesMonevPdfExport
{
    protected function downloadMonevPdf(Anak $anak, MonevSummary $summary): Response
    {
        return app(MonevPdfService::class)->download($anak, $summary);
    }

    protected function resolveMonevSummaryForExport(Anak $anak, Request $request, MonevSummaryService $service): MonevSummary
    {
        [$tahun, $bulan] = $service->parsePeriodeFromRequest(
            $request->integer('tahun') ?: null,
            $request->integer('bulan') ?: null
        );

        $summary = MonevSummary::query()
            ->where('anak_id', $anak->id)
            ->forPeriode($tahun, $bulan)
            ->first();

        abort_unless($summary, 404);

        return $summary;
    }
}
