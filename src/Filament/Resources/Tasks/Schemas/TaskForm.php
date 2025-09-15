<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Resources\Tasks\Schemas;

use Amicus\FilamentEmployeeManagement\Models\Employee;
use Amicus\FilamentEmployeeManagement\Models\Project;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TaskForm
{
    public static function configure(Schema $schema, bool $isQuickProjectCreate=false, array $extraFields = []): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('title')
                    ->label('Zadatak')
                    ->columnSpanFull()
                    ->autofocus()
                    ->placeholder('Izradi izvedbenu shemu')
                    ->required(),

                RichEditor::make('description')
                    ->label('Opis zadatka')
                    ->columnSpanFull()
                    ->placeholder('Detaljno opisati što je potrebno napraviti...')
                    ->grow()
                    ->toolbarButtons([
                        ['attachFiles', 'bold', 'italic', 'underline', 'strike','link'],
                        ['undo', 'redo'],
                    ])
                    ->helperText('Detaljan opis zadatka i svih potrebnih informacija'),

                ...$extraFields,

                Select::make('project_id')
                    ->label('Projekt')
                    ->options(Project::options())
                    ->placeholder('Odaberite projekt (opcionalno)')
                    ->helperText('Ostavite prazno ako je jednokratan zadatak ili nije vezan uz projekt')
                    ->visible(fn()=>!$isQuickProjectCreate)
                    ->searchable()
                    ->preload(),

                Select::make('assignee_id')
                    ->required()
                    ->label('Zadužena osoba')
                    ->options(Employee::options())
                    ->placeholder('Odaberite zaduženu osobu')
                    ->searchable()
                    ->preload(),

                DatePicker::make('due_at')
                    ->label('Rok izvršavanja')
                    ->placeholder('Odaberite datum i vrijeme')
                    ->default(now()->addDay(7))
                    ->afterOrEqual(now())
                    ->native(true),

                Section::make()
                    ->hiddenLabel()
                    ->key("naplata-billing")
                    ->columnSpanFull()
                    ->columns(2)

                ->schema([
                    Checkbox::make('is_billable')
                        ->label('Naplativi zadatak')
                        ->helperText("Ako nije naplatno klijentu, pokriva NetEko.")
                        ->live()
                        ->default(true),

                    TextInput::make('billed_amount')
                        ->label('Iznos naplate klijentu (€)')
                        ->placeholder('150.00')
                        ->numeric()
                        ->prefix('€')
                        ->step(0.01)
                        ->visible(fn ($get) => $get('is_billable')),
                ])
            ]);
    }
}
