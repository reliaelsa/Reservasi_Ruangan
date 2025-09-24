<?php

namespace App\Mail;

use App\Models\Reservations;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Reservation;

class ReservationCancelByOverlapMail extends Mailable
{
    use Queueable, SerializesModels;

    public $reservation;
    public $approvedReservation; // reservation that caused the cancel

    public function __construct(Reservations $reservation, Reservations $approvedReservation)
    {
        $this->reservation = $reservation;
        $this->approvedReservation = $approvedReservation;
    }

    public function build()
    {
        return $this->subject('Reservasi Anda Dibatalkan karena Konflik Waktu')
                    ->view('emails.reservation_canceled_overlap')
                    ->with([
                        'reservation' => $this->reservation,
                        'approvedReservation' => $this->approvedReservation
                    ]);
    }
}
