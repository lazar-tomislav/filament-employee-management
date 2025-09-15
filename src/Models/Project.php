<?php

namespace Amicus\FilamentEmployeeManagement\Models;

use Amicus\FilamentEmployeeManagement\Enums\StatusProjekta;
use App\Enums\TipProjekta;
use App\Models\Client;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'client_id',
        'employee_id',
        'type',
        'status',
        'site_location',
        'contract_amount',
        'start_date',
        'end_date',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'type' => TipProjekta::class,
            'status' => StatusProjekta::class,
            'contract_amount' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    protected function contractAmountFormatted(): Attribute
    {
        return Attribute::make(
            get: fn() => number_format($this->contract_amount, 2, ',', '.') . ' €',
        );
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id')
            ->withTrashed();
    }

    public static function options()
    {
        return self::all()->pluck(function ($employee) {
            return $employee->name;
        }, 'id');
    }
}
