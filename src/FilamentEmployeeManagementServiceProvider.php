<?php

namespace Amicus\FilamentEmployeeManagement;

use Amicus\FilamentEmployeeManagement\Commands\FilamentEmployeeManagementCommand;
use Amicus\FilamentEmployeeManagement\Commands\TestMonthlyReportNotificationCommand;
use Amicus\FilamentEmployeeManagement\Commands\TestTelegramNotificationCommand;
use Amicus\FilamentEmployeeManagement\Testing\TestsFilamentEmployeeManagement;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Livewire\Features\SupportTesting\Testable;
use NotificationChannels\Telegram\TelegramServiceProvider;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentEmployeeManagementServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-employee-management';

    public static string $viewNamespace = 'filament-employee-management';

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name(static::$name)
            ->hasCommands($this->getCommands())
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('amicus/filament-employee-management');
            });

        $configFileName = $package->shortName();

        if (file_exists($package->basePath("/../config/{$configFileName}.php"))) {
            $package->hasConfigFile();
        }

        if (file_exists($package->basePath('/../database/migrations'))) {
            $package->hasMigrations($this->getMigrations());
        }

        if (file_exists($package->basePath('/../resources/lang'))) {
            $package->hasTranslations();
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }
    }

    public function packageRegistered(): void {}

    public function packageBooted(): void
    {
        // Asset Registration
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );

        FilamentAsset::registerScriptData(
            $this->getScriptData(),
            $this->getAssetPackageName()
        );

        // Icon Registration
        FilamentIcon::register($this->getIcons());

        // Handle Stubs
        if (app()->runningInConsole()) {
            foreach (app(Filesystem::class)->files(__DIR__ . '/../stubs/') as $file) {
                $this->publishes([
                    $file->getRealPath() => base_path("stubs/filament-employee-management/{$file->getFilename()}"),
                ], 'filament-employee-management-stubs');
            }

            // Publish config
            $this->publishes([
                __DIR__ . '/../config/employee-management.php' => config_path('employee-management.php'),
            ], 'employee-management-config');
        }

        // Testing
        Testable::mixin(new TestsFilamentEmployeeManagement);


        $this->app->booted(function (Application $app) {
            $schedule = $app->make(Schedule::class);
            $schedule->job(new \Amicus\FilamentEmployeeManagement\Jobs\SendMonthlyHoursReportNotification)
                ->dailyAt('17:00')
                ->when(function () {
                    $today = now();
                    if (!$today->isWorkday()) {
                        return false;
                    }
                    // Check if the next workday is in the next month.
                    $nextWorkday = $today->copy()->addWorkday();
                    return $today->month !== $nextWorkday->month;
                });
        });

    }

    protected function getAssetPackageName(): ?string
    {
        return 'amicus/filament-employee-management';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [
            // AlpineComponent::make('filament-employee-management', __DIR__ . '/../resources/dist/components/filament-employee-management.js'),
            // Css::make('filament-employee-management-styles', __DIR__ . '/../resources/dist/filament-employee-management.css'),
            // Js::make('filament-employee-management-scripts', __DIR__ . '/../resources/dist/filament-employee-management.js'),
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            FilamentEmployeeManagementCommand::class,
            TestTelegramNotificationCommand::class,
            TestMonthlyReportNotificationCommand::class,
        ];
    }

    /**
     * @return array<string>
     */
    protected function getIcons(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getRoutes(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getScriptData(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            'create_employees_table',
            'create_leave_allowances_table',
            'create_leave_requests_table',
            'create_time_logs_table',
            'create_holidays_table',
        ];
    }
}
