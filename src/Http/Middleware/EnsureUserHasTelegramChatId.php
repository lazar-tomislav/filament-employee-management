<?php

namespace Amicus\FilamentEmployeeManagement\Http\Middleware;

use Amicus\FilamentEmployeeManagement\Filament\Pages\MissingEmployeePage;
use Amicus\FilamentEmployeeManagement\Filament\Pages\MissingTelegramChatIdPage;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasTelegramChatId
{
    public function handle(Request $request, Closure $next): Response
    {
        // Skip if not authenticated
        if(!auth()->check()){
            return $next($request);
        }

        // Skip if already on the missing employee page
        if($request->routeIs(MissingTelegramChatIdPage::getRouteName())){
            return $next($request);
        }

        // Skip if not in admin area
        if(!$request->is('admin*') && !$request->is('app*')){
            return $next($request);
        }

        // Skip logout route
        if ($request->is('logout')) {
            return $next($request);
        }
        // if the user is not an employee or has no employee record, allow access because other middleware will handle it
        if(!auth()->user()->employee()->exists()){
            return $next($request);
        }

        if(auth()->user()->employee->telegram_chat_id || auth()->user()->employee->telegram_denied_at){
            return $next($request);
        }


        // Redirect to missing telegram page
        return redirect()->to(MissingTelegramChatIdPage::getUrl());
    }
}
