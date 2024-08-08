<?php

namespace App\Mail;

use App\Models\RoomNumber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookAssignRoom extends Mailable
{
    use Queueable, SerializesModels;

    /** 
     * Create a new message instance.
     */
    public function __construct(private $data, private $room_number_id)
    {
        $this->room_number_id = RoomNumber::find($room_number_id);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your reservation has been assigned a room.',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.assign_room_mail',
            with: ['booking' => $this->data, 'room_no' => $this->room_number_id->room_no],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
