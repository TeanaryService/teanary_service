<?php

use App\Http\Middleware\CustomRedirectIfAuthenticated;
use App\Livewire\Manager\Currencies;
use App\Livewire\Manager\CurrencyForm;
use App\Livewire\Manager\Home;
use App\Livewire\Manager\LanguageForm;
use App\Livewire\Manager\Languages;
use App\Livewire\Manager\Login;
use App\Livewire\Manager\Notifications;
use App\Livewire\Manager\TrafficStatistics;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::prefix('manager')->group(function () {
    // 认证路由（访客）
    Route::middleware([CustomRedirectIfAuthenticated::class . ':manager'])->group(function () {
        Route::get('login', Login::class)->name('manager.login');
    });

    // 需要认证的路由
    Route::middleware('auth:manager')->group(function () {
        Route::get('/', Home::class)->name('manager.home');
        Route::get('notifications', Notifications::class)->name('manager.notifications');
        Route::get('traffic-statistics', TrafficStatistics::class)->name('manager.traffic-statistics');
        
        // 语言管理
        Route::get('languages', Languages::class)->name('manager.languages');
        Route::get('languages/create', LanguageForm::class)->name('manager.languages.create');
        Route::get('languages/{id}/edit', LanguageForm::class)->name('manager.languages.edit');
        
        // 货币管理
        Route::get('currencies', Currencies::class)->name('manager.currencies');
        Route::get('currencies/create', CurrencyForm::class)->name('manager.currencies.create');
        Route::get('currencies/{id}/edit', CurrencyForm::class)->name('manager.currencies.edit');

        Route::post('logout', function () {
            Auth::guard('manager')->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect(locaRoute('manager.login'));
        })->name('manager.logout');
    });
});
