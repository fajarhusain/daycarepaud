<?php

namespace App\Http\Resources\Api\V1;

use App\Models\KritikSaran;
use App\Support\Api\ApiMedia;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin KritikSaran */
class MasukanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'message' => $this->message,
            'status' => $this->status,
            'sekolah' => $this->sekolah ? [
                'id' => $this->sekolah->id,
                'name' => $this->sekolah->name,
            ] : null,
            'photo' => ApiMedia::fromPath($this->photo),
            'umpan_balik' => $this->umpan_balik,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
