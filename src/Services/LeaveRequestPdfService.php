<?php

namespace Amicus\FilamentEmployeeManagement\Services;

use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use Amicus\FilamentEmployeeManagement\Settings\HumanResourcesSettings;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelPdf\Facades\Pdf;

class LeaveRequestPdfService
{
    public static function generatePdf(LeaveRequest $leaveRequest): string
    {
        try {
            $settings = app(HumanResourcesSettings::class);

            $logoPath = self::getFilePath($settings->hr_documents_logo);
            $directorSignature = self::getFilePath($settings->director_signature);
            $headOfDepartmentSignature = self::getFilePath($settings->head_of_department_signature);

            $companyName = $settings->company_name_for_hr_documents ?: '-';

            // Set locale for Croatian days
            \Carbon\Carbon::setLocale('hr');

            $fileName = 'zahtjev_za_godisnji_odmor_'.$leaveRequest->id.'.pdf';
            $path = 'user/'.$leaveRequest->employee_id.'/odsustva/'.$fileName;
            $fullPath = Storage::disk('local')->path($path);

            // Ensure directory exists
            $directory = dirname($fullPath);
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            Pdf::view('filament-employee-management::leave_request_pdf', [
                'leaveRequest' => $leaveRequest,
                'logoPath' => $logoPath,
                'companyName' => $companyName,
                'directorSignature' => $directorSignature,
                'headOfDepartmentSignature' => $headOfDepartmentSignature,
            ])->save($fullPath);

            return $path;
        } catch (\Exception $e) {
            \Log::error('PDF generation failed: '.$e->getMessage());

            return '';
        }
    }

    private static function getFilePath(?string $settingsPath): ?string
    {
        if ($settingsPath && Storage::disk('public')->exists($settingsPath)) {
            return Storage::disk('public')->url($settingsPath);
        }

        return null;
    }
}
