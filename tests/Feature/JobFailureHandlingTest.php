<?php

namespace Tests\Feature;

use App\Enums\AuctionStatus;
use App\Jobs\CloseAuctionJob;
use App\Jobs\ProcessPaymentJob;
use App\Jobs\SendAdminJobFailureAlert;
use App\Models\Bid;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class JobFailureHandlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_close_auction_job_logs_failure_with_context(): void
    {
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'Failed to close auction'
                    && isset($context['job'])
                    && isset($context['product_id'])
                    && isset($context['product_title'])
                    && isset($context['creator_id'])
                    && isset($context['auction_end'])
                    && isset($context['status'])
                    && isset($context['bid_count'])
                    && isset($context['reserve_price'])
                    && isset($context['error_message'])
                    && isset($context['error_file'])
                    && isset($context['error_line'])
                    && isset($context['stack_trace'])
                    && isset($context['attempt']);
            });

        $product = Product::factory()->create([
            'status' => AuctionStatus::Active,
        ]);

        $job = new CloseAuctionJob($product);
        $exception = new \Exception('Test exception');

        $job->failed($exception);
    }

    public function test_close_auction_job_sends_admin_alert_on_failure(): void
    {
        Queue::fake();

        $product = Product::factory()->create([
            'status' => AuctionStatus::Active,
        ]);

        $job = new CloseAuctionJob($product);
        $exception = new \Exception('Test exception');

        $job->failed($exception);

        Queue::assertPushed(SendAdminJobFailureAlert::class, function ($job) use ($product) {
            return $job->jobName === CloseAuctionJob::class
                && $job->context['product_id'] === $product->id
                && $job->context['product_title'] === $product->title
                && $job->errorMessage === 'Test exception';
        });
    }

    public function test_process_payment_job_logs_failure_with_context(): void
    {
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'Payment processing failed'
                    && isset($context['job'])
                    && isset($context['bid_id'])
                    && isset($context['user_id'])
                    && isset($context['product_id'])
                    && isset($context['product_title'])
                    && isset($context['bid_amount'])
                    && isset($context['payment_method_id'])
                    && isset($context['creator_id'])
                    && isset($context['error_message'])
                    && isset($context['error_file'])
                    && isset($context['error_line'])
                    && isset($context['stack_trace'])
                    && isset($context['attempt']);
            });

        $bid = Bid::factory()->create();

        $job = new ProcessPaymentJob($bid, 'pm_test_123');
        $exception = new \Exception('Payment failed');

        $job->failed($exception);
    }

    public function test_process_payment_job_sends_admin_alert_on_failure(): void
    {
        Queue::fake();

        $bid = Bid::factory()->create();

        $job = new ProcessPaymentJob($bid, 'pm_test_123');
        $exception = new \Exception('Payment failed');

        $job->failed($exception);

        Queue::assertPushed(SendAdminJobFailureAlert::class, function ($job) use ($bid) {
            return $job->jobName === ProcessPaymentJob::class
                && $job->context['bid_id'] === $bid->id
                && $job->context['user_id'] === $bid->user_id
                && $job->context['product_id'] === $bid->product_id
                && $job->errorMessage === 'Payment failed';
        });
    }

    public function test_admin_alert_includes_all_required_context(): void
    {
        Queue::fake();

        $product = Product::factory()->create();
        $bid = Bid::factory()->for($product)->create();

        $job = new CloseAuctionJob($product);
        $exception = new \Exception('Critical failure');

        $job->failed($exception);

        Queue::assertPushed(SendAdminJobFailureAlert::class, function ($alertJob) use ($product) {
            $context = $alertJob->context;
            
            return isset($context['product_id'])
                && isset($context['product_title'])
                && isset($context['creator_id'])
                && isset($context['auction_end'])
                && isset($context['status'])
                && isset($context['bid_count'])
                && isset($context['reserve_price'])
                && isset($context['attempt']);
        });
    }
}
