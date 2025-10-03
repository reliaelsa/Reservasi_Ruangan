<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Reservations;

class ReservationCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $reservation;

    public function __construct(Reservations $reservation)
    {
        $this->reservation = $reservation;
    }

    public function build()
    {
        return $this->subject('Reservasi Baru Dibuat')
            ->view('emails.reservations.reservation_mail');
    }
}
