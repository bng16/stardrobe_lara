# Task 23.1: Queue Job Retry Logic Configuration

## Summary

Configured queue job retry logic to ensure failed jobs are retried up to 3 times with exponential backoff before being marked as permanently failed. This improves system reliability for critical operations like email sending, auction closing, and payment processing.

## Changes Made

### 1. Queue Configuration (`config/queue.php`)

Added exponential backoff strategy to both database and redis queue connections:

```php
'backoff' => [10, 30, 60], // Exponential backoff: 10s, 30s, 60s
```

This configuration means:
- **First retry**: After 10 seconds
- **Second retry**: After 30 seconds  
- **Third retry**: After 60 seconds
- **After 3 attempts**: Job is marked as failed and moved to `failed_jobs` table

### 2. Job Classes Updated

Added retry configuration to all job classes:

#### Critical Jobs
- **CloseAuctionJob**: Handles auction closing logic
- **ProcessPaymentJob**: Processes Stripe payments

#### Email Notification Jobs
- **SendAuctionWonEmail**: Notifies winner
- **SendAuctionSoldEmail**: Notifies creator of sale
- **SendPaymentConfirmationEmail**: Confirms payment to buyer
- **SendSaleConfirmationEmail**: Confirms sale to creator
- **SendCreatorInviteEmail**: Sends invite to new creators
- **SendNewFollowerNotification**: Notifies creator of new follower
- **SendNewProductNotification**: Notifies followers of new product

Each job now includes:

```php
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
```

### 3. Failed Jobs Table

The `failed_jobs` table is already configured via the existing migration:
- `database/migrations/0001_01_01_000002_create_jobs_table.php`

This table stores:
- Job UUID
- Connection and queue name
- Job payload
- Exception details
- Failed timestamp

## Benefits

1. **Improved Reliability**: Transient failures (network issues, temporary service outages) are automatically retried
2. **Exponential Backoff**: Prevents overwhelming external services during outages
3. **Failure Tracking**: Failed jobs are logged in `failed_jobs` table for manual review
4. **Consistent Behavior**: All jobs follow the same retry strategy

## Queue Worker Configuration

To run the queue worker with proper retry handling:

```bash
php artisan queue:work --tries=3 --backoff=10,30,60
```

Or use a process manager like Supervisor:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --tries=3 --backoff=10,30,60
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/worker.log
stopwaitsecs=3600
```

## Testing Retry Logic

To test the retry logic:

1. **Simulate a failing job**:
```php
// In a job's handle() method
if ($this->attempts() < 3) {
    throw new \Exception('Simulated failure');
}
// Success on 3rd attempt
```

2. **Check failed jobs**:
```bash
php artisan queue:failed
```

3. **Retry failed jobs**:
```bash
php artisan queue:retry all
```

4. **Clear failed jobs**:
```bash
php artisan queue:flush
```

## Monitoring

Monitor job failures in production:

1. Check `failed_jobs` table regularly
2. Set up alerts for critical job failures (CloseAuctionJob, ProcessPaymentJob)
3. Review logs for patterns in failures
4. Consider integrating with monitoring services (Sentry, Bugsnag)

## Requirements Validated

- **Requirement 19.5**: Queue job retry logic configured with max 3 attempts and exponential backoff
- **Property 34**: Job Retry Logic - System retries failed jobs up to 3 times before marking as permanently failed

## Related Tasks

- Task 23.2: Write property test for job retry logic (optional)
- Task 23.3: Implement job failure handling (partially complete - failed() methods exist)
