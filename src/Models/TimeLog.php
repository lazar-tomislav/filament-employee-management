<?php

namespace Amicus\FilamentEmployeeManagement\Models;

use Amicus\FilamentEmployeeManagement\Enums\LogType;
use Amicus\FilamentEmployeeManagement\Enums\TimeLogStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TimeLog extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'date',
        'hours',
        'description',
        'status',
        'log_type',
        'is_work_from_home',
    ];

    protected $casts = [
        'date' => 'date',
        'hours' => 'decimal:2',
        'status' => TimeLogStatus::class,
        'log_type' => LogType::class,
        'is_work_from_home' => 'boolean',
    ];

    public function employee()
    {
        // ignore deleted_at for the employee relationship
        return $this->belongsTo(Employee::class)
            ->withTrashed();
    }

    public function formattedHours(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->hours) {
                    return '00:00';
                }

                // Ako je već u HH:MM formatu, vrati tako
                if (str_contains($this->hours, ':')) {
                    return $this->hours;
                }

                // Konvertiraj decimal u HH:MM
                $totalMinutes = (float) $this->hours * 60;
                $hours = intval($totalMinutes / 60);
                $minutes = $totalMinutes % 60;

                return sprintf('%02d:%02d', $hours, $minutes);
            }
        );
    }

    public static function convertTimeToMinutes($timeString): int
    {
        if (! $timeString) {
            return 0;
        }

        if (str_contains($timeString, ':')) {
            $timeParts = explode(':', $timeString);
            if (count($timeParts) >= 2) {
                $hours = (int) $timeParts[0];
                $minutes = (int) $timeParts[1];

                return ($hours * 60) + $minutes;
            }
        }

        // Ako su sati u decimal formatu (npr. 8.5)
        return (int) ((float) $timeString * 60);
    }

    public static function formatMinutesToTime(int $totalMinutes): string
    {
        $hours = (int) ($totalMinutes / 60);
        $minutes = $totalMinutes % 60;

        return sprintf('%02d:%02d', $hours, $minutes);
    }

    /**
     * Izračunaj ukupne minute za jedan dan (uključuje TimeLog, praznike i odsutnosti).
     * NAPOMENA: Ako se dodaju nove kategorije sati, ažuriraj ovu metodu.
     */
    private static function calculateTotalMinutesForDate(int $employeeId, Carbon $date): int
    {
        // 1. TimeLog sati (uneseno radno vrijeme)
        $timeLogs = self::where('employee_id', $employeeId)
            ->whereDate('date', $date)
            ->get();

        $totalMinutes = 0;
        foreach ($timeLogs as $timeLog) {
            $totalMinutes += self::convertTimeToMinutes($timeLog->hours);
        }

        // 2. Praznici (8h)
        $holidays = Holiday::getHolidaysForDate($date);
        $isPublicHoliday = $holidays->count() > 0;

        if ($isPublicHoliday) {
            $totalMinutes += Employee::HOURS_PER_WORK_DAY * 60;
        }

        // 3. Odsutnosti - godišnji, bolovanje, plaćeni dopust, rodiljni, itd. (8h)
        // Samo na radne dane koji nisu praznici
        if (! $isPublicHoliday && in_array($date->dayOfWeek, Employee::WORK_DAYS)) {
            $leaveRequest = LeaveRequest::getLeaveRequestsForDate($employeeId, $date)->first();
            if ($leaveRequest) {
                $totalMinutes += Employee::HOURS_PER_WORK_DAY * 60;
            }
        }

        return $totalMinutes;
    }

    public static function getTotalHoursForDate($employeeId, $date): string
    {
        $parsedDate = Carbon::parse($date);
        $totalMinutes = self::calculateTotalMinutesForDate($employeeId, $parsedDate);

        return self::formatMinutesToTime($totalMinutes);
    }

    public static function getTotalHoursForWeek($employeeId, $startDate, $endDate): string
    {
        $parsedStartDate = Carbon::parse($startDate);
        $parsedEndDate = Carbon::parse($endDate);

        $totalMinutes = 0;
        $currentDate = $parsedStartDate->copy();

        while ($currentDate->lte($parsedEndDate)) {
            $totalMinutes += self::calculateTotalMinutesForDate($employeeId, $currentDate->copy());
            $currentDate->addDay();
        }

        return self::formatMinutesToTime($totalMinutes);
    }

    public static function getTimeLogsForDate($employeeId, $date)
    {
        return self::where('employee_id', $employeeId)
            ->whereDate('date', $date)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Popuni cijeli mjesec s 8h za sve radne dane.
     * Preskače dane koji već imaju unose, praznike ili odobrene odsutnosti.
     *
     * @return array{created: int, skipped: int}
     */
    public static function fillMonthWithDefaultHours(int $employeeId, int $month, int $year): array
    {
        $result = [
            'created' => 0,
            'skipped' => 0,
        ];

        $startDate = Carbon::create($year, $month, 1);
        $daysInMonth = $startDate->daysInMonth;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($year, $month, $day);

            if (! in_array($date->dayOfWeek, Employee::WORK_DAYS)) {
                $result['skipped']++;

                continue;
            }

            $existingTimeLog = self::where('employee_id', $employeeId)
                ->whereDate('date', $date)
                ->exists();

            if ($existingTimeLog) {
                $result['skipped']++;

                continue;
            }

            $holidays = Holiday::getHolidaysForDate($date);
            if ($holidays->count() > 0) {
                $result['skipped']++;

                continue;
            }

            $leaveRequests = LeaveRequest::getLeaveRequestsForDate($employeeId, $date->format('Y-m-d'));
            if ($leaveRequests->count() > 0) {
                $result['skipped']++;

                continue;
            }

            self::create([
                'employee_id' => $employeeId,
                'date' => $date,
                'hours' => Employee::HOURS_PER_WORK_DAY,
                'description' => null,
                'status' => TimeLogStatus::default(),
                'log_type' => LogType::RADNI_SATI,
                'is_work_from_home' => false,
            ]);

            $result['created']++;
        }

        return $result;
    }
}
