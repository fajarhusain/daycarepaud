<?php

namespace App\Services\Api;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class ApiRoleService
{
    /**
     * @return list<string>
     */
    public function names(User $user): array
    {
        $roleTable = config('permission.table_names.roles', 'roles');
        $pivotTable = config('permission.table_names.model_has_roles', 'model_has_roles');
        $modelKey = config('permission.column_names.model_morph_key', 'model_id');
        $guard = config('api.guard', 'web');

        return DB::table($pivotTable.' as model_has_roles')
            ->join($roleTable.' as roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.'.$modelKey, $user->getKey())
            ->where('model_has_roles.model_type', $user->getMorphClass())
            ->where('roles.guard_name', $guard)
            ->distinct()
            ->orderBy('roles.name')
            ->pluck('roles.name')
            ->map(fn ($name) => (string) $name)
            ->values()
            ->all();
    }

    public function hasRole(User $user, string $roleName): bool
    {
        return in_array($roleName, $this->names($user), true);
    }

    /**
     * @param  list<string>  $roleNames
     */
    public function hasAnyRole(User $user, array $roleNames): bool
    {
        return array_intersect($roleNames, $this->names($user)) !== [];
    }

    /**
     * Alias role yang dikenali mobile, diambil dari config/api.php.
     *
     * @return list<string>
     */
    public function mobileAliases(User $user): array
    {
        $names = $this->names($user);
        $aliases = [];

        foreach (config('api.roles', []) as $alias => $roleName) {
            if (in_array($roleName, $names, true)) {
                $aliases[] = $alias;
            }
        }

        return $aliases;
    }

    public function roleNameForAlias(string $alias): ?string
    {
        $roleName = config('api.roles.'.$alias);

        return is_string($roleName) && $roleName !== '' ? $roleName : null;
    }
}
