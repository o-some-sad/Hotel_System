<?php

namespace App\Http\Controllers;

use App\Models\Ban;
use App\Models\Client;
use App\Models\Manager;
use App\Models\Receptionist;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Http\JsonResponse;

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
            $search = $request->input('search');
            $query->whereHas('banned', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhere('reason', 'like', "%{$search}%");
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
        
        return Inertia::render('Bans/Create', [
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
        
        // Create the ban with the correct field names
        Ban::create([
            'banned_id' => $validated['user_id'],
            'banned_type' => $bannedType,
            'banned_by_id' => $bannedById,
            'banned_by_type' => $bannedByType,
            'reason' => $validated['reason'],
            'expires_at' => $expiresAt,
            'is_permanent' => $isPermanent
        ]);
        
        $routeName = $isAdmin ? 'admin.bans.index' : 'manager.bans.index';
        
        return redirect()->route($routeName)->with('success', 'User banned successfully');
    }
    
    public function revoke(Ban $ban)
    {
        $isAdmin = Auth::guard('admin')->check();
        $ban->delete();
        
        $routeName = $isAdmin ? 'admin.bans.index' : 'manager.bans.index';
        
        return redirect()->route($routeName)->with('success', 'Ban revoked successfully');
    }
    
    public function getManagers(): JsonResponse
    {
        $managers = Manager::select('id', 'name', 'email')->get();
        
        return response()->json([
            'managers' => $managers
        ]);
    }
    
    public function getClients(): JsonResponse
    {
        $clients = Client::select('id', 'name', 'email')->get();
        
        return response()->json([
            'clients' => $clients
        ]);
    }
    
    public function getReceptionists(): JsonResponse
    {
        $receptionists = Receptionist::select('id', 'name', 'email')->get();
        
        return response()->json([
            'receptionists' => $receptionists
        ]);
    }
}