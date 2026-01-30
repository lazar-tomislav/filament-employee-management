<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Pages;

use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources;
use Amicus\FilamentEmployeeManagement\Models\Employee;
use Amicus\FilamentEmployeeManagement\Settings\HumanResourcesSettings;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class HumanResourcesSettingsPage extends SettingsPage
{
    use HasPageShield;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string $settings = HumanResourcesSettings::class;

    protected static ?string $cluster = HumanResources::class;

    protected static ?int $navigationSort = 1500;

    protected static ?string $navigationLabel = 'Postavke';

    protected static string | UnitEnum | null $navigationGroup = 'Ostalo';

    protected static ?string $title = 'Postavke';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Postavke ljudskih resursa')
                    ->icon(Heroicon::OutlinedBuildingOffice2)
                    ->components([
                        Forms\Components\TextInput::make('company_name_for_hr_documents')
                            ->label('Naziv tvrtke za HR dokumente')
                            ->helperText('Ovaj naziv se prikazuje u PDF-ovima i službenim dokumentima za zaposlenike.')
                            ->required(),

                        FileUpload::make('hr_documents_logo')
                            ->label('Logo za HR dokumente')
                            ->helperText('Ovaj logo se prikazuje u PDF-ovima i službenim dokumentima za zaposlenike.')
                            ->image()
                            ->disk('public')
                            ->previewable()
                            ->downloadable()
                            ->directory('hr-documents')
                            ->visibility('public'),
                    ]),

                Grid::make(1)->schema([
                    Section::make('Direktor')
                        ->icon(Heroicon::OutlinedUserCircle)
                        ->description('Direktor je osoba koja ima finalno odobrenje za zahtjeve za odsustvo.')
                        ->components([
                            Forms\Components\Select::make('employee_director_id')
                                ->label('Direktor')
                                ->options(Employee::options())
                                ->searchable()
                                ->preload()
                                ->nullable()
                                ->helperText('Odaberite zaposlenika koji će imati ulogu direktora za odobravanje zahtjeva.'),

                            FileUpload::make('director_signature')
                                ->label('Potpis direktora')
                                ->helperText('Potpis direktora koji se prikazuje na HR dokumentima poput zahtjeva za G.O nakon odobrenja.')
                                ->image()
                                ->disk('public')
                                ->previewable()
                                ->downloadable()
                                ->directory('hr-documents/signatures')
                                ->visibility('public'),
                        ]),

                    Section::make('Voditelj za radne sate')
                        ->icon(Heroicon::OutlinedClock)
                        ->description('Osoba odgovorna za pregled i odobravanje mjesečnih izvještaja radnih sati.')
                        ->components([
                            Forms\Components\Select::make('employee_work_hours_approver_id')
                                ->label('Voditelj za radne sate')
                                ->options(Employee::options())
                                ->searchable()
                                ->preload()
                                ->nullable()
                                ->helperText('Odaberite zaposlenika koji će pregledavati i odobravati mjesečne izvještaje.'),
                        ]),
                ]),

            ]);
    }
}
