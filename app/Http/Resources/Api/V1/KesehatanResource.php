<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Kesehatan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Kesehatan */
class KesehatanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tanggal_pemeriksaan' => $this->tanggal_pemeriksaan?->toDateString(),
            'berat_badan' => $this->number($this->berat_badan),
            'tinggi_badan' => $this->number($this->tinggi_badan),
            'lingkar_kepala' => $this->number($this->lingkar_kepala),
            'gigi' => $this->gigi,
            'telinga' => $this->telinga,
            'kuku' => $this->kuku,
            'alergi' => $this->alergi,
            'anak' => $this->anak ? [
                'id' => $this->anak->id,
                'name' => $this->anak->name,
            ] : null,
        ];
    }

    private function number(mixed $value): ?float
    {
        return $value === null ? null : (float) $value;
    }
}
