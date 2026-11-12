<?php

namespace App\Services\Api;

use App\Models\Anak;
use App\Models\Pengajar;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Support\Collection;

class ApiTenantAccessService
{
    public function __construct(private readonly ApiRoleService $roles) {}

    public function allows(User $user, Sekolah $sekolah): bool
    {
        foreach (config('api.tenant_access', []) as $roleName => $strategy) {
            if (! $this->roles->hasRole($user, $roleName)) {
                continue;
            }

            if ($this->strategyAllows($strategy, $user, $sekolah)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Collection<int, Sekolah>
     */
    public function accessibleSchools(User $user): Collection
    {
        $roleNames = $this->roles->names($user);
        $strategies = [];

        foreach (config('api.tenant_access', []) as $roleName => $strategy) {
            if (in_array($roleName, $roleNames, true)) {
                $strategies[] = $strategy;
            }
        }

        if ($strategies === []) {
            return collect();
        }

        if (in_array('all', $strategies, true)) {
            return Sekolah::query()->orderBy('name')->get();
        }

        $ids = [];

        foreach (array_unique($strategies) as $strategy) {
            switch ($strategy) {
                case 'approved_children':
                    $ids = array_merge($ids, $this->approvedSekolahIds($user));
                    break;

                case 'user_school':
                    $sekolahId = (int) ($user->getAttributes()['sekolah_id'] ?? 0);
                    if ($sekolahId > 0) {
                        $ids[] = $sekolahId;
                    }
                    break;

                case 'pengajar_school':
                    $sekolahId = $this->pengajarSekolahId($user);
                    if ($sekolahId > 0) {
                        $ids[] = $sekolahId;
                    }
                    break;

                case 'lembaga_school':
                    $lembagaId = (int) ($user->getAttributes()['lembaga_id'] ?? 0);
                    if ($lembagaId > 0) {
                        $ids = array_merge(
                            $ids,
                            Sekolah::query()
                                ->where('lembaga_id', $lembagaId)
                                ->pluck('id')
                                ->map(fn ($id) => (int) $id)
                                ->all(),
                        );
                    }
                    break;
            }
        }

        $ids = array_values(array_unique($ids));

        if ($ids === []) {
            return collect();
        }

        return Sekolah::query()
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->get();
    }

    private function strategyAllows(string $strategy, User $user, Sekolah $sekolah): bool
    {
        return match ($strategy) {
            'all' => true,
            'approved_children' => in_array((int) $sekolah->id, $this->approvedSekolahIds($user), true),
            'user_school' => (int) ($user->getAttributes()['sekolah_id'] ?? 0) === (int) $sekolah->id,
            'pengajar_school' => $this->pengajarSekolahId($user) === (int) $sekolah->id,
            'lembaga_school' => (int) ($user->getAttributes()['lembaga_id'] ?? 0) === (int) $sekolah->lembaga_id
                && (int) $sekolah->lembaga_id > 0,
            default => false,
        };
    }

    /**
     * @return list<int>
     */
    private function approvedSekolahIds(User $user): array
    {
        return Anak::withoutSekolahScope()
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->distinct()
            ->pluck('sekolah_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function pengajarSekolahId(User $user): int
    {
        return (int) (Pengajar::withoutSekolahScope()
            ->where('user_id', $user->id)
            ->value('sekolah_id') ?? 0);
    }
}
