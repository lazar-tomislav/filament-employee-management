<?php

namespace Amicus\FilamentEmployeeManagement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function leaveAllowances()
    {
        return $this->hasMany(LeaveAllowance::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public static function options()
    {
        // pluck all to array (first_name, last name (email) as value) and id as key
        return self::all()->pluck(function ($employee) {
            return "{$employee->first_name} {$employee->last_name} ({$employee->email})";
        }, 'id');

    }
}
