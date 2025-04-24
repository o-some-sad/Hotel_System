<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsStaff
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user || !($this->isStaffUser($user))) {
            return response()->json([
                'message' => 'Unauthorized. Staff access only can reach this'
            ], 403);
        }
        return $next($request);
    }


    private function isStaffUser($user): bool
    {
        $userClass = get_class($user);

        $staffModels = [
            'App\Models\Admin',
            'App\Models\Manager',
            'App\Models\Receptionist'
        ];
        //Check if the class exist in the array
        return in_array($userClass, $staffModels);
    }
}
