<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('vmi:replenishment-evaluate')->dailyAt('02:00');
Schedule::command('vmi:low-stock-detect')->dailyAt('03:00');
Schedule::command('vmi:overdue-po-monitor')->dailyAt('04:00');
