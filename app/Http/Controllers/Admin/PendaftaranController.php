<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\DownloadsExcel;
use App\Http\Controllers\Controller;
use App\Models\Anak;
use App\Support\PaginationPerPage;
use Illuminate\Http\Request;

class PendaftaranController extends Controller
{
    use DownloadsExcel;

    public function index(Request $request)
    {
        $sekolahId = auth()->user()->sekolah_id;
        $search = trim((string) $request->input('search'));

        $queryFor = function (string $status, array $relations) use ($sekolahId, $search) {
            return Anak::with($relations)
                ->where('sekolah_id', $sekolahId)
                ->where('status', $status)
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('name', 'like', '%'.$search.'%')
                            ->orWhere('parent_name', 'like', '%'.$search.'%')
                            ->orWhereHas('user', function ($query) use ($search) {
                                $query->where('name', 'like', '%'.$search.'%')
                                    ->orWhere('email', 'like', '%'.$search.'%');
                            });
                    });
                })
                ->latest();
        };

        $pending = $queryFor('pending', ['user.anaks'])->paginate(PaginationPerPage::resolve($request, 'pending_per_page'), ['*'], 'pending_page')->withQueryString();
        $approved = $queryFor('approved', ['user'])->paginate(PaginationPerPage::resolve($request, 'approved_per_page'), ['*'], 'approved_page')->withQueryString();
        $rejected = $queryFor('rejected', ['user'])->paginate(PaginationPerPage::resolve($request, 'rejected_per_page'), ['*'], 'rejected_page')->withQueryString();

        return view('admin.pendaftaran.index', compact('pending', 'approved', 'rejected'));
    }

    public function export(Request $request)
    {
        $sekolahId = auth()->user()->sekolah_id;

        $mapRow = fn (Anak $anak) => [
            $anak->name,
            $anak->parent_name ?? $anak->user?->name ?? '-',
            $anak->user?->email ?? '-',
            ($anak->user && $anak->user->anaks->where('status', 'approved')->isNotEmpty()) ? 'Anak Tambahan' : 'Baru',
            $anak->dob?->format('Y-m-d') ?? '-',
            $anak->catatan_ortu ?? '-',
            $anak->catatan_admin ?? '-',
            $anak->updated_at?->format('Y-m-d H:i') ?? '-',
        ];

        $pending = Anak::with(['user.anaks'])->where('sekolah_id', $sekolahId)->where('status', 'pending')->latest()->get();
        $approved = Anak::with(['user.anaks'])->where('sekolah_id', $sekolahId)->where('status', 'approved')->latest()->get();
        $rejected = Anak::with(['user.anaks'])->where('sekolah_id', $sekolahId)->where('status', 'rejected')->latest()->get();

        return $this->downloadExcelSheets([
            [
                'title' => 'Pending',
                'headings' => ['Nama Anak', 'Orang Tua', 'Email', 'Tipe', 'Tgl. Lahir', 'Catatan Ortu', 'Catatan Admin', 'Disetujui Pada'],
                'rows' => $pending->map($mapRow)->all(),
            ],
            [
                'title' => 'Approved',
                'headings' => ['Nama Anak', 'Orang Tua', 'Email', 'Tipe', 'Tgl. Lahir', 'Catatan Ortu', 'Catatan Admin', 'Disetujui Pada'],
                'rows' => $approved->map($mapRow)->all(),
            ],
            [
                'title' => 'Rejected',
                'headings' => ['Nama Anak', 'Orang Tua', 'Email', 'Tipe', 'Tgl. Lahir', 'Catatan Ortu', 'Alasan Penolakan', 'Disetujui Pada'],
                'rows' => $rejected->map($mapRow)->all(),
            ],
        ], 'pendaftaran-siswa-'.now()->format('Y-m-d').'.xlsx');
    }

    public function approve(Anak $anak)
    {
        abort_if($anak->sekolah_id !== auth()->user()->sekolah_id, 403);

        $anak->update(['status' => 'approved']);

        // Also update user's sekolah_id for role-based scoping
        $anak->user->update(['sekolah_id' => $anak->sekolah_id]);

        // Assign Orang Tua role if not already
        if (! $anak->user->hasRole('Orang Tua')) {
            $anak->user->assignRole('Orang Tua');
        }

        return back()->with('success', "Pendaftaran {$anak->name} telah disetujui! ✅");
    }

    public function reject(Request $request, Anak $anak)
    {
        abort_if($anak->sekolah_id !== auth()->user()->sekolah_id, 403);

        $request->validate(['catatan_admin' => 'nullable|string|max:500']);

        $anak->update([
            'status' => 'rejected',
            'catatan_admin' => $request->catatan_admin,
        ]);

        return back()->with('success', "Pendaftaran {$anak->name} telah ditolak.");
    }
}
