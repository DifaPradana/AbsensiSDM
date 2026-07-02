<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('absensi:delete-photos')
    ->monthlyOn(5, '02:00') // tanggal 5, jam 02:00
    ->withoutOverlapping();

Schedule::command('absensi:create-alpha')
    ->dailyAt('23:00')
    ->weekdays()
    ->withoutOverlapping();

Schedule::command('export:delete-old-absensi --days=7')
    ->daily()
    ->at('00:00')
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('export:delete-old-daily-report --days=7')
    ->daily()
    ->at('00:00')
    ->withoutOverlapping()
    ->runInBackground();

Schedule::job(new \App\Jobs\ExportDailyReportMonthlyJob())
    ->monthlyOn(1, '01:00') // tanggal 1, jam 01:00
    ->withoutOverlapping();

// Schedule::command('app:delete-daily-report')
//     ->monthlyOn(5, '02:00')
//     ->withoutOverlapping();
