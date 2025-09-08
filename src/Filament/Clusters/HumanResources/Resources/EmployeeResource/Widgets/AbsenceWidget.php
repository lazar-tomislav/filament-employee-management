<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Widgets;

use Amicus\FilamentEmployeeManagement\Enums\LeaveRequestStatus;
use Amicus\FilamentEmployeeManagement\Enums\LeaveRequestType;
use Amicus\FilamentEmployeeManagement\Models\Employee;
use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class AbsenceWidget extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    public string $absenceType = 'current';

    public Employee $record;

    protected $listeners = [
        'leave-request-created' => '$refresh',
    ];

    protected function getTableQuery(): Builder
    {
        $query = LeaveRequest::query()
            ->where('employee_id', $this->record->id);

        if($this->absenceType === 'current'){
             $query->where('end_date', '>=', now()->toDateString());
        }else{
            $query->where('end_date', '<', now()->toDateString());
        }
        return $query;
    }

    protected function isTablePaginationEnabled(): bool
    {
        return $this->absenceType !== 'current';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->paginated($this->isTablePaginationEnabled())
            ->striped()
            ->defaultSort("created_at", 'desc')
            ->heading(null)
            ->columns([
                TextColumn::make('type')
                    ->label('Razlog odsutnosti')
                    ->formatStateUsing(fn(LeaveRequestType $state): string => ucfirst(str_replace('_', ' ', $state->value))),

                TextColumn::make('absence')
                    ->label('Odsutnost'),

                TextColumn::make('notes')
                    ->label('Napomena')
                    ->placeholder('-'),

                TextColumn::make('status')
                    ->badge()
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'warning' => 'canceled',
                    ])
                    ->formatStateUsing(function (LeaveRequestStatus $state): string {
                        return match ($state->value) {
                            'pending' => 'Na čekanju',
                            'approved' => 'Odobren',
                            'rejected' => 'Odbijen',
                            'canceled' => 'Otkazano',
                            default => ucfirst($state->value)
                        };
                    }),
            ])
            ->recordActions(
                ActionGroup::make([
                    \Filament\Actions\Action::make('approve')
                        ->icon(Heroicon::OutlinedCheck)
                        ->label('Odobri zahtjev')
                        ->color('success')
                        ->visible(auth()->user()->isUredAdministrativnoOsoblje())
                        ->requiresConfirmation()
                        ->action(function (LeaveRequest $record) {
                            $record->update([
                                'status' => LeaveRequestStatus::APPROVED->value,
                                'approved_by' => auth()->id(),
                            ]);
                            Notification::make()
                                ->title('Zahtjev odobren')
                                ->body('Zaposlenik je obaviješten o promjeni statusa.')
                                ->success()
                                ->send();
                        }),
                    \Filament\Actions\Action::make('reject')
                        ->label('Odbij zahtjev')
                        ->icon(Heroicon::OutlinedXCircle)
                        ->visible(auth()->user()->isUredAdministrativnoOsoblje())
                        ->color('danger')
                        ->schema([
                            Textarea::make('rejection_reason')
                                ->label('Razlog odbijanja')
                                ->helperText("Zaposlenik će primiti obavijest o odbijanju zahtjeva s razlogom.")
                                ->required(),
                        ])
                        ->action(function (LeaveRequest $record, array $data) {
                            $record->update([
                                'status' => LeaveRequestStatus::REJECTED->value,
                                'approved_by' => auth()->id(),
                                'rejection_reason' => $data['rejection_reason'],
                            ]);
                            Notification::make()
                                ->title('Zahtjev obijen')
                                ->body('Zaposlenik je obaviješten o promjeni statusa.')
                                ->success()
                                ->send();
                        }),

                    \Filament\Actions\Action::make('cancel_request')
                        ->label('Otkaži zahtjev')
                        ->icon(Heroicon::OutlinedXMark)
                        ->color('danger')
                        ->visible(fn(LeaveRequest $record): bool => auth()->user()->isEmployee() && auth()->user()->employee->id == $record->employee->id)
                        ->requiresConfirmation()
                        ->action(function (LeaveRequest $record) {
                            $record->update([
                                'status' => LeaveRequestStatus::CANCELED->value,
                            ]);
                            Notification::make()
                                ->title('Zahtjev uspješno otkazan')
                                ->warning()
                                ->send();
                        }),
                ])
            );
    }
}
