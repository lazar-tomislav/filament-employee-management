<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('human_resources.employee_work_hours_approver_id', null);
    }

    public function down(): void
    {
        $this->migrator->delete('human_resources.employee_work_hours_approver_id');
    }
};
