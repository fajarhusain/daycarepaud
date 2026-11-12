<?php

namespace App\Services;

use App\Http\Traits\CanUploadImage;
use App\Models\Anak;
use App\Models\User;
use Illuminate\Http\UploadedFile;

class AnakRegistrationService
{
    use CanUploadImage;

    public function createPendingForParent(
        User $parent,
        array $data,
        int $sekolahId,
        ?UploadedFile $photo = null
    ): Anak {
        return $this->createForParent($parent, $data, 'pending', $sekolahId, $photo);
    }

    public function createApprovedForParent(
        User $parent,
        array $data,
        int $sekolahId,
        ?UploadedFile $photo = null
    ): Anak {
        return $this->createForParent($parent, $data, 'approved', $sekolahId, $photo);
    }

    protected function createForParent(
        User $parent,
        array $data,
        string $status,
        int $sekolahId,
        ?UploadedFile $photo = null
    ): Anak {
        $attributes = [
            'user_id' => $parent->id,
            'sekolah_id' => $sekolahId,
            'name' => $data['name'],
            'dob' => $data['dob'] ?? null,
            'parent_name' => $parent->name,
            'status' => $status,
        ];

        if (array_key_exists('catatan_ortu', $data)) {
            $attributes['catatan_ortu'] = $data['catatan_ortu'];
        }

        foreach (['nickname', 'nik', 'alamat', 'jenis_kelamin', 'kelas_id', 'nik_bapak', 'nama_bapak', 'nik_ibu', 'nama_ibu'] as $field) {
            if (array_key_exists($field, $data)) {
                $attributes[$field] = $data[$field];
            }
        }

        if (isset($attributes['nickname'])) {
            $attributes['nickname'] = filled(trim((string) $attributes['nickname']))
                ? trim((string) $attributes['nickname'])
                : null;
        }

        if ($photo) {
            $attributes['photo'] = $this->uploadImage($photo, 'anak');
        }

        return Anak::withoutSekolahScope()->create($attributes);
    }
}
