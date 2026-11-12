<?php

namespace App\Models\Concerns;

use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait BelongsToSekolah
{
    public static function bootBelongsToSekolah(): void
    {
        static::addGlobalScope('sekolah', function (Builder $builder): void {
            if (TenantContext::isBypassed()) {
                return;
            }

            $sekolahId = TenantContext::sekolahId();
            if ($sekolahId !== null) {
                $builder->where($builder->getModel()->getTable().'.sekolah_id', $sekolahId);
            }
        });

        static::creating(function (Model $model): void {
            if (TenantContext::isBypassed()) {
                return;
            }

            $sekolahId = TenantContext::sekolahId();
            if ($sekolahId !== null && empty($model->sekolah_id)) {
                $model->sekolah_id = $sekolahId;
            }
        });
    }

    public function scopeWithoutSekolahScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('sekolah');
    }
}
