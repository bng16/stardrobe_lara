<?php

namespace App\Mail;

use App\Models\Product;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SaleConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $creator,
        public Product $product
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Sale Confirmed - Payment Received',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.sale-confirmation',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
