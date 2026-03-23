<?php

namespace App\Jobs;

use App\Mail\PaymentConfirmationMail;
use App\Models\Product;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendPaymentConfirmationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array<int>
     */
    public $backoff = [10, 30, 60];

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $buyer,
        public Product $product
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->buyer->email)->send(
            new PaymentConfirmationMail($this->buyer, $this->product)
        );

        // Log notification
        \App\Models\NotificationLog::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'user_id' => $this->buyer->id,
            'type' => 'payment_confirmation',
            'subject' => 'Payment Confirmed - Order Details',
            'sent_at' => now(),
        ]);
    }
}
