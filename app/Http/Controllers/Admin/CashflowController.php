<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\DownloadsExcel;
use App\Http\Controllers\Controller;
use App\Models\Akun;
use App\Models\Cashflow;
use App\Models\SumberDana;
use App\Services\AkuntansiService;
use App\Services\KwitansiService;
use App\Support\PaginationPerPage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CashflowController extends Controller
{
    use DownloadsExcel;

    public function __construct(
        private AkuntansiService $akuntansiService,
        private KwitansiService $kwitansiService,
    ) {}

    public function index(Request $request)
    {
        $sekolahId = auth()->user()->sekolah_id;
        $bulan = $request->filled('bulan') ? (int) $request->input('bulan') : null;
        $tahun = $request->filled('tahun') ? (int) $request->input('tahun') : null;

        $filtered = $this->filteredCashflowQuery($sekolahId, $request);

        $cashflows = (clone $filtered)
            ->with(['akun', 'akunLawan', 'sumberDana', 'jurnal.lines.akun'])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(PaginationPerPage::resolve($request))->withQueryString();

        $totalIn = (float) (clone $filtered)->where('type', 'in')->sum('amount');
        $totalOut = (float) (clone $filtered)->where('type', 'out')->sum('amount');

        $balance = (float) Cashflow::where('sekolah_id', $sekolahId)->where('type', 'in')->sum('amount')
            - (float) Cashflow::where('sekolah_id', $sekolahId)->where('type', 'out')->sum('amount');

        $summaryArusKas = (clone $filtered)
            ->with(['akun', 'akunLawan'])
            ->get()
            ->groupBy(fn (Cashflow $c) => $c->akunLawan?->kategori_arus_kas
                ?? $c->akun?->kategori_arus_kas
                ?? 'tidak_diketahui');

        $netPeriod = $totalIn - $totalOut;
        $filterPeriodLabel = $this->cashflowFilterPeriodLabel($request, $bulan, $tahun);
        $filterType = $request->input('type', 'all');
        $arusKasBreakdown = $this->arusKasBreakdown($summaryArusKas)
            ->where('key', '!=', 'operasi')
            ->values();
        $showArusKasBreakdown = $filterType === 'all' && $arusKasBreakdown->isNotEmpty();

        $kelompokOptions = $this->distinctAkunKelompok($sekolahId);
        $subkelompokOptions = $this->distinctAkunSubkelompok($sekolahId, $request->input('kelompok'));

        $setting = $this->akuntansiService->getSetting($sekolahId);
        $akunAset = Akun::where('sekolah_id', $sekolahId)
            ->aktif()
            ->whereIn('jenis', $setting->jenisUntukAkunAset())
            ->orderBy('kode')
            ->get();
        $akunOptions = Akun::where('sekolah_id', $sekolahId)->aktif()->orderBy('kode')->get();
        $sumberDanas = SumberDana::where('sekolah_id', $sekolahId)->aktif()->orderBy('urutan')->get();

        return view('admin.cashflow.index', compact(
            'cashflows', 'totalIn', 'totalOut', 'balance', 'netPeriod', 'filterPeriodLabel',
            'filterType', 'arusKasBreakdown', 'showArusKasBreakdown',
            'bulan', 'tahun', 'akunAset', 'akunOptions', 'setting', 'sumberDanas',
            'kelompokOptions', 'subkelompokOptions',
        ));
    }

    public function export(Request $request)
    {
        $sekolahId = auth()->user()->sekolah_id;
        $bulan = $request->filled('bulan') ? (int) $request->input('bulan') : null;
        $tahun = $request->filled('tahun') ? (int) $request->input('tahun') : null;

        $cashflows = $this->filteredCashflowQuery($sekolahId, $request)
            ->with(['akun', 'akunLawan', 'sumberDana'])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $rows = $cashflows->map(fn (Cashflow $c) => [
            $c->date?->format('Y-m-d') ?? '-',
            $c->akun?->nama ?? '-',
            $c->description ?? '-',
            $c->type === 'in' ? 'Masuk' : 'Keluar',
            (float) $c->amount,
        ])->all();

        return $this->downloadExcel(
            ['Tanggal', 'Akun', 'Keterangan', 'Jenis', 'Nominal (Rp)'],
            $rows,
            $bulan && $tahun
                ? sprintf('cashflow-%02d-%d.xlsx', $bulan, $tahun)
                : 'cashflow-semua-'.now()->format('Y-m-d').'.xlsx',
            'Cashflow',
            [4],
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'type' => 'required|in:in,out',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string',
            'akun_id' => 'nullable|exists:akuns,id',
            'akun_lawan_id' => 'nullable|exists:akuns,id',
            'sumber_dana_id' => 'nullable|exists:sumber_danas,id',
        ]);

        DB::transaction(function () use ($request) {
            $cashflow = Cashflow::create([
                'sekolah_id' => auth()->user()->sekolah_id,
                'date' => $request->date,
                'type' => $request->type,
                'amount' => $request->amount,
                'description' => $request->description,
                'akun_id' => $request->akun_id,
                'akun_lawan_id' => $request->akun_lawan_id,
                'sumber_dana_id' => $request->type === 'out' ? $request->sumber_dana_id : null,
            ]);

            $this->akuntansiService->buatJurnalDariCashflow($cashflow);
        });

        return redirect()->route('admin.cashflow.index', $this->cashflowIndexQueryFromRequest($request))
            ->with('success', 'Transaksi berhasil ditambahkan.');
    }

    public function update(Request $request, Cashflow $cashflow)
    {
        abort_if($cashflow->sekolah_id !== auth()->user()->sekolah_id, 403);

        $request->validate([
            'date' => 'required|date',
            'type' => 'required|in:in,out',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string',
            'akun_id' => 'nullable|exists:akuns,id',
            'akun_lawan_id' => 'nullable|exists:akuns,id',
            'sumber_dana_id' => 'nullable|exists:sumber_danas,id',
        ]);

        DB::transaction(function () use ($request, $cashflow) {
            // Kunci baris agar submit ganda tidak membuat jurnal duplikat.
            $cashflow = Cashflow::whereKey($cashflow->id)->lockForUpdate()->firstOrFail();

            if ($cashflow->jurnal_id) {
                $this->akuntansiService->hapusJurnal($cashflow->jurnal);
            }

            $cashflow->update([
                'date' => $request->date,
                'type' => $request->type,
                'amount' => $request->amount,
                'description' => $request->description,
                'akun_id' => $request->akun_id,
                'akun_lawan_id' => $request->akun_lawan_id,
                'sumber_dana_id' => $request->type === 'out' ? $request->sumber_dana_id : null,
                'jurnal_id' => null,
            ]);

            $this->akuntansiService->buatJurnalDariCashflow($cashflow->fresh());
        });

        return redirect()->route('admin.cashflow.index', $this->cashflowIndexQueryFromRequest($request))
            ->with('success', 'Transaksi berhasil diperbarui.');
    }

    public function destroy(Cashflow $cashflow, Request $request)
    {
        abort_if($cashflow->sekolah_id !== auth()->user()->sekolah_id, 403);

        DB::transaction(function () use ($cashflow) {
            $cashflow = Cashflow::whereKey($cashflow->id)->lockForUpdate()->first();

            if (! $cashflow) {
                return;
            }

            if ($cashflow->jurnal_id) {
                $this->akuntansiService->hapusJurnal($cashflow->jurnal);
            }

            $cashflow->delete();
        });

        return redirect()->route('admin.cashflow.index', $this->cashflowIndexQueryFromRequest($request))
            ->with('success', 'Transaksi berhasil dihapus.');
    }

    public function kwitansiDefaults(Cashflow $cashflow): JsonResponse
    {
        abort_if($cashflow->sekolah_id !== auth()->user()->sekolah_id, 403);

        return response()->json($this->kwitansiService->defaultsFromCashflow($cashflow));
    }

    public function kwitansiPdf(Request $request, Cashflow $cashflow)
    {
        abort_if($cashflow->sekolah_id !== auth()->user()->sekolah_id, 403);

        $data = $request->validate($this->kwitansiService->validationRules());

        return $this->kwitansiService->download(
            $data,
            $this->kwitansiService->jenisForCashflow($cashflow)
        );
    }

    private function filteredCashflowQuery(int $sekolahId, Request $request): Builder
    {
        $query = Cashflow::where('sekolah_id', $sekolahId);
        $this->applyPeriodFilter($query, $request);
        $this->applyTypeFilter($query, $request);
        $this->applyKelompokFilter($query, $request);

        return $query;
    }

    /** @param  \Illuminate\Support\Collection<string, \Illuminate\Support\Collection<int, Cashflow>>  $summaryArusKas */
    private function arusKasBreakdown(\Illuminate\Support\Collection $summaryArusKas): \Illuminate\Support\Collection
    {
        $labels = [
            'operasi' => 'Operasi',
            'investasi' => 'Investasi',
            'pendanaan' => 'Pendanaan',
        ];

        return collect($labels)->map(function (string $label, string $key) use ($summaryArusKas) {
            $group = $summaryArusKas->get($key, collect());
            $in = (float) $group->where('type', 'in')->sum('amount');
            $out = (float) $group->where('type', 'out')->sum('amount');

            return [
                'key' => $key,
                'label' => $label,
                'in' => $in,
                'out' => $out,
                'net' => $in - $out,
            ];
        })->filter(fn (array $r) => $r['in'] >= 0.005 || $r['out'] >= 0.005)->values();
    }

    private function cashflowFilterPeriodLabel(Request $request, ?int $bulan, ?int $tahun): string
    {
        if ($request->filled('dari') && $request->filled('sampai')) {
            return $request->input('dari').' s/d '.$request->input('sampai');
        }

        if ($bulan && $tahun) {
            return \Carbon\Carbon::createFromDate($tahun, $bulan, 1)
                ->locale('id')
                ->translatedFormat('F Y');
        }

        return 'Semua periode';
    }

    private function applyPeriodFilter(Builder $query, Request $request): void
    {
        if ($request->filled('dari') && $request->filled('sampai')) {
            $query->whereBetween('date', [$request->input('dari'), $request->input('sampai')]);

            return;
        }

        if ($request->filled('bulan') && $request->filled('tahun')) {
            $query->whereYear('date', (int) $request->input('tahun'))
                ->whereMonth('date', (int) $request->input('bulan'));
        }
    }

    /** @return array<string, int|string> */
    private function cashflowIndexQueryFromRequest(Request $request): array
    {
        return array_filter([
            'bulan' => $request->input('bulan'),
            'tahun' => $request->input('tahun'),
            'dari' => $request->input('dari'),
            'sampai' => $request->input('sampai'),
            'type' => $request->input('type'),
            'kelompok' => $request->input('kelompok'),
            'subkelompok' => $request->input('subkelompok'),
        ], fn ($v) => $v !== null && $v !== '' && $v !== 'all');
    }

    private function applyTypeFilter(Builder $query, Request $request): void
    {
        $type = $request->input('type', 'all');
        if (in_array($type, ['in', 'out'], true)) {
            $query->where('type', $type);
        }
    }

    private function applyKelompokFilter(Builder $query, Request $request): void
    {
        $kelompok = $request->input('kelompok');
        $subkelompok = $request->input('subkelompok');

        if (! $kelompok && ! $subkelompok) {
            return;
        }

        $query->where(function (Builder $q) use ($kelompok, $subkelompok) {
            $q->where(function (Builder $inner) use ($kelompok, $subkelompok) {
                $inner->whereHas('akun', fn (Builder $a) => $this->scopeAkunKelompok($a, $kelompok, $subkelompok));
            })->orWhere(function (Builder $inner) use ($kelompok, $subkelompok) {
                $inner->whereHas('akunLawan', fn (Builder $a) => $this->scopeAkunKelompok($a, $kelompok, $subkelompok));
            });
        });
    }

    private function scopeAkunKelompok(Builder $query, ?string $kelompok, ?string $subkelompok): void
    {
        if ($kelompok) {
            $query->where('snp', $kelompok);
        }
        if ($subkelompok) {
            $query->where('komponen', $subkelompok);
        }
    }

    /**
     * @return list<string>
     */
    private function distinctAkunKelompok(int $sekolahId): array
    {
        return Akun::where('sekolah_id', $sekolahId)
            ->aktif()
            ->whereNotNull('snp')
            ->where('snp', '!=', '')
            ->distinct()
            ->orderBy('snp')
            ->pluck('snp')
            ->all();
    }

    /**
     * @return list<string>
     */
    private function distinctAkunSubkelompok(int $sekolahId, ?string $kelompok): array
    {
        $q = Akun::where('sekolah_id', $sekolahId)
            ->aktif()
            ->whereNotNull('komponen')
            ->where('komponen', '!=', '');

        if ($kelompok) {
            $q->where('snp', $kelompok);
        }

        return $q->distinct()->orderBy('komponen')->pluck('komponen')->all();
    }
}
