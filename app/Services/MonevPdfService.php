<?php

namespace App\Services;

use App\Models\Anak;
use App\Models\MonevSummary;
use App\Support\MonevSummaryPresenter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class MonevPdfService
{
    public function download(Anak $anak, MonevSummary $summary): Response
    {
        $anak->loadMissing('kelas', 'sekolah');

        $pdf = Pdf::loadView('monev.pdf', [
            'anak' => $anak,
            'summary' => $summary,
            'sections' => MonevSummaryPresenter::sections($summary),
            'scoreDist' => MonevSummaryPresenter::scoreDistribution($summary),
            'pieSegments' => MonevSummaryPresenter::pieChartSegments(
                MonevSummaryPresenter::scoreDistribution($summary)
            ),
            'perAspek' => MonevSummaryPresenter::perAspek($summary),
            'feedbacks' => MonevSummaryPresenter::feedbackSamples($summary),
            'totalEntri' => MonevSummaryPresenter::totalEntri($summary),
        ])->setPaper('a4', 'portrait');

        $filename = MonevSummaryPresenter::pdfFilename($anak, $summary);

        return $pdf->download($filename);
    }
}
