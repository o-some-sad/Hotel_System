<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Client;
use App\Models\Reservation;
use App\Models\Room;

class ReservationController extends Controller
{
    public function loggedInReservations() // show logged-in reservations
    {
        $client = Client::find(40); // suppose this is the logged-in client
        $reservations = $client->reservations()->with('room')->paginate(10); // get paginated reservations with room data
        $rooms = Room::where('status', 'available')->get(); // get all available rooms;
        return Inertia::render('Homepage', [
            'reservations' => $reservations
        ]);
    }

    public function deleteLoggedInReservation($reservationId) // delete logged-in reservations
    {
        $client = Client::find(40); // get the client, should be the current logged-in client
        $reservation = $client->reservations->find($reservationId); // get the reservation
        $reservation->delete(); // delete the reservation, soft deletion

        return redirect()->route('client.reservations')->with('success', 'Reservation deleted successfully');
    }




}
