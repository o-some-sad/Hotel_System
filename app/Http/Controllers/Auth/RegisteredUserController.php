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
use App\Models\Room;
class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        if (Auth::guard('admin')->check()) {
           //redirect to admin dashboard
            return Inertia::render('Admin/Dashboard', [
                'auth' => [
                    'user' => auth()->guard('admin')->user()
                ]
            ]);
        }
    
        if (Auth::guard('manager')->check()) {
            return Inertia::render('Manager/Dashboard', [
                'auth' => [
                    'user' => auth()->guard('manager')->user()
                ]
            ]);
        }
    
        if (Auth::guard('receptionist')->check()) {
            return Inertia::render('Receptionist/Dashboard', [
                'auth' => [
                    'user' => auth()->guard('receptionist')->user()
                ]
            ]);
        }
    
        if (Auth::guard('client')->check()) {
            // Redirect to the client reservation or homepage
            $client = Client::find(auth()->guard('client')->user()['id']); // suppose this is the logged-in client
            $reservations = $client->reservations()->with('room')->paginate(10); // get paginated reservations with room data
        // dd($client->room);
            $rooms = Room::where('is_available', 1)->get();
            return Inertia::render('Homepage', [
                'reservations' => $reservations,
                'rooms' => $rooms,
                'auth' => [
                'user' => auth()->guard('client')->user()
            ]
        ]);
        }
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
            $img->cover(300, 300); // Resize to square

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
            'created_by_type' => 'client',
            'email_verified_at' => null, // Ensure email is not verified initially
        ]);

        // Send email verification notification
        $user->sendEmailVerificationNotification();

        return redirect()->route('login')->with('status', 'verification-link-sent');
    }
}
