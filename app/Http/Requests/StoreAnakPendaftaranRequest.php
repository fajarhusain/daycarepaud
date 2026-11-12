<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAnakPendaftaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('Orang Tua') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return self::baseRules();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public static function baseRules(bool $requireDob = true): array
    {
        $dobRule = $requireDob
            ? ['required', 'date', 'before:today']
            : ['nullable', 'date', 'before:today'];

        return [
            'name' => ['required', 'string', 'max:255'],
            'dob' => $dobRule,
            'photo' => ['nullable', 'image', 'max:2048'],
            'catatan_ortu' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public static function adminOptionalRules(): array
    {
        return [
            'nickname' => ['nullable', 'string', 'max:50'],
            'nik' => ['nullable', 'string', 'max:50'],
            'alamat' => ['nullable', 'string'],
            'jenis_kelamin' => ['nullable', 'in:Laki-laki,Perempuan'],
            'kelas_id' => ['nullable', 'exists:kelas,id'],
            'nik_bapak' => ['nullable', 'string', 'max:50'],
            'nama_bapak' => ['nullable', 'string', 'max:255'],
            'nik_ibu' => ['nullable', 'string', 'max:50'],
            'nama_ibu' => ['nullable', 'string', 'max:255'],
        ];
    }
}
