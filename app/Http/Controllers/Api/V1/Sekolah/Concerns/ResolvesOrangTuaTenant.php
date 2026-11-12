<?php

namespace App\Http\Controllers\Api\V1\Sekolah\Concerns;

use App\Exceptions\ApiErrorCode;
use App\Exceptions\ApiException;
use App\Models\Anak;
use App\Models\Sekolah;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

trait ResolvesOrangTuaTenant
{
    protected function sekolah(Request $request): Sekolah
    {
        $sekolah = $request->attributes->get('sekolah');

        if (! $sekolah instanceof Sekolah) {
            throw ApiException::make(ApiErrorCode::KESALAHAN_SERVER);
        }

        return $sekolah;
    }

    /**
     * @return Collection<int, Anak>
     */
    protected function approvedAnaks(Request $request): Collection
    {
        return Anak::withoutSekolahScope()
            ->with('kelas')
            ->where('user_id', $request->user()->id)
            ->where('sekolah_id', TenantContext::requireSekolahId())
            ->where('status', 'approved')
            ->orderBy('name')
            ->get();
    }

    /**
     * @param  Collection<int, Anak>  $anaks
     */
    protected function requestedAnakId(Request $request, Collection $anaks): ?int
    {
        if (! $request->filled('anak_id')) {
            return null;
        }

        $anakId = (int) $request->input('anak_id');

        if ($anakId < 1 || ! $anaks->contains('id', $anakId)) {
            throw ApiException::make(ApiErrorCode::ANAK_TIDAK_DITEMUKAN);
        }

        return $anakId;
    }

    protected function findAnak(Request $request, int $anakId): Anak
    {
        $anak = Anak::withoutSekolahScope()
            ->with('kelas')
            ->where('user_id', $request->user()->id)
            ->where('sekolah_id', TenantContext::requireSekolahId())
            ->where('status', 'approved')
            ->whereKey($anakId)
            ->first();

        if (! $anak) {
            throw ApiException::make(ApiErrorCode::ANAK_TIDAK_DITEMUKAN);
        }

        return $anak;
    }
}
