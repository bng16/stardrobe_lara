<?php

namespace Tests\Feature;

use App\Jobs\CloseAuctionJob;
use App\Jobs\ProcessPaymentJob;
use App\Jobs\SendAuctionWonEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class QueueRetryConfigurationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that queue configuration has exponential backoff configured.
     */
    public function test_queue_configuration_has_exponential_backoff(): void
    {
        $databaseConfig = config('queue.connections.database');
        $redisConfig = config('queue.connections.redis');

        // Verify database queue has backoff configured
        $this->assertArrayHasKey('backoff', $databaseConfig);
        $this->assertEquals([10, 30, 60], $databaseConfig['backoff']);

        // Verify redis queue has backoff configured
        $this->assertArrayHasKey('backoff', $redisConfig);
        $this->assertEquals([10, 30, 60], $redisConfig['backoff']);
    }

    /**
     * Test that failed jobs table is configured.
     */
    public function test_failed_jobs_table_is_configured(): void
    {
        $failedConfig = config('queue.failed');

        $this->assertEquals('database-uuids', $failedConfig['driver']);
        $this->assertEquals('failed_jobs', $failedConfig['table']);
    }

    /**
     * Test that CloseAuctionJob has retry configuration.
     */
    public function test_close_auction_job_has_retry_configuration(): void
    {
        $product = \App\Models\Product::factory()->create();
        $job = new CloseAuctionJob($product);

        $this->assertEquals(3, $job->tries);
        $this->assertEquals([10, 30, 60], $job->backoff);
    }

    /**
     * Test that ProcessPaymentJob has retry configuration.
     */
    public function test_process_payment_job_has_retry_configuration(): void
    {
        $bid = \App\Models\Bid::factory()->create();
        $job = new ProcessPaymentJob($bid, 'pm_test_123');

        $this->assertEquals(3, $job->tries);
        $this->assertEquals([10, 30, 60], $job->backoff);
    }

    /**
     * Test that email jobs have retry configuration.
     */
    public function test_email_jobs_have_retry_configuration(): void
    {
        $user = \App\Models\User::factory()->create();
        $product = \App\Models\Product::factory()->create();
        
        $job = new SendAuctionWonEmail($user, $product);

        $this->assertEquals(3, $job->tries);
        $this->assertEquals([10, 30, 60], $job->backoff);
    }

    /**
     * Test that jobs are queued with proper configuration.
     */
    public function test_jobs_are_queued_with_proper_configuration(): void
    {
        Queue::fake();

        $product = \App\Models\Product::factory()->create();
        
        CloseAuctionJob::dispatch($product);

        Queue::assertPushed(CloseAuctionJob::class, function ($job) {
            return $job->tries === 3 && $job->backoff === [10, 30, 60];
        });
    }
}
