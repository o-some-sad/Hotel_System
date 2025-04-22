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
        $imagePath = 'images/default.jpg';
        if ($request->hasFile('image')) {
            $file = $request->file('image');

            $filename = uniqid().'.'.$file->getClientOriginalExtension();
        
            $file->move(public_path('images/receptionist'), $filename);
            
            $imagePath = 'images/receptionist/'.$filename;        
        }

        $latestId = Receptionist::max('id') ?? 0;
        $newId = $latestId + 1;


        $newManager = [
            'name' => $request->name,
            'actual_email' => $request->email,
            'email' => $this->generateVirtualEmail( $newId),
            'password' => bcrypt($request->password),
            'nationalId' => $request->nationalId,
            'image' => $imagePath,
        ];

        if($this->role === 'admin') {
            $admin = Admin::find($this->user['id']);
            $admin->receptionists()->create($newManager);
        }else {
            $manager = Manager::find($this->user['id']);
            $manager->receptionists()->create($newManager);
        }

        return back()->with('success', "Manager created successfully You can now login with the email: {$newManager['email']}");
    }

    public function update($id,Request $request)
    {

        if ($this->role !== 'admin' ) {
            $manager = Manager::find($this->user['id']);
            $editable_ids = $manager->receptionists()->pluck('id')->toArray();
            if (!in_array($id, $editable_ids)) {
                return to_route('receptionists.index');
            }
        }

        $receptionist = Receptionist::find($id);
        if(! $receptionist) {
            return back()->with('failed', "Receptionist not found");
        }

        
        if ($request->hasFile('image')) {
            $filePath = public_path($receptionist->image);

            if (
                $receptionist->image && 
                $receptionist->image !== 'images/default.jpg' && 
                file_exists($filePath)) {

                    unlink($filePath);
            }
            $file = $request->file('image');

            $filename = uniqid().'.'.$file->getClientOriginalExtension();
        
            $file->move(public_path('images/receptionist'), $filename);
        
            $imagePath = 'images/receptionist/'.$filename;    
            $receptionist->image = $imagePath;
        }
        $receptionist->name = $request->name;
        $receptionist->actual_email = $request->email;
        $receptionist->nationalId = $request->nationalId;
        $receptionist->save();

        return back()->with('success', "Receptionist updated successfully You can now login with the email: {$manager['email']}");
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
            return back()->with('success', `Manager deleted successfully 
                Name : {$receptionist['name']}
                Email : {$receptionist['email']}`);
        } else {
            return back()->with('failed', `Failed to delete manager
                Name : {$receptionist['name']}
                Email : {$receptionist['email']}`);
        }
    }

}
