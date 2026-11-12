<?php

namespace App\Http\Requests\Admin;

use App\Models\KomponenGaji;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KomponenGajiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'nama' => 'required|string|max:255',
            'jenis' => ['required', Rule::in([KomponenGaji::JENIS_TAMBAH, KomponenGaji::JENIS_POTONG])],
            'nominal_default' => 'nullable|numeric|min:0',
            'urutan' => 'nullable|integer|min:0|max:65535',
            'is_aktif' => 'nullable|boolean',
        ];
    }
}
