<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Anak;
use App\Support\Api\ApiMedia;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Anak */
class PendaftaranAnakResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'dob' => $this->dob?->toDateString(),
            'status' => $this->status,
            'sekolah' => $this->sekolah ? [
                'id' => $this->sekolah->id,
                'name' => $this->sekolah->name,
            ] : null,
            'photo' => ApiMedia::fromPath($this->photo),
            'catatan_ortu' => $this->catatan_ortu,
            'catatan_admin' => $this->catatan_admin,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
