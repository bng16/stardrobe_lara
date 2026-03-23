<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminJobFailureAlert extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $jobName,
        public array $context,
        public string $errorMessage,
        public string $stackTrace
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Critical Job Failure: {$this->jobName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-job-failure',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
