<?php

declare(strict_types=1);

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\LeaveRequestResource\Pages;

use Amicus\FilamentEmployeeManagement\Enums\LeaveRequestType;
use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\LeaveRequestResource;
use Amicus\FilamentEmployeeManagement\Models\LeaveAllowance;
use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use Amicus\FilamentEmployeeManagement\Services\LeaveRequestAdminService;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class EditLeaveRequest extends EditRecord
{
    protected static string $resource = LeaveRequestResource::class;

    public function getTitle(): string
    {
        return 'Uredi zahtjev (admin)';
    }

    public static function canAccess(array $parameters = []): bool
    {
        /** @var User|null $user */
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->canSeeAllLeave()) {
            return true;
        }

        $record = $parameters['record'] ?? null;

        if ($record instanceof LeaveRequest) {
            return $user->hodDepartmentIds()->contains($record->employee?->department_id);
        }

        return false;
    }

    public function form(Schema $schema): Schema
    {
        /** @var LeaveRequest $record */
        $record = $this->record;
        $employeeId = $record->employee_id;

        $allowances = LeaveAllowance::query()
            ->where('employee_id', $employeeId)
            ->orderBy('year', 'desc')
            ->get()
            ->mapWithKeys(fn (LeaveAllowance $a) => [$a->id => "Godišnji odmor {$a->year} (ukupno {$a->total_days} dana)"]);

        return $schema->components([
            Section::make('Podaci zahtjeva')
                ->description('Datumi, tip i vezana kvota godišnjeg odmora.')
                ->schema([
                    Grid::make(2)->schema([
                        DatePicker::make('start_date')
                            ->label('Datum od')
                            ->native()
                            ->required()
                            ->displayFormat('d.m.Y')
                            ->format('Y-m-d')
                            ->live(),

                        DatePicker::make('end_date')
                            ->label('Datum do')
                            ->native()
                            ->required()
                            ->displayFormat('d.m.Y')
                            ->format('Y-m-d')
                            ->live(),
                    ]),

                    Select::make('type')
                        ->label('Tip')
                        ->options(collect(LeaveRequestType::cases())
                            ->mapWithKeys(fn (LeaveRequestType $t) => [$t->value => $t->getLabel()])
                            ->all())
                        ->required()
                        ->live(),

                    Select::make('leave_allowance_id')
                        ->label('Vezana godišnja kvota (samo godišnji)')
                        ->options($allowances)
                        ->visible(fn ($get) => $get('type') === LeaveRequestType::ANNUAL_LEAVE->value)
                        ->nullable(),

                    Textarea::make('notes')
                        ->label('Bilješka')
                        ->rows(3),
                ]),

            Section::make('Administratorska izmjena')
                ->description('Razlog izmjene se evidentira u povijesti admin akcija.')
                ->schema([
                    Textarea::make('admin_edit_reason')
                        ->label('Razlog izmjene (admin)')
                        ->required()
                        ->minLength(5)
                        ->rows(3),
                ]),
        ]);
    }

    protected function handleRecordUpdate($record, array $data): Model
    {
        /** @var LeaveRequest $record */
        /** @var User $user */
        $user = auth()->user();

        $reason = $data['admin_edit_reason'] ?? '';
        unset($data['admin_edit_reason']);

        if (isset($data['start_date']) && isset($data['end_date'])) {
            $start = Carbon::parse($data['start_date']);
            $end = Carbon::parse($data['end_date']);
            $data['days_count'] = $start->diffInDaysFiltered(
                fn (Carbon $date) => ! $date->isWeekend(),
                $end->copy()->addDay(),
            );
        }

        app(LeaveRequestAdminService::class)->editRequest($record, $data, $reason, $user);

        Notification::make()
            ->title('Zahtjev ažuriran')
            ->success()
            ->send();

        return $record->fresh();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
