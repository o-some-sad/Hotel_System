<?php

namespace App\Http\Controllers;

use App\Models\Receptionist;
use App\Models\Manager;
use App\Models\Admin;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class ReceptionistController extends Controller
{
        
    private function generateVirtualEmail(int $id): string {
        return "receptionist.{$id}@receptionist.com";
    }

    public function index()
    {
        $user = [
            'role' => $this->role,
            'id' => $this->user['id'],
        ];

        $receptionists = Receptionist::paginate(10);

        if ($user['role'] === 'manager') {
            $manager = Manager::find($user['id']);
            $editable_ids = $manager->receptionists()->pluck('id')->toArray();
        }
        $receptionists->getCollection()->transform(function ($receptionist) {
            $receptionist->image = asset($receptionist->image);
            return $receptionist;
        });

        return Inertia::render('Manager/ReceptionistIndex', [
            'receptionists' => $receptionists, 
            'isAdmin' => $user['role'] === 'admin',
            'ids' => $editable_ids ?? null,
        ]);
    }
    

    public function create()
    {
        return to_route('receptionists.index');
    }

public function store(Request $request)
{
    $validatedData = $request->validate([
        'name' => 'required|regex:/^[\pL\s]+$/u',
        'email' => 'required|email|unique:receptionists,actual_email',
        'password' => 'required|min:6',
        'nationalId' => 'required|digits:14|unique:receptionists,nationalId',
        'image' => 'nullable|image|max:2048',
    ]);

    $imagePath = 'images/default.jpg';
    if ($request->hasFile('image')) {
        $file = $request->file('image');
        $filename = uniqid().'.'.$file->getClientOriginalExtension();
        $file->move(public_path('images/receptionist'), $filename);
        $imagePath = 'images/receptionist/'.$filename;        
    }

    $latestId = Receptionist::withTrashed()->max('id') ?? 0;
    $newId = $latestId + 1;

    $newReceptionist = [
        'name' => $validatedData['name'],
        'actual_email' => $validatedData['email'],
        'email' => $this->generateVirtualEmail($newId),
        'password' => bcrypt($validatedData['password']),
        'nationalId' => $validatedData['nationalId'],
        'image' => $imagePath,
    ];

    if($this->role === 'admin') {
        $admin = Admin::find($this->user['id']);
        $admin->receptionists()->create($newReceptionist);
    } else {
        $manager = Manager::find($this->user['id']);
        $manager->receptionists()->create($newReceptionist);
    }

    return back()->with('success', "Receptionist created successfully. You can now login with the email: {$newReceptionist['email']}");
}

public function update($id, Request $request)
{
    $receptionist = Receptionist::findOrFail($id);

    if ($this->role !== 'admin') {
        $manager = Manager::find($this->user['id']);
        $editable_ids = $manager->receptionists()->pluck('id')->toArray();
        if (!in_array($id, $editable_ids)) {
            return to_route('receptionists.index');
        }
    }

    $validatedData = $request->validate([
        'name' => 'required|regex:/^[\pL\s]+$/u',
        'email' => 'required|email|unique:receptionists,actual_email,' . $receptionist->id,
        'nationalId' => 'required|digits:14|unique:receptionists,nationalId,' . $receptionist->id,
        'image' => 'nullable|image|max:2048',
    ]);

    if ($request->hasFile('image')) {
        $filePath = public_path($receptionist->image);
        if ($receptionist->image !== 'images/default.jpg' && file_exists($filePath)) {
            unlink($filePath);
        }
        $file = $request->file('image');
        $filename = uniqid().'.'.$file->getClientOriginalExtension();
        $file->move(public_path('images/receptionist'), $filename);
        $imagePath = 'images/receptionist/'.$filename;    
        $receptionist->image = $imagePath;
    }

    $receptionist->name = $validatedData['name'];
    $receptionist->actual_email = $validatedData['email'];
    $receptionist->nationalId = $validatedData['nationalId'];
    $receptionist->save();

    return back()->with('success', "Receptionist updated successfully. You can now login with the email: {$receptionist->email}");
}

    public function show($id)
    {
        return to_route('receptionists.index');
    }
    
    public function edit($id)
    {
        return to_route('receptionists.index');
    }
 
    public function destroy($id)
    {
        $receptionist = Receptionist::find($id);
        if ($receptionist) {
            $filePath = public_path($receptionist->image);
            if (
                $receptionist->image && 
                $receptionist->image !== 'images/default.jpg' && 
                file_exists($filePath)) {
                    unlink($filePath);
            }
            $receptionist->delete();
            return back()->with('success', `Receptionist deleted successfully 
                Name : {$receptionist['name']}
                Email : {$receptionist['email']}`);
        } else {
            return back()->with('failed', `Failed to delete receptionist
                Name : {$receptionist['name']}
                Email : {$receptionist['email']}`);
        }
    }

}
