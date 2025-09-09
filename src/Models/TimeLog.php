<?php

namespace Amicus\FilamentEmployeeManagement\Models;

use Amicus\FilamentEmployeeManagement\Enums\LogType;
use Amicus\FilamentEmployeeManagement\Enums\TimeLogStatus;
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
    ];

    protected $casts = [
        'date' => 'date',
        'hours' => 'decimal:2',
        'status' => TimeLogStatus::class,
        'log_type' => LogType::class,
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
                if (!$this->hours) {
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
        if (!$timeString) {
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
        $hours = intval($totalMinutes / 60);
        $minutes = $totalMinutes % 60;

        return sprintf('%02d:%02d', $hours, $minutes);
    }

    public static function getTotalHoursForDate($employeeId, $date): string
    {
        $timeLogs = self::where('employee_id', $employeeId)
            ->whereDate('date', $date)
            ->get();

        $totalMinutes = 0;

        foreach ($timeLogs as $timeLog) {
            $totalMinutes += self::convertTimeToMinutes($timeLog->hours);
        }

        return self::formatMinutesToTime($totalMinutes);
    }

    public static function getTotalHoursForWeek($employeeId, $startDate, $endDate): string
    {
        $timeLogs = self::where('employee_id', $employeeId)
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->get();

        $totalMinutes = 0;

        foreach ($timeLogs as $timeLog) {
            $totalMinutes += self::convertTimeToMinutes($timeLog->hours);
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

    public static function getOvertimeHoursForDate($employeeId, $date): string
    {
        // Placeholder za prekovremene sate - za sada vraća '0'
        // TODO: Implementirati logiku za računanje prekovremenih sati
        // Možda provjeriti ako je ukupno sati > 8 sati dnevno ili > 40 sati tjedno
        return '0';
    }
}
