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

class SekolahProvisioningService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function registerPending(array $data): Sekolah
    {
        return $this->provision($data, Lembaga::STATUS_PENDING, Sekolah::STATUS_PENDING);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function provisionActive(array $data): Sekolah
    {
        return $this->provision($data, Lembaga::STATUS_ACTIVE, Sekolah::STATUS_ACTIVE);
    }

    public function approve(Sekolah $sekolah, User $approvedBy): void
    {
        DB::transaction(function () use ($sekolah, $approvedBy) {
            $sekolah->update(['status' => Sekolah::STATUS_ACTIVE]);

            $lembaga = $sekolah->lembaga;
            if ($lembaga) {
                $lembaga->update([
                    'status' => Lembaga::STATUS_ACTIVE,
                    'approved_at' => now(),
                    'approved_by' => $approvedBy->id,
                    'rejection_reason' => null,
                ]);
            }
        });
    }

    public function reject(Sekolah $sekolah, string $reason): void
    {
        DB::transaction(function () use ($sekolah, $reason) {
            $sekolah->update(['status' => Sekolah::STATUS_REJECTED]);

            $lembaga = $sekolah->lembaga;
            if ($lembaga) {
                $lembaga->update([
                    'status' => Lembaga::STATUS_REJECTED,
                    'rejection_reason' => $reason,
                ]);
            }
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function provision(array $data, string $lembagaStatus, string $sekolahStatus): Sekolah
    {
        return DB::transaction(function () use ($data, $lembagaStatus, $sekolahStatus) {
            $schoolName = $data['sekolah_name'];
            $adminName = $data['admin_name'] ?? $data['contact_name'];
            $adminEmail = $data['admin_email'] ?? $data['contact_email'];
            $adminPhone = $data['admin_phone'] ?? $data['contact_phone'] ?? null;

            $lembaga = $this->createAutoLembaga($schoolName, $lembagaStatus, [
                'address' => $data['sekolah_address'] ?? null,
                'phone' => $data['sekolah_phone'] ?? null,
                'contact_name' => $adminName,
                'contact_email' => $adminEmail,
                'contact_phone' => $adminPhone,
            ]);

            $sekolah = Sekolah::create([
                'lembaga_id' => $lembaga->id,
                'name' => $schoolName,
                'address' => $data['sekolah_address'] ?? null,
                'phone' => $data['sekolah_phone'] ?? null,
                'status' => $sekolahStatus,
                'slug' => $this->uniqueSekolahSlug($schoolName),
            ]);

            SkalaPencapaian::seedDefaultsForSekolah($sekolah->id);
            MonevGuruKriteria::seedDefaultsForSekolah($sekolah->id);
            KomponenGaji::seedDefaultsForSekolah($sekolah->id);

            $password = $data['password'] ?? 'password123';

            $user = User::create([
                'name' => $adminName,
                'email' => $adminEmail,
                'password' => Hash::make($password),
                'lembaga_id' => $lembaga->id,
                'sekolah_id' => $sekolah->id,
            ]);

            Role::firstOrCreate(['name' => 'Admin Sekolah', 'guard_name' => 'web']);
            app(PermissionRegistrar::class)->forgetCachedPermissions();
            $user->assignRole('Admin Sekolah');

            return $sekolah;
        });
    }

    /**
     * @param  array<string, mixed>  $contact
     */
    private function createAutoLembaga(string $schoolName, string $status, array $contact = []): Lembaga
    {
        return Lembaga::create([
            'name' => $schoolName,
            'address' => $contact['address'] ?? null,
            'phone' => $contact['phone'] ?? null,
            'status' => $status,
            'slug' => $this->uniqueLembagaSlug($schoolName),
            'contact_name' => $contact['contact_name'] ?? null,
            'contact_email' => $contact['contact_email'] ?? null,
            'contact_phone' => $contact['contact_phone'] ?? null,
        ]);
    }

    private function uniqueLembagaSlug(string $name): string
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
