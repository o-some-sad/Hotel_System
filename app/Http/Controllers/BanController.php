<?php

namespace App\Http\Controllers;

use App\Models\Ban;
use App\Models\Client;
use App\Models\Manager;
use App\Models\Receptionist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class BanController extends Controller
{
    public function index(Request $request)
    {
        $isAdmin = Auth::guard('admin')->check();
        $isManager = Auth::guard('manager')->check();
        
        $query = Ban::with(['banned', 'banned_by']);
        
        if ($isManager) {
            $query->where('banned_by_id', Auth::guard('manager')->id())
                  ->where('banned_by_type', 'App\\Models\\Manager');
        }
        
        if ($request->has('search')) {
        }
        
        $bans = $query->latest()->paginate(10);
        
        return Inertia::render('Bans/Index', [
            'bans' => $bans,
            'isAdmin' => $isAdmin
        ]);
    }
    
    public function create()
    {
        $isAdmin = Auth::guard('admin')->check();
        
        $clients = Client::get(['id', 'name', 'email']);
        $receptionists = Receptionist::get(['id', 'name', 'email']);
        
        $managers = $isAdmin ? Manager::get(['id', 'name', 'email']) : [];
        
        return Inertia::render('Bans/Create', [
            'clients' => $clients,
            'receptionists' => $receptionists,
            'managers' => $managers,
            'isAdmin' => $isAdmin
        ]);
    }
    
    public function store(Request $request)
    {
        $isAdmin = Auth::guard('admin')->check();
        $isManager = Auth::guard('manager')->check();
        
        $validated = $request->validate([
            'user_type' => 'required|string|in:client,receptionist,manager',
            'user_id' => 'required|integer',
            'reason' => 'required|string|min:10',
            'duration' => 'required|string|in:1day,1week,1month,permanent',
        ]);
        
        if (!$isAdmin && $validated['user_type'] === 'manager') {
            return redirect()->back()->with('error', 'You do not have permission to ban managers');
        }
        
        $expiresAt = null;
        $isPermanent = false;
        
        if ($validated['duration'] === 'permanent') {
            $isPermanent = true;
        } else {
            switch ($validated['duration']) {
                case '1day':
                    $expiresAt = now()->addDay();
                    break;
                case '1week':
                    $expiresAt = now()->addWeek();
                    break;
                case '1month':
                    $expiresAt = now()->addMonth();
                    break;
            }
        }
        
        $bannedType = 'App\\Models\\' . ucfirst($validated['user_type']);
        
        $bannedById = $isAdmin ? Auth::guard('admin')->id() : Auth::guard('manager')->id();
        $bannedByType = $isAdmin ? 'App\\Models\\Admin' : 'App\\Models\\Manager';
        
        Ban::create([
            'banned_id' => $validated['user_id'],
            'banned_type' => $bannedType,
            'banned_by_id' => $bannedById,
            'banned_by_type' => $bannedByType,
            'reason' => $validated['reason'],
            'expires_at' => $expiresAt,
            'is_permanent' => $isPermanent
        ]);
        
        return redirect()->route('bans.index')->with('success', 'User banned successfully');
    }
    
    public function revoke(Ban $ban)
    {
        $ban->delete();
        
        return redirect()->route('bans.index')->with('success', 'Ban revoked successfully');
    }
}