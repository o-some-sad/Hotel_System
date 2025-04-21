<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReservationRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Client;
use App\Models\Reservation;
use App\Models\Room;

class ReservationController extends Controller
{

    //Show all reservation to the stuff
    public function index()
    {
        $reservations = Reservation::with(['client', 'room'])->paginate(10);
        return Inertia::render('reservations/index', [
            'reservations' => $reservations
        ]);
    }


    public function edit($reservationId)
    {
        $reservation = Reservation::findOrFail($reservationId);

        return Inertia::render('/reservations/updateReservation', ['reservation' => $reservation]);
    }

    public function update(ReservationRequest $request, $reservationId)
    {
        $reservation = Reservation::findOrFail($reservationId);
        $validatedRequest = $request->validated();
        Reservation::update($validatedRequest);
        return to_route('reservation.index')->with('success', 'Reservation updated successfully');
    }

    public function delete($reservationId)
    {
        $reservation = Reservation::findOrFail($reservationId);

        return Inertia::render('reservations/deleteReservations', [
            'reservation' => $reservation
        ]);
    }

    public function destroy($reservationId)
    {
        $reservation = Reservation::findOrFail($reservationId);

        $reservation->delete();

        return to_route('reservation.index')->with('success', 'Reservation deleted successfully');
    }


    public function loggedInReservations() // show logged-in reservations
    {
        $client = Client::find(40); // suppose this is the logged-in client
        $reservations = $client->reservations()->with('room')->paginate(10); // get paginated reservations with room data
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
        $reservation->delete(); // delete the reservation, soft deletion

        return redirect()->route('client.reservations')->with('success', 'Reservation deleted successfully');
    }

    public function store(Request $request) // create reservation
    {
        // dd($request->all());
        $client = Client::find(40); // get the client, should be the current logged-in client
        $room = Room::find($request->room_id); // get the room
        dd($room);
        $reservation = new Reservation();
        $reservation->client_id = $client->id;
        $reservation->room_id = $room->id;
        $reservation->check_in = $request->check_in;
        $reservation->check_out = $request->check_out;
        $reservation->accompanying_number = $request->accompanying_number;
        $reservation->created_by_id = $reservation->client_id;
        $reservation->created_by_type = 'App\Models\Client';
        $reservation->price = $room->price;
        $reservation->save(); // save the reservation
        return redirect()->route('client.reservations')->with('success', 'Reservation created successfully');
    }
}
