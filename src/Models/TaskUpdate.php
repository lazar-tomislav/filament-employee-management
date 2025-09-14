<?php

namespace Amicus\FilamentEmployeeManagement\Models;

use Amicus\FilamentEmployeeManagement\Observers\TaskUpdateObserver;
use App\Classes\Str;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[ObservedBy([TaskUpdateObserver::class])]
class TaskUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'employee_id',
        'body',
    ];

    /**
     * Dohvaća zadatak kojem ovo ažuriranje pripada.
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id')->withTrashed();
    }

    /**
     * Dohvaća sve zaposlenike koji su spomenuti (mentioned) u ovom ažuriranju.
     */
    public function mentions(): BelongsToMany
    {
        return $this->belongsToMany(
            Employee::class,
            'task_update_mentions', // Naziv pivot tablice
            'task_update_id',       // Vanjski ključ u pivotu koji se odnosi na ovaj model
            'mentioned_employee_id' // Vanjski ključ u pivotu koji se odnosi na spomenutog zaposlenika
        );
    }
    protected function body(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => $value ? Str::parseHtmlMentions($value) : null
        );
    }
}
