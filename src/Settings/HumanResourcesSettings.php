<?php

namespace Amicus\FilamentEmployeeManagement\Settings;

use Spatie\LaravelSettings\Settings;

class HumanResourcesSettings extends Settings
{
    public string $company_name_for_hr_documents;
    public null|string $hr_documents_logo;
    public null|string $director_signature;
    public null|string $head_of_department_signature;

    public static function group(): string
    {
        return 'human_resources';
    }
}
