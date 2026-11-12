<?php

namespace App\Services;

use App\Models\MonevGuruEvaluasi;
use App\Models\MonevGuruKriteria;
use App\Models\MonevGuruPenilaianItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MonevGuruService
{
    public function computeSkorKeseluruhan(MonevGuruEvaluasi $evaluasi): ?int
    {
        $evaluasi->loadMissing('items.kriteria');

        $totalBobot = 0;
        $weightedSum = 0;

        foreach ($evaluasi->items as $item) {
            if ($item->skor === null || ! $item->kriteria || ! $item->kriteria->is_active) {
                continue;
            }

            $bobot = (int) $item->kriteria->bobot;
            if ($bobot <= 0) {
                continue;
            }

            $totalBobot += $bobot;
            $weightedSum += $item->skor * $bobot;
        }

        if ($totalBobot === 0) {
            return null;
        }

        return (int) round($weightedSum / $totalBobot);
    }

    /**
     * @param  array<int, array{kriteria_id: int, skor?: int|null, catatan?: string|null}>  $items
     */
    public function syncPenilaianItems(MonevGuruEvaluasi $evaluasi, array $items): void
    {
        $kriteriaIds = MonevGuruKriteria::query()
            ->where('sekolah_id', $evaluasi->sekolah_id)
            ->where('is_active', true)
            ->pluck('id')
            ->all();

        foreach ($items as $item) {
            $kriteriaId = (int) ($item['kriteria_id'] ?? 0);
            if (! in_array($kriteriaId, $kriteriaIds, true)) {
                continue;
            }

            MonevGuruPenilaianItem::query()->updateOrCreate(
                [
                    'monev_guru_evaluasi_id' => $evaluasi->id,
                    'monev_guru_kriteria_id' => $kriteriaId,
                ],
                [
                    'skor' => isset($item['skor']) && $item['skor'] !== '' ? (int) $item['skor'] : null,
                    'catatan' => $item['catatan'] ?? null,
                ]
            );
        }

        MonevGuruPenilaianItem::query()
            ->where('monev_guru_evaluasi_id', $evaluasi->id)
            ->whereNotIn('monev_guru_kriteria_id', $kriteriaIds)
            ->delete();
    }

    public function finalize(MonevGuruEvaluasi $evaluasi): void
    {
        $evaluasi->loadMissing('items.kriteria');

        $activeKriteria = MonevGuruKriteria::query()
            ->where('sekolah_id', $evaluasi->sekolah_id)
            ->where('is_active', true)
            ->orderBy('urutan')
            ->get();

        $itemsByKriteria = $evaluasi->items->keyBy('monev_guru_kriteria_id');

        foreach ($activeKriteria as $kriteria) {
            $item = $itemsByKriteria->get($kriteria->id);
            $skor = $item?->skor;

            if ($skor === null || $skor < 1 || $skor > 100) {
                throw ValidationException::withMessages([
                    "items.{$kriteria->id}.skor" => "Skor untuk \"{$kriteria->nama}\" wajib diisi (1–100) saat finalisasi.",
                ]);
            }
        }

        $evaluasi->update([
            'skor_keseluruhan' => $this->computeSkorKeseluruhan($evaluasi),
            'status' => MonevGuruEvaluasi::STATUS_FINAL,
            'finalized_at' => now(),
        ]);
    }

    public function seedDefaultKriteria(int $sekolahId): void
    {
        MonevGuruKriteria::seedDefaultsForSekolah($sekolahId);
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, array{kriteria_id: int, skor?: int|null, catatan?: string|null}>  $items
     */
    public function storeEvaluasi(array $data, array $items, bool $finalize = false): MonevGuruEvaluasi
    {
        return DB::transaction(function () use ($data, $items, $finalize) {
            $evaluasi = MonevGuruEvaluasi::create($data);
            $this->syncPenilaianItems($evaluasi, $items);

            if ($finalize) {
                $this->finalize($evaluasi);
            } else {
                $evaluasi->update([
                    'skor_keseluruhan' => $this->computeSkorKeseluruhan($evaluasi->fresh(['items.kriteria'])),
                ]);
            }

            return $evaluasi->fresh(['items.kriteria', 'pengajar', 'evaluator']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, array{kriteria_id: int, skor?: int|null, catatan?: string|null}>  $items
     */
    public function updateEvaluasi(MonevGuruEvaluasi $evaluasi, array $data, array $items, bool $finalize = false): MonevGuruEvaluasi
    {
        if ($evaluasi->isFinal()) {
            throw ValidationException::withMessages([
                'status' => 'Evaluasi yang sudah final tidak dapat diubah.',
            ]);
        }

        return DB::transaction(function () use ($evaluasi, $data, $items, $finalize) {
            $evaluasi->update($data);
            $this->syncPenilaianItems($evaluasi, $items);

            if ($finalize) {
                $this->finalize($evaluasi);
            } else {
                $evaluasi->update([
                    'skor_keseluruhan' => $this->computeSkorKeseluruhan($evaluasi->fresh(['items.kriteria'])),
                ]);
            }

            return $evaluasi->fresh(['items.kriteria', 'pengajar', 'evaluator']);
        });
    }
}
