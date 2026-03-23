<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Console\Command;

class ExpireOrdersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark unpaid orders as expired after 48 hours';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $expiredOrders = Order::where('status', OrderStatus::Pending)
            ->where('payment_deadline', '<', now())
            ->get();

        foreach ($expiredOrders as $order) {
            $order->update(['status' => OrderStatus::Expired]);
        }

        $count = $expiredOrders->count();
        $this->info("Marked {$count} order(s) as expired");
    }
}
