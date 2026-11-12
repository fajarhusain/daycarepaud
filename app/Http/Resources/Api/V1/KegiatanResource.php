<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Kegiatan;
use App\Support\Api\ApiMedia;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Kegiatan */
class KegiatanResource extends JsonResource
{
    /**
     * @param  array<int, int>  $anakIds
     */
    public function __construct(Kegiatan $resource, private readonly array $anakIds = [])
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        $pencapaians = $this->pencapaians ?? collect();

        if ($this->anakIds !== []) {
            $pencapaians = $pencapaians->whereIn('anak_id', $this->anakIds)->values();
        }

        $media = ApiMedia::collection(array_filter(array_merge(
            $this->photos ?? [],
            [$this->photo],
        )));

        return [
            'id' => $this->id,
            'title' => $this->title,
            'date' => $this->date?->toDateString(),
            'description' => $this->description,
            'kelas' => $this->kelas ? [
                'id' => $this->kelas->id,
                'name' => $this->kelas->name,
            ] : null,
            'pengajar' => $this->pengajar ? [
                'id' => $this->pengajar->id,
                'name' => $this->pengajar->name,
            ] : null,
            'media' => $media,
            'is_terlaksana' => $media !== [],
            'pencapaians' => PencapaianResource::collection($pencapaians)->resolve(),
        ];
    }
}
