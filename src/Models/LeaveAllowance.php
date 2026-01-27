<?php

namespace Amicus\FilamentEmployeeManagement\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([\Amicus\FilamentEmployeeManagement\Observers\LeaveAllowanceObserver::class])]
class LeaveAllowance extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'year',
        'total_days',
        'valid_until_date',
        'notes',
    ];

    protected $casts = [
        'year' => 'integer',
        'total_days' => 'integer',
        'valid_until_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    protected function usedDays(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->leaveRequests()
                ->where('status', 'approved')
                ->sum('days_count'),
        );
    }

    protected function availableDays(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->total_days - $this->used_days,
        );

    }
}
