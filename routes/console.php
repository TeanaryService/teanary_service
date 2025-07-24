<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('app:update-ecb')
    ->timezone('Europe/Berlin')
    ->at('18:00');

Schedule::command('app:clear-cache-if-expired')
    ->everyMinute();

Schedule::command('app:clean-orphans')
    ->daily();
