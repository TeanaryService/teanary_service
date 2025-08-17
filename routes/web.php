<?php

use App\Http\Controllers\SyncController;
use App\Http\Middleware\SetLocaleAndCurrency;
use App\Livewire\ArticleDetail;
use App\Livewire\ArticleList;
use App\Livewire\Auth\EmailVerificationPrompt;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\ResetPassword;
use App\Livewire\Auth\VerifyEmail;
use App\Livewire\Cart;
use App\Livewire\Checkout;
use App\Livewire\Home;
use App\Livewire\Payment\Cancel;
use App\Livewire\Payment\Checkout as PaymentCheckout;
use App\Livewire\Payment\Failure;
use App\Livewire\Payment\Success;
use App\Livewire\Product;
use App\Livewire\ProductDetail;
use App\Livewire\User\Addresses;
use App\Livewire\User\AddressForm;
use App\Livewire\User\AddressList;
use App\Livewire\User\OrderDetail;
use App\Livewire\User\Orders;
use App\Livewire\User\Profile;
use App\Models\User;
use App\Services\LocaleCurrencyService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

$service = new LocaleCurrencyService();

Route::get('/', function () use ($service) {
    //重定向到带语言前缀的首页
    $lang = $service->getDefaultLanguageCode();
    return redirect($lang);
});


$supportedLocales = $service->getLanguages()->pluck('code')->toArray(); // 返回 ['en', 'zh', 'fr', ...]

// 路由组
Route::prefix('{locale}')->middleware([SetLocaleAndCurrency::class])->group(function () {
    //Auth
    Route::get('login', Login::class)->name('auth.login');
    Route::get('register', Register::class)->name('auth.register');
    Route::get('forgot-password', ForgotPassword::class)->name('auth.forgot-password');
    Route::get('reset-password/{token}', ResetPassword::class)
        ->middleware('guest')
        ->name('auth.password.reset');
    Route::post('logout', function () {
        Auth::logout();
        $locale = app()->getLocale();
        return redirect()->route('home', ['locale' => $locale]);
    })->name('auth.logout');

    Route::post('/currency-switcher/update', [\App\Http\Controllers\LanguageCurrencySwitcherController::class, 'update'])
        ->name('currency-switcher.update');

    Route::get('/', Home::class)->name('home');
    Route::get('product', Product::class)->name('product');
    Route::get('product/{slug}', ProductDetail::class)->name('product.show');
    Route::get('cart', Cart::class)->name('cart');
    Route::get('checkout', Checkout::class)->name('checkout');

    Route::get('payment/success', Success::class)->name('payment.success');
    Route::get('payment/cancel', Cancel::class)->name('payment.cancel');
    Route::get('payment/failure', Failure::class)->name('payment.failure');
    Route::get('payment/checkout/{orderId}', PaymentCheckout::class)->name('payment.checkout');

    Route::get('articles', ArticleList::class)->name('article.index');
    Route::get('articles/{slug}', ArticleDetail::class)->name('article.show');

    Route::get('login-as/{id}', function (string $locale, int $id) {
        Auth::logout();
        $user = User::find($id);
        // 更新认证令牌
        Auth::guard('web')->loginUsingId($id);
        // 在会话中存储令牌
        session()->put('auth_token', $user->auth_token);
        return redirect()->route('user.profile', ['locale' => app()->getLocale()]);
    })->middleware(['web'])->name('login-as');

    Route::middleware(['auth'])->group(function () {
        // 邮箱验证提示页
        Route::get('email-verification/prompt', EmailVerificationPrompt::class)
            ->name('verification.notice');

        // 邮箱验证链接处理页
        Route::get('email-verification/verify/{id}/{hash}', VerifyEmail::class)
            ->middleware(['signed', 'throttle:6,1'])
            ->name('verification.verify');

        Route::middleware(['verified'])->group(function () {
            Route::get('profile', Profile::class)
                ->name('user.profile');

            Route::get('orders', Orders::class)
                ->name('user.orders');

            Route::get('orders/{order}', OrderDetail::class)
                ->name('user.orders.show');

            Route::get('addresses', AddressList::class)->name('user.addresses');

            Route::get('addresses/form', AddressForm::class)->name('user.addresses.form');
        });
    });

    Route::get('/search', \App\Livewire\Search::class)->name('search');

    Route::fallback(function () {
        return abort(404);
    });
})->where(['locale' => $supportedLocales]);