<?php

namespace Amicus\FilamentEmployeeManagement\Commands;

use Amicus\FilamentEmployeeManagement\Models\Holiday;
use Illuminate\Console\Command;

class PopulateHolidays extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employee:populate-holidays {--year=* : Years to populate (default: 2025,2026)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate holidays for specified years (2025 and 2026 by default)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $years = $this->option('year') ?: ['2025', '2026'];

        foreach ($years as $year) {
            $this->populateYear((int) $year);
        }

        $this->info('Holidays populated successfully!');

        return Command::SUCCESS;
    }

    private function populateYear(int $year): void
    {
        $this->info("Populating holidays for {$year}...");

        $holidays = $this->getHolidaysForYear($year);

        foreach ($holidays as $holiday) {
            Holiday::updateOrCreate(
                [
                    'date' => $holiday['date'],
                    'name' => $holiday['name'],
                ],
                [
                    'is_recurring' => $holiday['is_recurring'],
                ]
            );
        }

        $this->info("Added " . count($holidays) . " holidays for {$year}");
    }

    private function getHolidaysForYear(int $year): array
    {
        $baseHolidays = [
            ['name' => 'Nova godina', 'month' => 1, 'day' => 1, 'is_recurring' => true],
            ['name' => 'Bogojavljenje ili Sveta tri kralja', 'month' => 1, 'day' => 6, 'is_recurring' => true],
            ['name' => 'Praznik rada', 'month' => 5, 'day' => 1, 'is_recurring' => true],
            ['name' => 'Dan državnosti', 'month' => 5, 'day' => 30, 'is_recurring' => true],
            ['name' => 'Tijelo', 'month' => 6, 'day' => 19, 'is_recurring' => false], // This varies by year
            ['name' => 'Dan antifašističke borbe', 'month' => 6, 'day' => 22, 'is_recurring' => true],
            ['name' => 'Dan pobjede i domovinske zahvalnosti i Dan hrvatskih branitelja', 'month' => 8, 'day' => 5, 'is_recurring' => true],
            ['name' => 'Velika Gospa', 'month' => 8, 'day' => 15, 'is_recurring' => true],
            ['name' => 'Dan svih svetih', 'month' => 11, 'day' => 1, 'is_recurring' => true],
            ['name' => 'Dan sjećanja na žrtve Domovinskog rata', 'month' => 11, 'day' => 18, 'is_recurring' => true],
            ['name' => 'Božić', 'month' => 12, 'day' => 25, 'is_recurring' => true],
            ['name' => 'Sveti Stjepan', 'month' => 12, 'day' => 26, 'is_recurring' => true],
        ];

        // Year-specific holidays based on the images
        $yearSpecificHolidays = [
            2025 => [
                ['name' => 'Uskrs', 'month' => 4, 'day' => 20, 'is_recurring' => false],
                ['name' => 'Uskrsni ponedjeljak', 'month' => 4, 'day' => 21, 'is_recurring' => false],
                ['name' => 'Tijelovo', 'month' => 6, 'day' => 19, 'is_recurring' => false],
            ],
            2026 => [
                ['name' => 'Uskrs', 'month' => 4, 'day' => 5, 'is_recurring' => false],
                ['name' => 'Uskrsni ponedjeljak', 'month' => 4, 'day' => 6, 'is_recurring' => false],
                ['name' => 'Tijelovo', 'month' => 6, 'day' => 4, 'is_recurring' => false],
            ],
        ];

        $holidays = [];

        // Add recurring holidays
        foreach ($baseHolidays as $holiday) {
            // Skip Tijelovo from base holidays as it's handled in year-specific
            if ($holiday['name'] === 'Tijelo') {
                continue;
            }

            $holidays[] = [
                'name' => $holiday['name'],
                'date' => sprintf('%d-%02d-%02d', $year, $holiday['month'], $holiday['day']),
                'is_recurring' => $holiday['is_recurring'],
            ];
        }

        // Add year-specific holidays
        if (isset($yearSpecificHolidays[$year])) {
            foreach ($yearSpecificHolidays[$year] as $holiday) {
                $holidays[] = [
                    'name' => $holiday['name'],
                    'date' => sprintf('%d-%02d-%02d', $year, $holiday['month'], $holiday['day']),
                    'is_recurring' => $holiday['is_recurring'],
                ];
            }
        }

        return $holidays;
    }
}
