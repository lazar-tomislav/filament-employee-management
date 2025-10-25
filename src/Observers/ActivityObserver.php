<?php

namespace Amicus\FilamentEmployeeManagement\Observers;

use Amicus\FilamentEmployeeManagement\Models\Activity;
use Amicus\FilamentEmployeeManagement\Models\Employee;
use Amicus\FilamentEmployeeManagement\Notifications\ActivityMentionNotification;
use App\Classes\Str;
use Illuminate\Support\Facades\Log;

class ActivityObserver
{
    public function creating(Activity $activity): void
    {
        //
    }

    public function created(Activity $activity): void
    {
        $this->handleMentions($activity);
    }

    public function updated(Activity $activity): void
    {
        $this->handleMentions($activity);
    }

    private function handleMentions(Activity $activity): void
    {
        $newMentionIds = Str::extractMentionIds($activity->body);
        $oldMentionIds = $activity->mentions()->pluck('mentioned_employee_id')->toArray();

        $activity->mentions()->sync($newMentionIds);

        $addedMentionIds = array_diff($newMentionIds, $oldMentionIds);
        if (! empty($addedMentionIds)) {
            $mentionedEmployees = Employee::query()->whereIn('id', $addedMentionIds)->get();

            /** @var Employee $employee */
            foreach ($mentionedEmployees as $employee) {
                Log::debug($employee);
                $employee->notify(new ActivityMentionNotification($activity));
            }
        }
    }

    /**
     * Handle the Activity "deleted" event.
     */
    public function deleted(Activity $activity): void
    {
        //
    }

    public function restored(Activity $activity): void
    {
        //
    }

    public function forceDeleted(Activity $activity): void
    {
        //
    }
}
