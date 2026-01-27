<?php

use App\Http\Middleware\SetLocaleAndCurrency;
use App\Livewire\ArticleDetail;
use App\Livewire\ArticleList;
use App\Livewire\Cart;
use App\Livewire\Checkout;
use App\Livewire\Home;
use App\Livewire\IndexPage;
use App\Livewire\OrderQuery;
use App\Livewire\Payment\Cancel;
use App\Livewire\Payment\Checkout as PaymentCheckout;
use App\Livewire\Payment\Failure;
use App\Livewire\Payment\Success;
use App\Livewire\Product;
use App\Livewire\ProductDetail;
use App\Services\LocaleCurrencyService;
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
Route::prefix('{locale}')->middleware([SetLocaleAndCurrency::class, \App\Http\Middleware\TrackTraffic::class])->group(function () {
    Route::livewire('index.html', IndexPage::class)->name('teanary.open');

    // 引入用户相关路由
    require __DIR__.'/users.php';

    // 引入管理员相关路由
    require __DIR__.'/manager.php';

    // 引入用户相关路由
    require __DIR__.'/manager.php';

    Route::post('/currency-switcher/update', [\App\Http\Controllers\LanguageCurrencySwitcherController::class, 'update'])
        ->name('currency-switcher.update');

    Route::livewire('/', Home::class)->name('home');
    Route::livewire('product', Product::class)->name('product');
    Route::livewire('product/{slug}', ProductDetail::class)->name('product.show');
    Route::livewire('cart', Cart::class)->name('cart');
    Route::livewire('checkout', Checkout::class)->name('checkout');

    Route::livewire('payment/success', Success::class)->name('payment.success');
    Route::livewire('payment/cancel', Cancel::class)->name('payment.cancel');
    Route::livewire('payment/failure', Failure::class)->name('payment.failure');
    Route::livewire('payment/checkout/{orderId}', PaymentCheckout::class)->name('payment.checkout');

    Route::livewire('articles', ArticleList::class)->name('article.index');
    Route::livewire('articles/{slug}', ArticleDetail::class)->name('article.show');

    Route::livewire('/search', \App\Livewire\Search::class)->name('search');

    Route::livewire('order-query', OrderQuery::class)->name('order.query');

    Route::fallback(function () {
        abort(404);
    });
})->where(['locale' => $supportedLocales]);
