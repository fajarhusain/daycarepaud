<?php

namespace App\Http\Resources\Api\V1;

use App\Models\KegiatanRutin;
use App\Support\Api\ApiMedia;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin KegiatanRutin */
class KegiatanRutinResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tanggal' => $this->tanggal?->toDateString(),
            'aspek' => $this->aspek,
            'kegiatan' => $this->kegiatan,
            'status_pencapaian' => $this->status_pencapaian,
            'keterangan' => $this->keterangan,
            'anak' => $this->anak ? [
                'id' => $this->anak->id,
                'name' => $this->anak->name,
            ] : null,
            'pengajar' => $this->pengajar ? [
                'name' => $this->pengajar->name,
            ] : null,
            'photo' => ApiMedia::fromPath($this->photo),
        ];
    }
}
