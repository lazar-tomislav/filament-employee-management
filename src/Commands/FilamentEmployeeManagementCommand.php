<?php

namespace Amicus\FilamentEmployeeManagement\Commands;

use Illuminate\Console\Command;

class FilamentEmployeeManagementCommand extends Command
{
    public $signature = 'filament-employee-management';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
