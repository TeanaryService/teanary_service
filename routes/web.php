<?php

use App\Http\Middleware\SetLocaleAndCurrency;
use App\Livewire\ArticleDetail;
use App\Livewire\ArticleList;
use App\Livewire\Cart;
use App\Livewire\Checkout;
use App\Livewire\Home;
use App\Livewire\IndexPage;
use App\Livewire\Payment\Cancel;
use App\Livewire\Payment\Checkout as PaymentCheckout;
use App\Livewire\Payment\Failure;
use App\Livewire\OrderQuery;
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
    Route::get('index.html', IndexPage::class)->name('teanary.open');
    
    // 引入用户相关路由
    require __DIR__.'/users.php';
    
    // 引入管理员相关路由
    require __DIR__.'/manager.php';

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

    Route::get('/search', \App\Livewire\Search::class)->name('search');

    Route::get('order-query', OrderQuery::class)->name('order.query');

    Route::fallback(function () {
        return abort(404);
    });
})->where(['locale' => $supportedLocales]);
