<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AppointmentCancellationMail extends Mailable
{
    use Queueable, SerializesModels;
    
    public $tries = 1; // Không retry nếu lỗi

    public $appointment;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Thông báo hủy lịch hẹn - PolyHair')
                    ->view('emails.appointment-cancellation')
                    ->with([
                        'appointment' => $this->appointment,
                    ]);
    }
}

