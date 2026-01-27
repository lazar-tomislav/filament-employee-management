<?php

namespace Amicus\FilamentEmployeeManagement\Services;

use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use Amicus\FilamentEmployeeManagement\Settings\HumanResourcesSettings;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class LeaveRequestPdfService
{
    public static function generatePdf(LeaveRequest $leaveRequest): string
    {
        try {
            $settings = app(HumanResourcesSettings::class);

            $logoPath = self::getFileBase64($settings->hr_documents_logo);
            $directorSignature = self::getFileBase64($settings->director_signature);

            // Get approver's signature from their employee profile
            $headOfDepartmentSignature = null;
            if ($leaveRequest->approver && $leaveRequest->approver->employee) {
                $headOfDepartmentSignature = self::getFileBase64($leaveRequest->approver->employee->signature_path);
            }

            // Get employee's signature from their profile
            $employeeSignature = $leaveRequest->employee
                ? self::getFileBase64($leaveRequest->employee->signature_path)
                : null;

            $companyName = $settings->company_name_for_hr_documents ?: '-';

            // Set locale for Croatian days
            \Carbon\Carbon::setLocale('hr');

            $fileName = 'zahtjev_za_godisnji_odmor_' . $leaveRequest->id . '.pdf';
            $path = 'user/' . $leaveRequest->employee_id . '/odsustva/' . $fileName;
            $fullPath = Storage::disk('local')->path($path);

            // Ensure directory exists
            $directory = dirname($fullPath);
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $pdf = Pdf::loadView('filament-employee-management::leave_request_pdf', [
                'leaveRequest' => $leaveRequest,
                'logoPath' => $logoPath,
                'companyName' => $companyName,
                'directorSignature' => $directorSignature,
                'headOfDepartmentSignature' => $headOfDepartmentSignature,
                'employeeSignature' => $employeeSignature,
            ]);

            $pdf->save($fullPath);

            return $path;
        } catch (\Exception $e) {
            \Log::error('PDF generation failed: ' . $e->getMessage());

            return '';
        }
    }

    private static function getFileBase64(?string $settingsPath): ?string
    {
        if ($settingsPath && Storage::disk('public')->exists($settingsPath)) {
            $content = Storage::disk('public')->get($settingsPath);
            $mimeType = Storage::disk('public')->mimeType($settingsPath);

            return 'data:' . $mimeType . ';base64,' . base64_encode($content);
        }

        return null;
    }
}
