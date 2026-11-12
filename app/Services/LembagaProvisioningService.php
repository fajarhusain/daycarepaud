<?php

namespace App\Services;

use App\Models\Lembaga;
use App\Models\KomponenGaji;
use App\Models\MonevGuruKriteria;
use App\Models\Sekolah;
use App\Models\SkalaPencapaian;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class LembagaProvisioningService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function registerPending(array $data): Lembaga
    {
        return DB::transaction(function () use ($data) {
            $lembaga = Lembaga::create([
                'name' => $data['lembaga_name'],
                'address' => $data['lembaga_address'] ?? null,
                'phone' => $data['lembaga_phone'] ?? null,
                'pendiri' => $data['pendiri'] ?? null,
                'organisasi' => $data['organisasi'] ?? null,
                'status' => Lembaga::STATUS_PENDING,
                'slug' => $this->uniqueSlug($data['lembaga_name']),
                'contact_name' => $data['contact_name'],
                'contact_email' => $data['contact_email'],
                'contact_phone' => $data['contact_phone'] ?? null,
            ]);

            $sekolah = Sekolah::create([
                'lembaga_id' => $lembaga->id,
                'name' => $data['sekolah_name'],
                'address' => $data['sekolah_address'] ?? null,
                'phone' => $data['sekolah_phone'] ?? null,
                'status' => Sekolah::STATUS_PENDING,
                'slug' => $this->uniqueSekolahSlug($data['sekolah_name']),
            ]);

            SkalaPencapaian::seedDefaultsForSekolah($sekolah->id);
            MonevGuruKriteria::seedDefaultsForSekolah($sekolah->id);
            KomponenGaji::seedDefaultsForSekolah($sekolah->id);

            $user = User::create([
                'name' => $data['contact_name'],
                'email' => $data['contact_email'],
                'password' => Hash::make($data['password']),
                'lembaga_id' => $lembaga->id,
                'sekolah_id' => null,
            ]);

            Role::firstOrCreate(['name' => 'Lembaga', 'guard_name' => 'web']);
            app(PermissionRegistrar::class)->forgetCachedPermissions();
            $user->assignRole('Lembaga');

            return $lembaga;
        });
    }

    public function approve(Lembaga $lembaga, User $approvedBy): void
    {
        DB::transaction(function () use ($lembaga, $approvedBy) {
            $lembaga->update([
                'status' => Lembaga::STATUS_ACTIVE,
                'approved_at' => now(),
                'approved_by' => $approvedBy->id,
                'rejection_reason' => null,
            ]);

            $lembaga->sekolahs()->update(['status' => Sekolah::STATUS_ACTIVE]);
        });
    }

    public function reject(Lembaga $lembaga, string $reason): void
    {
        $lembaga->update([
            'status' => Lembaga::STATUS_REJECTED,
            'rejection_reason' => $reason,
        ]);
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'lembaga';
        $slug = $base;
        $i = 1;
        while (Lembaga::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    private function uniqueSekolahSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'sekolah';
        $slug = $base;
        $i = 1;
        while (Sekolah::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
