<?php

namespace App\Http\Resources\Api\V1;

use App\Models\MonevSummary;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin MonevSummary */
class MonevSummaryResource extends JsonResource
{
    public function __construct(MonevSummary $resource, private readonly bool $detailed = false)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        $payload = [
            'id' => $this->id,
            'anak' => $this->anak ? [
                'id' => $this->anak->id,
                'name' => $this->anak->name,
            ] : null,
            'periode' => [
                'bulan' => (int) $this->bulan,
                'tahun' => (int) $this->tahun,
                'label' => $this->periodeLabel(),
            ],
            'ringkasan' => $this->ringkasan,
            'sumber' => $this->sumber,
            'generated_at' => $this->generated_at?->toIso8601String(),
            'pdf_tersedia' => true,
        ];

        if ($this->detailed) {
            $payload['data_snapshot'] = $this->data_snapshot;
        }

        return $payload;
    }
}
