<?php

namespace App\Providers;

use App\Models\Order;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 注册 Media Observer 用于同步
        if (config('sync.enabled')) {
            \App\Models\Media::observe(\App\Observers\MediaObserver::class);
        }

        // 配置 Order 路由模型绑定，支持 Snowflake ID
        Route::bind('order', function ($value) {
            return Order::findOrFail($value);
        });
    }
}
