<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Lembaga;
use App\Services\LembagaProvisioningService;
use App\Support\PaginationPerPage;
use Illuminate\Http\Request;

class LembagaController extends Controller
{
    public function __construct(
        protected LembagaProvisioningService $provisioning
    ) {}

    public function index(Request $request)
    {
        $status = $request->query('status', 'all');

        $query = Lembaga::withCount('sekolahs')->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $lembagas = $query->paginate(PaginationPerPage::resolve($request))->withQueryString();
        $pendingCount = Lembaga::where('status', Lembaga::STATUS_PENDING)->count();

        return view('superadmin.lembaga.index', compact('lembagas', 'status', 'pendingCount'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'pendiri' => 'nullable|string|max:255',
            'organisasi' => 'nullable|string|max:255',
            'no_akta' => 'nullable|string|max:255',
            'no_pengesahan' => 'nullable|string|max:255',
        ]);

        Lembaga::create(array_merge($validated, ['status' => Lembaga::STATUS_ACTIVE]));

        return redirect()->route('superadmin.lembaga.index')
            ->with('success', 'Lembaga berhasil ditambahkan.');
    }

    public function update(Request $request, Lembaga $lembaga)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'pendiri' => 'nullable|string|max:255',
            'organisasi' => 'nullable|string|max:255',
            'no_akta' => 'nullable|string|max:255',
            'no_pengesahan' => 'nullable|string|max:255',
        ]);

        $lembaga->update($validated);

        return redirect()->route('superadmin.lembaga.index')
            ->with('success', 'Data lembaga berhasil diperbarui.');
    }

    public function approve(Request $request, Lembaga $lembaga)
    {
        abort_unless($lembaga->status === Lembaga::STATUS_PENDING, 400);

        $this->provisioning->approve($lembaga, $request->user());

        return back()->with('success', 'Lembaga "'.$lembaga->name.'" disetujui dan aktif.');
    }

    public function reject(Request $request, Lembaga $lembaga)
    {
        abort_unless($lembaga->status === Lembaga::STATUS_PENDING, 400);

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $this->provisioning->reject($lembaga, $validated['rejection_reason']);

        return back()->with('success', 'Pendaftaran lembaga ditolak.');
    }

    public function destroy(Lembaga $lembaga)
    {
        if ($lembaga->sekolahs()->exists()) {
            return back()->withErrors(['lembaga' => 'Lembaga masih memiliki cabang sekolah. Hapus sekolah terlebih dahulu.']);
        }

        $lembaga->delete();

        return redirect()->route('superadmin.lembaga.index')
            ->with('success', 'Lembaga berhasil dihapus.');
    }
}
