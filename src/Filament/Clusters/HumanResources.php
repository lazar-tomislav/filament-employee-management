<?php

namespace Amicus\FilamentEmployeeManagement\Filament\Clusters;

use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class HumanResources extends Cluster
{
    use HasPageShield;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserCircle;

    protected static ?string $navigationLabel = "Ljudski resursi";
}
