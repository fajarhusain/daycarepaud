<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class MonevGuruKriteriaRequest extends FormRequest
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
            'deskripsi' => 'nullable|string|max:2000',
            'bobot' => 'required|integer|min:1|max:100',
            'urutan' => 'nullable|integer|min:0|max:65535',
            'is_active' => 'nullable|boolean',
        ];
    }
}
