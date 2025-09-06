<?php

namespace Amicus\FilamentEmployeeManagement\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Holiday extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'date',
        'is_recurring',
    ];

    protected $casts = [
        'date' => 'date',
        'is_recurring' => 'boolean',
    ];

    public static function getHolidaysForMonth(Carbon $month): array
    {
        $startDate = $month->copy()->startOfMonth();
        $endDate = $month->copy()->endOfMonth();

        return self::getHolidayDatesInRange($startDate, $endDate);
    }

    public static function getHolidayDatesInRange(Carbon $startDate, Carbon $endDate): array
    {
        $holidays = [];

        // Get non-recurring holidays
        $nonRecurring = self::where('is_recurring', false)
            ->whereBetween('date', [$startDate, $endDate])
            ->pluck('date');

        foreach ($nonRecurring as $date) {
            $holidays[] = $date->format('Y-m-d');
        }

        // Get recurring holidays
        $recurring = self::where('is_recurring', true)->get();

        for ($year = $startDate->year; $year <= $endDate->year; $year++) {
            foreach ($recurring as $holiday) {
                $holidayDate = Carbon::parse($holiday->date)->setYear($year);
                if ($holidayDate->between($startDate, $endDate)) {
                    $holidays[] = $holidayDate->format('Y-m-d');
                }
            }
        }

        return array_unique($holidays);
    }

    public static function getHolidaysForDate(Carbon $date): \Illuminate\Support\Collection
    {
        $dateString = $date->format('Y-m-d');

        // Non-recurring holidays
        $nonRecurring = self::where('is_recurring', false)
            ->whereDate('date', $dateString)
            ->get();

        // Recurring holidays
        $recurring = self::where('is_recurring', true)
            ->whereMonth('date', $date->month)
            ->whereDay('date', $date->day)
            ->get();

        return $nonRecurring->merge($recurring);
    }
}