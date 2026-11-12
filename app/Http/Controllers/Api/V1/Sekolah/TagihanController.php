<?php

namespace App\Http\Controllers\Api\V1\Sekolah;

use App\Exceptions\ApiErrorCode;
use App\Exceptions\ApiException;
use App\Http\Controllers\Api\V1\Sekolah\Concerns\ResolvesOrangTuaTenant;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\TagihanResource;
use App\Models\PembayaranBulanan;
use App\Services\PembayaranInvoicePdfService;
use App\Support\Api\ApiMedia;
use App\Support\ApiResponse;
use App\Support\Pagination;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TagihanController extends Controller
{
    use ResolvesOrangTuaTenant;

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'anak_id' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],
            'periode_bulan' => ['nullable', 'integer', 'between:1,12'],
            'periode_tahun' => ['nullable', 'integer', 'between:2000,2100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $anaks = $this->approvedAnaks($request);
        $anakId = $this->requestedAnakId($request, $anaks);

        $query = $this->tagihanQuery($request)
            ->with(['anak', 'items', 'diskon'])
            ->when($anakId !== null, fn ($builder) => $builder->where('anak_id', $anakId))
            ->when($request->filled('status'), fn ($builder) => $builder->where('status', $request->query('status')))
            ->when($request->filled('periode_bulan'), fn ($builder) => $builder->where('periode_bulan', $request->integer('periode_bulan')))
            ->when($request->filled('periode_tahun'), fn ($builder) => $builder->where('periode_tahun', $request->integer('periode_tahun')))
            ->orderByDesc('periode_tahun')
            ->orderByDesc('periode_bulan')
            ->orderByDesc('id');

        $paginator = $query
            ->paginate(Pagination::offsetPerPage($request))
            ->appends($request->query());

        $data = collect($paginator->items())
            ->map(fn (PembayaranBulanan $item) => (new TagihanResource($item))->resolve())
            ->values()
            ->all();

        return ApiResponse::success(
            $data,
            Pagination::offsetMeta($paginator),
            links: Pagination::offsetLinks($paginator),
        );
    }

    public function summary(Request $request): JsonResponse
    {
        $request->validate([
            'anak_id' => ['nullable', 'integer', 'min:1'],
            'periode_bulan' => ['nullable', 'integer', 'between:1,12'],
            'periode_tahun' => ['nullable', 'integer', 'between:2000,2100'],
        ]);

        $anaks = $this->approvedAnaks($request);
        $anakId = $this->requestedAnakId($request, $anaks);
        $selectedAnaks = $anakId !== null
            ? $anaks->where('id', $anakId)->values()
            : $anaks;

        $rows = $this->tagihanQuery($request)
            ->whereIn('status', ['pending', 'rejected'])
            ->when($anakId !== null, fn ($builder) => $builder->where('anak_id', $anakId))
            ->when($request->filled('periode_bulan'), fn ($builder) => $builder->where('periode_bulan', $request->integer('periode_bulan')))
            ->when($request->filled('periode_tahun'), fn ($builder) => $builder->where('periode_tahun', $request->integer('periode_tahun')))
            ->selectRaw('anak_id, COUNT(*) as jumlah_tagihan, COALESCE(SUM(total_bayar), 0) as total_belum_dibayar')
            ->groupBy('anak_id')
            ->get()
            ->keyBy('anak_id');

        $perAnak = $selectedAnaks
            ->map(function ($anak) use ($rows) {
                $row = $rows->get($anak->id);
                $total = (float) ($row?->total_belum_dibayar ?? 0);

                return [
                    'id' => $anak->id,
                    'name' => $anak->name,
                    'jumlah_tagihan' => (int) ($row?->jumlah_tagihan ?? 0),
                    'total_belum_dibayar' => $this->money($total),
                    'total_belum_dibayar_label' => 'Rp '.number_format($total, 0, ',', '.'),
                ];
            })
            ->values()
            ->all();

        $grandTotal = (float) $selectedAnaks->sum(
            fn ($anak) => (float) ($rows->get($anak->id)?->total_belum_dibayar ?? 0)
        );
        $billCount = (int) $selectedAnaks->sum(
            fn ($anak) => (int) ($rows->get($anak->id)?->jumlah_tagihan ?? 0)
        );

        return ApiResponse::success([
            'total_belum_dibayar' => $this->money($grandTotal),
            'total_belum_dibayar_label' => 'Rp '.number_format($grandTotal, 0, ',', '.'),
            'jumlah_tagihan' => $billCount,
            'anak' => $perAnak,
        ]);
    }

    public function show(Request $request, int $sekolah, int $tagihan): JsonResponse
    {
        return ApiResponse::success(
            (new TagihanResource($this->findTagihan($request, $tagihan), true))->resolve(),
        );
    }

    public function invoice(
        Request $request,
        int $sekolah,
        int $tagihan,
        PembayaranInvoicePdfService $pdf,
    ): Response {
        $item = $this->findTagihan($request, $tagihan);

        if (! $item->isApproved()) {
            throw ApiException::make(ApiErrorCode::INVOICE_BELUM_TERSEDIA);
        }

        try {
            return $pdf->download($item);
        } catch (\Throwable $exception) {
            report($exception);

            throw ApiException::make(ApiErrorCode::PDF_TIDAK_TERSEDIA);
        }
    }

    public function uploadProof(Request $request, int $sekolah, int $tagihan): JsonResponse
    {
        $item = $this->findTagihan($request, $tagihan);

        if ($item->isApproved()) {
            throw ApiException::make(ApiErrorCode::TAGIHAN_SUDAH_LUNAS);
        }

        if (! $item->isPending() && ! $item->isRejected()) {
            throw ApiException::make(ApiErrorCode::TAGIHAN_SUDAH_DIPROSES);
        }

        $validated = $request->validate([
            'bukti_transfer' => ['required', 'file', 'max:5120'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ], [
            'bukti_transfer.required' => 'Bukti transfer wajib diunggah.',
            'bukti_transfer.max' => 'Ukuran bukti transfer maksimal 5 MB.',
            'bukti_transfer.uploaded' => 'Bukti transfer gagal diunggah. Coba lagi.',
            'catatan.max' => 'Catatan maksimal 500 karakter.',
        ]);

        $file = $request->file('bukti_transfer');
        $realPath = $file->getRealPath();
        $imageInfo = $realPath !== false ? @getimagesize($realPath) : false;
        $mimeType = is_array($imageInfo) ? ($imageInfo['mime'] ?? null) : null;

        if (! in_array($mimeType, ['image/jpeg', 'image/png'], true)) {
            throw ApiException::make(
                ApiErrorCode::BUKTI_PEMBAYARAN_TIDAK_VALID,
                fields: ['bukti_transfer' => [ApiErrorCode::message(ApiErrorCode::BUKTI_PEMBAYARAN_TIDAK_VALID)]],
            );
        }

        $path = $file->store('bukti-transfer', 'public');

        if (! is_string($path) || $path === '') {
            throw ApiException::make(ApiErrorCode::BUKTI_PEMBAYARAN_TIDAK_VALID);
        }

        $oldPath = $item->bukti_transfer;

        try {
            $updated = PembayaranBulanan::withoutSekolahScope()
                ->whereKey($item->id)
                ->whereIn('status', ['pending', 'rejected'])
                ->update([
                    'bukti_transfer' => $path,
                    'status' => 'pending',
                    'catatan_admin' => $validated['catatan'] ?? null,
                    'approved_by' => null,
                    'approved_at' => null,
                ]);
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($path);

            throw $exception;
        }

        if ($updated === 0) {
            Storage::disk('public')->delete($path);

            $current = PembayaranBulanan::withoutSekolahScope()->find($item->id);

            throw ApiException::make(
                $current?->isApproved()
                    ? ApiErrorCode::TAGIHAN_SUDAH_LUNAS
                    : ApiErrorCode::TAGIHAN_SUDAH_DIPROSES
            );
        }

        if (filled($oldPath) && $oldPath !== $path) {
            Storage::disk('public')->delete($oldPath);
        }

        $item = $this->findTagihan($request, $tagihan);

        return ApiResponse::success([
            'status' => $item->status,
            'status_label' => $item->status_label,
            'bukti_transfer' => ApiMedia::fromPath($item->bukti_transfer),
        ], [], 'Bukti pembayaran berhasil diunggah. Menunggu verifikasi admin.', 201);
    }

    public function proof(Request $request, int $sekolah, int $tagihan): JsonResponse
    {
        $item = $this->findTagihan($request, $tagihan);
        $media = ApiMedia::fromPath($item->bukti_transfer);

        if ($media === null) {
            throw ApiException::make(ApiErrorCode::DATA_TIDAK_DITEMUKAN);
        }

        return ApiResponse::success([
            'tagihan_id' => $item->id,
            'status' => $item->status,
            'status_label' => $item->status_label,
            'bukti_transfer' => $media,
            'catatan_admin' => $item->catatan_admin,
            'approved_at' => $item->approved_at?->toIso8601String(),
        ]);
    }

    private function tagihanQuery(Request $request)
    {
        $anakIds = $this->approvedAnaks($request)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return PembayaranBulanan::query()
            ->where('sekolah_id', TenantContext::requireSekolahId())
            ->whereIn('anak_id', $anakIds);
    }

    private function findTagihan(Request $request, int $id): PembayaranBulanan
    {
        $item = $this->tagihanQuery($request)
            ->with(['anak', 'items', 'diskon', 'biayaBulananSekolah'])
            ->whereKey($id)
            ->first();

        if (! $item) {
            throw ApiException::make(ApiErrorCode::DATA_TIDAK_DITEMUKAN);
        }

        return $item;
    }

    private function money(mixed $value): int|float
    {
        $number = round((float) $value, 2);

        return floor($number) === $number ? (int) $number : $number;
    }
}
