<?php

namespace App\Http\Controllers;

use App\Models\Floor;
use App\Models\Manager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class FloorController extends Controller
{
    public function index(Request $request)
    {
        try {
            $isAdmin = Auth::guard('admin')->check();
            $isManager = Auth::guard('manager')->check();

            // Use correct guard for ID
            $userId = $isAdmin ? Auth::guard('admin')->id() : 
                     ($isManager ? Auth::guard('manager')->id() : null);

            // Set proper type with fully qualified class name
            $userType = $isAdmin ? 'App\\Models\\Admin' : 
                       ($isManager ? 'App\\Models\\Manager' : null);
            

            
            $query = Floor::with('created_by')
                          ->withCount('rooms');
            
            // Base filters for user permissions
            if (!$isAdmin) {
                $query->where('created_by_id', $userId)
                      ->where('created_by_type', $userType);
            }
            
            // Apply search filter if provided
            if ($request->has('search') && $request->input('search')) {
                $search = $request->input('search');
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('number', 'like', "%{$search}%");
                });
            }
            
            // Filter by manager if requested and user is admin
            if ($isAdmin && $request->has('manager') && $request->input('manager')) {
                $managerId = $request->input('manager');
                $query->where('created_by_id', $managerId)
                      ->where('created_by_type', 'App\\Models\\Manager');
            }
            
            // Paginate the results - 10 floors per page
            $floors = $query->latest()->paginate(10)->withQueryString();
            
            // Calculate stats for admin (using total count)
            $floorStats = [
                'total' => $isAdmin ? Floor::count() : $floors->total(),
                'withRooms' => Floor::whereHas('rooms')->count(),
                'empty' => Floor::whereDoesntHave('rooms')->count(),
            ];
            
            return Inertia::render('Floors/Index', [
                'floors' => $floors,
                'isAdmin' => $isAdmin,
                'userId' => $userId,
                'userType' => $userType,
                'floorStats' => $floorStats,
                'filters' => [
                    'search' => $request->input('search'),
                    'manager' => $request->input('manager'),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Floor index error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return Inertia::render('Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

public function store(Request $request)
{
    // Determine user type first to validate correctly
    $isAdmin = Auth::guard('admin')->check();
    $isManager = Auth::guard('manager')->check();
    
    // Log the raw incoming data
    Log::info('Raw request data:', $request->all());
    
    // Different validation rules based on user type
    if ($isAdmin) {
        $validated = $request->validate([
            'name' => 'required|string|min:3|max:255',
            'manager_id' => 'nullable|exists:managers,id',
        ]);
    } else {
        $validated = $request->validate([
            'name' => 'required|string|min:3|max:255',
        ]);
    }
    
    // Log the validated data
    Log::info('Validated data:', $validated);
    
    try {
        // For Admin users
        if ($isAdmin) {
            // If admin assigns to manager and manager_id is not null
            if (isset($validated['manager_id']) && $validated['manager_id'] != null) {
                $createdById = $validated['manager_id'];
                $createdByType = 'App\\Models\\Manager';
                Log::info('Admin assigned floor to manager ID: ' . $createdById);
            } else {
                // Admin creates for self
                $createdById = Auth::guard('admin')->id();
                $createdByType = 'App\\Models\\Admin';
                Log::info('Admin created floor for self, ID: ' . $createdById);
            }
        } 
        // For Manager users - always assign to the current manager
        else if ($isManager) {
            $createdById = Auth::guard('manager')->id();
            $createdByType = 'App\\Models\\Manager';
            Log::info('Manager created floor, ID: ' . $createdById);
        } 
        // Not authenticated
        else {
            Log::warning('Unauthenticated user tried to create a floor');
            return redirect()->back()->with('error', 'You must be logged in to create a floor');
        }
        
        // Generate floor number
        $floorNumber = $this->generateFloorNumber();
        Log::info('Generated floor number: ' . $floorNumber);
        
        // Create the floor
        $floor = Floor::create([
            'name' => $validated['name'],
            'number' => $floorNumber,
            'created_by_id' => $createdById,
            'created_by_type' => $createdByType,
        ]);
        
        Log::info('Floor created successfully:', [
            'id' => $floor->id,
            'name' => $floor->name,
            'number' => $floor->number,
            'created_by_id' => $floor->created_by_id,
            'created_by_type' => $floor->created_by_type
        ]);
        
        return redirect()->back()->with('success', 'Floor created successfully!');
    } catch (\Exception $e) {
        Log::error('Floor creation error: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        return redirect()->back()->with('error', 'Failed to create floor: ' . $e->getMessage());
    }
}


    public function update(Request $request, Floor $floor)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:3|max:255',
            'manager_id' => 'nullable|exists:managers,id', // Changed from users,id to managers,id
        ]);
        
        try {
            if (Auth::guard('admin')->check() && isset($validated['manager_id'])) {
                $floor->created_by_id = $validated['manager_id'];
                $floor->created_by_type = 'App\\Models\\Manager'; // Set correct type
            }
            
            $floor->name = $validated['name'];
            $floor->save();
            
            return redirect()->back()->with('success', 'Floor updated successfully!');
        } catch (\Exception $e) {
            Log::error('Floor update error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update floor: ' . $e->getMessage());
        }
    }
    
    public function destroy(Floor $floor)
    {
        
        // Check if floor has rooms
        if ($floor->rooms()->count() > 0) {
            return response()->json([
                'error' => 'Cannot delete a floor with rooms.'
            ], 422);
        }
        
        $floor->delete();
        
        return response()->json(['success' => true]);
    }
    
    public function getManagers()
    {
        if (!Auth::guard('admin')->check()) {
            abort(403);
        }
        
        try {
            // Get all users who are managers
            $managers = Manager::get(['id', 'name']);
                        
            return response()->json([
                'managers' => $managers
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching managers: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch managers',
                'managers' => []
            ], 500);
        }
    }
    
    private function generateFloorNumber()
    {
        $highestFloor = Floor::max('number');
        if (!$highestFloor) {
            return 'F0001'; // At least 4 digits
        }
        // Extract the numeric part and increment
        $number = intval(substr($highestFloor, 1)) + 1;
        return 'F' . str_pad($number, 4, '0', STR_PAD_LEFT); // Ensuring 4 digits
    }
}