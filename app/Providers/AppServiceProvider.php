<?php

namespace App\Providers;

use App\Services\QueryCacheService;
use Filament\Facades\Filament;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        $this->app->singleton(QueryCacheService::class, function () {
            return new QueryCacheService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Filament::registerRenderHook(
            'panels::topbar.end',
            fn() => \Livewire\Livewire::mount(\App\Filament\Widgets\LanguageCurrencySwitcher::class)
        );

        Filament::registerRenderHook(
            'panels::topbar.start',
            fn() => '<a class="text-md font-bold" target="_blank" href="' . locaRoute('home') . '">' . __('app.home') . '</a>'
        );
    }
}
