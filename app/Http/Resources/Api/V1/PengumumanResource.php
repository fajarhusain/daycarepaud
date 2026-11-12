<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Pengumuman;
use App\Support\Api\ApiMedia;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Pengumuman */
class PengumumanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $sudahDibaca = $this->relationLoaded('reads')
            ? $this->reads->isNotEmpty()
            : false;

        return [
            'id' => $this->id,
            'judul' => $this->judul,
            'kategori' => $this->kategori,
            'isi' => $this->isi,
            'gambar' => ApiMedia::fromPath($this->gambar),
            'mulai_tayang' => $this->mulai_tayang?->toDateString(),
            'selesai_tayang' => $this->selesai_tayang?->toDateString(),
            'kelas' => $this->kelas ? [
                'id' => $this->kelas->id,
                'name' => $this->kelas->name,
            ] : null,
            'sudah_dibaca' => $sudahDibaca,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
