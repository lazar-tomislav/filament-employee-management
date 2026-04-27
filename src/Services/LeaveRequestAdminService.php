<?php

declare(strict_types=1);

namespace Amicus\FilamentEmployeeManagement\Services;

use Amicus\FilamentEmployeeManagement\Enums\LeaveRequestStatus;
use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use Amicus\FilamentEmployeeManagement\Notifications\LeaveRequestAdminOverrideNotification;
use Amicus\FilamentEmployeeManagement\Notifications\LeaveRequestStatusChangeNotification;
use Amicus\FilamentEmployeeManagement\Settings\HumanResourcesSettings;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class LeaveRequestAdminService
{
    public function deletePending(LeaveRequest $request, string $reason, User $admin): void
    {
        DB::transaction(function () use ($request, $reason, $admin): void {
            activity('leave_request')
                ->causedBy($admin)
                ->performedOn($request)
                ->withProperties([
                    'reason' => $reason,
                    'action' => 'delete_pending',
                    'snapshot' => $this->snapshot($request),
                ])
                ->log('admin.delete_pending');

            $request->disableLogging();
            $request->delete();
        });
    }

    public function deleteApproved(LeaveRequest $request, string $reason, User $admin): void
    {
        DB::transaction(function () use ($request, $reason, $admin): void {
            $this->deletePdfFile($request);

            $request->updateQuietly(['pdf_path' => null]);

            activity('leave_request')
                ->causedBy($admin)
                ->performedOn($request)
                ->withProperties([
                    'reason' => $reason,
                    'action' => 'delete_approved',
                    'snapshot' => $this->snapshot($request),
                ])
                ->log('admin.delete_approved');

            $request->disableLogging();
            $request->delete();

            $this->notifyEmployee($request, $reason, 'canceled');
        });
    }

    /**
     * @param  array{start_date?: string, end_date?: string, type?: string, leave_allowance_id?: int|null, days_count?: int, notes?: string|null}  $data
     *
     * @throws Throwable
     */
    public function editRequest(LeaveRequest $request, array $data, string $reason, User $admin): void
    {
        DB::transaction(function () use ($request, $data, $reason, $admin): void {
            $datesChanged = (isset($data['start_date']) && (string) $request->start_date->format('Y-m-d') !== (string) $data['start_date'])
                || (isset($data['end_date']) && (string) $request->end_date->format('Y-m-d') !== (string) $data['end_date']);

            $wasApproved = $request->status === LeaveRequestStatus::APPROVED;

            $loggedAttributes = ['status', 'start_date', 'end_date', 'days_count', 'type', 'leave_allowance_id', 'notes', 'rejection_reason'];

            $oldValues = collect($loggedAttributes)
                ->mapWithKeys(fn (string $attr) => [$attr => $this->scalarValue($request->getOriginal($attr))])
                ->all();

            $request->updateQuietly($data);
            $request->refresh();

            $newValues = collect($loggedAttributes)
                ->mapWithKeys(fn (string $attr) => [$attr => $this->scalarValue($request->getAttribute($attr))])
                ->all();

            $changes = [];
            foreach ($loggedAttributes as $attr) {
                if (($oldValues[$attr] ?? null) !== ($newValues[$attr] ?? null)) {
                    $changes['attributes'][$attr] = $newValues[$attr];
                    $changes['old'][$attr] = $oldValues[$attr];
                }
            }

            activity('leave_request')
                ->causedBy($admin)
                ->performedOn($request)
                ->withProperties([
                    'reason' => $reason,
                    'action' => 'edit',
                    'attributes' => $changes['attributes'] ?? [],
                    'old' => $changes['old'] ?? [],
                ])
                ->log('admin.edit');

            if ($wasApproved && $datesChanged) {
                $this->deletePdfFile($request);
                $newPdfPath = LeaveRequestPdfService::generatePdf($request);
                $request->updateQuietly(['pdf_path' => $newPdfPath ?: null]);
            }
        });
    }

    private function scalarValue(mixed $value): mixed
    {
        if ($value instanceof \BackedEnum) {
            return $value->value;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        return $value;
    }

    public function overrideStatus(LeaveRequest $request, LeaveRequestStatus $newStatus, string $reason, User $admin): void
    {
        DB::transaction(function () use ($request, $newStatus, $reason, $admin): void {
            $oldStatus = $request->status;

            if ($oldStatus === $newStatus) {
                return;
            }

            $updates = ['status' => $newStatus->value];

            if ($newStatus === LeaveRequestStatus::APPROVED) {
                $settings = app(HumanResourcesSettings::class);

                if (! $request->approved_by_director_id && $settings->employee_director_id) {
                    $updates['approved_by_director_id'] = $settings->employee_director_id;
                    $updates['approved_by_director_at'] = now();
                }
            }

            if (in_array($newStatus, [LeaveRequestStatus::REJECTED, LeaveRequestStatus::CANCELED], true)) {
                $updates['rejection_reason'] = $reason;
            }

            $request->updateQuietly($updates);

            activity('leave_request')
                ->causedBy($admin)
                ->performedOn($request)
                ->withProperties([
                    'reason' => $reason,
                    'action' => 'override_status',
                    'from' => $oldStatus->value,
                    'to' => $newStatus->value,
                ])
                ->log('admin.override_status');

            if ($newStatus === LeaveRequestStatus::APPROVED) {
                $this->finalizeApproval($request->fresh());
            }

            if (in_array($newStatus, [LeaveRequestStatus::REJECTED, LeaveRequestStatus::CANCELED], true)) {
                $this->deletePdfFile($request);
                $request->updateQuietly(['pdf_path' => null]);

                $statusKey = $newStatus === LeaveRequestStatus::REJECTED ? 'rejected' : 'canceled';
                $this->notifyEmployee($request, $reason, $statusKey);
            }
        });
    }

    private function deletePdfFile(LeaveRequest $request): void
    {
        if ($request->pdf_path && Storage::disk('local')->exists($request->pdf_path)) {
            Storage::disk('local')->delete($request->pdf_path);
        }
    }

    private function finalizeApproval(LeaveRequest $request): void
    {
        $pdfPath = LeaveRequestPdfService::generatePdf($request);
        $request->updateQuietly(['pdf_path' => $pdfPath ?: null]);

        $employee = $request->employee;

        if ($employee?->user) {
            $employee->user->notify(new LeaveRequestStatusChangeNotification($request));
        }
    }

    private function notifyEmployee(LeaveRequest $request, string $reason, string $statusKey): void
    {
        $employee = $request->employee;

        if (! $employee?->user) {
            return;
        }

        $employee->user->notify(new LeaveRequestAdminOverrideNotification($request, $reason, $statusKey));
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(LeaveRequest $request): array
    {
        return [
            'status' => $request->status?->value,
            'start_date' => $request->start_date?->format('Y-m-d'),
            'end_date' => $request->end_date?->format('Y-m-d'),
            'days_count' => $request->days_count,
            'type' => $request->type?->value,
            'leave_allowance_id' => $request->leave_allowance_id,
        ];
    }
}
