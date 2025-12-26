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
