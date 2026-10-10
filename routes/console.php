<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('tradeon:backup --keep=7')->dailyAt('02:30')->withoutOverlapping();
Schedule::command('queue:prune-batches --hours=48')->daily();
Schedule::command('auth:clear-resets')->everyFifteenMinutes();
