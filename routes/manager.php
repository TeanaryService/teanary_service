<?php

use App\Http\Middleware\CustomRedirectIfAuthenticated;
use App\Livewire\Manager\AddressForm;
use App\Livewire\Manager\Addresses;
use App\Livewire\Manager\ArticleForm;
use App\Livewire\Manager\Articles;
use App\Livewire\Manager\Countries;
use App\Livewire\Manager\CountryForm;
use App\Livewire\Manager\Currencies;
use App\Livewire\Manager\CurrencyForm;
use App\Livewire\Manager\Home;
use App\Livewire\Manager\LanguageForm;
use App\Livewire\Manager\Languages;
use App\Livewire\Manager\Login;
use App\Livewire\Manager\Notifications;
use App\Livewire\Manager\TrafficStatistics;
use App\Livewire\Manager\ZoneForm;
use App\Livewire\Manager\Zones;
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
        
        // 国家管理
        Route::get('countries', Countries::class)->name('manager.countries');
        Route::get('countries/create', CountryForm::class)->name('manager.countries.create');
        Route::get('countries/{id}/edit', CountryForm::class)->name('manager.countries.edit');
        
        // 地区管理
        Route::get('zones', Zones::class)->name('manager.zones');
        Route::get('zones/create', ZoneForm::class)->name('manager.zones.create');
        Route::get('zones/{id}/edit', ZoneForm::class)->name('manager.zones.edit');
        
        // 文章管理
        Route::get('articles', Articles::class)->name('manager.articles');
        Route::get('articles/create', ArticleForm::class)->name('manager.articles.create');
        Route::get('articles/{id}/edit', ArticleForm::class)->name('manager.articles.edit');
        
        // 地址管理
        Route::get('addresses', Addresses::class)->name('manager.addresses');
        Route::get('addresses/create', AddressForm::class)->name('manager.addresses.create');
        Route::get('addresses/{id}/edit', AddressForm::class)->name('manager.addresses.edit');

        Route::post('logout', function () {
            Auth::guard('manager')->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect(locaRoute('manager.login'));
        })->name('manager.logout');
    });
});
