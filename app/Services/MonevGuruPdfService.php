<?php

namespace App\Services;

use App\Models\MonevGuruEvaluasi;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class MonevGuruPdfService
{
    public function download(MonevGuruEvaluasi $evaluasi): Response
    {
        $evaluasi->loadMissing(['pengajar', 'evaluator', 'sekolah', 'items.kriteria']);

        $pdf = Pdf::loadView('monev-guru.pdf', [
            'evaluasi' => $evaluasi,
        ])->setPaper('a4', 'portrait');

        $nama = preg_replace('/[^a-zA-Z0-9_-]+/', '-', $evaluasi->pengajar->name ?? 'guru');
        $filename = 'monev-guru-'.trim($nama, '-').'-'.$evaluasi->id.'.pdf';

        return $pdf->download($filename);
    }
}
