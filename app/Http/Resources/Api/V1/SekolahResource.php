<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Sekolah;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Sekolah */
class SekolahResource extends JsonResource
{
    public function __construct(Sekolah $resource, private readonly int $jumlahAnak = 0)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'alamat' => $this->address,
            'telepon' => $this->phone,
            'jumlah_anak' => $this->jumlahAnak,
        ];
    }
}
