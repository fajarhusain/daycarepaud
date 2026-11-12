<?php

namespace App\Http\Controllers\Admin;

use App\Exports\MatrikulasiTemplateExport;
use App\Http\Controllers\Concerns\DownloadsExcel;
use App\Http\Controllers\Controller;
use App\Imports\MatrikulasiImport;
use App\Models\Matrikulasi;
use App\Support\PaginationPerPage;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MatrikulasiController extends Controller
{
    use DownloadsExcel;
    private function sekolahId(): ?int
    {
        $id = auth()->user()->sekolah_id;

        return $id !== null ? (int) $id : null;
    }

    public function index(Request $request)
    {
        $sekolah_id = $this->sekolahId();
        abort_if($sekolah_id === null, 403, 'Akun tidak terikat sekolah.');

        $matrikulasis = Matrikulasi::query()
            ->where('sekolah_id', $sekolah_id)
            ->latest()
            ->paginate(PaginationPerPage::resolve($request))->withQueryString();

        return view('admin.matrikulasi.index', compact('matrikulasis'));
    }

    public function export(Request $request)
    {
        $sekolah_id = $this->sekolahId();
        abort_if($sekolah_id === null, 403);

        $rows = Matrikulasi::query()
            ->where('sekolah_id', $sekolah_id)
            ->latest()
            ->get()
            ->map(fn (Matrikulasi $m) => [
                $m->aspek,
                $m->indicator,
                $m->description,
                $m->tujuan,
                $m->strategi,
            ])
            ->all();

        return $this->downloadExcel(
            MatrikulasiTemplateExport::headings(),
            $rows,
            'matrikulasi-'.now()->format('Y-m-d').'.xlsx',
            'Matrikulasi'
        );
    }

    public function downloadTemplate(): BinaryFileResponse
    {
        abort_if($this->sekolahId() === null, 403);

        return Excel::download(
            new MatrikulasiTemplateExport,
            'template-import-matrikulasi.xlsx'
        );
    }

    public function import(Request $request)
    {
        abort_if($this->sekolahId() === null, 403);

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120'],
        ]);

        $import = $this->runImport($request, dryRun: false);

        $successCount = $import->successCount;
        $failedRows = $import->failedRows;
        $failedCount = count($failedRows);

        if ($successCount === 0 && $failedCount === 0) {
            return redirect()
                ->route('admin.matrikulasi.index')
                ->with('warning', 'Tidak ada data yang diimport. Pastikan file berisi baris data selain header.');
        }

        $message = "Import selesai: {$successCount} berhasil";
        if ($failedCount > 0) {
            $message .= ", {$failedCount} gagal.";
        } else {
            $message .= '.';
        }

        return redirect()
            ->route('admin.matrikulasi.index')
            ->with('success', $message)
            ->with('import_errors', $failedRows);
    }

    public function testImport(Request $request)
    {
        abort_if($this->sekolahId() === null, 403);

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120'],
        ]);

        $import = $this->runImport($request, dryRun: true);
        $payload = $this->buildImportTestPayload($import);

        if ($request->expectsJson()) {
            return response()->json($payload);
        }

        return redirect()
            ->route('admin.matrikulasi.index')
            ->with('import_test', $payload);
    }

    /**
     * @return array{
     *     valid_count: int,
     *     invalid_count: int,
     *     valid_rows: array<int, string>,
     *     invalid_rows: array<int, string>,
     *     can_import: bool,
     *     message: string
     * }
     */
    protected function buildImportTestPayload(MatrikulasiImport $import): array
    {
        $validCount = $import->successCount;
        $invalidCount = count($import->failedRows);

        if ($validCount === 0 && $invalidCount === 0) {
            return [
                'valid_count' => 0,
                'invalid_count' => 0,
                'valid_rows' => [],
                'invalid_rows' => [],
                'can_import' => false,
                'message' => 'Tidak ada data yang dites. Pastikan file berisi baris data selain header.',
            ];
        }

        return [
            'valid_count' => $validCount,
            'invalid_count' => $invalidCount,
            'valid_rows' => $import->validRows,
            'invalid_rows' => $import->failedRows,
            'can_import' => $invalidCount === 0 && $validCount > 0,
            'message' => $invalidCount === 0
                ? "File siap diimport. {$validCount} baris valid."
                : "Ditemukan {$invalidCount} baris bermasalah dari ".($validCount + $invalidCount).' baris.',
        ];
    }

    protected function runImport(Request $request, bool $dryRun): MatrikulasiImport
    {
        $sekolahId = $this->sekolahId();
        abort_if($sekolahId === null, 403);

        $import = new MatrikulasiImport($sekolahId, $dryRun);

        Excel::import($import, $request->file('file'));

        return $import;
    }

    public function store(Request $request)
    {
        $sekolah_id = $this->sekolahId();
        abort_if($sekolah_id === null, 403, 'Akun tidak terikat sekolah.');

        $request->validate([
            'aspek' => 'nullable|string|max:255',
            'indicator' => 'required|string|max:255',
            'description' => 'required|string',
            'tujuan' => 'nullable|string',
            'strategi' => 'nullable|string',
        ]);

        Matrikulasi::create([
            'sekolah_id' => $sekolah_id,
            'aspek' => $request->aspek,
            'indicator' => $request->indicator,
            'description' => $request->description,
            'tujuan' => $request->tujuan,
            'strategi' => $request->strategi,
        ]);

        return redirect()->route('admin.matrikulasi.index')->with('success', 'Indikator matrikulasi berhasil ditambahkan.');
    }

    public function update(Request $request, Matrikulasi $matrikulasi)
    {
        $sekolah_id = $this->sekolahId();
        abort_if($sekolah_id === null, 403);
        abort_if((int) $matrikulasi->sekolah_id !== $sekolah_id, 403);

        $request->validate([
            'aspek' => 'nullable|string|max:255',
            'indicator' => 'required|string|max:255',
            'description' => 'required|string',
            'tujuan' => 'nullable|string',
            'strategi' => 'nullable|string',
        ]);

        $matrikulasi->update([
            'aspek' => $request->aspek,
            'indicator' => $request->indicator,
            'description' => $request->description,
            'tujuan' => $request->tujuan,
            'strategi' => $request->strategi,
        ]);

        return redirect()->route('admin.matrikulasi.index')->with('success', 'Indikator matrikulasi berhasil diperbarui.');
    }

    public function destroy(Matrikulasi $matrikulasi)
    {
        $sekolah_id = $this->sekolahId();
        abort_if($sekolah_id === null, 403);
        abort_if((int) $matrikulasi->sekolah_id !== $sekolah_id, 403);
        $matrikulasi->delete();

        return redirect()->route('admin.matrikulasi.index')->with('success', 'Indikator matrikulasi berhasil dihapus.');
    }
}
