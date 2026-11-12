<?php

namespace App\Services;

use App\Models\Kelas;
use App\Models\Pengajar;
use App\Models\User;

class WaliKelasAssignmentService
{
    public function assign(User $user, int $kelasId): void
    {
        $pengajar = $this->ensurePengajar($user);

        $oldWaliPengajarId = Kelas::whereKey($kelasId)->value('wali_kelas_id');

        Kelas::where('wali_kelas_id', $pengajar->id)
            ->where('id', '!=', $kelasId)
            ->update(['wali_kelas_id' => null]);

        Kelas::whereKey($kelasId)->update(['wali_kelas_id' => $pengajar->id]);
        $pengajar->kelas()->syncWithoutDetaching([$kelasId]);
        $user->forceFill(['kelas_id' => $kelasId])->save();
        $user->assignRole('Wali Kelas');

        if ($oldWaliPengajarId && (int) $oldWaliPengajarId !== (int) $pengajar->id) {
            $this->removeRoleIfNoLongerWali((int) $oldWaliPengajarId);
        }
    }

    public function clear(User $user): void
    {
        $pengajar = $user->pengajar;

        if (! $pengajar) {
            $user->forceFill(['kelas_id' => null])->save();

            return;
        }

        Kelas::where('wali_kelas_id', $pengajar->id)->update(['wali_kelas_id' => null]);
        $user->forceFill(['kelas_id' => null])->save();
    }

    public function assignByPengajarId(int $pengajarId, int $kelasId): void
    {
        $pengajar = Pengajar::find($pengajarId);
        if (! $pengajar?->user) {
            return;
        }

        $this->assign($pengajar->user, $kelasId);
    }

    public function removeRoleIfNoLongerWali(int $pengajarId): void
    {
        if (Kelas::where('wali_kelas_id', $pengajarId)->exists()) {
            return;
        }

        $pengajar = Pengajar::find($pengajarId);
        if ($pengajar?->user && $pengajar->user->hasRole('Wali Kelas')) {
            $pengajar->user->removeRole('Wali Kelas');
            $pengajar->user->forceFill(['kelas_id' => null])->save();
        }
    }

    private function ensurePengajar(User $user): Pengajar
    {
        $pengajar = $user->pengajar;

        if (! $pengajar) {
            return Pengajar::create([
                'user_id' => $user->id,
                'sekolah_id' => $user->sekolah_id,
                'name' => $user->name,
                'jabatan' => 'Wali Kelas',
            ]);
        }

        $pengajar->update([
            'name' => $user->name,
            'sekolah_id' => $user->sekolah_id,
            'jabatan' => filled($pengajar->jabatan) ? $pengajar->jabatan : 'Wali Kelas',
        ]);

        return $pengajar;
    }
}
