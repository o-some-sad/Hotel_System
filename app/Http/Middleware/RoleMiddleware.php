<?php
// app/Http/Middleware/RoleMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    protected $roleHierarchy = [
        'admin' => ['admin', 'manager', 'receptionist', 'client'],
        'manager' => ['manager', 'receptionist', 'client'],
        'receptionist' => ['receptionist', 'client'],
        'client' => ['client']
    ];

    public function handle(Request $request, Closure $next, $roles)
    {
        $user = null;
        $currentRole = null;

        $rolesArray = array_map('trim', explode(',', $roles));

        foreach (['admin', 'manager', 'receptionist', 'client'] as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();
                $currentRole = $guard;

                // Check if any of the required roles are accessible by current role
                foreach ($rolesArray as $requiredRole) {
                    if (in_array($requiredRole, $this->roleHierarchy[$currentRole] ?? [])) {
                        $request->merge([
                            'current_user' => $user->only(['id', 'name', 'actual_email']),
                            'current_role' => $currentRole
                        ]);
                        return $next($request);
                    }
                }
            }
        }

        abort(403, 'Unauthorized action.');
    }
}
