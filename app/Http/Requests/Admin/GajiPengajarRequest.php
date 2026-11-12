<?php

namespace App\Http\Requests\Admin;

use App\Models\KomponenGaji;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GajiPengajarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $sekolahId = auth()->user()?->sekolah_id;
        $gaji = $this->route('gaji_pengajar');

        return [
            'pengajar_id' => [
                'required',
                Rule::exists('pengajars', 'id')->where(fn ($q) => $q->where('sekolah_id', $sekolahId)),
                Rule::unique('gaji_pengajars', 'pengajar_id')
                    ->where('periode_bulan', (int) $this->input('periode_bulan'))
                    ->where('periode_tahun', (int) $this->input('periode_tahun'))
                    ->where('sekolah_id', $sekolahId)
                    ->ignore($gaji?->id),
            ],
            'periode_bulan' => 'required|integer|min:1|max:12',
            'periode_tahun' => 'required|integer|min:2000|max:2100',
            'catatan' => 'nullable|string|max:5000',
            'komponen' => 'required|array|min:1',
            'komponen.*.nama' => 'required|string|max:255',
            'komponen.*.jenis' => ['required', Rule::in([KomponenGaji::JENIS_TAMBAH, KomponenGaji::JENIS_POTONG])],
            'komponen.*.nominal' => 'required|numeric|min:0',
            'approve' => 'nullable|boolean',
        ];
    }
}
