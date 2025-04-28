<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $emailMessage; // <- renamed

    public function __construct($subject, $message)
    {
        $this->subject = $subject;
        $this->emailMessage = $message;
    }

    public function envelope()
    {
        return new Envelope(
            subject: $this->subject,
        );
    }

    public function content()
    {
        return new Content(
            view: 'emails.notification',
            with: [
                'emailMessage' => $this->emailMessage,
            ],
        );
    }

    public function attachments()
    {
        return [];
    }
}