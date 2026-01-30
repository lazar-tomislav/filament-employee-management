<?php

namespace Amicus\FilamentEmployeeManagement\Exports;

use Amicus\FilamentEmployeeManagement\Models\Employee;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EmployeeReportTemplateExport
{
    protected int $employeeId;

    protected int $month;

    protected int $year;

    protected Employee $employee;

    /**
     * Column mapping for days 1-31
     * Day 1 = D, Day 2 = E, ..., Day 31 = AH
     */
    protected array $dayColumns = [
        1 => 'D', 2 => 'E', 3 => 'F', 4 => 'G', 5 => 'H', 6 => 'I', 7 => 'J',
        8 => 'K', 9 => 'L', 10 => 'M', 11 => 'N', 12 => 'O', 13 => 'P', 14 => 'Q',
        15 => 'R', 16 => 'S', 17 => 'T', 18 => 'U', 19 => 'V', 20 => 'W', 21 => 'X',
        22 => 'Y', 23 => 'Z', 24 => 'AA', 25 => 'AB', 26 => 'AC', 27 => 'AD',
        28 => 'AE', 29 => 'AF', 30 => 'AG', 31 => 'AH',
    ];

    /**
     * Row mapping for different hour types
     */
    protected array $hourTypeRows = [
        'vacation_hours' => 16,          // Godišnji odmor
        'sick_leave_hours' => 18,        // Bolovanje
        'maternity_leave_hours' => 20,   // Rodiljni dopust
        'other_hours' => 22,             // Plaćeni dopust
        'work_hours' => 30,              // Redovan rad
        'work_from_home_hours' => 32,    // Rad na daljinu
        'overtime_hours' => 33,          // Prekovremeni sati
    ];

    /**
     * Sheet names for each month (Croatian)
     */
    protected array $monthSheets = [
        1 => 'Siječanj',
        2 => 'Veljača',
        3 => 'Ožujak',
        4 => 'Travanj',
        5 => 'Svibanj',
        6 => 'Lipanj',
        7 => 'Srpanj',
        8 => 'Kolovoz',
        9 => 'Rujan',
        10 => 'Listopad',
        11 => 'Studeni',
        12 => 'Prosinac',
    ];

    public function __construct(int $employeeId, int $month, int $year)
    {
        $this->employeeId = $employeeId;
        $this->month = $month;
        $this->year = $year;
        $this->employee = Employee::find($employeeId);
    }

    public function download(string $fileName): BinaryFileResponse
    {
        // Increase limits
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        $templatePath = $this->getTemplatePath();
        $tempPath = $this->generateFile($templatePath);

        return response()->download($tempPath, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    protected function generateFile(string $templatePath): string
    {
        // Load template - XLSX is much faster than ODS
        $reader = IOFactory::createReader('Xlsx');
        $spreadsheet = $reader->load($templatePath);

        // Get the correct sheet for the month
        $sheetName = $this->monthSheets[$this->month];
        $sheet = $spreadsheet->getSheetByName($sheetName);

        if (! $sheet) {
            throw new \Exception("Sheet '{$sheetName}' not found in template");
        }

        // Set as active sheet
        $spreadsheet->setActiveSheetIndexByName($sheetName);

        // Get employee work report data
        $month = Carbon::create($this->year, $this->month);
        $report = $this->employee->getMonthlyWorkReport($month);

        // Fill employee name (row 3, after the label)
        $sheet->setCellValue('C3', $this->employee->full_name);

        // Fill department if available
        if ($this->employee->department) {
            $sheet->setCellValue('C4', $this->employee->department->name);
        }

        // Fill daily data
        foreach ($report['daily_data'] as $daily) {
            $day = $daily['date']->day;

            if (! isset($this->dayColumns[$day])) {
                continue;
            }

            $col = $this->dayColumns[$day];

            // Fill work hours (regular work) - only if NOT work from home
            if (! empty($daily['work_hours']) && empty($daily['work_from_home_hours'])) {
                $sheet->setCellValue($col . $this->hourTypeRows['work_hours'], $daily['work_hours']);
            }

            // Fill work from home hours
            if (! empty($daily['work_from_home_hours'])) {
                $sheet->setCellValue($col . $this->hourTypeRows['work_from_home_hours'], $daily['work_from_home_hours']);
            }

            // Fill vacation hours
            if (! empty($daily['vacation_hours'])) {
                $sheet->setCellValue($col . $this->hourTypeRows['vacation_hours'], $daily['vacation_hours']);
            }

            // Fill sick leave hours
            if (! empty($daily['sick_leave_hours'])) {
                $sheet->setCellValue($col . $this->hourTypeRows['sick_leave_hours'], $daily['sick_leave_hours']);
            }

            // Fill overtime hours
            if (! empty($daily['overtime_hours'])) {
                $sheet->setCellValue($col . $this->hourTypeRows['overtime_hours'], $daily['overtime_hours']);
            }

            // Fill maternity leave hours
            if (! empty($daily['maternity_leave_hours'])) {
                $sheet->setCellValue($col . $this->hourTypeRows['maternity_leave_hours'], $daily['maternity_leave_hours']);
            }

            // Fill other hours (paid leave)
            if (! empty($daily['other_hours'])) {
                $sheet->setCellValue($col . $this->hourTypeRows['other_hours'], $daily['other_hours']);
            }
        }

        // Save to temp file
        $tempDir = storage_path('app/temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $tempPath = $tempDir . '/' . uniqid('report_', true) . '.xlsx';

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($tempPath);

        // Clean up
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $tempPath;
    }

    protected function getTemplatePath(): string
    {
        $paths = [
            storage_path('templates/evidencija_radnog_vremena_2025.xlsx'),
            storage_path('app/templates/evidencija_radnog_vremena_2025.xlsx'),
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        throw new \Exception(
            'Template file not found. Please place evidencija_radnog_vremena_2025.xlsx in storage/templates/ or storage/app/templates/'
        );
    }
}
