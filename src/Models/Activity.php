<?php

namespace Amicus\FilamentEmployeeManagement\Models;

use Amicus\FilamentEmployeeManagement\Classes\Str;
use Amicus\FilamentEmployeeManagement\Observers\ActivityObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[ObservedBy([ActivityObserver::class])]
class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'activityable_type',
        'activityable_id',
        'employee_id',
        'body',
    ];

    public function activityable(): MorphTo
    {
        return $this->morphTo();
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id')->withTrashed();
    }

    public function mentions(): BelongsToMany
    {
        return $this->belongsToMany(
            Employee::class,
            'activity_mentions',
            'activity_id',
            'mentioned_employee_id'
        );
    }

    protected function body(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? Str::parseHtmlMentions($value) : null
        );
    }
}
