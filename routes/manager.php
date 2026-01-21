<?php

use App\Http\Middleware\CustomRedirectIfAuthenticated;
use App\Livewire\Manager\Home;
use App\Livewire\Manager\Login;
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

        Route::post('logout', function () {
            Auth::guard('manager')->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect(locaRoute('manager.login'));
        })->name('manager.logout');
    });
});
