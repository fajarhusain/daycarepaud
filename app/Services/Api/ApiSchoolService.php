<?php

namespace App\Services\Api;

use App\Http\Resources\Api\V1\SekolahResource;
use App\Models\Anak;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Support\Collection;

class ApiSchoolService
{
    public function __construct(private readonly ApiTenantAccessService $access) {}

    /**
     * @return Collection<int, Sekolah>
     */
    public function accessible(User $user): Collection
    {
        return $this->access->accessibleSchools($user);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function payloads(User $user, Collection $schools): array
    {
        $counts = $this->childCounts($user);

        return $schools
            ->map(fn (Sekolah $sekolah) => (new SekolahResource(
                $sekolah,
                (int) ($counts[$sekolah->id] ?? 0),
            ))->resolve())
            ->values()
            ->all();
    }

    /**
     * @return array<int, int>
     */
    public function childCounts(User $user): array
    {
        return Anak::withoutSekolahScope()
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->selectRaw('sekolah_id, COUNT(*) as total')
            ->groupBy('sekolah_id')
            ->pluck('total', 'sekolah_id')
            ->map(fn ($total) => (int) $total)
            ->all();
    }
}
