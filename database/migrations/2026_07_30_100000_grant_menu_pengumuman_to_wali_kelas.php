<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permission = Permission::firstOrCreate([
            'name' => 'menu.pengumuman',
            'guard_name' => 'web',
        ]);

        $role = Role::where('name', 'Wali Kelas')->where('guard_name', 'web')->first();
        if ($role && ! $role->hasPermissionTo($permission)) {
            $role->givePermissionTo($permission);
        }
    }

    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $role = Role::where('name', 'Wali Kelas')->where('guard_name', 'web')->first();
        if ($role) {
            $role->revokePermissionTo('menu.pengumuman');
        }
    }
};
