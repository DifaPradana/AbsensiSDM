<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('absensi:delete-photos')
    ->monthlyOn(1, '02:00') // tanggal 1, jam 02:00
    ->withoutOverlapping();
