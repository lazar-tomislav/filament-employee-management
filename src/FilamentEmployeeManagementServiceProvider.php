<?php

namespace Amicus\FilamentEmployeeManagement;

use Amicus\FilamentEmployeeManagement\Commands\FilamentEmployeeManagementCommand;
use Amicus\FilamentEmployeeManagement\Commands\PopulateHolidays;
use Amicus\FilamentEmployeeManagement\Commands\TestMonthlyReportNotificationCommand;
use Amicus\FilamentEmployeeManagement\Commands\TestTelegramNotificationCommand;
use Amicus\FilamentEmployeeManagement\Models\Employee;
use Amicus\FilamentEmployeeManagement\Models\Holiday;
use Amicus\FilamentEmployeeManagement\Models\LeaveAllowance;
use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use Amicus\FilamentEmployeeManagement\Models\TimeLog;
use Amicus\FilamentEmployeeManagement\Policies\EmployeePolicy;
use Amicus\FilamentEmployeeManagement\Policies\HolidayPolicy;
use Amicus\FilamentEmployeeManagement\Policies\LeaveAllowancePolicy;
use Amicus\FilamentEmployeeManagement\Policies\LeaveRequestPolicy;
use Amicus\FilamentEmployeeManagement\Policies\TimeLogPolicy;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
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

        FilamentAsset::register([
            Js::make('rich-content-plugins/mention', __DIR__ . '/../resources/dist/rich-content-plugins/mention.js')
                ->loadedOnRequest(),

            Css::make('rich-content-plugins/mention', __DIR__ . '/../resources/css/mention.css'),
        ]);

        // Icon Registration
        FilamentIcon::register($this->getIcons());

        Gate::policy(Employee::class, EmployeePolicy::class);
        Gate::policy(Holiday::class, HolidayPolicy::class);
        Gate::policy(LeaveAllowance::class, LeaveAllowancePolicy::class);
        Gate::policy(LeaveRequest::class, LeaveRequestPolicy::class);
        Gate::policy(TimeLog::class, TimeLogPolicy::class);

        // Register Livewire components
        $this->discoverLivewireComponents();

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

        $this->app->booted(function (Application $app) {
            $schedule = $app->make(Schedule::class);
            $schedule->job(new \Amicus\FilamentEmployeeManagement\Jobs\SendMonthlyHoursReportNotification)
                ->dailyAt('17:00')
                ->when(function () {
                    $today = now();
                    if (!$today->isWeekday()) {
                        return false;
                    }
                    // Check if the next workday is in the next month.
                    $nextWorkday = $today->copy()->addWeekday();
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
             Css::make('filament-employee-management-styles', __DIR__ . '/../resources/dist/filament-employee-management.css'),
//             Js::make('filament-employee-management-scripts', __DIR__ . '/../resources/dist/filament-employee-management.js'),
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
            PopulateHolidays::class,
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
            'create_projects_table',
            'create_tasks_table',
            'insert_user_roles',
            'alter_employees_table',
            'create_activities_table',
            'create_activity_mentions_table',
        ];
    }

    protected function discoverLivewireComponents(): void
    {
        $livewirePath = __DIR__ . '/Livewire';

        if (is_dir($livewirePath)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($livewirePath)
            );

            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $relativePath = str_replace($livewirePath . DIRECTORY_SEPARATOR, '', $file->getPathname());
                    $relativePath = str_replace(DIRECTORY_SEPARATOR, '\\', $relativePath);
                    $className = str_replace('.php', '', $relativePath);

                    $fullClass = "Amicus\\FilamentEmployeeManagement\\Livewire\\$className";

                    if (class_exists($fullClass)) {
                        // Convert PascalCase to kebab-case
                        $componentPath = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $className));
                        $componentPath = str_replace('\\', '.', $componentPath);
                        $componentName = 'filament-employee-management::' . $componentPath;

                        Livewire::component($componentName, $fullClass);
                    }
                }
            }
        }
    }
}
