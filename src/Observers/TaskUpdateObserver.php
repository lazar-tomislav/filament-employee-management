<?php

namespace Amicus\FilamentEmployeeManagement\Observers;

use Amicus\FilamentEmployeeManagement\Models\Employee;
use Amicus\FilamentEmployeeManagement\Models\TaskUpdate;
use Amicus\FilamentEmployeeManagement\Notifications\TaskUpdateMentionNotification;
use App\Classes\Str;
use Illuminate\Support\Facades\Log;

class TaskUpdateObserver
{
    public function creating(TaskUpdate $taskUpdate): void
    {
        //
    }

    public function created(TaskUpdate $taskUpdate): void
    {
        $this->handleMentions($taskUpdate);
    }

    public function updated(TaskUpdate $taskUpdate): void
    {
        $this->handleMentions($taskUpdate);
    }

    private function handleMentions(TaskUpdate $taskUpdate): void
    {
        $newMentionIds = Str::extractMentionIds($taskUpdate->body);
        $oldMentionIds = $taskUpdate->mentions()->pluck('mentioned_employee_id')->toArray();

        $taskUpdate->mentions()->sync($newMentionIds);

        $addedMentionIds = array_diff($newMentionIds, $oldMentionIds);
        if (!empty($addedMentionIds)) {
            $mentionedEmployees = Employee::query()->whereIn('id', $addedMentionIds)->get();

            /** @var Employee $employee */
            foreach ($mentionedEmployees as $employee) {
                Log::debug($employee);
                $employee->notify(new TaskUpdateMentionNotification($taskUpdate));
            }
        }
    }

    /**
     * Handle the TaskUpdate "deleted" event.
     */
    public function deleted(TaskUpdate $taskUpdate): void
    {
        //
    }

    public function restored(TaskUpdate $taskUpdate): void
    {
        //
    }
    public function forceDeleted(TaskUpdate $taskUpdate): void
    {
        //
    }
}
