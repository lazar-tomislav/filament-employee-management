<?php

namespace Amicus\FilamentEmployeeManagement\Http\Middleware;

use Amicus\FilamentEmployeeManagement\Filament\Pages\MissingEmployeePage;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasEmployeeRecord
{
    public function handle(Request $request, Closure $next): Response
    {
        // Skip if not authenticated
        if (!auth()->check()) {
            return $next($request);
        }

        // Skip if already on the missing employee page
        if ($request->routeIs(MissingEmployeePage::getRouteName())) {
            return $next($request);
        }

        // Skip if not in admin area
        if (!$request->is('admin*')&& !$request->is('app*')) {
            return $next($request);
        }
        // Skip if user already has employee record
        if (auth()->user()->isEmployee() || auth()->user()->employee()->exists()) {

            return $next($request);
        }

        // Redirect to missing employee page
        return redirect()->to(MissingEmployeePage::getUrl());
    }
}
