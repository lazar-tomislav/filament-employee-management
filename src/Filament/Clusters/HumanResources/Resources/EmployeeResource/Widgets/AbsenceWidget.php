<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Widgets;

use Amicus\FilamentEmployeeManagement\Enums\LeaveRequestStatus;
use Amicus\FilamentEmployeeManagement\Enums\LeaveRequestType;
use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\LeaveRequestResource\Actions\LeaveRequestActions;
use Amicus\FilamentEmployeeManagement\Models\Employee;
use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use Filament\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

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

        if ($this->absenceType === 'current') {
            $query->where('end_date', '>=', now()->toDateString());
        } else {
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
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $isAdmin = $user && method_exists($user, 'isAdmin') && $user->isAdmin();

        return $table
            ->query($this->getTableQuery())
            ->paginated($this->isTablePaginationEnabled())
            ->striped()
            ->emptyStateHeading('Nema zapisa.')
            ->defaultSort('created_at', 'desc')
            ->heading(null)
            ->columns([
                TextColumn::make('type')
                    ->label('Razlog odsutnosti')
                    ->formatStateUsing(fn (LeaveRequestType $state): string => ucfirst(str_replace('_', ' ', $state->value))),

                TextColumn::make('absence')
                    ->label('Odsutnost'),

                TextColumn::make('days_count')
                    ->label('Broj radnih dana'),

                $isAdmin
                    ? TextInputColumn::make('notes')->label('Napomena')
                    : TextColumn::make('notes')->label('Napomena')->placeholder('-'),

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
                    LeaveRequestActions::approveAction(),
                    LeaveRequestActions::rejectAction(),
                    LeaveRequestActions::cancelRequestAction(),
                    LeaveRequestActions::downloadPdfAction(),
                ])
            );
    }
}
