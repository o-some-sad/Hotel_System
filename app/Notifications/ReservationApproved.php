<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservationApproved extends Notification implements ShouldQueue
{
    use Queueable;

    protected $reservation;

    /**
     * Create a new notification instance.
     */
    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Reservation Has Been Approved')
            ->greeting("Hello {$notifiable->name},")
            ->line('We are pleased to inform you that your reservation has been approved.')
            ->line('Reservation Details:')
            ->line("Room: {$this->reservation->room->name} (#{$this->reservation->room->number})")
            ->line("Check-in: " . \Carbon\Carbon::parse($this->reservation->check_in)->format('F j, Y'))
            ->line("Check-out: " . \Carbon\Carbon::parse($this->reservation->check_out)->format('F j, Y'))
            ->action('View Reservation', url('/client/reservations'))
            ->line('Thank you for choosing our hotel!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'reservation_id' => $this->reservation->id,
            'message' => 'Your reservation has been approved',
            'room_name' => $this->reservation->room->name,
            'check_in' => $this->reservation->check_in,
            'check_out' => $this->reservation->check_out
        ];
    }
}
