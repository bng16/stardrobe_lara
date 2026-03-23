<?php

namespace App\Jobs;

use App\Enums\OrderStatus;
use App\Models\Bid;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProcessPaymentJob implements ShouldQueue
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
        public Bid $bid,
        public string $paymentMethodId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::transaction(function () {
            $user = $this->bid->user;
            $product = $this->bid->product;

            // Create Stripe payment intent via Cashier
            $payment = $user->charge(
                $this->bid->amount * 100, // Convert to cents
                $this->paymentMethodId,
                ['description' => "Purchase: {$product->title}"]
            );

            // Create order record
            $order = Order::create([
                'id' => Str::uuid(),
                'user_id' => $user->id,
                'product_id' => $product->id,
                'bid_id' => $this->bid->id,
                'amount' => $this->bid->amount,
                'stripe_payment_id' => $payment->id,
                'status' => OrderStatus::Completed,
            ]);

            // Transfer funds to creator (via Stripe Connect)
            $this->transferToCreator($product->creator, $this->bid->amount);

            // Send confirmation emails
            SendPaymentConfirmationEmail::dispatch($user, $product);
            SendSaleConfirmationEmail::dispatch($product->creator, $product);
        });
    }

    /**
     * Transfer funds to creator's Stripe Connect account.
     */
    protected function transferToCreator($creator, $amount): void
    {
        $creatorPrivateInfo = $creator->creatorShop->privateInfo;

        if ($creatorPrivateInfo && $creatorPrivateInfo->stripe_account_id) {
            // Platform fee (e.g., 10%)
            $platformFee = $amount * 0.10;
            $creatorAmount = $amount - $platformFee;

            // Transfer to creator's Stripe Connect account
            // This would use Stripe's Transfer API
            // Implementation depends on Stripe Connect setup
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        // Log failure with detailed context
        \Log::error('Payment processing failed', [
            'job' => self::class,
            'bid_id' => $this->bid->id,
            'user_id' => $this->bid->user_id,
            'product_id' => $this->bid->product_id,
            'product_title' => $this->bid->product->title,
            'bid_amount' => $this->bid->amount,
            'payment_method_id' => $this->paymentMethodId,
            'creator_id' => $this->bid->product->creator_id,
            'error_message' => $exception->getMessage(),
            'error_file' => $exception->getFile(),
            'error_line' => $exception->getLine(),
            'stack_trace' => $exception->getTraceAsString(),
            'attempt' => $this->attempts(),
        ]);

        // Send admin alert for critical job failure
        SendAdminJobFailureAlert::dispatch(
            self::class,
            [
                'bid_id' => $this->bid->id,
                'user_id' => $this->bid->user_id,
                'user_email' => $this->bid->user->email,
                'product_id' => $this->bid->product_id,
                'product_title' => $this->bid->product->title,
                'bid_amount' => $this->bid->amount,
                'payment_method_id' => $this->paymentMethodId,
                'creator_id' => $this->bid->product->creator_id,
                'attempt' => $this->attempts(),
            ],
            $exception->getMessage(),
            $exception->getTraceAsString()
        );
    }
}
