<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Room;
use App\Models\Reservation;


class UpdateStaffReservationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        //Get the reservation from route to get the old room
        $reservationId = $this->route('reservation');
        $reservation = Reservation::find($reservationId);
        return [
            'client_id' => 'required|exists:clients,id',
            'room_id' => [
                'required',
                'exists:rooms,id',
                function ($attribute, $value, $fail) use ($reservation) {
                    // Only check availability if room is changing
                    if ($reservation && $reservation->room_id != $value) {
                        $room = Room::find($value);
                        if (!$room || !$room->is_available) {
                            $fail('The selected room is not available.');
                        }
                    }
                },
            ],
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'accompanying_number' => [
                'required',
                'integer',
                'min:0',
                function ($attribute, $value, $fail) {
                    $room = Room::find($this->room_id);
                    if ($room && $value > $room->capacity) {
                        $fail('The number of guests exceeds the room capacity.');
                    }
                },
            ],
            'is_approved' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.required' => 'Client id required please try again',
            'client_id.exists' => 'Client not found please try again',
            'room_id.required' => 'Please select a room',
            'room_id.exists' => 'The selected room does not exist',
            'check_in.required' => 'Check-in date is required',
            'check_in.date' => 'Check-in must be a valid date',
            'check_in.after_or_equal' => 'Check-in date cannot be in the past',
            'check_out.required' => 'Check-out date is required',
            'check_out.date' => 'Check-out must be a valid date',
            'check_out.after' => 'Check-out date must be after check-in date',
            'accompanying_number.required' => 'Number of guests is required',
            'accompanying_number.integer' => 'Number of guests must be a whole number',
            'accompanying_number.min' => 'Number of guests cannot be negative',
            'is_approved.in' => 'New reservations cannot be pre-approved',
        ];
    }
}
