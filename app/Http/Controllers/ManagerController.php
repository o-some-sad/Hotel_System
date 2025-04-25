<?php

namespace App\Http\Controllers;

use App\Models\Manager;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;


class ManagerController extends Controller
{
    
    private function generateVirtualEmail(int $id): string {
        return "manager.{$id}@manager.com";
    }

    public function index()
    {
        $user = [
            'role' => $this->role,
            'id' => $this->user['id'],
        ];
        $managers = Manager::paginate(10);

        if ($user['role'] === 'manager') {
            $ownRecord = Manager::find($user['id']);
            $otherManagers = $managers->getCollection()->reject(fn($m) => $m->id == $user['id']);

            $managers->setCollection(collect([$ownRecord])->merge($otherManagers));
        }    
        $managers->getCollection()->transform(function ($manager) {
            $manager->image = asset($manager->image);
            return $manager;
        });
    
        return Inertia::render('Manager/Index', [
            'managers' => $managers,
            'isAdmin' => $user['role'] === 'admin',
            'id' => $user['role'] === 'manager' ? $user['id'] : null,
            'user' => auth()->guard('client')->user()
        ]);
    }
    

    public function create()
    {
        return to_route('managers.index');
    }

    public function store(Request $request)
    {

    $validatedData = $request->validate([
        'name' => 'required|regex:/^[\pL\s]+$/u',
        'email' => 'required|email|unique:managers,actual_email',
        'password' => 'required|min:6',
        'nationalId' => 'required|digits:14|unique:managers,nationalId',
        'image' => 'nullable|image|max:2048',
    ]);

        if($this->role !== 'admin') {
            return to_route('managers.index');
        }

        $imagePath = 'images/default.jpg';
        if ($request->hasFile('image')) {
            $file = $request->file('image');

            $filename = uniqid().'.'.$file->getClientOriginalExtension();
        
            $file->move(public_path('images'), $filename);
        
            $imagePath = 'images/'.$filename;        
        }

        $latestId = Manager::withTrashed()->max('id') ?? 0;
        $newId = $latestId + 1;


        $newManager = [
        'name' => $validatedData['name'],
        'actual_email' => $validatedData['email'],
        'email' => $this->generateVirtualEmail($newId),
        'password' => bcrypt($validatedData['password']),
        'nationalId' => $validatedData['nationalId'],
        'image' => $imagePath,
        'admin_id' => $this->user['id'],
        ];

        Manager::create($newManager);

        return back()->with('success', "Manager created successfully You can now login with the email: {$newManager['email']}");
    }

    public function update($id,Request $request)
    {
        $manager = Manager::find($id);
    
        if(! $manager) {
            return back()->with('failed', "Manager not found");
        }

        $validatedData = $request->validate([
            'name' => 'required|regex:/^[\pL\s]+$/u',
            'email' => 'required|email|unique:managers,actual_email,' . $manager->id,
            'nationalId' => 'required|digits:14|unique:managers,nationalId,' . $manager->id,
            'image' => 'nullable|image|max:2048',
        ]);
        
        if ($request->hasFile('image')) {
            $filePath = public_path($manager->image);

            if (
                $manager->image && 
                $manager->image !== 'images/default.jpg' && 
                file_exists($filePath)) {

                    unlink($filePath);
            }
            $file = $request->file('image');

            $filename = uniqid().'.'.$file->getClientOriginalExtension();
        
            $file->move(public_path('images'), $filename);
        
            $imagePath = 'images/'.$filename;    
            $manager->image = $imagePath;
        }
        $manager->name = $request->name;
        $manager->actual_email = $request->email;
        $manager->nationalId = $request->nationalId;
        $manager->save();

        return back()->with('success', "Manager updated successfully You can now login with the email: {$manager['email']}");
    }

    public function show($id)
    {
        return to_route('managers.index');
    }
    
    public function edit($id)
    {
        return to_route('managers.index');
    }
 
    public function destroy($id)
    {
        $manager = Manager::find($id);
        if ($manager) {
            $filePath = public_path($manager->image);
            if (
                $manager->image && 
                $manager->image !== 'images/default.jpg' && 
                file_exists($filePath)) {
                    unlink($filePath);
            }
            $manager->delete();
            return back()->with('success', `Manager deleted successfully 
                Name : {$manager['name']}
                Email : {$manager['email']}`);
        } else {
            return back()->with('failed', `Failed to delete manager
                Name : {$manager['name']}
                Email : {$manager['email']}`);
        }
    }
}
