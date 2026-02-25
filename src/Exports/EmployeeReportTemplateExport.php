<?php

namespace Amicus\FilamentEmployeeManagement\Exports;

use Amicus\FilamentEmployeeManagement\Models\Employee;
use Amicus\FilamentEmployeeManagement\Settings\HumanResourcesSettings;
use App\Services\TenantFeatureService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
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
    protected array $defaultHourTypeRows = [
        'vacation_hours' => 16,          // Godišnji odmor
        'holiday_hours' => 17,           // Plaćeni neradni dani i blagradi
        'sick_leave_hours' => 18,        // Bolovanje
        'maternity_leave_hours' => 20,   // Rodiljni dopust
        'other_hours' => 22,             // Plaćeni dopust
        'work_hours' => 30,              // Redovan rad
        'work_from_home_hours' => 32,    // Rad na daljinu
        'overtime_hours' => 33,          // Prekovremeni sati
    ];

    // For months 11 and 12, rows shift down by 1 after row 20 due to "Mirovanje radnog odnosa" at row 21
    protected array $hourTypeRowsNovemberDecember = [
        'vacation_hours' => 16,          // Godišnji odmor
        'holiday_hours' => 17,           // Plaćeni neradni dani i blagradi
        'sick_leave_hours' => 18,        // Bolovanje
        'maternity_leave_hours' => 20,   // Rodiljni dopust
        'other_hours' => 23,             // Plaćeni dopust (shifted down by 1)
        'work_hours' => 31,              // Redovan rad (shifted down by 1)
        'work_from_home_hours' => 33,    // Rad na daljinu (shifted down by 1)
        'overtime_hours' => 34,          // Prekovremeni sati (shifted down by 1)
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

    /**
     * Generate all months (1-12) in a single XLSX file.
     * Loads the full template once and fills each month sheet.
     */
    public function downloadAllMonths(string $fileName): BinaryFileResponse
    {
        set_time_limit(600);
        ini_set('memory_limit', '1G');

        $templatePath = $this->getTemplatePath();
        $tempPath = $this->generateAllMonthsFile($templatePath);

        return response()->download($tempPath, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    protected function generateFile(string $templatePath): string
    {
        // Load template - only the needed sheet for performance
        $reader = IOFactory::createReader('Xlsx');
        $sheetName = $this->monthSheets[$this->month];
        $reader->setLoadSheetsOnly([$sheetName]);
        $spreadsheet = $reader->load($templatePath);

        // Get the correct sheet for the month
        $sheet = $spreadsheet->getSheetByName($sheetName);

        if (! $sheet) {
            throw new \Exception("Sheet '{$sheetName}' not found in template");
        }

        // Set as active sheet
        $spreadsheet->setActiveSheetIndexByName($sheetName);

        // Fill this month's data
        $this->fillSheet($sheet, $this->month, $this->year);

        return $this->saveToTemp($spreadsheet);
    }

    protected function generateAllMonthsFile(string $templatePath): string
    {
        // Load full template with all sheets
        $reader = IOFactory::createReader('Xlsx');
        $spreadsheet = $reader->load($templatePath);

        for ($month = 1; $month <= 12; $month++) {
            $sheetName = $this->monthSheets[$month];
            $sheet = $spreadsheet->getSheetByName($sheetName);

            if (! $sheet) {
                continue;
            }

            $this->fillSheet($sheet, $month, $this->year);
        }

        // Set first month as active
        $spreadsheet->setActiveSheetIndexByName($this->monthSheets[1]);

        return $this->saveToTemp($spreadsheet);
    }

    /**
     * Fill a single sheet with employee data for the given month/year.
     */
    protected function fillSheet(Worksheet $sheet, int $month, int $year): void
    {
        // Resolve correct row mapping for this month
        $hourTypeRows = in_array($month, [11, 12])
            ? $this->hourTypeRowsNovemberDecember
            : $this->defaultHourTypeRows;

        // Get employee work report data
        $carbonMonth = Carbon::create($year, $month);
        $report = $this->employee->getMonthlyWorkReport($carbonMonth);

        // Update the year and month in the title
        $existingTitle = $sheet->getCell('L3')->getValue();
        if ($existingTitle) {
            // Replace any 4-digit year (e.g., 2025) with the correct year
            $updatedTitle = preg_replace('/\b\d{4}\b/', (string) $year, $existingTitle);
            // Also replace month name if needed
            $monthName = mb_strtoupper($this->monthSheets[$month]);
            $updatedTitle = preg_replace('/MJESEC\s+\w+\s+/', "MJESEC {$monthName} ", $updatedTitle);
            $sheet->setCellValue('L3', $updatedTitle);
        }

        // Fill employee name (D3:K3 merged cell)
        $sheet->setCellValue('D3', $this->employee->full_name);

        // Fill department if available (D4:N4 merged cell)
        if ($this->employee->department) {
            $sheet->setCellValue('D4', $this->employee->department->name);
        }

        // Fill job position if available (W4 merged cell)
        if (! empty($this->employee->job_position)) {
            $sheet->setCellValue('W4', 'Radno mjesto: '.$this->employee->job_position);
        }

        // Clear all pre-existing cell values from the template (e.g. 2025 holiday hours)
        foreach ($this->dayColumns as $day => $col) {
            foreach ($hourTypeRows as $row) {
                $sheet->setCellValue($col.$row, null);
            }
        }

        // Fill daily data
        foreach ($report['daily_data'] as $daily) {
            $day = $daily['date']->day;

            if (! isset($this->dayColumns[$day])) {
                continue;
            }

            $col = $this->dayColumns[$day];

            // Fill vacation hours
            if (! empty($daily['vacation_hours'])) {
                $sheet->setCellValue($col.$hourTypeRows['vacation_hours'], $daily['vacation_hours']);
            }

            // Fill holiday hours (public holidays)
            if (! empty($daily['holiday_hours'])) {
                $sheet->setCellValue($col.$hourTypeRows['holiday_hours'], $daily['holiday_hours']);
            }

            // Fill sick leave hours
            if (! empty($daily['sick_leave_hours'])) {
                $sheet->setCellValue($col.$hourTypeRows['sick_leave_hours'], $daily['sick_leave_hours']);
            }

            // Fill maternity leave hours
            if (! empty($daily['maternity_leave_hours'])) {
                $sheet->setCellValue($col.$hourTypeRows['maternity_leave_hours'], $daily['maternity_leave_hours']);
            }

            // Fill other hours (paid leave)
            if (! empty($daily['other_hours'])) {
                $sheet->setCellValue($col.$hourTypeRows['other_hours'], $daily['other_hours']);
            }

            // Logic for work hours based on client requirements:
            // 1. Workday + office work: first 8h → row 30 (Redovan rad), rest → row 33 (Prekovremeni)
            // 2. Workday + WFH: first 8h → row 32 (Rad na daljinu), rest → row 33 (Prekovremeni)
            // 3. Weekend + any work: all hours → row 33/34 (Prekovremeni) - regardless of WFH or office

            $totalHours = (float) ($daily['total_hours'] ?? 0);
            $totalWfhHours = (float) ($daily['total_wfh_hours'] ?? 0);
            $isWeekend = $daily['is_weekend'] ?? false;

            if ($totalHours > 0) {
                if ($isWeekend) {
                    // Weekend: ALL work goes to overtime
                    $sheet->setCellValue($col.$hourTypeRows['overtime_hours'], $totalHours);
                } else {
                    // Workday
                    if ($totalWfhHours > 0) {
                        // Workday + WFH: first 8h → WFH row, rest → overtime
                        $sheet->setCellValue($col.$hourTypeRows['work_from_home_hours'], min($totalWfhHours, 8));

                        if ($totalHours > 8) {
                            $sheet->setCellValue($col.$hourTypeRows['overtime_hours'], $totalHours - 8);
                        }
                    } else {
                        // Workday + office work: first 8h → work row, rest → overtime
                        $sheet->setCellValue($col.$hourTypeRows['work_hours'], min($totalHours, 8));

                        if ($totalHours > 8) {
                            $sheet->setCellValue($col.$hourTypeRows['overtime_hours'], $totalHours - 8);
                        }
                    }
                }
            }
        }

        // Fill start/end work times (row 7 = početak rada, row 8 = završetak rada)
        $tenantService = app(TenantFeatureService::class);
        $defaultStartHour = (int) explode(':', $tenantService->getDefaultStartTime())[0];
        $defaultEndHour = (int) explode(':', $tenantService->getDefaultEndTime())[0];

        $holidayDays = [];
        $workedDays = [];
        $workTimesByDay = [];
        foreach ($report['daily_data'] as $daily) {
            $day = $daily['date']->day;
            if (! empty($daily['is_holiday'])) {
                $holidayDays[] = $day;
            }
            if (($daily['total_hours'] ?? 0) > 0) {
                $workedDays[] = $day;
            }
            $workTimesByDay[$day] = [
                'start' => $daily['work_start_time'] ?? null,
                'end' => $daily['work_end_time'] ?? null,
            ];
        }

        $daysInMonth = Carbon::create($year, $month)->daysInMonth;
        for ($day = 1; $day <= $daysInMonth; $day++) {
            if (! isset($this->dayColumns[$day])) {
                continue;
            }

            $col = $this->dayColumns[$day];
            $date = Carbon::create($year, $month, $day);
            $isWeekend = in_array($date->dayOfWeek, [0, 6]);
            $isHoliday = in_array($day, $holidayDays);
            $hasWorkedHours = in_array($day, $workedDays);

            // Radni dan: uvijek popuni default
            // Vikend/praznik: popuni samo ako zaposlenik ima unesene sate
            if ((! $isWeekend && ! $isHoliday) || $hasWorkedHours) {
                $startTime = $workTimesByDay[$day]['start'] ?? null;
                $endTime = $workTimesByDay[$day]['end'] ?? null;

                $startHour = $startTime ? (int) substr($startTime, 0, 2) : $defaultStartHour;
                $endHour = $endTime ? (int) substr($endTime, 0, 2) : $defaultEndHour;

                $sheet->setCellValue($col.'7', $startHour);
                $sheet->setCellValue($col.'8', $endHour);
            }
        }

        // Set number format for data rows only (not the entire rectangular range)
        $firstCol = $this->dayColumns[1];
        $lastCol = $this->dayColumns[$daysInMonth];
        foreach (array_unique(array_values($hourTypeRows)) as $row) {
            $sheet->getStyle("{$firstCol}{$row}:{$lastCol}{$row}")
                ->getNumberFormat()
                ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_GENERAL);
        }

        // Add total formulas for each hour type row
        $lastDayColumn = $this->dayColumns[$daysInMonth];
        $totalColumn = match ($daysInMonth) {
            28 => 'AF',
            29 => 'AG',
            30 => 'AH',
            31 => 'AI',
            default => throw new \UnexpectedValueException("Neočekivan broj dana u mjesecu: {$daysInMonth}"),
        };

        // Rows 14-33 need totals (or 14-34 for Nov/Dec), plus one more row for grand total
        $lastDataRow = in_array($month, [11, 12]) ? 34 : 33;
        $grandTotalRow = $lastDataRow + 1;

        for ($row = 14; $row <= $lastDataRow; $row++) {
            $sheet->setCellValue($totalColumn.$row, "=SUM(D{$row}:{$lastDayColumn}{$row})");
        }

        // Grand total of all data rows
        $sheet->setCellValue($totalColumn.$grandTotalRow, "=SUM({$totalColumn}14:{$totalColumn}{$lastDataRow})");

        // Apply day formatting (white/gray/red) based on actual calendar
        $this->applyDayFormatting($sheet, Carbon::createFromDate($year, $month, 1), $report);

        // Fill signatures (employee, voditelj, direktor)
        $signatureRow = $grandTotalRow + 1;
        $this->fillSignatures($sheet, $signatureRow);
    }

    protected function saveToTemp(Spreadsheet $spreadsheet): string
    {
        $tempDir = storage_path('app/temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $tempPath = $tempDir.'/'.uniqid('report_', true).'.xlsx';

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($tempPath);

        // Clean up
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $tempPath;
    }

    protected function getTemplatePath(): string
    {
        // Get template path from config
        $configPath = config('employee-management.monthly_report.template_path');

        // Try configured path first
        $primaryPath = storage_path($configPath);
        if (file_exists($primaryPath)) {
            return $primaryPath;
        }

        // Fallback paths for backwards compatibility
        $fallbackPaths = [
            storage_path('templates/evidencija_radnog_vremena.xlsx'),
            storage_path('app/templates/evidencija_radnog_vremena.xlsx'),
            storage_path('templates/evidencija_radnog_vremena_2025.xlsx'),
            storage_path('app/templates/evidencija_radnog_vremena_2025.xlsx'),
        ];

        foreach ($fallbackPaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        throw new \Exception(
            "Template file not found. Please configure the template path in config/employee-management.php or place the file at: {$primaryPath}"
        );
    }

    /**
     * Fill signature images and names for employee, voditelj (head of department), and direktor.
     * Row positions: D = zaposlenik, N = voditelj, W = direktor.
     */
    protected function fillSignatures(Worksheet $sheet, int $signatureRow): void
    {
        $settings = app(HumanResourcesSettings::class);

        // Zaposlenik potpis (D{signatureRow})
        $this->addSignatureToCell(
            $sheet, 'D', $signatureRow,
            $this->employee->signature_path,
            $this->employee->full_name
        );

        // Voditelj odjela potpis (N{signatureRow})
        $voditelj = $this->employee->department?->headOfDepartment;
        $this->addSignatureToCell(
            $sheet, 'N', $signatureRow,
            $voditelj?->signature_path,
            $voditelj?->full_name
        );

        // Direktor potpis (W{signatureRow})
        $director = $settings->employee_director_id
            ? Employee::find($settings->employee_director_id)
            : null;
        $this->addSignatureToCell(
            $sheet, 'W', $signatureRow,
            $settings->director_signature,
            $director?->full_name
        );
    }

    /**
     * Add a signature image (if available) and name to a cell.
     */
    protected function addSignatureToCell(Worksheet $sheet, string $col, int $row, ?string $signaturePath, ?string $name): void
    {
        if ($name) {
            $sheet->setCellValue($col.$row, $name);
        }

        if ($signaturePath && Storage::disk('public')->exists($signaturePath)) {
            $fullPath = Storage::disk('public')->path($signaturePath);

            $drawing = new Drawing;
            $drawing->setName('Potpis');
            $drawing->setPath($fullPath);
            $drawing->setCoordinates($col.($row - 1));
            $drawing->setHeight(40);
            $drawing->setOffsetY(5);
            $drawing->setWorksheet($sheet);
        }
    }

    /**
     * Apply background color to each day column:
     * - Red for public holidays
     * - Gray for weekends (Saturday/Sunday)
     * - White for normal workdays
     */
    protected function applyDayFormatting(Worksheet $sheet, Carbon $monthDate, array $report): void
    {
        $daysInMonth = $monthDate->daysInMonth;
        $startRow = 7;
        $endRow = in_array($monthDate->month, [11, 12]) ? 34 : 33;

        // Build a set of holiday days from report data
        $holidayDays = [];
        foreach ($report['daily_data'] as $daily) {
            if (! empty($daily['is_holiday'])) {
                $holidayDays[] = $daily['date']->day;
            }
        }

        // Paint every day column using range-based styling (1 call per column instead of per cell)
        for ($day = 1; $day <= $daysInMonth; $day++) {
            if (! isset($this->dayColumns[$day])) {
                continue;
            }

            $col = $this->dayColumns[$day];
            $date = $monthDate->copy()->setDay($day);
            $isWeekend = in_array($date->dayOfWeek, [0, 6]);
            $isHoliday = in_array($day, $holidayDays);

            if ($isHoliday) {
                $sheet->getStyle("{$col}{$startRow}:{$col}{$endRow}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFFF0000'],
                    ],
                ]);
            } elseif ($isWeekend) {
                $sheet->getStyle("{$col}{$startRow}:{$col}{$endRow}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFD3D3D3'],
                    ],
                ]);
            } else {
                $sheet->getStyle("{$col}{$startRow}:{$col}{$endRow}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_NONE,
                    ],
                ]);
            }
        }
    }
}
