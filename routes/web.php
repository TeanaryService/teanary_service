<?php

use App\Http\Middleware\SetLocaleAndCurrency;
use App\Livewire\ArticleDetail;
use App\Livewire\ArticleList;
use App\Livewire\Cart;
use App\Livewire\Checkout;
use App\Livewire\Home;
use App\Livewire\Payment\Cancel;
use App\Livewire\Payment\Checkout as PaymentCheckout;
use App\Livewire\Payment\Failure;
use App\Livewire\Payment\Success;
use App\Livewire\Product;
use App\Livewire\ProductDetail;
use App\Models\User;
use App\Services\LocaleCurrencyService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

$service = new LocaleCurrencyService;

Route::get('/', function () use ($service) {
    // 重定向到带语言前缀的首页
    $lang = $service->getDefaultLanguageCode();

    return redirect($lang);
});

// 获取支持的语言代码，如果表不存在则使用默认值（迁移时的情况）
$supportedLocales = $service->getLanguages()->pluck('code')->toArray();
if (empty($supportedLocales)) {
    $supportedLocales = ['en']; // 默认语言代码
}

// 路由组
Route::prefix('{locale}')->middleware([SetLocaleAndCurrency::class])->group(function () {
    // Auth routes moved to Filament user panel (/user)

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

    // 管理员登录为其他用户（仅限管理员访问）
    Route::get('login-as/{id}', function (string $locale, int $id) {
        // 安全检查：只有已登录的管理员才能使用此功能
        if (! Auth::check() || ! Auth::user()?->canAccessPanel(\Filament\Facades\Filament::getPanel('manager'))) {
            abort(403, 'Unauthorized');
        }

        $user = User::find($id);

        if (! $user) {
            abort(404, 'User not found');
        }

        Auth::logout();
        Auth::guard('web')->loginUsingId($id);

        return redirect('/user/profile');
    })->middleware(['web', 'auth'])->name('login-as');

    // Email verification routes moved to Filament user panel (/user)

    Route::get('/search', \App\Livewire\Search::class)->name('search');

    Route::fallback(function () {
        return abort(404);
    });
})->where(['locale' => $supportedLocales]);
