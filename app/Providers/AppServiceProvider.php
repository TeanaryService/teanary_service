<?php

namespace App\Providers;

use App\Http\Middleware\SetLocaleAndCurrency;
use App\Models\Media;
use App\Models\Order;
use App\Observers\MediaObserver;
use App\Services\LocaleCurrencyService;
use App\Services\SnowflakeService;
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
        $this->app->singleton(SnowflakeService::class, function ($app) {
            return new SnowflakeService;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 注册 Media Observer 用于同步
        if (config('sync.enabled')) {
            Media::observe(MediaObserver::class);
        }

        // 配置 Order 路由模型绑定，支持 Snowflake ID
        Route::bind('order', function ($value) {
            return Order::findOrFail($value);
        });

        // 为 Livewire update 路由添加中间件，确保从 session 获取语言
        $this->configureLivewireUpdateRoute();
    }

    /**
     * 配置 Livewire update 路由，支持多语言。
     *
     * 注意：这里不要依赖「数据库中的语言列表」来限制路由的 {locale}，
     * 否则在使用 route:cache 情况下，之后新增/修改语言 code 时正则不会更新，
     * 会导致新语言前缀的 /{locale}/livewire/update 匹配失败。
     * 我们改为接受任意 {locale}，由中间件 SetLocaleAndCurrency 自行处理合法性与回退。
     */
    protected function configureLivewireUpdateRoute(): void
    {
        Livewire::setUpdateRoute(function ($handle) {
            return Route::post('{locale}/livewire/update', $handle)
                // 这里不对 locale 进行具体枚举限制，仅限制字符格式，避免 route:cache 后语言变更失效
                ->where('locale', '[A-Za-z0-9_-]+')
                ->middleware(['web', SetLocaleAndCurrency::class]);
        });
    }

    /**
     * 获取支持的语言代码列表（与 web.php 一致，迁移未执行时回退为 en）.
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
