<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;

class MangeClientController extends Controller
{

    //get the clients

    public function index()
    {
        $clients = User::paginate(10);
        dd($clients);
        return Inertia::render('mangeClients/Index', [
            'clients' => $clients
        ]);
    }

    //create

    //update

    //delete



}
