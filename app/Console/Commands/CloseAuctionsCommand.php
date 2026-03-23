<?php

namespace App\Console\Commands;

use App\Jobs\CloseAuctionJob;
use App\Models\Product;
use Illuminate\Console\Command;

class CloseAuctionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auctions:close';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Close auctions that have ended';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $endedAuctions = Product::needsClosure()->get();

        foreach ($endedAuctions as $product) {
            CloseAuctionJob::dispatch($product);
        }

        $count = $endedAuctions->count();
        $this->info("Dispatched {$count} auction closure job(s)");
    }
}
