<?php

namespace App\Providers;

use App\Models\Order;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // 将 SnowflakeService 注册为单例，确保所有模型使用同一个实例
        // 这样可以保证序列号的连续性，避免 ID 冲突
        $this->app->singleton(\App\Services\SnowflakeService::class, function ($app) {
            return new \App\Services\SnowflakeService;
        });
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
