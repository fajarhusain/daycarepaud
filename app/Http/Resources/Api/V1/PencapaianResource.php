<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Pencapaian;
use App\Support\Api\ApiMedia;
use App\Support\LabelSkorPencapaian;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Pencapaian */
class PencapaianResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $sekolahId = (int) ($this->anak?->sekolah_id ?? TenantContext::sekolahId() ?? 0);

        return [
            'id' => $this->id,
            'score' => $this->score,
            'score_label' => LabelSkorPencapaian::label($this->score, $sekolahId ?: null),
            'feedback' => $this->feedback,
            'updated_at' => $this->updated_at?->toIso8601String(),
            'anak' => $this->anak ? [
                'id' => $this->anak->id,
                'name' => $this->anak->name,
            ] : null,
            'kegiatan' => $this->kegiatan ? [
                'id' => $this->kegiatan->id,
                'title' => $this->kegiatan->title,
                'date' => $this->kegiatan->date?->toDateString(),
                'media' => ApiMedia::collection(array_filter(array_merge(
                    $this->kegiatan->photos ?? [],
                    [$this->kegiatan->photo],
                ))),
            ] : null,
            'matrikulasi' => $this->matrikulasi ? [
                'aspek' => $this->matrikulasi->aspek,
                'indicator' => $this->matrikulasi->indicator,
            ] : null,
            'pengajar' => $this->pengajar ? [
                'name' => $this->pengajar->name,
            ] : null,
            'media' => ApiMedia::fromPath($this->photo),
        ];
    }
}
