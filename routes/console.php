<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('app:update-ecb')
    ->timezone('Europe/Berlin')
    ->at('18:00');

Schedule::command('app:clear-cache-if-expired')
    ->everyMinute();

Schedule::command('app:clean-orphans')
    ->daily();

Schedule::command('app:sitemap')
    ->daily();

Schedule::command('carts:clear-empty')
    ->daily();

// 数据同步任务
Schedule::command('sync:pending --queue')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('sync:retry-failed --queue')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground();
