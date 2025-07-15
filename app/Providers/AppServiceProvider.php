<?php

namespace App\Providers;

use App\Models\Currency;
use App\Models\Language;
use App\Observers\CurrencyObserver;
use App\Observers\LanguageObserver;
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
            fn() => \Livewire\Livewire::mount(\App\Filament\Widgets\LocaleCurrencySwitcher::class)
        );

        Language::observe(LanguageObserver::class);
        Currency::observe(CurrencyObserver::class);
    }
}
