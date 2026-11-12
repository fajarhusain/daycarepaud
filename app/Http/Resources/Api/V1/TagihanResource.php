<?php

namespace App\Http\Resources\Api\V1;

use App\Models\PembayaranBulanan;
use App\Support\Api\ApiMedia;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PembayaranBulanan */
class TagihanResource extends JsonResource
{
    public function __construct($resource, private readonly bool $detailed = false)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'anak' => $this->anak ? [
                'id' => $this->anak->id,
                'name' => $this->anak->name,
            ] : null,
            'periode' => [
                'bulan' => $this->periode_bulan,
                'tahun' => $this->periode_tahun,
                'label' => $this->getPeriodeLabel(),
            ],
            'subtotal' => $this->money($this->subtotal),
            'nilai_diskon' => $this->money($this->nilai_diskon),
            'diskon_keterangan' => $this->diskon_keterangan ?: $this->diskon?->nama_diskon,
            'biaya_tambahan' => $this->money($this->items->sum('jumlah')),
            'total_bayar' => $this->money($this->total_bayar),
            'total_bayar_label' => $this->getTotalFormatted(),
            'status' => $this->status,
            'status_label' => $this->status_label,
            'bukti_transfer' => ApiMedia::fromPath($this->bukti_transfer),
            'catatan_admin' => $this->catatan_admin,
            'approved_at' => $this->approved_at?->toIso8601String(),
        ];

        if ($this->detailed) {
            $data['items'] = $this->items
                ->map(fn ($item) => [
                    'id' => $item->id,
                    'nama_item' => $item->nama_item,
                    'jumlah' => $this->money($item->jumlah),
                ])
                ->values()
                ->all();
            $data['biaya'] = $this->biayaBulananSekolah ? [
                'id' => $this->biayaBulananSekolah->id,
                'nama' => $this->biayaBulananSekolah->nama_biaya,
            ] : null;
            $data['hari_efektif'] = $this->hari_efektif;
            $data['hari_hadir'] = $this->hari_hadir;
            $data['biaya_per_hari'] = $this->money($this->biaya_per_hari);
            $data['is_tagihan_tambahan'] = $this->is_tagihan_tambahan;
            $data['invoice_tersedia'] = $this->isApproved();
        }

        return $data;
    }

    private function money(mixed $value): int|float
    {
        $number = round((float) $value, 2);

        return floor($number) === $number ? (int) $number : $number;
    }
}
