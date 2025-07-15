<?php

use App\Livewire\Home;
use App\Services\LocaleCurrencyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', Home::class);

Route::post('/locale-currency/change-language', function (Request $request) {
    $service = new LocaleCurrencyService();
    $language = $service->getLanguageByCode($request->input('lang'));
    session(['lang' => $language->code]);
    return back();
})->name('locale-currency-switcher.change-language');

Route::post('/locale-currency/change-currency', function (Request $request) {
    $service = new LocaleCurrencyService();
    $currency = $service->getCurrencyByCode($request->input('currency'));
    session(['currency' => $currency->code]);
    return back();
})->name('locale-currency-switcher.change-currency');
