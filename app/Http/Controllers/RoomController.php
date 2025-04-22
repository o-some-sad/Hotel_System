<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Floor;
use App\Models\Manager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class RoomController extends Controller
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
            
            // Query for rooms with related data
            $query = Room::with(['created_by', 'floor'])
                         ->withCount('reservations');
            
            // Base filters for user permissions
            if (!$isAdmin) {
                $query->where('created_by_id', $userId)
                      ->where('created_by_type', $userType);
            }
            
            // Apply search filter if provided
            if ($request->has('search') && $request->input('search')) {
                $search = $request->input('search');
                $query->where(function($q) use ($search) {
                    $q->where('number', 'like', "%{$search}%")
                      ->orWhere('capacity', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%");
                });
            }
            
            // Filter by floor if requested
            if ($request->has('floor') && $request->input('floor')) {
                $floorId = $request->input('floor');
                $query->where('floor_id', $floorId);
            }
            
            // Filter by manager if requested and user is admin
            if ($isAdmin && $request->has('manager') && $request->input('manager')) {
                $managerId = $request->input('manager');
                $query->where('created_by_id', $managerId)
                      ->where('created_by_type', 'App\\Models\\Manager');
            }
            
            // Paginate the results
            $rooms = $query->latest()->paginate(10)->withQueryString();
            
            // Get floors for dropdown, filtered by permission
            $floors = Floor::when(!$isAdmin, function($query) use ($userId, $userType) {
                return $query->where('created_by_id', $userId)
                             ->where('created_by_type', $userType);
            })->get(['id', 'name', 'number']);
            
            // Calculate room stats
            $roomStats = [
                'total' => Room::when(!$isAdmin, function($query) use ($userId, $userType) {
                    return $query->where('created_by_id', $userId)
                                 ->where('created_by_type', $userType);
                })->count(),
                'reserved' => Room::whereHas('reservations')
                    ->when(!$isAdmin, function($query) use ($userId, $userType) {
                        return $query->where('created_by_id', $userId)
                                     ->where('created_by_type', $userType);
                    })->count(),
                'available' => Room::whereDoesntHave('reservations')
                    ->when(!$isAdmin, function($query) use ($userId, $userType) {
                        return $query->where('created_by_id', $userId)
                                     ->where('created_by_type', $userType);
                    })->count(),
            ];
            
            return Inertia::render('Rooms/Index', [
                'rooms' => $rooms,
                'floors' => $floors,
                'isAdmin' => $isAdmin,
                'userId' => $userId,
                'userType' => $userType,
                'roomStats' => $roomStats,
                'filters' => [
                    'search' => $request->input('search'),
                    'floor' => $request->input('floor'),
                    'manager' => $request->input('manager'),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Room index error: ' . $e->getMessage());
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
        
        Log::info('Raw room request data:', $request->all());
        
        $validated = $request->validate([
            'name' => 'required|string|min:2|max:255',
            'number' => 'required|string|min:2|unique:rooms,number',
            'capacity' => 'required|integer|min:1',
            'price_in_dollars' => 'required|numeric|min:0',
            'floor_id' => 'required|exists:floors,id',
            'manager_id' => $isAdmin ? 'nullable|exists:managers,id' : 'nullable',
        ]);
        
        // Convert dollars to cents
        $priceInCents = round($validated['price_in_dollars'] * 100);
        
        try {
            // Determine creator
            if ($isAdmin && isset($validated['manager_id']) && $validated['manager_id'] != null) {
                $createdById = $validated['manager_id'];
                $createdByType = 'App\\Models\\Manager';
                Log::info('Admin assigned room to manager ID: ' . $createdById);
            } else if ($isAdmin) {
                $createdById = Auth::guard('admin')->id();
                $createdByType = 'App\\Models\\Admin';
                Log::info('Admin created room for self, ID: ' . $createdById);
            } else if ($isManager) {
                $createdById = Auth::guard('manager')->id();
                $createdByType = 'App\\Models\\Manager';
                Log::info('Manager created room, ID: ' . $createdById);
            } else {
                return redirect()->back()->with('error', 'You must be logged in to create a room');
            }
            
            // Check if user has access to the selected floor
            $floor = Floor::findOrFail($validated['floor_id']);
            
            // If manager, validate they can only use their own floors
            if ($isManager && ($floor->created_by_id != $createdById || $floor->created_by_type != $createdByType)) {
                return redirect()->back()->with('error', 'You do not have permission to create rooms on this floor');
            }
            
            // Create the room
            $room = Room::create([
                'name' => $validated['name'], // Add the name field
                'number' => $validated['number'],
                'capacity' => $validated['capacity'],
                'price' => $priceInCents,
                'floor_id' => $validated['floor_id'],
                'created_by_id' => $createdById,
                'created_by_type' => $createdByType,
            ]);
            
            Log::info('Room created successfully:', [
                'id' => $room->id,
                'name' => $room->name,
                'number' => $room->number
            ]);
            
            return redirect()->back()->with('success', 'Room created successfully!');
        } catch (\Exception $e) {
            Log::error('Room creation error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Failed to create room: ' . $e->getMessage());
        }
    }
    
    public function update(Request $request, Room $room)
{
    $isAdmin = Auth::guard('admin')->check();
    
    $validated = $request->validate([
        'name' => 'required|string|min:2|max:255', // Add this line
        'capacity' => 'required|integer|min:1',
        'price_in_dollars' => 'required|numeric|min:0',
        'floor_id' => 'required|exists:floors,id',
        'manager_id' => $isAdmin ? 'nullable|exists:managers,id' : 'nullable',
    ]);
    
    try {
        // If admin is reassigning the room to a manager
        if ($isAdmin && isset($validated['manager_id']) && $validated['manager_id'] != null) {
            $room->created_by_id = $validated['manager_id'];
            $room->created_by_type = 'App\\Models\\Manager';
        }
        
        // Check floor access
        $floor = Floor::findOrFail($validated['floor_id']);
        $userId = Auth::id();
        $userType = $isAdmin ? 'App\\Models\\Admin' : 'App\\Models\\Manager';
        
        if (!$isAdmin && ($floor->created_by_id != $userId || $floor->created_by_type != $userType)) {
            return redirect()->back()->with('error', 'You do not have permission to move rooms to this floor');
        }
        
        // Convert dollars to cents for storage
        $priceInCents = round($validated['price_in_dollars'] * 100);
        
        $room->name = $validated['name']; // Update the name field
        $room->capacity = $validated['capacity'];
        $room->price = $priceInCents;
        $room->floor_id = $validated['floor_id'];
        $room->save();
        
        return redirect()->back()->with('success', 'Room updated successfully!');
    } catch (\Exception $e) {
        Log::error('Room update error: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Failed to update room: ' . $e->getMessage());
    }
}
    
    public function destroy(Room $room)
    {
        try {
            // Check if room has reservations
            if ($room->reservations()->count() > 0) {
                return response()->json([
                    'error' => 'Cannot delete a room with reservations.'
                ], 422);
            }
            
            $room->delete();
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Room delete error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to delete room',
                'details' => app()->environment('local') ? $e->getMessage() : null
            ], 500);
        }
    }
    
    public function getManagers()
    {
        if (!Auth::guard('admin')->check()) {
            abort(403);
        }
        
        try {
            $managers = Manager::get(['id', 'name']);
            
            return response()->json([
                'managers' => $managers
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching managers for rooms: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch managers',
                'managers' => []
            ], 500);
        }
    }
}