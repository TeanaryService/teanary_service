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

Schedule::command('app:clear-carts-empty')
    ->daily();

// 数据同步任务
Schedule::command('app:sync-pending --queue')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('app:sync-retry-failed --queue')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground();
