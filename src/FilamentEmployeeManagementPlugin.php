<?php

namespace Amicus\FilamentEmployeeManagement;


use Amicus\FilamentEmployeeManagement\Http\Middleware\EnsureUserHasEmployeeRecord;
use Amicus\FilamentEmployeeManagement\Http\Middleware\EnsureUserHasTelegramChatId;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Contracts\Plugin;
use Filament\Panel;

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
            ->middleware([
                EnsureUserHasEmployeeRecord::class,
                EnsureUserHasTelegramChatId::class,
            ])
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->discoverPages(in: __DIR__ . '/Filament/Pages', for: 'Amicus\\FilamentEmployeeManagement\\Filament\\Pages')
            ->discoverWidgets(in: __DIR__ . '/Filament/Widgets', for: 'Amicus\\FilamentEmployeeManagement\\Filament\\Widgets')
            ->discoverResources(in: __DIR__ . '/Filament/Resources', for: 'Amicus\\FilamentEmployeeManagement\\Filament\\Resources')
            ->discoverClusters(in: __DIR__ . '/Filament/Clusters', for: 'Amicus\\FilamentEmployeeManagement\\Filament\\Clusters')
            ->pages([
            ])
//            ->navigationItems([
//                NavigationItem::make("Profi")
//                    ->visible(fn() => auth()->user()->isEmployee() && auth()->user()->employee->id)
//                    ->sort(2)
//                    ->url(fn() => EmployeeResource::getUrl('view', ['record' => auth()->user()->employee->id]))
//                    ->icon(Heroicon::OutlinedUserCircle),
//            ])
        ;
    }

    public function boot(Panel $panel): void
    {
        //
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
