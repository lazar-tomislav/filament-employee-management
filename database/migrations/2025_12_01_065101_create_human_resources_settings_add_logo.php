<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->migrator->add('human_resources.hr_documents_logo', null);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No database schema changes to reverse
    }
};
