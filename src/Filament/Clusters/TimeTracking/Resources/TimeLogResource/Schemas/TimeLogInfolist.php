<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters\TimeTracking\Resources\TimeLogResource\Schemas;

use Filament\Infolists;
use Filament\Schemas\Schema;

class TimeLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Infolists\Components\TextEntry::make('employee_id')
                    ->numeric(),
                Infolists\Components\TextEntry::make('date')
                    ->date(),
                Infolists\Components\TextEntry::make('hours')
                    ->numeric(),
                Infolists\Components\TextEntry::make('status'),
                Infolists\Components\TextEntry::make('log_type'),
                Infolists\Components\TextEntry::make('created_at')
                    ->dateTime(),
                Infolists\Components\TextEntry::make('updated_at')
                    ->dateTime(),
                Infolists\Components\TextEntry::make('deleted_at')
                    ->dateTime(),
            ]);
    }
}
