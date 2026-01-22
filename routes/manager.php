<?php

use App\Http\Middleware\CustomRedirectIfAuthenticated;
use App\Livewire\Manager\AddressForm;
use App\Livewire\Manager\Addresses;
use App\Livewire\Manager\ArticleForm;
use App\Livewire\Manager\Articles;
use App\Livewire\Manager\AttributeForm;
use App\Livewire\Manager\AttributeValueForm;
use App\Livewire\Manager\AttributeValues;
use App\Livewire\Manager\Attributes;
use App\Livewire\Manager\Carts;
use App\Livewire\Manager\Categories;
use App\Livewire\Manager\CategoryForm;
use App\Livewire\Manager\Contacts;
use App\Livewire\Manager\Countries;
use App\Livewire\Manager\ManagerForm;
use App\Livewire\Manager\Managers;
use App\Livewire\Manager\ProductForm;
use App\Livewire\Manager\Products;
use App\Livewire\Manager\ProductReviews;
use App\Livewire\Manager\SpecificationForm;
use App\Livewire\Manager\Specifications;
use App\Livewire\Manager\SpecificationValueForm;
use App\Livewire\Manager\SpecificationValues;
use App\Livewire\Manager\UserForm;
use App\Livewire\Manager\Users;
use App\Livewire\Manager\CountryForm;
use App\Livewire\Manager\Currencies;
use App\Livewire\Manager\CurrencyForm;
use App\Livewire\Manager\Dashboard;
use App\Livewire\Manager\LanguageForm;
use App\Livewire\Manager\Languages;
use App\Livewire\Manager\Login;
use App\Livewire\Manager\Notifications;
use App\Livewire\Manager\OrderDetail;
use App\Livewire\Manager\Orders;
use App\Livewire\Manager\TrafficStatistics;
use App\Livewire\Manager\ZoneForm;
use App\Livewire\Manager\Zones;
use App\Livewire\Manager\PromotionForm;
use App\Livewire\Manager\Promotions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::prefix('manager')->group(function () {
    // 认证路由（访客）
    Route::middleware([CustomRedirectIfAuthenticated::class . ':manager'])->group(function () {
        Route::get('login', Login::class)->name('manager.login');
    });

    // 需要认证的路由
    Route::middleware('auth:manager')->group(function () {
        Route::get('/', Dashboard::class)->name('manager.dashboard');
        Route::get('notifications', Notifications::class)->name('manager.notifications');
        Route::get('traffic-statistics', TrafficStatistics::class)->name('manager.traffic-statistics');
        
        // 语言管理
        Route::get('languages', Languages::class)->name('manager.languages');
        Route::get('languages/create', LanguageForm::class)->name('manager.languages.create');
        Route::get('languages/{id}/edit', LanguageForm::class)->name('manager.languages.edit');
        
        // 货币管理
        Route::get('currencies', Currencies::class)->name('manager.currencies');
        Route::get('currencies/create', CurrencyForm::class)->name('manager.currencies.create');
        Route::get('currencies/{id}/edit', CurrencyForm::class)->name('manager.currencies.edit');
        
        // 国家管理
        Route::get('countries', Countries::class)->name('manager.countries');
        Route::get('countries/create', CountryForm::class)->name('manager.countries.create');
        Route::get('countries/{id}/edit', CountryForm::class)->name('manager.countries.edit');
        
        // 地区管理
        Route::get('zones', Zones::class)->name('manager.zones');
        Route::get('zones/create', ZoneForm::class)->name('manager.zones.create');
        Route::get('zones/{id}/edit', ZoneForm::class)->name('manager.zones.edit');
        
        // 文章管理
        Route::get('articles', Articles::class)->name('manager.articles');
        Route::get('articles/create', ArticleForm::class)->name('manager.articles.create');
        Route::get('articles/{id}/edit', ArticleForm::class)->name('manager.articles.edit');

        // 促销管理
        Route::get('promotions', Promotions::class)->name('manager.promotions');
        Route::get('promotions/create', PromotionForm::class)->name('manager.promotions.create');
        Route::get('promotions/{id}/edit', PromotionForm::class)->name('manager.promotions.edit');
        
        // 地址管理
        Route::get('addresses', Addresses::class)->name('manager.addresses');
        Route::get('addresses/create', AddressForm::class)->name('manager.addresses.create');
        Route::get('addresses/{id}/edit', AddressForm::class)->name('manager.addresses.edit');
        
        // 商品管理
        Route::get('products', Products::class)->name('manager.products');
        Route::get('products/create', ProductForm::class)->name('manager.products.create');
        Route::get('products/{id}/edit', ProductForm::class)->name('manager.products.edit');
        Route::get('products/{productId}/reviews', ProductReviews::class)->name('manager.products.reviews');
        
        // 属性管理
        Route::get('attributes', Attributes::class)->name('manager.attributes');
        Route::get('attributes/create', AttributeForm::class)->name('manager.attributes.create');
        Route::get('attributes/{id}/edit', AttributeForm::class)->name('manager.attributes.edit');
        
        // 属性值管理
        Route::get('attribute-values', AttributeValues::class)->name('manager.attribute-values');
        Route::get('attribute-values/create', AttributeValueForm::class)->name('manager.attribute-values.create');
        Route::get('attribute-values/{id}/edit', AttributeValueForm::class)->name('manager.attribute-values.edit');

        // 规格管理
        Route::get('specifications', Specifications::class)->name('manager.specifications');
        Route::get('specifications/create', SpecificationForm::class)->name('manager.specifications.create');
        Route::get('specifications/{id}/edit', SpecificationForm::class)->name('manager.specifications.edit');

        // 规格值管理
        Route::get('specification-values', SpecificationValues::class)->name('manager.specification-values');
        Route::get('specification-values/create', SpecificationValueForm::class)->name('manager.specification-values.create');
        Route::get('specification-values/{id}/edit', SpecificationValueForm::class)->name('manager.specification-values.edit');
        
        // 购物车管理
        Route::get('carts', Carts::class)->name('manager.carts');
        
        // 分类管理
        Route::get('categories', Categories::class)->name('manager.categories');
        Route::get('categories/create', CategoryForm::class)->name('manager.categories.create');
        Route::get('categories/{id}/edit', CategoryForm::class)->name('manager.categories.edit');
        
        // 联系人管理
        Route::get('contacts', Contacts::class)->name('manager.contacts');
        
        // 订单管理
        Route::get('orders', Orders::class)->name('manager.orders');
        Route::get('orders/create', \App\Livewire\Manager\OrderForm::class)->name('manager.orders.create');
        Route::get('orders/{id}/edit', \App\Livewire\Manager\OrderForm::class)->name('manager.orders.edit');
        Route::get('orders/{id}', OrderDetail::class)->name('manager.orders.detail');
        
        // 管理员管理
        Route::get('managers', Managers::class)->name('manager.managers');
        Route::get('managers/create', ManagerForm::class)->name('manager.managers.create');
        Route::get('managers/{id}/edit', ManagerForm::class)->name('manager.managers.edit');
        
        // 用户管理
        Route::get('users', Users::class)->name('manager.users');
        Route::get('users/create', UserForm::class)->name('manager.users.create');
        Route::get('users/{id}/edit', UserForm::class)->name('manager.users.edit');

        Route::post('logout', function () {
            Auth::guard('manager')->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect(locaRoute('manager.login'));
        })->name('manager.logout');
    });
});
