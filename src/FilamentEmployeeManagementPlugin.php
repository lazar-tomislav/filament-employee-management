<?php

namespace Amicus\FilamentEmployeeManagement;

use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Pages\RequestLeavePage;
use Amicus\FilamentEmployeeManagement\Filament\Clusters\HumanResources\Resources\EmployeeResource;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Contracts\Plugin;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\Support\Icons\Heroicon;

class FilamentEmployeeManagementPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-employee-management';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->plugins([
                FilamentShieldPlugin::make(),
            ])
            ->discoverPages(in: __DIR__ . '/Filament/Pages', for: 'Amicus\\FilamentEmployeeManagement\\Filament\\Pages')
            ->discoverWidgets(in: __DIR__ . '/Filament/Widgets', for: 'Amicus\\FilamentEmployeeManagement\\Filament\\Widgets')
            ->discoverResources(in: __DIR__ . '/Filament/Resources', for: 'Amicus\\FilamentEmployeeManagement\\Filament\\Resources')
            ->discoverClusters(in: __DIR__ . '/Filament/Clusters', for: 'Amicus\\FilamentEmployeeManagement\\Filament\\Clusters')
            ->navigationItems([
                NavigationItem::make("Profi")
                    ->visible(fn() => auth()->user()->isEmployee() && auth()->user()->employee->id)
                    ->sort(2)
                    ->url(fn() => EmployeeResource::getUrl('view', ['record' => auth()->user()->employee->id]))
                    ->isActiveWhen(fn() => request()->routeIs(EmployeeResource::getRouteBaseName()))
                    ->icon(Heroicon::OutlinedUserCircle),

                NavigationItem::make("leave_requests")
                    ->label("Zatraži odsustvo")
                    ->visible(fn() => auth()->user()->isEmployee() && auth()->user()->employee->id)
                    ->sort(3)
                    ->url(fn() => RequestLeavePage::getUrl())
                    ->isActiveWhen(fn() => request()->routeIs(RequestLeavePage::getRouteName()))
                    ->icon(Heroicon::OutlinedCalendarDays),
            ])
        ;
    }

    public function boot(Panel $panel): void
    {
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}
