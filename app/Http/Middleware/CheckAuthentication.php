<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class CheckAuthentication
{
    public function handle(Request $request, Closure $next)
    {
        if (
            !Auth::guard('admin')->check() &&
            !Auth::guard('manager')->check() &&
            !Auth::guard('receptionist')->check() &&
            !Auth::guard('client')->check()
        ) {

            // Clear all cookies if not authenticated
            foreach ($request->cookies as $name => $value) {
                Cookie::queue(Cookie::forget(name: $name));
            }
            return redirect('/login');
        }

        return $next($request);
    }
}
