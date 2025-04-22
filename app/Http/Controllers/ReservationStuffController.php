<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Client;
use App\Models\Reservation;
use App\Models\Room;
use App\Http\Requests\stuffReservationRequest;
use App\Http\Requests\UpdateStuffReservationRequest;
use App\Notifications\ReservationApproved;
use Illuminate\Auth\Events\Validated;

class ReservationStuffController extends Controller
{
    //Show all reservation to the stuff
    public function index()
    {
        [$user, $userType] = $this->getAuthenticatedUser();

        $reservations = Reservation::with(['client', 'room'])->paginate(10);

        return Inertia::render('reservations/index', [
            'reservations' => $reservations,
            'currentUser' => [
                'id' => $user->id,
                'type' => $userType,
                'isAdmin' => $userType ===  'App\Models\Admin'
            ]
        ]);
    }

    public function create()
    {
        //return the valid rooms to the create
        $rooms = Room::where('is_available', true)->get();
        return Inertia::render('reservations/createReservation', [
            'rooms' => $rooms
        ]);
    }

    public function store(stuffReservationRequest $request) // create reservation
    {
        $validated = $request->validated();

        $client = Client::findOrFail($validated['client_id']); // validate the client that sent from form exist in database

        $room = Room::findOrFail($validated['room_id']); // make sure room exist in database too

        // Calculate price based on number of days
        $checkIn = new \DateTime($validated['check_in']);
        $checkOut = new \DateTime($validated['check_out']);
        $days = $checkIn->diff($checkOut)->days ?: 1;
        $totalPrice = $room->price * $days;

        [$user, $userType] = $this->getAuthenticatedUser();


        #TODOHandle who created the reservation later
        $data = array_merge($validated, [
            'created_by_id' => $user ? $user->id : null,
            'created_by_type' => $userType,
            'is_approved' => 1,
            'price' => $totalPrice,
        ]);


        Reservation::create($data);

        // Update room availability
        $room->is_available = false;
        $room->save();

        return redirect()->route('stuff.reservation.index')->with('success', 'Reservation created successfully');
    }

    public function edit($reservationId)
    {
        $reservation = Reservation::with(['client', 'room'])->findOrFail($reservationId);

        [$user, $userType] = $this->getAuthenticatedUser();
        if ($userType !== 'App\Models\Admin' && ($reservation->created_by_id !== $user->id || $reservation->created_by_type !== $userType)) {
            return redirect()->route('stuff.reservation.index')->with('error', 'You are not authorized to update this reservation');
        }

        //return the valid rooms to the stuff and room in used too
        $rooms = Room::where('is_available', true)
            ->orWhere('id', $reservation->room_id)
            ->get();

        return Inertia::render('reservations/updateReservation', ['reservation' => $reservation, 'rooms' => $rooms]);
    }

    public function update($reservationId, UpdateStuffReservationRequest $request) // update reservation
    {
        $validated = $request->validated();

        $reservation = Reservation::findOrFail($reservationId); // get the reservation

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

        $data = array_merge($validated, [
            'price' => $totalPrice,
        ]);

        $reservation->update($data);


        return redirect()->route('stuff.reservation.index')->with('success', 'Reservation updated successfully');
    }



    public function delete($reservationId)
    {
        $reservation = Reservation::with(['client', 'room'])->findOrFail($reservationId);

        [$user, $userType] = $this->getAuthenticatedUser();

        if ($userType !== 'App\Models\Admin' && ($reservation->created_by_id !== $user->id || $reservation->created_by_type !== $userType)) {
            return redirect()->route('stuff.reservation.index')->with('error', 'You are not authorized to update this reservation');
        }

        return Inertia::render('reservations/deleteReservation', [
            'reservation' => $reservation
        ]);
    }

    public function destroy($reservationId)
    {
        $reservation = Reservation::findOrFail($reservationId);

        [$user, $userType] = $this->getAuthenticatedUser();

        if ($userType !== 'App\Models\Admin' && ($reservation->created_by_id !== $user->id || $reservation->created_by_type !== $userType)) {
            return redirect()->route('stuff.reservation.index')->with('error', 'You are not authorized to update this reservation');
        }

        $room = Room::find($reservation->room_id);
        if ($room) {
            $room->is_available = true;
            $room->save();
        }
        $reservation->delete();

        return to_route('stuff.reservation.index')->with('success', 'Reservation deleted successfully');
    }

    public function approveReservation($reservationId)
    {
        $reservation = Reservation::findOrFail($reservationId);
        $reservation->is_approved = 1;
        $reservation->save();

        //send notification to client
        $reservation->client->notify(new ReservationApproved($reservation));

        return to_route('stuff.reservation.index')->with('success', 'Reservation approved successfully');
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
