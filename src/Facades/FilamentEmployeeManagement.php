<?php

namespace Amicus\FilamentEmployeeManagement\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Amicus\FilamentEmployeeManagement\FilamentEmployeeManagement
 */
class FilamentEmployeeManagement extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Amicus\FilamentEmployeeManagement\FilamentEmployeeManagement::class;
    }
}
