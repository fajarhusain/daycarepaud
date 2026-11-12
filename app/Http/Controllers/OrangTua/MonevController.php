<?php

namespace App\Http\Controllers\OrangTua;

use App\Http\Controllers\Concerns\HandlesMonevPdfExport;
use App\Http\Controllers\Controller;
use App\Models\Anak;
use App\Models\MonevSummary;
use App\Models\User;
use App\Services\MonevSummaryService;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class MonevController extends Controller
{
    use HandlesMonevPdfExport;

    public function __construct(
        protected MonevSummaryService $monevService
    ) {}

    public function index(Request $request)
    {
        $sekolahId = TenantContext::requireSekolahId();
        [$tahun, $bulan] = $this->monevService->parsePeriodeFromRequest(
            $request->integer('tahun') ?: null,
            $request->integer('bulan') ?: null
        );

        $anaks = $this->approvedAnaksForActiveSekolah($request->user(), $sekolahId);

        $selectedAnak = $this->resolveSelectedAnak($anaks, $request->integer('anak_id') ?: null);

        $summary = null;
        if ($selectedAnak) {
            $summary = MonevSummary::query()
                ->where('anak_id', $selectedAnak->id)
                ->forPeriode($tahun, $bulan)
                ->first();
        }

        return view('orangtua.monev.index', compact('anaks', 'selectedAnak', 'summary', 'tahun', 'bulan'));
    }

    public function show(Anak $anak, Request $request)
    {
        $this->authorizeAnakForTenant($anak);

        [$tahun, $bulan] = $this->monevService->parsePeriodeFromRequest(
            $request->integer('tahun') ?: null,
            $request->integer('bulan') ?: null
        );

        return redirect()->route('orangtua.monev.index', [
            'anak_id' => $anak->id,
            'tahun' => $tahun,
            'bulan' => $bulan,
        ]);
    }

    public function exportPdf(Anak $anak, Request $request)
    {
        $this->authorizeAnakForTenant($anak);

        $summary = $this->resolveMonevSummaryForExport($anak, $request, $this->monevService);

        return $this->downloadMonevPdf($anak, $summary);
    }

    /**
     * @return Collection<int, Anak>
     */
    protected function approvedAnaksForActiveSekolah(User $user, int $sekolahId): Collection
    {
        return Anak::withoutSekolahScope()
            ->where('user_id', $user->id)
            ->where('sekolah_id', $sekolahId)
            ->where('status', 'approved')
            ->with('kelas')
            ->orderBy('name')
            ->get();
    }

    protected function authorizeAnakForTenant(Anak $anak): void
    {
        $user = auth()->user();
        $sekolahId = TenantContext::requireSekolahId();

        abort_unless(
            Anak::withoutSekolahScope()
                ->where('user_id', $user->id)
                ->where('id', $anak->id)
                ->where('sekolah_id', $sekolahId)
                ->where('status', 'approved')
                ->exists(),
            403
        );
    }

    /**
     * @param  Collection<int, Anak>  $anaks
     */
    protected function resolveSelectedAnak(Collection $anaks, ?int $anakId): ?Anak
    {
        if ($anaks->isEmpty()) {
            return null;
        }

        if ($anakId && $anaks->contains('id', $anakId)) {
            return $anaks->firstWhere('id', $anakId);
        }

        return $anaks->first();
    }
}
