<?php

namespace App\Jobs;

use App\Mail\SaleConfirmationMail;
use App\Models\Product;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendSaleConfirmationEmail implements ShouldQueue
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
        public User $creator,
        public Product $product
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->creator->email)->send(
            new SaleConfirmationMail($this->creator, $this->product)
        );

        // Log notification
        \App\Models\NotificationLog::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'user_id' => $this->creator->id,
            'type' => 'sale_confirmation',
            'subject' => 'Sale Confirmed - Payment Received',
            'sent_at' => now(),
        ]);
    }
}
