<?php

namespace Amicus\FilamentEmployeeManagement\Exports;

use Amicus\FilamentEmployeeManagement\Enums\LeaveRequestType;
use Amicus\FilamentEmployeeManagement\Models\TimeLog;
use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use Amicus\FilamentEmployeeManagement\Models\Employee;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;

class EmployeeReportExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    protected int $employeeId;
    protected int $month;
    protected int $year;
    protected Employee $employee;

    protected int $totalWorkHours = 0;
    protected ?int $totalVacationHours = 0;
    protected ?int $totalSickLeaveHours = 0;
    protected ?int $totalOvertimeHours = 0;
    protected ?int $totalOtherHours = 0;

    public function __construct(int $employeeId, int $month, int $year)
    {
        $this->employeeId = $employeeId;
        $this->month = $month;
        $this->year = $year;
        $this->employee = Employee::find($employeeId);
    }

    public function array(): array
    {
        $data = [];
        $daysInMonth = Carbon::create($this->year, $this->month)->daysInMonth;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($this->year, $this->month, $day);

            $this->totalWorkHours = $this->getDailyWorkHours($date);
            $this->totalVacationHours = $this->getLeaveHours($date, LeaveRequestType::ANNUAL_LEAVE);
            $this->totalSickLeaveHours = $this->getLeaveHours($date, LeaveRequestType::ANNUAL_LEAVE);
            $this->totalOvertimeHours = TimeLog::getOvertimeHoursForDate($this->employeeId, $date->format('Y-m-d'));
            $this->totalOtherHours = $this->getLeaveHours($date, LeaveRequestType::PAID_LEAVE);

            $data[] = [
                $day,
                strtoupper($this->getDayNameInCroatian($date->dayOfWeek)),
                $this->totalWorkHours ?: '',
                $this->totalVacationHours ?: '',
                $this->totalSickLeaveHours ?: '',
                $this->totalOvertimeHours ?: '',
                $this->totalOtherHours ?: '',
            ];
        }

        // Add empty rows
        $data[] = ['', '', '', '', '', '', ''];
        $data[] = ['', '', '', '', '', '', ''];
        $data[] = ['', '', '', '', '', '', ''];

        // Add totals row
        $monthName = $this->getMonthNameInCroatian($this->month);
        $data[] = [
            "UKUPNO {$monthName} {$this->year}.",
            '',
            '',
            '',
            '',
            '',
            ''
        ];
        $data[] = [
            'RADNI SATI',
            $this->totalWorkHours,
            '',
            '',
            '',
            '',
            ''
        ];
        $data[] = [
            'GODIŠNJI ODMOR',
            $this->totalVacationHours,
            '',
            '',
            '',
            '',
            ''
        ];
        $data[] = [
            'BOLOVANJE',
            $this->totalSickLeaveHours,
            '',
            '',
            '',
            '',
            ''
        ];
        $data[] = [
            'PREKOVREMENI SATI',
            $this->totalOvertimeHours,
            '',
            '',
            '',
            '',
            ''
        ];
        $data[] = [
            'OSTALO',
            $this->totalOtherHours,
            '',
            '',
            '',
            '',
            ''
        ];

        return $data;
    }

    public function headings(): array
    {
        return [
            'DATUM',
            'DAN',
            'RADNI SATI',
            'GODIŠNJI ODMOR',
            'BOLOVANJE',
            'PREKOVREMENI SATI',
            'OSTALO'
        ];
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): array
    {
        $styles = [
            // center header row and first column
            1 => [
                'font' => ['bold' => true],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ],
            "C1:C99" => [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ],
            "D1:D99" => [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ],
            "E1:E99" => [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ],
            "F1:F99" => [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ],
            "G1:G99" => [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ],
            'A2:A32' => [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ],
            'A32:A40' => [
                'font' => ['bold' => true],
            ],
        ];

        $daysInMonth = Carbon::create($this->year, $this->month)->daysInMonth;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($this->year, $this->month, $day);
            if ($date->isWeekend()) {
                $rowNumber = $day + 1;
                $styles[$rowNumber] = [
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => [
                            'argb' => 'FFD3D3D3',
                        ],
                    ],
                ];
            }
        }

        return $styles;
    }

    private function getDailyWorkHours(Carbon $date): float
    {
        $timeLogs = TimeLog::where('employee_id', $this->employeeId)
            ->whereDate('date', $date->format('Y-m-d'))
            ->get();
        $totalMinutes = 0;
        foreach ($timeLogs as $timeLog) {
            $totalMinutes += (float)$timeLog->hours;
        }

        return $totalMinutes;
    }

    private function getLeaveHours(Carbon $date, LeaveRequestType $type): ?int
    {
        $leaveRequest = LeaveRequest::where('employee_id', $this->employeeId)
            ->where('type', $type->value)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $date->format('Y-m-d'))
            ->whereDate('end_date', '>=', $date->format('Y-m-d'))
            ->first();

        return $leaveRequest ? 8 : null;
    }

    private function getDayNameInCroatian(int $dayOfWeek): string
    {
        $days = [
            0 => 'Nedjelja',
            1 => 'Ponedjeljak',
            2 => 'Utorak',
            3 => 'Srijeda',
            4 => 'Četvrtak',
            5 => 'Petak',
            6 => 'Subota'
        ];

        return $days[$dayOfWeek];
    }

    private function getMonthNameInCroatian(int $month): string
    {
        $months = [
            1 => 'SIJEČANJ',
            2 => 'VELJAČA',
            3 => 'OŽUJAK',
            4 => 'TRAVANJ',
            5 => 'SVIBANJ',
            6 => 'LIPANJ',
            7 => 'SRPANJ',
            8 => 'KOLOVOZ',
            9 => 'RUJAN',
            10 => 'LISTOPAD',
            11 => 'STUDENI',
            12 => 'PROSINAC'
        ];

        return $months[$month];
    }
}
