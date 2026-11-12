<?php

namespace App\Http\Resources\Api\V1;

use App\Models\User;
use App\Services\Api\ApiRoleService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class UserResource extends JsonResource
{
    /**
     * @param  list<string>|null  $roleNames
     */
    public function __construct(User $resource, private readonly ?array $roleNames = null)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        $roles = $this->roleNames ?? app(ApiRoleService::class)->names($this->resource);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'roles' => $roles,
            'mobile_roles' => app(ApiRoleService::class)->mobileAliases($this->resource),
        ];
    }
}
