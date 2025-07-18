<?php

use App\Livewire\AboutUs;
use App\Livewire\Home;
use App\Livewire\Product;
use App\Livewire\ProductDetail;
use App\Models\User;
use App\Services\LocaleCurrencyService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

$service = new LocaleCurrencyService();
Route::get('/', function () use ($service) {
    //重定向到带语言前缀的首页
    $lang = session('lang') ?? $service->getDefaultLanguageCode();
    return redirect($lang);
});


$supportedLocales = $service->getLanguages()->pluck('code')->toArray(); // 返回 ['en', 'zh', 'fr', ...]

// 路由组
Route::prefix('{locale}')->group(function () {
    Route::post('/currency-switcher/update', [\App\Http\Controllers\CurrencySwitcherController::class, 'update'])
        ->name('currency-switcher.update');

    Route::get('/', Home::class)->name('home');
    Route::get('product', Product::class)->name('product');
    Route::get('product/{id}', ProductDetail::class)->name('product.show');

    Route::get('about-us', AboutUs::class)->name('about-us');

    Route::get('login-as/{id}', function (string $locale, int $id) {
        Auth::logout();
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
})->where(['locale' => $supportedLocales]);
