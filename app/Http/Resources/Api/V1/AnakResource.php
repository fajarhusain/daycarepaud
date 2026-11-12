<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Anak;
use App\Support\Api\ApiMedia;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Anak */
class AnakResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'nickname' => $this->nickname,
            'nik' => $this->nik,
            'jenis_kelamin' => $this->jenis_kelamin !== null
                ? strtolower((string) $this->jenis_kelamin)
                : null,
            'dob' => $this->dob?->toDateString(),
            'umur' => $this->age,
            'alamat' => $this->alamat,
            'status' => $this->status,
            'kelas' => $this->kelas ? [
                'id' => $this->kelas->id,
                'name' => $this->kelas->name,
            ] : null,
            'photo' => ApiMedia::fromPath($this->photo),
            'catatan_admin' => $this->catatan_admin,
        ];
    }
}
