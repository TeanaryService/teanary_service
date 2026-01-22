<?php

use App\Http\Middleware\CustomRedirectIfAuthenticated;
use App\Livewire\Users\Login;
use App\Livewire\Users\Register;
use App\Livewire\Users\ForgotPassword;
use App\Livewire\Users\ResetPassword;
use App\Livewire\Users\Profile;
use App\Livewire\Users\Orders;
use App\Livewire\Users\OrderDetail;
use App\Livewire\Users\Addresses;
use App\Livewire\Users\Notifications;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// 认证路由（访客）
Route::middleware([CustomRedirectIfAuthenticated::class])->group(function () {
    Route::livewire('login', Login::class)->name('auth.login');
    Route::livewire('register', Register::class)->name('auth.register');
    Route::livewire('forgot-password', ForgotPassword::class)->name('auth.forgot-password');
    Route::livewire('reset-password/{token}', ResetPassword::class)->name('password.reset');
});

// 需要认证的路由
Route::middleware('auth')->group(function () {
    Route::livewire('profile', Profile::class)->name('auth.profile');
    Route::livewire('orders', Orders::class)->name('auth.orders');
    Route::livewire('orders/{order}', OrderDetail::class)->name('auth.order-detail');
    Route::livewire('addresses', Addresses::class)->name('auth.addresses');
    Route::livewire('notifications', Notifications::class)->name('auth.notifications');
    Route::post('logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect(locaRoute('home'));
    })->name('auth.logout');
});

// 邮箱验证路由
Route::middleware(['auth', 'throttle:6,1'])->group(function () {
    Route::get('email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');
    
    Route::get('email/verify/{id}/{hash}', function (\Illuminate\Foundation\Auth\EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect(locaRoute('home'))->with('message', __('auth.email_verified'));
    })->middleware('signed')->name('verification.verify');
    
    Route::post('email/verification-notification', function (\Illuminate\Http\Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('message', __('auth.verification_link_sent'));
    })->name('verification.send');
});

// 管理员登录为其他用户（仅限管理员访问）
Route::get('login-as/{id}', function (string $locale, int $id) {
    // 检查管理员是否已登录（通过 manager guard）
    if (! Auth::guard('manager')->check()) {
        abort(403, 'Unauthorized: Please login to the manager panel first.');
    }

    // 查找要登录的用户
    $user = User::find($id);

    if (! $user) {
        abort(404, 'User not found');
    }

    // 登录为用户（这会自动覆盖之前登录的用户，因为 manager 和 user 是不同的 guard）
    // manager guard 保持登录状态，web guard 登录新用户
    Auth::guard('web')->loginUsingId($id);

    // 重定向到用户个人中心
    return redirect(locaRoute('auth.profile'));
})->middleware(['web'])->name('login-as');
