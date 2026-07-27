<?php

namespace Amicus\FilamentEmployeeManagement\Services;

use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Ručno okida sinkronizaciju kalendara za odobrene zahtjeve.
 *
 * Auto-odobrene vrste i administratorski override spremaju status preko
 * updateQuietly(), pa "updated" event — a time i observer kalendara — nikad ne
 * okine. Za te slučajeve sinkronizaciju treba pozvati eksplicitno.
 *
 * Observer kalendara živi u aplikaciji, ne u paketu, pa se razrješava uvjetno
 * kako bi paket ostao samostalan.
 */
class LeaveRequestCalendarSyncService
{
    private const OBSERVER_CLASS = '\App\Observers\LeaveRequestCalendarObserver';

    public static function syncApproved(LeaveRequest $leaveRequest): void
    {
        if (! class_exists(self::OBSERVER_CLASS)) {
            return;
        }

        try {
            app(self::OBSERVER_CLASS)->syncApprovedLeaveRequest($leaveRequest);
        } catch (Throwable $e) {
            Log::error('Failed to sync approved leave request to calendar.', [
                'leave_request_id' => $leaveRequest->id,
                'error' => $e->getMessage(),
            ]);

            report($e);
        }
    }
}
