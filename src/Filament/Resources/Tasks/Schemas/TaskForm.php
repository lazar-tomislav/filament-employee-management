<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Resources\Tasks\Schemas;

use Amicus\FilamentEmployeeManagement\Filament\Resources\Projects\Schemas\ProjectForm;
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
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('title')
                    ->label('Zadatak')
                    ->columnSpanFull()
                    ->autofocus()
                    ->placeholder('Novi zadatak')
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
                ProjectForm::projectIdSelect(),

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

            ]);
    }
}
