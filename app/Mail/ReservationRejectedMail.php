<?php

namespace App\Mail;

use App\Models\Reservations;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReservationRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $reservation;
    public $reason;

    public function __construct(Reservations $reservation, ?string $reason = null)
    {
        $this->reservation = $reservation;
        $this->reason = $reason;
    }

    public function build()
    {
        return $this->subject('Reservasi Anda DITOLAK')
                    ->view('emails.reservation_rejected')
                    ->with([
                        'reservation' => $this->reservation,
                        'reason' => $this->reason
                    ]);
    }
}
