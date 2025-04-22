<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cookie;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Clear all existing cookies before attempting new login
        $cookies = $request->cookies->all();
        foreach ($cookies as $name => $value) {
            cookie()->forget($name);
        }

        $credentials = $request->validated();
        $email = $credentials['email'];
        $domain = explode('@', $email)[1];

        // Determine which guard to use based on email domain
        $guard = match ($domain) {
            'admin.com' => 'admin',
            'manager.com' => 'manager',
            'receptionist.com' => 'receptionist',
            default => 'client',
        };

        // Logout from all guards before attempting new login
        Auth::guard('admin')->logout();
        Auth::guard('manager')->logout();
        Auth::guard('receptionist')->logout();
        Auth::guard('client')->logout();

        // Attempt authentication with the specific guard
        if (Auth::guard($guard)->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return match ($domain) {
                'admin.com' => redirect()->intended(route('admin.dashboard', absolute: false)),
                'manager.com' => redirect()->intended(route('manager.dashboard', absolute: false)),
                'receptionist.com' => redirect()->intended(route('receptionist.dashboard', absolute: false)),
                default => redirect()->intended(route('client.dashboard', absolute: false)),
            };
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
