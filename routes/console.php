<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule auction closing to run every minute
Schedule::command('auctions:close')->everyMinute();

// Schedule order expiration to run hourly
Schedule::command('orders:expire')->hourly();
