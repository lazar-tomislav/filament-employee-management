<?php

namespace Amicus\FilamentEmployeeManagement\Observers;

use Amicus\FilamentEmployeeManagement\Enums\TaskStatus;
use Amicus\FilamentEmployeeManagement\Models\Task;
use Amicus\FilamentEmployeeManagement\Notifications\TaskCompletedNotification;
use Illuminate\Support\Facades\Notification;

class TaskObserver
{
    /**
     * Handle the Task "creating" event.
     */
    public function creating(Task $task): void
    {
        // Set default status if not already set
        if (empty($task->status)) {
            $task->status = \Amicus\FilamentEmployeeManagement\Enums\TaskStatus::TODO;
        }

        // Set creator_id to current authenticated user if not already set
        if (empty($task->creator_id) && auth()->check()) {
            $task->creator_id = auth()->id();
        }
    }

    /**
     * Handle the Task "created" event.
     */
    public function created(Task $task): void
    {
        //
    }

    /**
     * Handle the Task "updating" event.
     */
    public function updating(Task $task): void
    {
        //
    }

    /**
     * Handle the Task "updated" event.
     */
    public function updated(Task $task): void
    {
        // Check if status was changed to DONE and task has a project (projektni zadatak)
        if ($task->wasChanged('status') &&
            $task->status === TaskStatus::DONE &&
            $task->project_id !== null) {

            // Create a general notification target for telegram
            $generalNotificationTarget = new \Amicus\FilamentEmployeeManagement\Services\GeneralNotificationTarget();

            // Send notification to general telegram channel
            $generalNotificationTarget->notify(new TaskCompletedNotification($task));

            logger("Task completed notification sent for task ID: {$task->id}");
        }
    }

    /**
     * Handle the Task "saving" event.
     */
    public function saving(Task $task): void
    {
        //
    }

    /**
     * Handle the Task "saved" event.
     */
    public function saved(Task $task): void
    {
        //
    }

    /**
     * Handle the Task "deleting" event.
     */
    public function deleting(Task $task): void
    {
        //
    }

    /**
     * Handle the Task "deleted" event.
     */
    public function deleted(Task $task): void
    {
        //
    }

    /**
     * Handle the Task "restoring" event.
     */
    public function restoring(Task $task): void
    {
        //
    }

    /**
     * Handle the Task "restored" event.
     */
    public function restored(Task $task): void
    {
        //
    }

    /**
     * Handle the Task "force deleted" event.
     */
    public function forceDeleted(Task $task): void
    {
        //
    }
}
