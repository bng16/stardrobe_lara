<?php

namespace App\Jobs;

use App\Mail\AdminJobFailureAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendAdminJobFailureAlert implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $jobName,
        public array $context,
        public string $errorMessage,
        public string $stackTrace
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $adminEmail = config('mail.admin_email', config('mail.from.address'));

        Mail::to($adminEmail)->send(
            new AdminJobFailureAlert(
                $this->jobName,
                $this->context,
                $this->errorMessage,
                $this->stackTrace
            )
        );
    }
}
