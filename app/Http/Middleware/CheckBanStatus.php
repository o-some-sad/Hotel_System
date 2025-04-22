<?php

namespace App\Http\Middleware;

use App\Models\Ban;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class CheckBanStatus
{
    public function handle(Request $request, Closure $next)
    {
        $user = null;
        $userType = null;
        $guardName = null;
        
        // Check which guard is active
        foreach (['client', 'receptionist', 'manager'] as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();
                $userType = 'App\\Models\\' . ucfirst($guard);
                $guardName = $guard;
                break;
            }
        }
        
        if ($user) {
            // Check for active bans
            $activeBan = Ban::where('banned_id', $user->id)
                ->where('banned_type', $userType)
                ->whereNull('deleted_at')
                ->where(function ($query) {
                    $query->where('is_permanent', true)
                        ->orWhere('expires_at', '>', now());
                })
                ->first();
            
            if ($activeBan) {
                Auth::guard($guardName)->logout();
                
                // Format ban message with expiry info
                $banMessage = 'Your account has been banned. Reason: ' . $activeBan->reason;
                
                if (!$activeBan->is_permanent && $activeBan->expires_at) {
                    $expiryDate = Carbon::parse($activeBan->expires_at)->format('F j, Y \a\t g:i A');
                    $banMessage .= ' This ban will expire on ' . $expiryDate . '.';
                } else {
                    $banMessage .= ' This is a permanent ban.';
                }
                
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => $banMessage
                    ], 403);
                }
                
                return redirect('/login')->with('error', $banMessage);
            }
        }
        
        return $next($request);
    }
}