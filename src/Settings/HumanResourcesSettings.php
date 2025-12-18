<?php

namespace Amicus\FilamentEmployeeManagement\Settings;

use Spatie\LaravelSettings\Settings;

class HumanResourcesSettings extends Settings
{
    public string $company_name_for_hr_documents;
    public null|string $hr_documents_logo;

    public static function group(): string
    {
        return 'human_resources';
    }
}
