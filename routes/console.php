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

// 流量统计批量写入任务（每5分钟执行一次）
Schedule::command('app:batch-write-traffic-stats --queue')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground();

// 流量统计数据清理任务（每天凌晨2点执行，清理90天前的数据）
Schedule::command('app:clean-old-traffic-stats')
    ->dailyAt('02:00')
    ->withoutOverlapping();
