<?php

use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    /** @var ClosureCommand $this */
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('orders:cancel-expired')->hourly()->timezone('Asia/Jakarta')->withoutOverlapping();
Schedule::command('database:backup')->dailyAt('02:00')->timezone('Asia/Jakarta')->withoutOverlapping();
