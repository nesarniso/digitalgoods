<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('notifications:send-scheduled')->everyMinute()->withoutOverlapping();
Schedule::command('notifications:send-automations')->dailyAt('09:00')->withoutOverlapping();
Schedule::command('cart:prune-expired')->hourly()->withoutOverlapping();
Schedule::command('digital:release-expired')->everyTenMinutes()->withoutOverlapping();

// Scheduled jobs (stock-reservation expiry, abandoned cart, report
// generation, backups) get registered here starting from the phase that
// introduces them — kept empty in Phase 1.
