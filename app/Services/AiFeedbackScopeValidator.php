<?php

namespace App\Services;

use App\Models\Anak;
use App\Models\Kegiatan;
use App\Models\Matrikulasi;
use App\Models\Pengajar;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

class AiFeedbackScopeValidator
{
    public function assertAllowedRole(User $user): void
    {
        if ($user->hasAnyRole(['Admin Sekolah', 'Wali Kelas', 'Pengajar'])) {
            return;
        }

        throw new AuthorizationException('Anda tidak memiliki akses ke fitur saran AI pencapaian.');
    }

    /**
     * @return array{anak: Anak, kegiatan: Kegiatan, matrikulasi: Matrikulasi, sekolah_id: int}
     */
    public function resolve(User $user, int $anakId, int $kegiatanId, int $matrikulasiId): array
    {
        $this->assertAllowedRole($user);

        $sekolahId = (int) $user->sekolah_id;
        abort_if($sekolahId < 1, 403, 'Akun tidak terikat sekolah.');

        $anak = Anak::query()
            ->where('id', $anakId)
            ->where('sekolah_id', $sekolahId)
            ->first();

        if ($anak === null) {
            throw ValidationException::withMessages([
                'anak_id' => 'Siswa tidak ditemukan atau tidak termasuk sekolah Anda.',
            ]);
        }

        if ($user->hasRole('Pengajar') && ! $user->hasRole('Admin Sekolah') && ! $user->hasRole('Wali Kelas')) {
            $this->assertPengajarScope($anak, $user);
        }

        $kegiatan = Kegiatan::query()->find($kegiatanId);
        if ($kegiatan === null) {
            throw ValidationException::withMessages([
                'kegiatan_id' => 'Kegiatan tidak ditemukan.',
            ]);
        }

        if ((int) $anak->kelas_id !== (int) $kegiatan->kelas_id) {
            throw ValidationException::withMessages([
                'kegiatan_id' => 'Siswa dan kegiatan harus berada di kelas yang sama.',
            ]);
        }

        $matrikulasi = Matrikulasi::query()->find($matrikulasiId);
        if ($matrikulasi === null) {
            throw ValidationException::withMessages([
                'matrikulasi_id' => 'Indikator matrikulasi tidak ditemukan.',
            ]);
        }

        $matrikulasiIds = $kegiatan->matrikulasis()->pluck('matrikulasis.id')->all();
        if (! in_array((int) $matrikulasi->id, $matrikulasiIds, true)) {
            throw ValidationException::withMessages([
                'matrikulasi_id' => 'Indikator tidak termasuk dalam kegiatan ini.',
            ]);
        }

        return [
            'anak' => $anak,
            'kegiatan' => $kegiatan,
            'matrikulasi' => $matrikulasi,
            'sekolah_id' => $sekolahId,
        ];
    }

    protected function assertPengajarScope(Anak $anak, User $user): void
    {
        $pengajar = Pengajar::query()->where('user_id', $user->id)->first();
        abort_if($pengajar === null, 403);
        abort_if((int) $anak->sekolah_id !== (int) $pengajar->sekolah_id, 403);

        $kelasIds = $pengajar->kelas()->pluck('kelas.id')->all();
        if ($kelasIds !== [] && ! in_array((int) $anak->kelas_id, $kelasIds, true)) {
            abort(403, 'Siswa tidak termasuk kelas yang Anda ampu.');
        }
    }
}
