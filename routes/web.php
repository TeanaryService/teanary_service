<?php

use App\Livewire\Home;
use App\Services\LocaleCurrencyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/locale-currency-switcher/update', [\App\Http\Controllers\LocaleCurrencySwitcherController::class, 'update'])
    ->name('locale-currency-switcher.update');


Route::get('/', Home::class)->name('home');