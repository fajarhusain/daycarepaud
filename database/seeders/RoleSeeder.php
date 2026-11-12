<?php

namespace Database\Seeders;

use App\Models\Lembaga;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Referensi  role default, lembaga/sekolah dasar, dan user @example.com.
 *
 *   php artisan db:seed --class=Database\Seeders\RoleSeeder
 *
 * Atau via ReferenceSeeder / DevSetupSeeder.
 * Additive (firstOrCreate)  tidak menghapus data, tapi bisa menambah entitas demo jika belum ada.
 */
class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Define Roles
        $roles = [
            'Superadmin',
            'Lembaga',
            'Admin Sekolah',
            'Wali Kelas',
            'Pengajar',
            'Orang Tua',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // Create Default Lembaga and Sekolah for relation testing
        $lembaga = Lembaga::firstOrCreate([
            'name' => 'Yayasan Pendidikan Anak Bangsa',
            'address' => 'Jl. Pendidikan No. 1, Jakarta',
            'phone' => '021-12345678',
        ]);

        $sekolah = Sekolah::firstOrCreate([
            'lembaga_id' => $lembaga->id,
            'name' => 'PAUD Bintang Kecil',
            'address' => 'Jl. Merdeka No. 10, Jakarta',
            'phone' => '021-87654321',
        ]);

        // Seed 1 User per Role
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@example.com',
                'password' => Hash::make('password'),
                'lembaga_id' => null,
                'sekolah_id' => null,
                'role' => 'Superadmin',
            ],
            [
                'name' => 'Admin Lembaga',
                'email' => 'lembaga@example.com',
                'password' => Hash::make('password'),
                'lembaga_id' => $lembaga->id,
                'sekolah_id' => null,
                'role' => 'Lembaga',
            ],
            [
                'name' => 'Admin Sekolah',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'lembaga_id' => $lembaga->id,
                'sekolah_id' => $sekolah->id,
                'role' => 'Admin Sekolah',
            ],
            [
                'name' => 'Pengajar Budi',
                'email' => 'pengajar@example.com',
                'password' => Hash::make('password'),
                'lembaga_id' => null,
                'sekolah_id' => $sekolah->id,
                'role' => 'Pengajar',
            ],
            [
                'name' => 'Orang Tua Siti',
                'email' => 'ortu@example.com',
                'password' => Hash::make('password'),
                'lembaga_id' => null,
                'sekolah_id' => $sekolah->id,
                'role' => 'Orang Tua',
            ],
        ];

        foreach ($users as $userData) {
            $roleName = $userData['role'];
            unset($userData['role']);

            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            app(PermissionRegistrar::class)->setPermissionsTeamId($user->sekolah_id);
            $user->unsetRelation('roles');

            $user->assignRole($roleName);
        }

        app(PermissionRegistrar::class)->setPermissionsTeamId(null);
    }
}
