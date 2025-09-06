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

        return self::where(function ($query) use ($startDate, $endDate) {
            $query->whereBetween('date', [$startDate, $endDate])
                ->orWhere(function ($query) use ($startDate) {
                    $query->where('is_recurring', true)
                        ->whereMonth('date', $startDate->month);
                });
        })->pluck('date')->map(function ($date) use ($startDate) {
            return Carbon::parse($date)->setYear($startDate->year)->format('Y-m-d');
        })->toArray();
    }
}
