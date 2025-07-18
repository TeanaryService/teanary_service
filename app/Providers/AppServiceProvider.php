<?php

namespace App\Providers;

use App\Services\LocaleCurrencyService;
use Filament\Facades\Filament;
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
        //
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
            fn() => '<a class="text-md font-bold" target="_blank" href="' . locaRoute('home') . '">首页</a>'
        );

        $service = new LocaleCurrencyService();

        $locale = $service->resolveLocale(request()->segment(1));

        Route::prefix($locale)
            ->middleware(['web'])
            ->group(function () use ($locale) {
                Livewire::setUpdateRoute(function ($handle) use ($locale) {
                    return Route::post('/livewire/update', $handle)->name("g-{$locale}");
                });
            });
    }
}
