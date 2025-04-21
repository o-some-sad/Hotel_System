<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManager;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        $countries = DB::table('lc_countries_translations')
        ->select('name')
        ->orderBy('name')
        ->pluck('name');

    return Inertia::render('auth/Register', [
        'countries' => $countries
    ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:clients',
            'password' => 'required|string|min:8|confirmed',
            'nationalId' => 'required|string|size:10|unique:clients',
            'country' => 'required|string|regex:/^[A-Za-z\s]+$/|exists:lc_countries_translations,name',
            'gender' => 'required|in:male,female',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,svg,webp|max:1024'
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            
            // Save the original image
            $imagePath = 'clients/' . $filename;
            
            $manager = new ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
            $img = $manager->read($image->getRealPath());
            $img->cover(300, 300);// Resize to square
            
            // Save the processed image
            Storage::disk('public')->put($imagePath, $img->toJpeg());
        }

        $user = Client::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'nationalId' => $request->nationalId,
            'country' => $request->country,
            'gender' => $request->gender,
            'image' => $imagePath,
            'created_by_type'=> 'client',
        ]);

        event(new Registered($user));

        // Auth::guard('client')->login($user);

        return redirect()->route('login')->with('message', 'Registration successful. Please log in.');
    }
}