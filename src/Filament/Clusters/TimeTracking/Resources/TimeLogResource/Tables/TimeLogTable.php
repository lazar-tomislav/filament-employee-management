<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\TimeTracking\Resources\TimeLogResource\Tables;

use Amicus\FilamentEmployeeManagement\Enums\LogType;
use Amicus\FilamentEmployeeManagement\Enums\TimeLogStatus;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Tables;
use Filament\Tables\Table;

class TimeLogTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.first_name')
                    ->label('Zaposlenik')
                    ->formatStateUsing(fn ($record): string => "{$record->employee->first_name} {$record->employee->last_name}")
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('date')
                    ->label('Datum')
                    ->date('d.m.Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('hours')
                    ->label('Sati')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' h')
                    ->alignEnd()
                    ->sortable(),

                Tables\Columns\TextColumn::make('log_type')
                    ->label('Tip')
                    ->formatStateUsing(fn (string $state): string => LogType::from($state)->getLabel())
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'radni_sati' => 'success',
                        'bolovanje' => 'warning',
                        'godisnji' => 'info',
                        'placeni_slobodan_dan' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (string $state): string => TimeLogStatus::from($state)->getLabel())
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'confirmed' => 'success',
                        'planned' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('description')
                    ->label('Opis')
                    ->limit(50)
                    ->tooltip(fn (?string $state): ?string => $state)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Uneseno')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Ažurirano')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Obrisano')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('employee_id')
                    ->label('Zaposlenik')
                    ->relationship('employee', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn ($record): string => "{$record->first_name} {$record->last_name}")
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('log_type')
                    ->label('Tip')
                    ->options(LogType::class),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(TimeLogStatus::class),

                Tables\Filters\Filter::make('date_range')
                    ->label('Datum')
                    ->schema([
                        DatePicker::make('from')
                            ->label('Od'),
                        DatePicker::make('until')
                            ->label('Do'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q) => $q->whereDate('date', '>=', $data['from']))
                            ->when($data['until'], fn ($q) => $q->whereDate('date', '<=', $data['until']));
                    }),

                Tables\Filters\TrashedFilter::make()
                    ->label('Obrisani zapisi'),
            ])
            ->recordActions([
                Actions\ViewAction::make()
                    ->label('Prikaži'),
                Actions\EditAction::make()
                    ->label('Uredi'),
                Actions\DeleteAction::make()
                    ->label('Obriši'),
            ])
            ->toolbarActions([
                Actions\CreateAction::make()
                    ->label('Novi unos'),
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make()
                        ->label('Obriši odabrane'),
                    Actions\ForceDeleteBulkAction::make()
                        ->label('Trajno obriši'),
                    Actions\RestoreBulkAction::make()
                        ->label('Vrati obrisane'),
                ]),
            ]);
    }
}
