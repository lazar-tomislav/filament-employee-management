<?php

namespace Amicus\FilamentEmployeeManagement\Settings;

use Spatie\LaravelSettings\Settings;

class HumanResourcesSettings extends Settings
{
    public string $company_name_for_hr_documents;

    public ?string $hr_documents_logo;

    public ?string $director_signature;

    public ?int $employee_director_id = null;

    public static function group(): string
    {
        return 'human_resources';
    }
}
