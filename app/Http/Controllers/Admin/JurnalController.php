<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\DownloadsExcel;
use App\Http\Controllers\Controller;
use App\Models\Akun;
use App\Models\Cashflow;
use App\Models\Jurnal;
use App\Services\AkuntansiService;
use App\Support\PaginationPerPage;
use App\Support\StandardCoa;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JurnalController extends Controller
{
    use DownloadsExcel;

    public function __construct(
        private AkuntansiService $akuntansiService
    ) {}

    public function index(Request $request)
    {
        $sekolahId = auth()->user()->sekolah_id;
        $bulan = (int) $request->input('bulan', now()->month);
        $tahun = (int) $request->input('tahun', now()->year);

        $jurnals = Jurnal::where('sekolah_id', $sekolahId)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->with(['lines.akun', 'createdBy'])
            ->orderBy('tanggal', 'desc')
            ->orderBy('no_jurnal', 'desc')
            ->paginate(PaginationPerPage::resolve($request))->withQueryString();

        return view('admin.jurnal.index', compact('jurnals', 'bulan', 'tahun'));
    }

    public function export(Request $request)
    {
        $sekolahId = auth()->user()->sekolah_id;
        $bulan = (int) $request->input('bulan', now()->month);
        $tahun = (int) $request->input('tahun', now()->year);

        $jurnals = Jurnal::where('sekolah_id', $sekolahId)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->with(['lines.akun', 'createdBy'])
            ->orderBy('tanggal', 'desc')
            ->orderBy('no_jurnal', 'desc')
            ->get();

        $rows = $jurnals->map(fn (Jurnal $j) => [
            $j->no_jurnal ?? '-',
            $j->tanggal?->format('Y-m-d') ?? '-',
            $j->deskripsi ?? '-',
            (float) $j->lines->sum('debit'),
            $j->sumber ?? '-',
        ])->all();

        return $this->downloadExcel(
            ['No. Jurnal', 'Tanggal', 'Deskripsi', 'Total', 'Sumber'],
            $rows,
            sprintf('jurnal-%02d-%d.xlsx', $bulan, $tahun),
            'Jurnal Umum',
            [3],
        );
    }

    public function create()
    {
        $sekolahId = auth()->user()->sekolah_id;
        $akuns = Akun::where('sekolah_id', $sekolahId)
            ->where('is_aktif', true)
            ->orderBy('kode')
            ->get()
            ->groupBy('jenis');

        return view('admin.jurnal.create', compact('akuns'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'deskripsi' => 'required|string',
            'lines' => 'required|array|min:2',
            'lines.*.akun_id' => 'required|exists:akuns,id',
            'lines.*.debit' => 'nullable|numeric|min:0',
            'lines.*.kredit' => 'nullable|numeric|min:0',
            'lines.*.keterangan' => 'nullable|string',
        ]);

        $lines = [];
        foreach ($request->lines as $line) {
            $debit = (float) ($line['debit'] ?? 0);
            $kredit = (float) ($line['kredit'] ?? 0);
            if ($debit <= 0 && $kredit <= 0) {
                continue;
            }
            $lines[] = ['akun_id' => $line['akun_id'], 'debit' => $debit, 'kredit' => $kredit, 'keterangan' => $line['keterangan'] ?? null];
        }

        if (count($lines) < 2) {
            return back()->withErrors(['lines' => 'Minimal 2 baris jurnal diperlukan.'])->withInput();
        }

        if (! $this->akuntansiService->validasiSaldo($lines)) {
            return back()->withErrors(['lines' => 'Total debit dan kredit harus sama dan lebih dari 0.'])->withInput();
        }

        $sekolahId = auth()->user()->sekolah_id;
        DB::transaction(function () use ($sekolahId, $request, $lines) {
            $lastError = null;
            $jurnal = null;

            for ($attempt = 0; $attempt < 3; $attempt++) {
                try {
                    $jurnal = Jurnal::create([
                        'sekolah_id' => $sekolahId,
                        'no_jurnal' => $this->akuntansiService->generateNoJurnal($sekolahId),
                        'tanggal' => $request->tanggal,
                        'deskripsi' => $request->deskripsi,
                        'created_by' => auth()->id(),
                        'source' => 'manual',
                    ]);
                    break;
                } catch (UniqueConstraintViolationException $e) {
                    $lastError = $e;
                }
            }

            if (! $jurnal) {
                throw $lastError;
            }

            foreach ($lines as $line) {
                $jurnal->lines()->create($line);
            }

            $akunKasIds = Akun::where('sekolah_id', $sekolahId)
                ->whereIn('kode', StandardCoa::KAS_BANK_KODES)
                ->pluck('id');

            foreach ($lines as $line) {
                if ($akunKasIds->contains($line['akun_id'])) {
                    $type = $line['debit'] > 0 ? 'in' : 'out';
                    $amount = max($line['debit'], $line['kredit']);
                    if ($amount > 0) {
                        Cashflow::create([
                            'sekolah_id' => $sekolahId,
                            'akun_id' => $line['akun_id'],
                            'jurnal_id' => $jurnal->id,
                            'type' => $type,
                            'amount' => $amount,
                            'description' => 'Jurnal: '.$request->deskripsi,
                            'date' => $request->tanggal,
                        ]);
                    }
                }
            }
        });

        return redirect()->route('admin.jurnal.index')->with('success', 'Jurnal berhasil dibuat.');
    }

    public function show(Jurnal $jurnal)
    {
        abort_if($jurnal->sekolah_id !== auth()->user()->sekolah_id, 403);

        $jurnal->load(['lines.akun', 'createdBy']);

        return view('admin.jurnal.show', compact('jurnal'));
    }

    public function destroy(Jurnal $jurnal)
    {
        abort_if($jurnal->sekolah_id !== auth()->user()->sekolah_id, 403);

        if ($this->akuntansiService->jurnalTerikatPembayaranLunas($jurnal)) {
            return back()->withErrors([
                'jurnal' => 'Jurnal ini terkait tagihan SPP yang sudah lunas dan tidak bisa dihapus.',
            ]);
        }

        Cashflow::where('jurnal_id', $jurnal->id)->delete();
        $this->akuntansiService->hapusJurnal($jurnal);

        return redirect()->route('admin.jurnal.index')->with('success', 'Jurnal berhasil dihapus.');
    }
}
