<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Contracts\Activity;

class ActivityLogger
{
    public static function scopeProperties(): array
    {
        $user = auth()->user();
        $request = request();

        return array_filter([
            'lembaga_id' => $user instanceof User ? $user->lembaga_id : null,
            'sekolah_id' => TenantContext::sekolahId() ?? ($user instanceof User ? $user->sekolah_id : null),
            'route_name' => $request?->route()?->getName(),
            'ip' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ], fn ($value) => $value !== null && $value !== '');
    }

    public static function log(string $description, ?Model $subject = null, array $properties = []): Activity
    {
        $activity = activity('admin')
            ->causedBy(auth()->user())
            ->withProperties(array_merge(self::scopeProperties(), $properties));

        if ($subject) {
            $activity->performedOn($subject);
        }

        return $activity->log($description);
    }
}
