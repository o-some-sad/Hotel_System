<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClientRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Client;
use Illuminate\Support\Facades\Storage;
use App\Notifications\ClientApproved;

class ClientController extends Controller
{

    //get the clients

    public function index()
    {
        [$user, $userType] = $this->getAuthenticatedUser();

        $clients = Client::paginate(10);

        return Inertia::render('mangeClients/Index', [
            'clients' => $clients,
            'currentUser' => [
                'id' => $user->id,
                'type' => $userType,
                'isAdmin' => $userType ===  'App\Models\Admin'
            ]
        ]);
    }

    //Will redirect user to form update
    public function create()
    {
        return Inertia::render('mangeClients/createClient');
    }

    //On user submit this function will be executed
    public function store(ClientRequest $request)
    {
        $validatedRequest = $request->validated();

        if ($request->hasFile('image')) {
            // User uploaded an image, store it
            $validatedRequest['image'] = $request->file('image')->store('clients', 'public');
        } else {
            // No image uploaded, set image to null in database
            $validatedRequest['image'] = null;
        }

        [$user, $userType] = $this->getAuthenticatedUser();

        $data = array_merge($validatedRequest, [
            'created_by_id' => $user ? $user->id : null,
            'created_by_type' => $userType,

        ]);

        Client::create($data);

        return redirect()->route('clients.index')->with('message', 'Client created successfully');
    }

    //Redirect user to edit form
    public function edit($clientId)
    {
        $client = Client::findOrFail($clientId);
        [$user, $userType] = $this->getAuthenticatedUser();
        if ($userType !== 'App\Models\Admin' && ($client->created_by_id !== $user->id || $client->created_by_type !== $userType)) {
            return redirect()->route('stuff.reservation.index')->with('error', 'You are not authorized to update this reservation');
        }

        return Inertia::render('mangeClients/updateClient', [
            'client' => $client
        ]);
    }

    //Logic of update will handle here
    public function update(ClientRequest $request, $clientId)
    {
        $client = Client::findOrFail($clientId);
        $validatedRequest = $request->validated();
        //If user enter a new password bcrypt it
        if (!empty($validatedRequest['password'])) {
            $client->password = bcrypt($validatedRequest['password']);
        } else {
            unset($validatedRequest['password']);
        }
        if ($request->hasFile('image')) {
            if ($client->image) {
                Storage::disk('public')->delete($client->image);
            }
            $validatedRequest['image'] = $validatedRequest->file('image')->store('clients', 'public');
        } else {
            unset($validatedRequest['image']);
        }

        $client->update($validatedRequest);
        return redirect()->route('clients.index')->with('message', 'Client updated successfully');
    }


    //Redirect user to the ui of delete
    public function delete($clientId)
    {
        $client = Client::findOrFail($clientId);
        [$user, $userType] = $this->getAuthenticatedUser();
        if ($userType !== 'App\Models\Admin' && ($client->created_by_id !== $user->id || $client->created_by_type !== $userType)) {
            return redirect()->route('stuff.reservation.index')->with('error', 'You are not authorized to update this reservation');
        }

        return Inertia::render('mangeClients/deleteClient', [
            'client' => $client
        ]);
    }

    //Handle logic of delete here
    public function destroy($clientId)
    {
        $client = Client::findOrFail($clientId);

        if ($client->image && Storage::disk('public')->exists($client->image)) {
            Storage::disk('public')->delete($client->image);
        }
        [$user, $userType] = $this->getAuthenticatedUser();
        if ($userType !== 'App\Models\Admin' && ($client->created_by_id !== $user->id || $client->created_by_type !== $userType)) {
            return redirect()->route('stuff.reservation.index')->with('error', 'You are not authorized to update this reservation');
        }

        $client->delete();

        return to_route('clients.index')->with('success', 'Client deleted successfully');
    }

    public function approve(Client $client)
    {
        if (!$client->verified_at) {
            $client->update([
                'verified_at' => now()
            ]);

            // Send approval notification
            $client->notify(new ClientApproved());

            return redirect()->route('clients.index')
                ->with('success', 'Client approved successfully and notification sent.');
        }

        return redirect()->route('clients.index')
            ->with('info', 'Client is already approved.');
        //To help stuff get the client when reservation
    }
    public function search(Request $request)
    {
        $query = $request->input('query');
        $clients = Client::where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->limit(10)
            ->get(['id', 'name', 'email', 'image']);

        return response()->json($clients);
    }

    private function getAuthenticatedUser()
    {
        //Detect the authenticated user
        $user = null;
        $userType = null;

        if (auth()->guard('receptionist')->check()) {
            $user = auth()->guard('receptionist')->user();
            $userType = 'App\Models\Receptionist';
        } else if (auth()->guard('manager')->check()) {
            $user = auth()->guard('manager')->user();
            $userType = 'App\Models\Manager';
        } else if (auth()->guard('admin')->check()) {
            $user = auth()->guard('admin')->user();
            $userType = 'App\Models\Admin';
        }

        return [$user, $userType];
    }
}
