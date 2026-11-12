<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MonevGuruEvaluasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $sekolahId = (int) auth()->user()->sekolah_id;
        $finalize = $this->boolean('finalize');

        $skorRule = $finalize
            ? 'required|integer|min:1|max:100'
            : 'nullable|integer|min:1|max:100';

        return [
            'pengajar_id' => [
                'required',
                'integer',
                Rule::exists('pengajars', 'id')->where('sekolah_id', $sekolahId),
            ],
            'judul' => 'nullable|string|max:255',
            'periode_mulai' => 'required|date',
            'periode_selesai' => 'required|date|after_or_equal:periode_mulai',
            'catatan_umum' => 'nullable|string|max:5000',
            'rekomendasi' => 'nullable|string|max:5000',
            'items' => 'required|array|min:1',
            'items.*.kriteria_id' => 'required|integer',
            'items.*.skor' => $skorRule,
            'items.*.catatan' => 'nullable|string|max:2000',
            'finalize' => 'nullable|boolean',
        ];
    }

    /** @return array<int, array{kriteria_id: int, skor?: int|null, catatan?: string|null}> */
    public function normalizedItems(): array
    {
        return collect($this->input('items', []))
            ->map(fn (array $item) => [
                'kriteria_id' => (int) ($item['kriteria_id'] ?? 0),
                'skor' => isset($item['skor']) && $item['skor'] !== '' ? (int) $item['skor'] : null,
                'catatan' => $item['catatan'] ?? null,
            ])
            ->all();
    }
}
