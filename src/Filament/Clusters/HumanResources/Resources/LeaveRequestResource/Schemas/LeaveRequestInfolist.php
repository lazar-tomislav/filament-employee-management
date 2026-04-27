<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\LeaveRequestResource\Schemas;

use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\LeaveRequestResource\Actions\LeaveRequestActions;
use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use App\Models\User;
use Filament\Infolists;
use Filament\Schemas\Schema;

class LeaveRequestInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Infolists\Components\KeyValueEntry::make('leave_request_details')
                    ->hiddenLabel()
                    ->keyLabel('Naziv')
                    ->state(function (LeaveRequest $record): array {
                        $hodApproval = $record->headOfDepartmentApprover
                            ? $record->headOfDepartmentApprover->full_name . ' (' . $record->approved_by_head_of_department_at?->format('d.m.Y H:i') . ')'
                            : 'Nije odobreno';

                        $directorApproval = $record->directorApprover
                            ? $record->directorApprover->full_name . ' (' . $record->approved_by_director_at?->format('d.m.Y H:i') . ')'
                            : 'Nije odobreno';

                        return [
                            'Zaposlenik' => $record->employee->full_name_email,
                            'Tip' => $record->type->getLabel(),
                            'Status' => $record->status->getLabel(),
                            'Datum početka' => $record->start_date ? $record->start_date->format('d.m.Y') : '-',
                            'Datum kraja' => $record->end_date ? $record->end_date->format('d.m.Y') : '-',
                            'Broj dana' => $record->days_count,
                            'Odgovor voditelja' => $hodApproval,
                            'Odgovor direktora' => $directorApproval,
                            'Razlog odbijanja' => $record->rejection_reason ?? 'Nije odbijeno',
                        ];
                    })
                    ->belowContent([
                        LeaveRequestActions::approveAsHeadOfDepartmentAction(),
                        LeaveRequestActions::rejectAsHeadOfDepartmentAction(),
                        LeaveRequestActions::approveAsDirectorAction(),
                        LeaveRequestActions::rejectAction(),
                        LeaveRequestActions::sendReminderAction(),
                        LeaveRequestActions::overrideStatusAction(),
                        LeaveRequestActions::deletePendingAction(),
                        LeaveRequestActions::deleteApprovedAction(),
                    ])
                    ->valueLabel('Vrijednost'),

                Infolists\Components\TextEntry::make('admin_activity_log')
                    ->label('Povijest admin akcija')
                    ->visible(function (LeaveRequest $record): bool {
                        /** @var User|null $user */
                        $user = auth()->user();

                        return $user?->canSeeAllLeave()
                            && $record->activitiesAsSubject()->where('log_name', 'leave_request')->where('description', 'like', 'admin.%')->exists();
                    })
                    ->html()
                    ->state(function (LeaveRequest $record): string {
                        $activities = $record->activitiesAsSubject()
                            ->where('log_name', 'leave_request')
                            ->where('description', 'like', 'admin.%')
                            ->latest()
                            ->limit(20)
                            ->get();

                        if ($activities->isEmpty()) {
                            return '-';
                        }

                        return $activities->map(function ($activity): string {
                            $causer = $activity->causer?->name ?? 'Nepoznat';
                            $when = $activity->created_at->format('d.m.Y H:i');
                            $action = e($activity->description);
                            $reason = e($activity->properties['reason'] ?? '-');
                            $extra = '';

                            if (($activity->properties['from'] ?? null) && ($activity->properties['to'] ?? null)) {
                                $extra = ' (' . e($activity->properties['from']) . ' → ' . e($activity->properties['to']) . ')';
                            }

                            $diff = '';
                            $attributes = $activity->properties['attributes'] ?? [];
                            $old = $activity->properties['old'] ?? [];
                            if (! empty($attributes)) {
                                $rows = collect($attributes)->map(function ($newVal, $key) use ($old) {
                                    $oldVal = $old[$key] ?? '∅';
                                    $newVal = $newVal === null ? '∅' : $newVal;

                                    return '<li>' . e($key) . ': ' . e((string) $oldVal) . ' → ' . e((string) $newVal) . '</li>';
                                })->implode('');
                                $diff = "<ul class=\"text-xs mt-1 ml-4 list-disc\">{$rows}</ul>";
                            }

                            return "<div class=\"mb-2\"><strong>{$action}</strong>{$extra}<br><small>{$causer} · {$when}</small><br>Razlog: {$reason}{$diff}</div>";
                        })->implode('');
                    }),
            ]);
    }
}
