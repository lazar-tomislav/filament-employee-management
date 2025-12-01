<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Pages;

use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources;
use Amicus\FilamentEmployeeManagement\Settings\HumanResourcesSettings;
use BackedEnum;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class HumanResourcesSettingsPage extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string $settings = HumanResourcesSettings::class;

    protected static ?string $cluster = HumanResources::class;

    protected static ?int $navigationSort = 1500;

    protected static ?string $navigationLabel = "Postavke";

    protected static ?string $title="Postavke";
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Postavke ljudskih resursa')
                    ->components([
                        Forms\Components\TextInput::make('company_name_for_hr_documents')
                            ->label('Naziv tvrtke za HR dokumente')
                            ->helperText('Ovaj naziv se prikazuje u PDF-ovima i službenim dokumentima za zaposlenike.')
                            ->required(),

                        FileUpload::make('hr_documents_logo')
                            ->label('Logo za HR dokumente')
                            ->helperText('Ovaj logo se prikazuje u PDF-ovima i službenim dokumentima za zaposlenike.')
                            ->image()
                            ->previewable()
                            ->downloadable()
                            ->directory('hr-documents')
                            ->visibility('public'),
                    ]),
            ]);
    }
}
