<?php

namespace Amicus\FilamentEmployeeManagement\Exports;

use Amicus\FilamentEmployeeManagement\Models\Employee;
use Amicus\FilamentEmployeeManagement\Settings\HumanResourcesSettings;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class EmployeeReportExport implements FromArray, ShouldAutoSize, WithCustomStartCell, WithDrawings, WithEvents, WithHeadings
{
    protected int $employeeId;

    protected int $month;

    protected int $year;

    protected Employee $employee;

    protected int $totalWorkHours = 0;

    protected float $totalWorkFromHomeHours = 0;

    protected ?int $totalVacationHours = 0;

    protected ?int $totalSickLeaveHours = 0;

    protected ?int $totalOvertimeHours = 0;

    protected ?int $totalOtherHours = 0;

    protected ?int $totalMaternityLeaveHours = 0;

    public function __construct(int $employeeId, int $month, int $year)
    {
        $this->employeeId = $employeeId;
        $this->month = $month;
        $this->year = $year;
        $this->employee = Employee::find($employeeId);
    }

    public function drawings()
    {
        $drawing = new Drawing;
        $drawing->setName('Logo');
        $drawing->setDescription('Logo');

        $logoPathFromSettings = app(HumanResourcesSettings::class)->hr_documents_logo;
        if ($logoPathFromSettings && \Illuminate\Support\Facades\Storage::disk('public')->exists($logoPathFromSettings)) {
            $logoPath = \Illuminate\Support\Facades\Storage::disk('public')->path($logoPathFromSettings);
        } else {
            $logoPath = public_path('images/logo.png');
        }
        $drawing->setPath($logoPath);

        $drawing->setHeight(120);
        $drawing->setCoordinates('A2');
        $drawing->setOffsetX(100);
        $drawing->setOffsetY(5);

        return $drawing;
    }

    public function array(): array
    {
        $month = Carbon::create($this->year, $this->month);
        $report = $this->employee->getMonthlyWorkReport($month);

        $data = [];
        foreach ($report['daily_data'] as $daily) {
            $workHours = $daily['work_hours'] ?: '';
            $wfhHours = $daily['work_from_home_hours'] ?: '';

            // If there are work-from-home hours, the 'RADNI SATI' column should be blank,
            // as per the requirement to only log WFH hours under the WFH column in the export.
            if (! empty($wfhHours)) {
                $workHours = '';
            }

            $data[] = [
                $daily['date']->day,
                strtoupper($this->getDayNameInCroatian($daily['date']->dayOfWeek)),
                $workHours,
                $wfhHours,
                $daily['vacation_hours'] ?: '',
                $daily['sick_leave_hours'] ?: '',
                $daily['overtime_hours'] ?: '',
                $daily['maternity_leave_hours'] ?: '',
                $daily['other_hours'] ?: '',
            ];
        }

        $this->totalWorkHours = $report['totals']['work_hours'];
        $this->totalWorkFromHomeHours = $report['totals']['work_from_home_hours'];
        $this->totalVacationHours = $report['totals']['vacation_hours'];
        $this->totalSickLeaveHours = $report['totals']['sick_leave_hours'];
        $this->totalOvertimeHours = $report['totals']['overtime_hours'];
        $this->totalOtherHours = $report['totals']['other_hours'];
        $this->totalMaternityLeaveHours = $report['totals']['maternity_leave_hours'];

        // Add empty rows
        $data[] = ['', '', '', '', '', '', '', '', ''];
        $data[] = ['', '', '', '', '', '', '', '', ''];
        $data[] = ['', '', '', '', '', '', '', '', ''];

        // Add totals row
        $monthName = $this->getMonthNameInCroatian($this->month);
        $data[] = [
            "UKUPNO {$monthName} {$this->year}.",
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
        ];
        $data[] = [
            'RADNI SATI',
            $this->totalWorkHours ?: 0,
            '',
            '',
            '',
            '',
            '',
            '',
            '',
        ];
        $data[] = [
            'RAD OD KUĆE',
            $this->totalWorkFromHomeHours ?: 0,
            '',
            '',
            '',
            '',
            '',
            '',
            '',
        ];
        $data[] = [
            'GODIŠNJI ODMOR',
            $this->totalVacationHours ?: 0,
            '',
            '',
            '',
            '',
            '',
            '',
            '',
        ];
        $data[] = [
            'BOLOVANJE',
            $this->totalSickLeaveHours ?: 0,
            '',
            '',
            '',
            '',
            '',
            '',
            '',
        ];
        $data[] = [
            'PREKOVREMENI SATI',
            $this->totalOvertimeHours ?: 0,
            '',
            '',
            '',
            '',
            '',
            '',
            '',
        ];
        $data[] = [
            'PORODILJNI',
            $this->totalMaternityLeaveHours ?: 0,
            '',
            '',
            '',
            '',
            '',
            '',
            '',
        ];
        $data[] = [
            'OSTALO (plaćeno odsustvo)',
            $this->totalOtherHours ?: 0,
            '',
            '',
            '',
            '',
            '',
            '',
            '',
        ];

        return $data;
    }

    public function headings(): array
    {
        return [
            'DATUM',
            'DAN',
            'RADNI SATI',
            'RAD OD KUĆE',
            'GODIŠNJI ODMOR',
            'BOLOVANJE',
            'PREKOVREMENI SATI',
            'PORODILJNI',
            'OSTALO (plaćeno odsustvo)',
        ];
    }

    public function startCell(): string
    {
        return 'A6';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->mergeCells('A2:B2');
                $event->sheet->getDelegate()->mergeCells('A3:B3');
                $event->sheet->getDelegate()->mergeCells('C2:E2');
                $event->sheet->getDelegate()->mergeCells('C3:E3');
                $event->sheet->getDelegate()->mergeCells('F2:I2');
                $event->sheet->getDelegate()->mergeCells('F3:I3');

                $event->sheet->getDelegate()->setCellValue('C2', 'RADNI SATI');

                $monthName = $this->getMonthNameInCroatian($this->month);
                $event->sheet->getDelegate()->setCellValue('C3', "{$monthName} {$this->year}.");

                $event->sheet->getDelegate()->setCellValue('F2', $this->employee->full_name);

                // Style header rows
                $styleArray = [
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ];

                $event->sheet->getDelegate()->getStyle('A2:I3')->applyFromArray($styleArray);
                $event->sheet->getDelegate()->getRowDimension(2)->setRowHeight(50);
                $event->sheet->getDelegate()->getRowDimension(3)->setRowHeight(50);

                // Style headings row (row 6)
                $event->sheet->getDelegate()->getStyle('A6:I6')->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => [
                            'argb' => 'FFD3D3D3',
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                // Style data columns for centering
                $daysInMonth = Carbon::create($this->year, $this->month)->daysInMonth;
                $dataStartRow = 7;
                $dataEndRow = $dataStartRow + $daysInMonth + 2; // +2 to include the empty rows after daily data

                // Apply center alignment to data and total rows
                $event->sheet->getDelegate()->getStyle("C{$dataStartRow}:I{$dataEndRow}")->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);
            },
        ];
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
            6 => 'Subota',
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
            12 => 'PROSINAC',
        ];

        return $months[$month];
    }
}
