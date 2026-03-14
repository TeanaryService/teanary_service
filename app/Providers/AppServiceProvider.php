<?php

namespace App\Providers;

use App\Http\Middleware\SetLocaleAndCurrency;
use App\Models\Order;
use App\Services\LocaleCurrencyService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

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

        // 为 Livewire update 路由添加中间件，确保从 session 获取语言
        $this->configureLivewireUpdateRoute();
    }

    /**
     * 配置 Livewire update 路由，支持多语言：使用 {locale} 参数，使任意语言前缀的 POST 都能匹配
     * （此前仅注册了 boot 时的默认语言，导致 /ja/livewire/update 等返回 405）
     */
    protected function configureLivewireUpdateRoute(): void
    {
        $supportedLocales = $this->getSupportedLocaleCodes();

        Livewire::setUpdateRoute(function ($handle) use ($supportedLocales) {
            return Route::post('{locale}/livewire/update', $handle)
                ->where('locale', implode('|', $supportedLocales))
                ->middleware(['web', SetLocaleAndCurrency::class]);
        });
    }

    /**
     * 获取支持的语言代码列表（与 web.php 一致，迁移未执行时回退为 en）
     */
    protected function getSupportedLocaleCodes(): array
    {
        try {
            $service = $this->app->make(LocaleCurrencyService::class);
            $codes = $service->getLanguages()->pluck('code')->toArray();
        } catch (\Throwable) {
            $codes = [];
        }

        return empty($codes) ? ['en'] : $codes;
    }
}
