<?php

namespace Amicus\FilamentEmployeeManagement\Services;

use Amicus\FilamentEmployeeManagement\Models\Employee;
use Amicus\FilamentEmployeeManagement\Models\Holiday;
use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use Amicus\FilamentEmployeeManagement\Settings\HumanResourcesSettings;
use App\Services\TenantFeatureService;
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
            $companyName = $settings->company_name_for_hr_documents ?: '-';

            // Set locale for Croatian days
            \Carbon\Carbon::setLocale('hr');

            $tenantKey = app(TenantFeatureService::class)->getActiveTenantKey();
            $isSplit = $tenantKey === 'rast_split';

            $fileName = $isSplit
                ? 'odluka_godisnji_odmor_' . $leaveRequest->id . '.pdf'
                : 'zahtjev_za_godisnji_odmor_' . $leaveRequest->id . '.pdf';

            $path = 'user/' . $leaveRequest->employee_id . '/odsustva/' . $fileName;
            $fullPath = Storage::disk('local')->path($path);

            // Ensure directory exists
            $directory = dirname($fullPath);
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            if ($isSplit) {
                $pdf = self::buildSplitPdf($leaveRequest, $settings, $logoPath, $directorSignature);
            } else {
                // Get head of department's signature from their employee profile
                $headOfDepartmentSignature = null;
                if ($leaveRequest->headOfDepartmentApprover) {
                    $headOfDepartmentSignature = self::getFileBase64($leaveRequest->headOfDepartmentApprover->signature_path);
                }

                // Get employee's signature from their profile
                $employeeSignature = $leaveRequest->employee
                    ? self::getFileBase64($leaveRequest->employee->signature_path)
                    : null;

                $pdf = Pdf::loadView('filament-employee-management::leave_request_pdf', [
                    'leaveRequest' => $leaveRequest,
                    'logoPath' => $logoPath,
                    'companyName' => $companyName,
                    'directorSignature' => $directorSignature,
                    'headOfDepartmentSignature' => $headOfDepartmentSignature,
                    'employeeSignature' => $employeeSignature,
                ]);
            }

            $pdf->save($fullPath);

            return $path;
        } catch (\Exception $e) {
            \Log::error('PDF generation failed: ' . $e->getMessage());

            return '';
        }
    }

    private static function buildSplitPdf(
        LeaveRequest $leaveRequest,
        HumanResourcesSettings $settings,
        ?string $logoPath,
        ?string $directorSignature
    ): \Barryvdh\DomPDF\PDF {
        $stampPath = self::getFileBase64($settings->company_stamp);

        // Dohvati allowance — direktno s zahtjeva ili fallback na allowance zaposlenika za tu godinu
        $allowance = $leaveRequest->leaveAllowance
            ?? $leaveRequest->employee->leaveAllowances()
                ->where('year', $leaveRequest->start_date->year)
                ->first();

        // Izračunaj prvi radni dan nakon godišnjeg (preskače vikende i praznike)
        $returnDate = $leaveRequest->end_date->copy()->addDay();
        $maxAttempts = 30;
        $attempts = 0;
        while ($attempts++ < $maxAttempts &&
            (! in_array($returnDate->dayOfWeek, Employee::WORK_DAYS) || Holiday::getHolidaysForDate($returnDate)->isNotEmpty())
        ) {
            $returnDate->addDay();
        }

        return Pdf::loadView('filament-employee-management::split_leave_request', [
            'logoPath' => $logoPath,
            'decisionDate' => now()->format('d.m.Y.'),
            'employeeName' => $leaveRequest->employee->full_name,
            'jobTitle' => $leaveRequest->employee->job_position ?? '-',
            'year' => $leaveRequest->start_date->year,
            'totalLeaveDays' => $allowance?->total_days ?? '-',
            'requestedDays' => $leaveRequest->days_count,
            'startDate' => $leaveRequest->start_date->format('d.m.Y.'),
            'returnDate' => $returnDate->format('d.m.Y.'),
            'totalUsedDays' => $allowance?->used_days ?? '-',
            'remainingDays' => $allowance?->available_days ?? '-',
            'directorSignaturePath' => $directorSignature,
            'stampPath' => $stampPath,
        ]);
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
