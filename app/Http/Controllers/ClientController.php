<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClientRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Client;

class ClientController extends Controller
{

    //get the clients

    public function index()
    {
        $clients = Client::paginate(10);
        return Inertia::render('mangeClients/Index', [
            'clients' => $clients
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
        Client::create($validatedRequest);

        return redirect()->route('clients.index')->with('message', 'Client created successfully');
    }

    //Redirect user to edit form
    public function edit()
    {
        return Inertia::render('mangeClients/updateClient');
    }

    //Logic of update will handle here
    public function update() {}


    //Redirect user to the ui of delete
    public function delete() {}

    //Handle logic of delete here
    public function destroy() {}
}
