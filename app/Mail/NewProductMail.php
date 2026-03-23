<?php

namespace App\Mail;

use App\Models\Product;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewProductMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $follower,
        public Product $product
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Product from a Creator You Follow',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-product',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
