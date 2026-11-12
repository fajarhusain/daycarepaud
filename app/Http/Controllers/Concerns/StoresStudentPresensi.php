<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Anak;
use App\Models\Presensi;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

trait StoresStudentPresensi
{
  /**
   * @param  list<int>  $anakIds
   * @param  array<int|string, array{status?: string, keterangan?: string|null}>  $presensiInput
   */
    protected function persistStudentPresensi(int $sekolahId, string $tanggal, array $anakIds, array $presensiInput): void
    {
        foreach ($anakIds as $anakId) {
            $anakId = (int) $anakId;
            $row = $presensiInput[$anakId] ?? $presensiInput[(string) $anakId] ?? [];
            $status = $row['status'] ?? 'alpha';
            if (! in_array($status, Presensi::statusOptions(), true)) {
                $status = 'alpha';
            }

            $anak = Anak::find($anakId);
            if (! $anak || $anak->status !== 'approved') {
                continue;
            }

            Presensi::updateOrCreate(
                [
                    'sekolah_id' => $sekolahId,
                    'anak_id' => $anakId,
                    'tanggal' => $tanggal,
                ],
                [
                    'kelas_id' => $anak->kelas_id,
                    'hadir' => Presensi::hadirFromStatus($status),
                    'status' => $status,
                    'keterangan' => isset($row['keterangan']) && $row['keterangan'] !== ''
                        ? $row['keterangan']
                        : null,
                ]
            );
        }
    }

    /**
     * @param  list<int>  $scopeAnakIds
     */
    protected function finishPresensiSave(Request $request, int $sekolahId, string $tanggal, array $scopeAnakIds, array $presensiInput, string $redirectRoute): JsonResponse|RedirectResponse
    {
        $anakIds = $scopeAnakIds;

        if ($request->expectsJson()) {
            $submitted = array_map('intval', array_keys($presensiInput));
            $anakIds = array_values(array_intersect($scopeAnakIds, $submitted));
            abort_if($anakIds === [], 422);
        }

        $this->persistStudentPresensi($sekolahId, $tanggal, $anakIds, $presensiInput);

        if (! $request->expectsJson()) {
            return redirect()
                ->route($redirectRoute, array_filter([
                    'tanggal' => $tanggal,
                    'filter_kelas_id' => $request->input('filter_kelas_id'),
                ]))
                ->with('success', 'Presensi tanggal '.Carbon::parse($tanggal)->translatedFormat('d M Y').' berhasil disimpan.');
        }

        $anakId = $anakIds[0];
        $day = Carbon::parse($tanggal);

        return response()->json([
            'anak_id' => $anakId,
            'hadir' => Presensi::where('sekolah_id', $sekolahId)
                ->whereDate('tanggal', $tanggal)
                ->where('hadir', true)
                ->whereIn('anak_id', $scopeAnakIds)
                ->count(),
            'total' => count($scopeAnakIds),
            'hadir_bulan' => Presensi::where('sekolah_id', $sekolahId)
                ->where('anak_id', $anakId)
                ->where('hadir', true)
                ->whereBetween('tanggal', [$day->copy()->startOfMonth()->toDateString(), $day->copy()->endOfMonth()->toDateString()])
                ->count(),
        ]);
    }
}
