<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Akun;
use App\Models\AkuntansiTabunganAkun;
use App\Models\Cashflow;
use App\Services\AkuntansiService;
use App\Support\PaginationPerPage;
use App\Support\StandardCoa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TabunganInvestasiController extends Controller
{
    public function __construct(
        private AkuntansiService $akuntansiService,
    ) {}

    public function index(Request $request)
    {
        $sekolahId = auth()->user()->sekolah_id;
        $bulan = (int) $request->input('bulan', now()->month);
        $tahun = (int) $request->input('tahun', now()->year);

        $tabunganAkuns = AkuntansiTabunganAkun::where('sekolah_id', $sekolahId)
            ->with('akun')
            ->get();

        $akunIds = $tabunganAkuns->pluck('akun_id')->all();

        $saldos = [];
        foreach ($tabunganAkuns as $row) {
            $saldos[$row->akun_id] = $this->akuntansiService->saldoTabunganAkun($sekolahId, $row->akun_id);
        }

        $mutasiQuery = Cashflow::where('sekolah_id', $sekolahId)
            ->where(function ($q) use ($akunIds) {
                $q->whereIn('akun_id', $akunIds)
                    ->orWhereIn('akun_lawan_id', $akunIds);
            })
            ->whereYear('date', $tahun)
            ->whereMonth('date', $bulan)
            ->with(['akun', 'akunLawan'])
            ->orderBy('date', 'desc');

        $mutasi = $akunIds === []
            ? Cashflow::whereRaw('1 = 0')->paginate(PaginationPerPage::resolve($request))
            : $mutasiQuery->paginate(PaginationPerPage::resolve($request))->withQueryString();

        $asetOptions = Akun::where('sekolah_id', $sekolahId)
            ->aktif()
            ->where('jenis', \App\Support\JenisAkun::ASSETS)
            ->orderBy('kode')
            ->get();

        $setting = $this->akuntansiService->getSetting($sekolahId);
        $selectedIds = $akunIds;

        $akunRekeningOptions = $this->akunRekeningOptions($sekolahId, $akunIds);

        return view('admin.tabungan-investasi.index', compact(
            'tabunganAkuns',
            'saldos',
            'mutasi',
            'bulan',
            'tahun',
            'asetOptions',
            'setting',
            'selectedIds',
            'akunRekeningOptions',
        ));
    }

    public function updateSettings(Request $request)
    {
        $sekolahId = auth()->user()->sekolah_id;

        $data = $request->validate([
            'akun_id' => 'nullable|array',
            'akun_id.*' => 'integer|exists:akuns,id',
        ]);

        $ids = $data['akun_id'] ?? [];

        $validIds = Akun::where('sekolah_id', $sekolahId)
            ->aktif()
            ->where('jenis', \App\Support\JenisAkun::ASSETS)
            ->whereIn('id', $ids)
            ->pluck('id')
            ->all();

        DB::transaction(function () use ($sekolahId, $validIds) {
            AkuntansiTabunganAkun::where('sekolah_id', $sekolahId)
                ->whereNotIn('akun_id', $validIds)
                ->delete();

            foreach ($validIds as $akunId) {
                AkuntansiTabunganAkun::firstOrCreate([
                    'sekolah_id' => $sekolahId,
                    'akun_id' => $akunId,
                ]);
            }
        });

        return redirect()->route('admin.tabungan-investasi.index')
            ->with('success', 'Akun tabungan/investasi berhasil disimpan.');
    }

    public function mutasi(Request $request)
    {
        $sekolahId = auth()->user()->sekolah_id;

        $data = $request->validate([
            'aksi' => 'required|in:setor,tarik',
            'akun_tabungan_id' => 'required|integer|exists:akuns,id',
            'akun_rekening_id' => 'required|integer|exists:akuns,id',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'description' => 'nullable|string|max:500',
        ]);

        $tabunganIds = AkuntansiTabunganAkun::where('sekolah_id', $sekolahId)
            ->pluck('akun_id')
            ->all();

        abort_unless(
            in_array((int) $data['akun_tabungan_id'], $tabunganIds, true),
            422,
            'Akun tabungan tidak terdaftar di pengaturan.'
        );

        $allowedRekening = $this->akunRekeningOptions($sekolahId, $tabunganIds)->pluck('id')->all();
        abort_unless(
            in_array((int) $data['akun_rekening_id'], $allowedRekening, true),
            422,
            'Akun sumber/tujuan tidak valid.'
        );

        abort_if(
            (int) $data['akun_rekening_id'] === (int) $data['akun_tabungan_id'],
            422,
            'Akun sumber dan tujuan tidak boleh sama.'
        );

        $tabungan = Akun::where('sekolah_id', $sekolahId)->findOrFail($data['akun_tabungan_id']);
        $rekening = Akun::where('sekolah_id', $sekolahId)->findOrFail($data['akun_rekening_id']);

        $type = $data['aksi'] === 'setor' ? 'out' : 'in';
        $deskripsi = $data['description']
            ?? ($data['aksi'] === 'setor'
                ? 'Setor ke '.$tabungan->nama.' dari '.$rekening->nama
                : 'Tarik dari '.$tabungan->nama.' ke '.$rekening->nama);

        DB::transaction(function () use ($sekolahId, $data, $type, $deskripsi, $tabungan, $rekening) {
            $cashflow = Cashflow::create([
                'sekolah_id' => $sekolahId,
                'date' => $data['date'],
                'type' => $type,
                'amount' => $data['amount'],
                'description' => $deskripsi,
                'akun_id' => $rekening->id,
                'akun_lawan_id' => $tabungan->id,
            ]);

            $this->akuntansiService->buatJurnalDariCashflow($cashflow);
        });

        return redirect()->route('admin.tabungan-investasi.index', [
            'bulan' => \Carbon\Carbon::parse($data['date'])->month,
            'tahun' => \Carbon\Carbon::parse($data['date'])->year,
        ])->with('success', 'Mutasi tabungan berhasil dicatat.');
    }

    /** Kas/bank + akun tabungan terdaftar (untuk sumber setor / tujuan tarik). */
    private function akunRekeningOptions(int $sekolahId, array $tabunganAkunIds)
    {
        return Akun::where('sekolah_id', $sekolahId)
            ->aktif()
            ->where('jenis', \App\Support\JenisAkun::ASSETS)
            ->where(function ($q) use ($tabunganAkunIds) {
                $q->where(function ($q2) {
                    $q2->whereIn('kode', StandardCoa::KAS_BANK_KODES);
                });
                if ($tabunganAkunIds !== []) {
                    $q->orWhereIn('id', $tabunganAkunIds);
                }
            })
            ->orderBy('kode')
            ->get();
    }
}
