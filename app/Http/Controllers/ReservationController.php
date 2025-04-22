<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Client;
use App\Models\Reservation;
use App\Models\Room;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\UpdateReservationRequest;

class ReservationController extends Controller
{



    public function loggedInReservations() // show logged-in reservations
    {
        $client = Client::find(40); // suppose this is the logged-in client
        $reservations = $client->reservations()->with('room')->paginate(10); // get paginated reservations with room data
        // dd($client->room);
        $rooms = Room::where('is_available', 1)->get(); // get all available rooms;
        return Inertia::render('Homepage', [
            'reservations' => $reservations,
            'rooms' => $rooms,
        ]);
    }

    public function deleteLoggedInReservation($reservationId) // delete logged-in reservations
    {
        $client = Client::find(40); // get the client, should be the current logged-in client
        $reservation = $client->reservations->find($reservationId); // get the reservation

        if (!$reservation) {
            return redirect()->route('client.reservations')->with('error', 'Reservation not found');
        }

        // Make the room available again
        $room = Room::find($reservation->room_id);
        if ($room) {
            $room->is_available = true;
            $room->save();
        }

        $reservation->delete(); // delete the reservation, soft deletion

        return redirect()->route('client.reservations')->with('success', 'Reservation deleted successfully');
    }



    public function store(StoreReservationRequest $request) // create reservation
    {
        $validated = $request->validated();

        $client = Client::find(40); // get the client, should be the current logged-in client
        $room = Room::find($validated['room_id']); // get the room

        // Calculate price based on number of days
        $checkIn = new \DateTime($validated['check_in']);
        $checkOut = new \DateTime($validated['check_out']);
        $days = $checkIn->diff($checkOut)->days ?: 1;
        $totalPrice = $room->price * $days;

        $reservation = new Reservation();
        $reservation->client_id = $client->id;
        $reservation->room_id = $room->id;
        $reservation->check_in = $validated['check_in'];
        $reservation->check_out = $validated['check_out'];
        $reservation->accompanying_number = $validated['accompanying_number'];
        $reservation->created_by_id = $client->id;
        $reservation->created_by_type = 'App\Models\Client';
        $reservation->is_approved = 0; // Ensure it's not approved
        $reservation->price = $totalPrice;
        $reservation->save(); // save the reservation

        // Update room availability
        // $room->is_available = false;
        $room->save();

        return redirect()->route('client.reservations')->with('success', 'Reservation created successfully');
    }






    public function update($reservationId, UpdateReservationRequest $request) // update reservation
    {
        $validated = $request->validated();

        $client = Client::find(40); // get the client, should be the current logged-in client
        $reservation = $client->reservations->find($reservationId); // get the reservation
        if (!$reservation) {
            return redirect()->route('client.reservations')->with('error', 'Reservation not found');
        }

        $room = Room::find($validated['room_id']); // get the room

        // If room is changing, update availability of both rooms
        if ($reservation->room_id != $room->id) {
            // Make old room available
            $oldRoom = Room::find($reservation->room_id);
            if ($oldRoom) {
                $oldRoom->is_available = true;
                $oldRoom->save();
            }

            // Make new room unavailable
            $room->is_available = false;
            $room->save();
        }

        // Calculate price based on number of days
        $checkIn = new \DateTime($validated['check_in']);
        $checkOut = new \DateTime($validated['check_out']);
        $days = $checkIn->diff($checkOut)->days ?: 1;
        $totalPrice = $room->price * $days;

        $reservation->room_id = $room->id;
        $reservation->check_in = $validated['check_in'];
        $reservation->check_out = $validated['check_out'];
        $reservation->accompanying_number = $validated['accompanying_number'];
        $reservation->price = $totalPrice;
        $reservation->save(); // save the reservation

        return redirect()->route('client.reservations')->with('success', 'Reservation updated successfully');
    }
}
