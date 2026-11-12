<?php

namespace App\Models\Concerns;

use App\Support\ActivityLogger;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

trait LogsScopedActivity
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        $options = LogOptions::defaults()
            ->useLogName('admin')
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();

        if (property_exists($this, 'activityLogExcept') && is_array($this->activityLogExcept)) {
            $options->logExcept($this->activityLogExcept);
        }

        return $options;
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        $activity->properties = $activity->properties->merge(ActivityLogger::scopeProperties());
    }
}
