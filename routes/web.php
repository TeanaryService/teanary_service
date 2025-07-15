<?php

use App\Http\Middleware\SetLocaleAndCurrency;
use App\Livewire\Home;
use App\Services\LocaleCurrencyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/locale-currency-switcher/update', [\App\Http\Controllers\LocaleCurrencySwitcherController::class, 'update'])
    ->name('locale-currency-switcher.update');

Route::prefix('/')->middleware([SetLocaleAndCurrency::class])->group(function () {
    Route::get('/', Home::class)->name('home');
});
