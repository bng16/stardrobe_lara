<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewFollowerMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $creator,
        public User $follower
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You Have a New Follower!',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-follower',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
