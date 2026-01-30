<?php

namespace Amicus\FilamentEmployeeManagement\Observers;

use Amicus\FilamentEmployeeManagement\Enums\TaskStatus;
use Amicus\FilamentEmployeeManagement\Models\Task;
use Amicus\FilamentEmployeeManagement\Notifications\TaskCompletedNotification;
use Amicus\FilamentEmployeeManagement\Services\GeneralNotificationTarget;

class TaskObserver
{
    /**
     * Handle the Task "creating" event.
     */
    public function creating(Task $task): void
    {
        // Set default status if not already set
        if (empty($task->status)) {
            $task->status = TaskStatus::TODO;
        }

        // Set creator_id only if not already set
        if (empty($task->creator_id)) {
            if (! auth()->check() || ! auth()->user()->employee) {
                throw new \Exception('Korisnik mora imati povezan Employee zapis za kreiranje zadatka.');
            }
            $task->creator_id = auth()->user()->employee->id;
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
        logger("TaskObserver updated() called for task ID: {$task->id}");
        logger('Task status: ' . $task->status->value);
        logger('Task project_id: ' . ($task->project_id ?? 'null'));

        $statusChanged = $task->wasChanged('status');
        logger('Status was changed: ' . ($statusChanged ? 'true' : 'false'));

        if ($statusChanged) {
            $originalStatus = $task->getOriginal('status');
            logger('Original status: ' . ($originalStatus ? $originalStatus->value : 'null'));
            logger('New status: ' . $task->status->value);
        }

        $isDone = $task->status === TaskStatus::DONE;
        logger('Status is DONE: ' . ($isDone ? 'true' : 'false'));

        $hasProject = $task->project_id !== null;
        logger('Has project: ' . ($hasProject ? 'true' : 'false'));

        // Check if status was changed to DONE and task has a project (projektni zadatak)
        if ($task->wasChanged('status') &&
            $task->status === TaskStatus::DONE &&
            $task->project_id !== null) {

            logger('All conditions met - sending notification');
            (new GeneralNotificationTarget)->notify(new TaskCompletedNotification($task));

            logger("Task completed notification sent for task ID: {$task->id}");
        } else {
            logger('Conditions not met - notification not sent');
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
