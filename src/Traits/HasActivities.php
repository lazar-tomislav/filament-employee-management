<?php

namespace Amicus\FilamentEmployeeManagement\Traits;

use Amicus\FilamentEmployeeManagement\Models\Activity;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasActivities
{
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'activityable')->with(['author', 'mentions']);
    }
}
