<?php

namespace Amicus\FilamentEmployeeManagement\Exports;

use Amicus\FilamentEmployeeManagement\Models\Employee;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class AllEmployeTimeReportExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithCustomStartCell, WithEvents, WithDrawings
{
    protected int $month;
    protected int $year;

    public function __construct(int $month, int $year)
    {
        $this->month = $month;
        $this->year = $year;
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('This is my logo');
        $drawing->setPath(public_path('images/logo.png'));
        $drawing->setHeight(120);
        $drawing->setCoordinates('A2');
        $drawing->setOffsetX(20);
        $drawing->setOffsetY(5);

        return $drawing;
    }

    public function array(): array
    {
        $employees = Employee::all();
        $data = [];
        $month = Carbon::create($this->year, $this->month);

        foreach ($employees as $employee) {
            $report = $employee->getMonthlyWorkReport($month);
            $totals = $report['totals'];

            $data[] = [
                $employee->full_name,
                $totals['work_hours'] + $totals['overtime_hours'],
                $totals['vacation_hours'] + $totals['other_hours'], // vacation_hours is annual leave, other_hours is paid leave
                $totals['sick_leave_hours'],
            ];
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'IMENA ZAPOSLENIKA',
            'RADNI SATI',
            'GODIŠNJI ODMOR',
            'BOLOVANJE',
        ];
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): array
    {
        return [
            6 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => [
                        'argb' => 'FFD3D3D3',
                    ],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ],
            "B:D" => [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }

    public function startCell(): string
    {
        return 'A6';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getDelegate()->mergeCells('A2:B2');
                $event->sheet->getDelegate()->mergeCells('A3:B3');
                $event->sheet->getDelegate()->mergeCells('C2:D2');
                $event->sheet->getDelegate()->mergeCells('C3:D3');

                $event->sheet->getDelegate()->setCellValue('C2',"RADNI SATI");

                $monthName = $this->getMonthNameInCroatian($this->month);
                $event->sheet->getDelegate()->setCellValue('C3', "{$monthName} {$this->year}.");

                $styleArray = [
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ];

                $event->sheet->getDelegate()->getStyle('A2:D3')->applyFromArray($styleArray);
                $event->sheet->getDelegate()->getRowDimension(2)->setRowHeight(50);
                $event->sheet->getDelegate()->getRowDimension(3)->setRowHeight(50);
            },
        ];
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
