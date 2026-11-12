<?php

namespace App\Services;

use App\Models\Anak;
use App\Models\Pencapaian;
use App\Support\IndonesianMonths;
use App\Support\LabelSkorPencapaian;
use Carbon\Carbon;

class MonevDataAggregator
{
    /**
     * @return array{
     *     anak_name: string,
     *     kelas_name: string,
     *     usia: string,
     *     periode_label: string,
     *     total_entri: int,
     *     distribusi_skor: array<string, int>,
     *     per_aspek: array<string, array{jumlah: int, skor: array<string, int>}>,
     *     cuplikan_feedback: array<string>,
     *     indikator_tercatat: array<string>
     * }
     */
    public function aggregate(Anak $anak, int $tahun, int $bulan): array
    {
        $anak->loadMissing('kelas');

        $start = Carbon::create($tahun, $bulan, 1)->startOfDay();
        $end = $start->copy()->endOfMonth()->endOfDay();

        $pencapaians = Pencapaian::query()
            ->where('anak_id', $anak->id)
            ->whereBetween('created_at', [$start, $end])
            ->with(['matrikulasi'])
            ->get();

        $sekolahId = (int) $anak->sekolah_id;
        $distribusiSkor = [];
        $perAspek = [];
        $cuplikanFeedback = [];
        $indikatorTercatat = [];

        foreach ($pencapaians as $p) {
            $score = (string) ($p->score ?? '');
            if ($score !== '') {
                $distribusiSkor[$score] = ($distribusiSkor[$score] ?? 0) + 1;
            }

            $aspek = $p->matrikulasi?->aspek ?: 'Lainnya';
            if (! isset($perAspek[$aspek])) {
                $perAspek[$aspek] = ['jumlah' => 0, 'skor' => []];
            }
            $perAspek[$aspek]['jumlah']++;
            if ($score !== '') {
                $perAspek[$aspek]['skor'][$score] = ($perAspek[$aspek]['skor'][$score] ?? 0) + 1;
            }

            if ($p->matrikulasi) {
                $label = ($p->matrikulasi->aspek ? $p->matrikulasi->aspek.': ' : '')
                    .$p->matrikulasi->indicator;
                $indikatorTercatat[$label] = true;
            }

            $feedback = trim((string) ($p->feedback ?? ''));
            if ($feedback !== '' && count($cuplikanFeedback) < 5) {
                $cuplikanFeedback[] = $feedback;
            }
        }

        $distribusiLabel = [];
        foreach ($distribusiSkor as $code => $count) {
            $distribusiLabel[LabelSkorPencapaian::scoreLabelForAi($code, $sekolahId)] = $count;
        }

        $perAspekFormatted = [];
        foreach ($perAspek as $aspek => $data) {
            $skorLabel = [];
            foreach ($data['skor'] as $code => $count) {
                $skorLabel[LabelSkorPencapaian::scoreLabelForAi($code, $sekolahId)] = $count;
            }
            $perAspekFormatted[$aspek] = [
                'jumlah' => $data['jumlah'],
                'skor' => $skorLabel,
            ];
        }

        return [
            'anak_name' => $anak->displayName(),
            'kelas_name' => $anak->kelas?->name ?? '',
            'usia' => $anak->age,
            'periode_label' => $this->periodeLabel($tahun, $bulan),
            'total_entri' => $pencapaians->count(),
            'distribusi_skor' => $distribusiLabel,
            'per_aspek' => $perAspekFormatted,
            'cuplikan_feedback' => $cuplikanFeedback,
            'indikator_tercatat' => array_keys($indikatorTercatat),
        ];
    }

    public function periodeLabel(int $tahun, int $bulan): string
    {
        return IndonesianMonths::label($bulan, $tahun);
    }
}
