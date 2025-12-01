<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource\Widgets;

use Amicus\FilamentEmployeeManagement\Enums\LeaveRequestStatus;
use Amicus\FilamentEmployeeManagement\Enums\LeaveRequestType;
use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\LeaveRequestResource\Actions\LeaveRequestActions;
use Amicus\FilamentEmployeeManagement\Models\Employee;
use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use Amicus\FilamentEmployeeManagement\Services\LeaveRequestPdfService;
use Filament\Actions;
use Filament\Actions\ActionGroup;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

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
            ->emptyStateHeading("Nema zapisa.")
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
                    LeaveRequestActions::approveAction(),
                    LeaveRequestActions::rejectAction(),
                    LeaveRequestActions::cancelRequestAction(),
                    Actions\Action::make('download_pdf')
                        ->label('Skini PDF')
                        ->icon(Heroicon::OutlinedDocumentArrowDown)
                        ->visible(fn($record) => $record->status === LeaveRequestStatus::APPROVED)
                        ->action(function (LeaveRequest $record) {
                            if (!$record->pdf_path) {
                                // Generate PDF if not exists
                                $pdfPath = LeaveRequestPdfService::generatePdf($record);
                                $record->update(['pdf_path' => $pdfPath]);
                            }
                            if ($record->pdf_path && Storage::disk('private')->exists($record->pdf_path)) {
                                $file = Storage::disk('private')->get($record->pdf_path);
                                $filename = basename($record->pdf_path);
                                return response()->streamDownload(function () use ($file) {
                                    echo $file;
                                }, $filename, ['Content-Type' => 'application/pdf']);
                            }
                        }),
                ])
            );
    }
}
