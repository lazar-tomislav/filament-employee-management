<?php

namespace Amicus\FilamentEmployeeManagement\Services;

use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use Amicus\FilamentEmployeeManagement\Settings\HumanResourcesSettings;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class LeaveRequestPdfService
{
    public static function generatePdf(LeaveRequest $leaveRequest): string
    {
        try {
            $logoPathFromSettings = app(HumanResourcesSettings::class)->hr_documents_logo;
            if($logoPathFromSettings && \Illuminate\Support\Facades\Storage::disk('public')->exists($logoPathFromSettings)){
                $logoPath = 'file://' .\Illuminate\Support\Facades\Storage::disk('public')->path($logoPathFromSettings);
            }else{
                $logoPath = null;
            }
            $companyName = app(HumanResourcesSettings::class)->company_name_for_hr_documents ?: '-';

            // Set locale for Croatian days
            \Carbon\Carbon::setLocale('hr');

            $html = view('filament-employee-management::leave_request_pdf', [
                'leaveRequest' => $leaveRequest,
                'logoPath' => $logoPath,
                'companyName' => $companyName,
            ])->render();

            $pdf = Pdf::loadHTML($html);

            $fileName = 'zahtjev_za_godisnji_odmor_' . $leaveRequest->id . '.pdf';
            $path = 'user/' . $leaveRequest->employee_id . '/odsustva/' . $fileName;

            Storage::disk('local')->put($path, $pdf->output());

            return $path;
        } catch (\Exception $e) {
            // Log error and return empty path if PDF generation fails
            \Log::error('PDF generation failed: ' . $e->getMessage());
            return '';
        }
    }
}
