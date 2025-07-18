<?php

use App\Http\Middleware\SetLocaleAndCurrency;
use App\Livewire\Home;
use App\Models\User;
use App\Services\LocaleCurrencyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::post('/locale-currency-switcher/update', [\App\Http\Controllers\LocaleCurrencySwitcherController::class, 'update'])
    ->name('locale-currency-switcher.update');

Route::prefix('/')->middleware([SetLocaleAndCurrency::class])->group(function () {
    Route::get('/', Home::class)->name('home');
});

Route::get('login-as/{id}', function (int $id) {
    $user = User::find($id);
    // 更新认证令牌
    Auth::guard('web')->loginUsingId($id);
    // 在会话中存储令牌
    session()->put('auth_token', $user->auth_token);
    return redirect()->route('filament.personal.pages.dashboard');
})->middleware(['web'])->name('login-as');

Route::fallback(function () {
    return abort(404);
});
