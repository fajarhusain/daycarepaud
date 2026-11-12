<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Anak;
use App\Models\Sekolah;
use App\Models\User;
use App\Services\SekolahProvisioningService;
use App\Support\PaginationPerPage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class SekolahController extends Controller
{
    public function __construct(
        protected SekolahProvisioningService $provisioning
    ) {}

    public function index(Request $request)
    {
        $status = $request->query('status', 'all');

        $query = Sekolah::with('lembaga')->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $sekolahs = $query->paginate(PaginationPerPage::resolve($request))->withQueryString();
        $pendingCount = Sekolah::where('status', Sekolah::STATUS_PENDING)->count();

        $adminEmails = User::role('Admin Sekolah')
            ->whereIn('sekolah_id', $sekolahs->pluck('id'))
            ->get()
            ->keyBy('sekolah_id');

        return view('superadmin.sekolah.index', compact('sekolahs', 'status', 'pendingCount', 'adminEmails'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sekolah_name' => ['required', 'string', 'max:255'],
            'sekolah_address' => ['nullable', 'string'],
            'sekolah_phone' => ['nullable', 'string', 'max:20'],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['nullable', Rules\Password::defaults()],
        ]);

        $this->provisioning->provisionActive($validated);

        return redirect()->route('superadmin.sekolah.index')
            ->with('success', 'Sekolah dan admin berhasil ditambahkan.');
    }

    public function update(Request $request, Sekolah $sekolah)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $sekolah->update([
            'name' => $validated['name'],
            'address' => $validated['address'] ?? null,
            'phone' => $validated['phone'] ?? null,
        ]);

        return redirect()->route('superadmin.sekolah.index')
            ->with('success', 'Data sekolah berhasil diperbarui.');
    }

    public function destroy(Sekolah $sekolah)
    {
        if (Anak::withoutSekolahScope()->where('sekolah_id', $sekolah->id)->exists()) {
            return back()->withErrors(['sekolah' => 'Sekolah masih memiliki data siswa. Hapus data terkait terlebih dahulu.']);
        }

        $lembaga = $sekolah->lembaga;
        $sekolah->delete();

        if ($lembaga && $lembaga->sekolahs()->count() === 0) {
            $lembaga->delete();
        }

        return redirect()->route('superadmin.sekolah.index')
            ->with('success', 'Sekolah berhasil dihapus.');
    }

    public function approve(Request $request, Sekolah $sekolah)
    {
        abort_unless($sekolah->status === Sekolah::STATUS_PENDING, 400);

        $this->provisioning->approve($sekolah, $request->user());

        return back()->with('success', 'Sekolah "'.$sekolah->name.'" disetujui dan aktif.');
    }

    public function reject(Request $request, Sekolah $sekolah)
    {
        abort_unless($sekolah->status === Sekolah::STATUS_PENDING, 400);

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $this->provisioning->reject($sekolah, $validated['rejection_reason']);

        return back()->with('success', 'Pendaftaran sekolah ditolak.');
    }
}
