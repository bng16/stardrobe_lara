<?php

namespace App\Jobs;

use App\Enums\AuctionStatus;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class CloseAuctionJob implements ShouldQueue
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
        public Product $product
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::transaction(function () {
            $winningBid = $this->product->getWinningBid();

            if ($winningBid && $winningBid->amount >= $this->product->reserve_price) {
                // Auction sold - winning bid meets reserve
                $this->product->update([
                    'status' => AuctionStatus::Sold,
                    'winning_bid_id' => $winningBid->id,
                ]);

                // Dispatch notifications
                SendAuctionWonEmail::dispatch($winningBid->user, $this->product);
                SendAuctionSoldEmail::dispatch($this->product->creator, $this->product);
            } else {
                // Auction unsold - no bids or none meet reserve
                $this->product->update([
                    'status' => AuctionStatus::Unsold,
                ]);
            }
        });
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        // Log failure with detailed context
        \Log::error('Failed to close auction', [
            'job' => self::class,
            'product_id' => $this->product->id,
            'product_title' => $this->product->title,
            'creator_id' => $this->product->creator_id,
            'auction_end' => $this->product->auction_end,
            'status' => $this->product->status->value,
            'bid_count' => $this->product->bids()->count(),
            'reserve_price' => $this->product->reserve_price,
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
                'product_id' => $this->product->id,
                'product_title' => $this->product->title,
                'creator_id' => $this->product->creator_id,
                'auction_end' => $this->product->auction_end->toDateTimeString(),
                'status' => $this->product->status->value,
                'bid_count' => $this->product->bids()->count(),
                'reserve_price' => $this->product->reserve_price,
                'attempt' => $this->attempts(),
            ],
            $exception->getMessage(),
            $exception->getTraceAsString()
        );
    }
}
