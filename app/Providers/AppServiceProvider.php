<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Illuminate\Support\ServiceProvider;

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
        Filament::registerRenderHook(
            'panels::topbar.end',
            fn () => \Livewire\Livewire::mount(\App\Filament\Widgets\LanguageCurrencySwitcher::class)
        );

        Filament::registerRenderHook(
            'panels::topbar.start',
            fn () => '<a class="text-md font-bold" target="_blank" href="'.locaRoute('home').'">'.__('app.home').'</a>'
        );

        // 注册 Media Observer 用于同步
        if (config('sync.enabled')) {
            \App\Models\Media::observe(\App\Observers\MediaObserver::class);
        }
    }
}
