<?php

namespace App\Services;

use App\Models\Cashflow;
use App\Models\Rkas;
use App\Models\RkasLine;
use App\Models\RkasRealisasi;
use App\Models\SumberDana;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RkasRealisasiService
{
    /** @return array{synced: int, unallocated: array{count: int, total: float}} */
    public function sync(Rkas $rkas): array
    {
        $rkas->load(['lines.akun', 'lines.anggarans']);

        $lineByAkun = $rkas->lines->keyBy('akun_id');
        $sumberIds = SumberDana::where('sekolah_id', $rkas->sekolah_id)->aktif()->pluck('id');

        $totals = [];
        $unallocated = ['count' => 0, 'total' => 0.0];

        $cashflows = Cashflow::where('sekolah_id', $rkas->sekolah_id)
            ->whereBetween('date', [$rkas->tanggal_mulai, $rkas->tanggal_akhir])
            ->get();

        foreach ($cashflows as $cf) {
            $akunId = $cf->akun_lawan_id;
            if (! $akunId || ! $cf->sumber_dana_id || ! $sumberIds->contains($cf->sumber_dana_id)) {
                $unallocated['count']++;
                $unallocated['total'] += (float) $cf->amount;

                continue;
            }

            $line = $lineByAkun->get($akunId);
            if (! $line) {
                $unallocated['count']++;
                $unallocated['total'] += (float) $cf->amount;

                continue;
            }

            $jenis = $line->akun->jenis;
            $expectedType = $jenis === 'pendapatan' ? 'in' : 'out';
            if ($cf->type !== $expectedType) {
                continue;
            }

            $key = $line->id.'_'.$cf->sumber_dana_id;
            $totals[$key] = ($totals[$key] ?? 0) + (float) $cf->amount;
        }

        $synced = 0;
        DB::transaction(function () use ($totals, $rkas, &$synced) {
            foreach ($rkas->lines as $line) {
                foreach (SumberDana::where('sekolah_id', $rkas->sekolah_id)->aktif()->get() as $sd) {
                    $key = $line->id.'_'.$sd->id;
                    $nominal = $totals[$key] ?? 0;

                    RkasRealisasi::updateOrCreate(
                        ['rkas_line_id' => $line->id, 'sumber_dana_id' => $sd->id],
                        ['nominal_otomatis' => $nominal],
                    );
                    $synced++;
                }
            }

            $rkas->update(['synced_at' => now()]);
        });

        return ['synced' => $synced, 'unallocated' => $unallocated];
    }

    /** @return array{rows: Collection, sumberDanas: Collection, health: array} */
    public function buildLaporan(Rkas $rkas): array
    {
        $this->sync($rkas);

        $rkas->load([
            'lines.akun',
            'lines.anggarans.sumberDana',
            'lines.realisasis.sumberDana',
        ]);

        $sumberDanas = SumberDana::where('sekolah_id', $rkas->sekolah_id)->aktif()->orderBy('urutan')->get();

        $rows = $rkas->lines->sortBy(fn ($l) => $l->akun->kode)->map(function (RkasLine $line) use ($sumberDanas) {
            $cells = [];
            $totalAnggaran = 0.0;
            $totalRealisasi = 0.0;

            foreach ($sumberDanas as $sd) {
                $anggaran = (float) ($line->anggarans->firstWhere('sumber_dana_id', $sd->id)?->nominal ?? 0);
                $realisasi = (float) ($line->realisasis->firstWhere('sumber_dana_id', $sd->id)?->efektif() ?? 0);
                $cells[$sd->id] = [
                    'anggaran' => $anggaran,
                    'realisasi' => $realisasi,
                    'sisa' => $anggaran - $realisasi,
                    'persen' => $anggaran > 0 ? round($realisasi / $anggaran * 100, 1) : null,
                ];
                $totalAnggaran += $anggaran;
                $totalRealisasi += $realisasi;
            }

            return [
                'line' => $line,
                'akun' => $line->akun,
                'cells' => $cells,
                'total_anggaran' => $totalAnggaran,
                'total_realisasi' => $totalRealisasi,
                'total_sisa' => $totalAnggaran - $totalRealisasi,
            ];
        });

        return ['rows' => $rows, 'sumberDanas' => $sumberDanas, 'health' => $this->healthCheck($rkas)];
    }

    public function healthCheck(Rkas $rkas): array
    {
        $total = Cashflow::where('sekolah_id', $rkas->sekolah_id)
            ->whereBetween('date', [$rkas->tanggal_mulai, $rkas->tanggal_akhir])
            ->count();

        $unallocated = Cashflow::where('sekolah_id', $rkas->sekolah_id)
            ->whereBetween('date', [$rkas->tanggal_mulai, $rkas->tanggal_akhir])
            ->where(function ($q) {
                $q->whereNull('akun_lawan_id')->orWhereNull('sumber_dana_id');
            })
            ->count();

        return [
            'total_cashflow' => $total,
            'unallocated_count' => $unallocated,
            'unallocated_pct' => $total > 0 ? round($unallocated / $total * 100, 1) : 0,
            'synced_at' => $rkas->synced_at,
        ];
    }
}
