<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\DownloadsExcel;
use App\Http\Controllers\Controller;
use App\Exports\AkunTemplateExport;
use App\Imports\AkunImport;
use App\Models\Akun;
use App\Models\Jurnal;
use App\Models\JurnalLine;
use App\Services\AkuntansiService;
use App\Support\JenisAkun;
use App\Support\PaginationPerPage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class AkunController extends Controller
{
    use DownloadsExcel;

    public function __construct(private AkuntansiService $akuntansi) {}

    public function index(Request $request)
    {
        $sekolahId = auth()->user()->sekolah_id;

        $query = $this->baseQuery($sekolahId, $request);
        $akunList = $query->paginate(PaginationPerPage::resolve($request))->withQueryString();
        $this->attachSaldo($akunList);
        $this->attachSaldoAwal($akunList);

        $kelompokOptions = $this->distinctKelompok($sekolahId);
        $subkelompokOptions = $this->distinctSubkelompok($sekolahId, $request->input('kelompok'));
        $jenisOptions = JenisAkun::ALL;

        return view('admin.akun.index', compact(
            'akunList',
            'kelompokOptions',
            'subkelompokOptions',
            'jenisOptions',
        ));
    }

    public function export(Request $request)
    {
        $sekolahId = auth()->user()->sekolah_id;

        $rows = $this->baseQuery($sekolahId, $request)
            ->get()
            ->map(fn (Akun $a) => [
                $a->kode,
                ucfirst($a->jenis ?? '-'),
                $a->nama,
                $a->snp ?? '',
                $a->komponen ?? '',
                $a->uraian ?? '',
                $a->saldo_normal,
            ])->all();

        return $this->downloadExcel(
            ['Kode Akun', 'Jenis', 'Nama Akun', 'Kelompok', 'Subkelompok', 'Uraian', 'Saldo Normal'],
            $rows,
            'kode-rekening-'.now()->format('Y-m-d').'.xlsx',
            'Kode Rekening'
        );
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $sekolahId = auth()->user()->sekolah_id;

        if ($this->kodeExists($sekolahId, $data['kode'], $data['snp'] ?? null, $data['komponen'] ?? null)) {
            return back()->withErrors(['kode' => 'Kode akun sudah ada.']);
        }

        Akun::create($data + [
            'sekolah_id' => $sekolahId,
            'tipe' => $request->input('tipe', 'rkas'),
            'is_aktif' => true,
        ]);

        return redirect()->route('admin.akun.index')->with('success', 'Akun berhasil ditambahkan. Saldo awal hanya lewat import Excel.');
    }

    public function update(Request $request, Akun $akun)
    {
        abort_if($akun->sekolah_id !== auth()->user()->sekolah_id, 403);

        $data = $this->validated($request);

        if ($this->kodeExists($akun->sekolah_id, $data['kode'], $data['snp'] ?? null, $data['komponen'] ?? null, $akun->id)) {
            return back()->withErrors(['kode' => 'Kode akun sudah ada.']);
        }

        if ($akun->isSistem()) {
            unset($data['tipe'], $data['jenis']);
        }

        $akun->update($data);

        return redirect()->route('admin.akun.index')->with('success', 'Akun berhasil diperbarui.');
    }

    public function riwayatJurnal(Akun $akun)
    {
        abort_if($akun->sekolah_id !== auth()->user()->sekolah_id, 403);

        $rows = $this->akuntansi->riwayatJurnalAkun($akun->id, 50)->map(function (JurnalLine $line) {
            $jurnal = $line->jurnal;

            return [
                'id' => $line->id,
                'jurnal_id' => $line->jurnal_id,
                'tanggal' => $jurnal?->tanggal?->format('d/m/Y') ?? '',
                'no_jurnal' => $jurnal?->no_jurnal ?? '',
                'deskripsi' => $jurnal?->deskripsi ?? '',
                'debit' => (float) $line->debit,
                'kredit' => (float) $line->kredit,
                'show_url' => $jurnal ? route('admin.jurnal.show', $jurnal) : null,
            ];
        });

        return response()->json(['rows' => $rows->values()]);
    }

    public function destroy(Akun $akun)
    {
        abort_if($akun->sekolah_id !== auth()->user()->sekolah_id, 403);

        if ($akun->isSistem()) {
            return back()->withErrors(['akun' => 'Akun sistem tidak bisa dihapus.']);
        }

        if ($akun->masihTerpakai()) {
            $akun->update(['is_aktif' => false]);

            return redirect()->route('admin.akun.index')->with(
                'success',
                'Akun masih dipakai (jurnal, pengaturan akuntansi, RKAS, dll.) dan tidak bisa dihapus. Akun telah dinonaktifkan.'
            );
        }

        DB::transaction(function () use ($akun) {
            $jurnalAwal = Jurnal::where('source', 'saldo-awal')
                ->where('sourceable_type', Akun::class)
                ->where('sourceable_id', $akun->id)
                ->first();
            if ($jurnalAwal) {
                $this->akuntansi->hapusJurnal($jurnalAwal);
            }

            $akun->delete();
        });

        return redirect()->route('admin.akun.index')->with('success', 'Akun berhasil dihapus.');
    }

    public function importTemplate()
    {
        return Excel::download(
            new AkunTemplateExport,
            'template-import-kode-rekening.xlsx'
        );
    }

    public function testImport(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120'],
        ]);

        $import = new AkunImport((int) auth()->user()->sekolah_id, dryRun: true);
        Excel::import($import, $request->file('file'));

        $valid = $import->validCount();
        $duplicate = $import->duplicateCount();
        $invalid = $import->invalidCount();

        $trial = $import->openingTrialBalance();

        return response()->json([
            'valid_count' => $valid,
            'duplicate_count' => $duplicate,
            'invalid_count' => $invalid,
            'rows' => $import->rows,
            'opening_trial' => $trial,
            'can_import' => $valid > 0 && $invalid === 0 && $trial['balanced'],
            'message' => $this->importMessage($valid, $duplicate, $invalid),
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120'],
            'ignore_duplicates' => 'nullable|boolean',
        ]);

        $sekolahId = (int) auth()->user()->sekolah_id;
        $ignore = $request->boolean('ignore_duplicates');
        $file = $request->file('file');

        $probe = new AkunImport($sekolahId, dryRun: true);
        Excel::import($probe, $file);

        if ($probe->duplicateCount() > 0 && ! $ignore) {
            return back()->withErrors([
                'file' => 'Ada '.$probe->duplicateCount().' baris duplikat. Centang abaikan duplikat, atau perbaiki file lalu tes ulang.',
            ]);
        }

        if ($probe->validCount() === 0) {
            return back()->withErrors(['file' => 'Tidak ada baris yang bisa diimport.']);
        }

        $trial = $probe->openingTrialBalance();
        if (! $trial['balanced']) {
            return back()->withErrors([
                'file' => 'Trial balance saldo awal tidak seimbang (debit '.$trial['total_debit'].' vs kredit '.$trial['total_kredit'].'). Perbaiki file lalu tes ulang.',
            ]);
        }

        $import = new AkunImport($sekolahId, dryRun: false, ignoreDuplicates: true);
        Excel::import($import, $file);

        $message = $import->imported.' akun diimport.';
        if ($import->ignored > 0) {
            $message .= ' '.$import->ignored.' duplikat diabaikan.';
        }
        if ($import->invalidCount() > 0) {
            $message .= ' '.$import->invalidCount().' baris rusak dilewati.';
        }

        return redirect()->route('admin.akun.index')->with('success', $message);
    }

    private function importMessage(int $valid, int $duplicate, int $invalid): string
    {
        if ($valid === 0 && $duplicate === 0 && $invalid === 0) {
            return 'Tidak ada baris data. Pastikan file punya header dan isi di bawahnya.';
        }

        $parts = ["{$valid} siap diimport"];
        if ($duplicate > 0) {
            $parts[] = "{$duplicate} duplikat";
        }
        if ($invalid > 0) {
            $parts[] = "{$invalid} rusak";
        }

        return implode(', ', $parts).'.';
    }

    private function attachSaldo($akunList): void
    {
        $items = $akunList->getCollection();
        if ($items->isEmpty()) {
            return;
        }

        $totals = JurnalLine::query()
            ->whereIn('akun_id', $items->pluck('id'))
            ->selectRaw('akun_id, SUM(debit) as total_debit, SUM(kredit) as total_kredit')
            ->groupBy('akun_id')
            ->get()
            ->keyBy('akun_id');

        foreach ($items as $akun) {
            $row = $totals->get($akun->id);
            $debit = (float) ($row->total_debit ?? 0);
            $kredit = (float) ($row->total_kredit ?? 0);
            $akun->setAttribute(
                'saldo',
                $akun->saldo_normal === 'debit' ? $debit - $kredit : $kredit - $debit
            );
        }
    }

    private function attachSaldoAwal($akunList): void
    {
        $items = $akunList->getCollection();
        foreach ($items as $akun) {
            $akun->setAttribute('saldo_awal', 0);
        }
        if ($items->isEmpty()) {
            return;
        }

        $jurnals = Jurnal::query()
            ->where('source', 'saldo-awal')
            ->where('sourceable_type', Akun::class)
            ->whereIn('sourceable_id', $items->pluck('id'))
            ->with('lines')
            ->get();

        foreach ($jurnals as $jurnal) {
            $akun = $items->firstWhere('id', $jurnal->sourceable_id);
            $line = $jurnal->lines->firstWhere('akun_id', $jurnal->sourceable_id);
            if (! $akun || ! $line) {
                continue;
            }
            $amount = $akun->saldo_normal === 'debit' ? (float) $line->debit : (float) $line->kredit;
            $akun->setAttribute('saldo_awal', $amount);
        }
    }

    private function baseQuery(int $sekolahId, Request $request): Builder
    {
        $query = Akun::where('sekolah_id', $sekolahId)->aktif()->orderBy('kode');

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                    ->orWhere('nama', 'like', "%{$search}%")
                    ->orWhere('uraian', 'like', "%{$search}%")
                    ->orWhere('snp', 'like', "%{$search}%")
                    ->orWhere('komponen', 'like', "%{$search}%");
            });
        }

        if ($kelompok = $request->input('kelompok')) {
            $query->where('snp', $kelompok);
        }

        if ($subkelompok = $request->input('subkelompok')) {
            $query->where('komponen', $subkelompok);
        }

        if ($jenis = $request->input('jenis')) {
            $query->where('jenis', JenisAkun::normalize($jenis));
        }

        return $query;
    }

    /**
     * @return list<string>
     */
    private function distinctKelompok(int $sekolahId): array
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
    private function distinctSubkelompok(int $sekolahId, ?string $kelompok): array
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

    private function validated(Request $request): array
    {
        $request->merge([
            'jenis' => JenisAkun::normalize((string) $request->input('jenis')),
        ]);

        $data = $request->validate([
            'kode' => 'required|string|max:20',
            'nama' => 'required|string|max:200',
            'snp' => 'nullable|string|max:255',
            'komponen' => 'nullable|string|max:255',
            'uraian' => 'nullable|string',
            'tipe' => 'nullable|in:sistem,rkas',
            'jenis' => 'required|in:'.implode(',', JenisAkun::ALL),
            'kategori_arus_kas' => 'nullable|in:operasi,investasi,pendanaan',
            'saldo_normal' => 'required|in:debit,kredit',
            'induk_id' => 'nullable|exists:akuns,id',
            'deskripsi' => 'nullable|string',
        ]);

        $data['jenis'] = JenisAkun::normalize($data['jenis']);

        return $data;
    }

    private function kodeExists(int $sekolahId, string $kode, ?string $snp, ?string $komponen, ?int $exceptId = null): bool
    {
        $q = Akun::where('sekolah_id', $sekolahId)
            ->where('kode', $kode)
            ->where('snp', $snp)
            ->where('komponen', $komponen);

        if ($exceptId) {
            $q->where('id', '!=', $exceptId);
        }

        return $q->exists();
    }
}
