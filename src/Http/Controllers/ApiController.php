<?php

namespace Amicus\FilamentEmployeeManagement\Http\Controllers;

use Amicus\FilamentEmployeeManagement\Models\Employee;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ApiController extends Controller
{
    public function userSearch(Request $request)
    {
        if(!auth()->check()){
            return response()->json([
                'error' => 'Unauthorized'
            ], 401);
        }
        Log::debug("search: " . $request->input('search'));

        // find employees by name or email and return array od id and label
        $searchQuery = $request->input('query');
        $query = Employee::query()
            ->select(['id', 'first_name', 'last_name', 'email']);

        if($request->has('search') || !empty($request->input('search'))){
            $query->where(function ($query) use ($searchQuery) {
                $query->where('first_name', 'like', '%' . $searchQuery . '%')
                    ->orWhere('last_name', 'like', '%' . $searchQuery . '%')
                    ->orWhere('email', 'like', '%' . $searchQuery . '%');
            });

        }

        $users = $query->take(20)
            ->get()
            ->pluck('first_name', 'id');

        $users = $users->map(function ($name, $id) {
            return [
                'id' => $id,
                'label' => $name,
            ];
        });
        Log::debug("users: ", $users->values()->all());
        return response()->json($users->values()->all());

    }
}
