<?php

namespace Amicus\FilamentEmployeeManagement\Models;

use Amicus\FilamentEmployeeManagement\Observers\EmployeeObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

#[ObservedBy([EmployeeObserver::class])]
class Employee extends Model
{
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'address',
        'city',
        'oib',
        'mobile_tariff',
        'note',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('is_active', true);
        });
    }

    public function leaveAllowances()
    {
        return $this->hasMany(LeaveAllowance::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    protected function fullName():Attribute
    {
        return Attribute::make(
            get: fn () => "{$this->first_name} {$this->last_name}",
        );
    }

    public static function options()
    {
        // pluck all to array (first_name, last name (email) as value) and id as key
        return self::all()->pluck(function ($employee) {
            return "{$employee->first_name} {$employee->last_name} ({$employee->email})";
        }, 'id');

    }
}
