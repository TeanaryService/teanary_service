<?php

use App\Livewire\Manager\Login;
use App\Livewire\Manager\Dashboard;
use App\Livewire\Manager\TrafficStatistics;
use App\Livewire\Manager\Notifications;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::prefix('manager')->group(function(){
    // 管理员登录路由（访客）
    Route::middleware('guest:manager')->group(function () {
        Route::get('login', Login::class)->name('manager.login');
    });

    // 需要管理员认证的路由
    Route::middleware('auth:manager')->group(function () {
        Route::get('/', Dashboard::class)->name('manager.dashboard');
        Route::get('traffic-statistics', TrafficStatistics::class)->name('manager.traffic-statistics');
        Route::get('notifications', Notifications::class)->name('manager.notifications');
        
        // Orders
        Route::get('orders', \App\Livewire\Manager\Orders::class)->name('manager.orders');
        Route::get('orders/{order}', \App\Livewire\Manager\OrderDetail::class)->name('manager.orders.show');
        
        // Users
        Route::get('users', \App\Livewire\Manager\Users::class)->name('manager.users');
        Route::get('users/{user}', \App\Livewire\Manager\UserDetail::class)->name('manager.users.show');
        
        // Categories
        Route::get('categories', \App\Livewire\Manager\Categories::class)->name('manager.categories');
        Route::get('categories/create', \App\Livewire\Manager\CategoryDetail::class)->name('manager.categories.create');
        Route::get('categories/{category}', \App\Livewire\Manager\CategoryDetail::class)->name('manager.categories.show');
        
        // Products
        Route::get('products', \App\Livewire\Manager\Products::class)->name('manager.products');
        Route::get('products/create', function() {
            return redirect()->to(locaRoute('manager.products'));
        })->name('manager.products.create');
        Route::get('products/{product}', function() {
            return redirect()->to(locaRoute('manager.products'));
        })->name('manager.products.show');
        
        Route::post('logout', function () {
            Auth::guard('manager')->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect()->route('manager.login', ['locale' => app()->getLocale()]);
        })->name('manager.logout');
    });
});