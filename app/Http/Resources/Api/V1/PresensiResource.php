<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Presensi;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Presensi */
class PresensiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $status = strtolower(trim((string) $this->status));

        if (! in_array($status, Presensi::statusOptions(), true)) {
            $status = $this->hadir ? 'hadir' : 'alpha';
        }

        return [
            'id' => $this->id,
            'tanggal' => $this->tanggal?->toDateString(),
            'status' => $status,
            'status_label' => Presensi::labelForStatus($status),
            'keterangan' => $this->keterangan,
            'anak' => $this->anak ? [
                'id' => $this->anak->id,
                'name' => $this->anak->name,
            ] : null,
        ];
    }
}
