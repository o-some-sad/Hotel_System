<?php

namespace App\Http\Controllers;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

abstract class Controller extends \Illuminate\Routing\Controller
{
 
    use AuthorizesRequests, ValidatesRequests;

    protected $user;
    protected $role;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = $request->current_user;
            $this->role = $request->current_role;
            return $next($request);
        });
    }

    protected function canAccess($requiredRole)
    {
        $roleHierarchy = [
            'admin' => ['admin', 'manager', 'receptionist', 'client'],
            'manager' => ['manager', 'receptionist', 'client'],
            'receptionist' => ['receptionist', 'client'],
            'client' => ['client']
        ];

        return in_array($requiredRole, $roleHierarchy[$this->role] ?? []);
    }
}