<?php

namespace Amicus\FilamentEmployeeManagement\Exports;

use Amicus\FilamentEmployeeManagement\Models\TimeLog;
use Amicus\FilamentEmployeeManagement\Models\LeaveRequest;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class EmployeeReportExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    protected int $employeeId;
    protected int $month;
    protected int $year;
    
    public function __construct(int $employeeId, int $month, int $year)
    {
        $this->employeeId = $employeeId;
        $this->month = $month;
        $this->year = $year;
    }

    public function array(): array
    {
        $data = [];
        $daysInMonth = Carbon::create($this->year, $this->month)->daysInMonth;
        
        $totalWorkHours = 0;
        $totalVacationHours = 0;
        $totalSickLeaveHours = 0;
        $totalOvertimeHours = 0;
        $totalOtherHours = 0;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($this->year, $this->month, $day);
            $dayName = $this->getDayNameInCroatian($date->dayOfWeek);
            
            // Dohvati radne sate za taj dan
            $dailyWorkHours = $this->getDailyWorkHours($date);
            $totalWorkHours += $this->convertTimeToMinutes($dailyWorkHours);
            
            // Dohvati godišnji odmor
            $vacationHours = $this->getLeaveHours($date, 'vacation');
            $totalVacationHours += $this->convertTimeToMinutes($vacationHours);
            
            // Dohvati bolovanje
            $sickLeaveHours = $this->getLeaveHours($date, 'sick_leave');
            $totalSickLeaveHours += $this->convertTimeToMinutes($sickLeaveHours);
            
            // Prekovremeni sati - placeholder za sada
            $overtimeHours = TimeLog::getOvertimeHoursForDate($this->employeeId, $date->format('Y-m-d'));
            $totalOvertimeHours += $this->convertTimeToMinutes($overtimeHours);
            
            // Ostalo
            $otherHours = $this->getLeaveHours($date, 'other');
            $totalOtherHours += $this->convertTimeToMinutes($otherHours);

            $data[] = [
                $day,
                strtoupper($dayName),
                $dailyWorkHours ?: '',
                $vacationHours ?: '',
                $sickLeaveHours ?: '',
                $overtimeHours ?: '',
                $otherHours ?: '',
            ];
        }
        
        // Dodaj prazne redove
        $data[] = ['', '', '', '', '', '', ''];
        $data[] = ['', '', '', '', '', '', ''];
        $data[] = ['', '', '', '', '', '', ''];
        
        // Dodaj ukupno
        $monthName = $this->getMonthNameInCroatian($this->month);
        $data[] = ["UKUPNO $monthName $this->year.", '', '', '', '', '', ''];
        $data[] = ['RADNI SATI', '', $this->formatMinutesToHours($totalWorkHours), '', '', '', ''];
        $data[] = ['GODIŠNJI ODMOR', '', '', $this->formatMinutesToHours($totalVacationHours), '', '', ''];
        $data[] = ['BOLOVANJE', '', '', '', $this->formatMinutesToHours($totalSickLeaveHours), '', ''];
        $data[] = ['PREKOVREMENI SATI', '', '', '', '', $this->formatMinutesToHours($totalOvertimeHours), ''];
        $data[] = ['OSTALO', '', '', '', '', '', $this->formatMinutesToHours($totalOtherHours)];

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

    public function styles(Worksheet $sheet)
    {
        $styles = [
            1 => ['font' => ['bold' => true]], // Header row
        ];
        
        // Style weekend rows (Saturday = 6, Sunday = 0)
        $daysInMonth = Carbon::create($this->year, $this->month)->daysInMonth;
        
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($this->year, $this->month, $day);
            if ($date->isWeekend()) {
                $rowNumber = $day + 1; // +1 because row 1 is header
                $styles[$rowNumber] = [
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => 'F3F4F6'], // Light gray
                    ]
                ];
            }
        }
        
        return $styles;
    }

    private function getDailyWorkHours(Carbon $date): string
    {
        $timeLogs = TimeLog::where('employee_id', $this->employeeId)
            ->whereDate('date', $date->format('Y-m-d'))
            ->get();
        
        $totalMinutes = 0;
        foreach ($timeLogs as $timeLog) {
            $totalMinutes += TimeLog::convertTimeToMinutes($timeLog->hours);
        }
        
        return $totalMinutes > 0 ? TimeLog::formatMinutesToTime($totalMinutes) : '';
    }

    private function getLeaveHours(Carbon $date, string $type): string
    {
        $typeMapping = [
            'vacation' => 'vacation',
            'sick_leave' => 'sick_leave', 
            'other' => 'paid_leave'
        ];
        
        $leaveRequest = LeaveRequest::where('employee_id', $this->employeeId)
            ->where('type', $typeMapping[$type])
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $date->format('Y-m-d'))
            ->whereDate('end_date', '>=', $date->format('Y-m-d'))
            ->first();
            
        return $leaveRequest ? '8' : '';
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

    private function convertTimeToMinutes(string $time): int
    {
        return TimeLog::convertTimeToMinutes($time);
    }

    private function formatMinutesToHours(int $minutes): string
    {
        return $minutes > 0 ? TimeLog::formatMinutesToTime($minutes) : '';
    }
}