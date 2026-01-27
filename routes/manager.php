<?php

use App\Http\Controllers\Manager\EditorUploadController;
use App\Http\Middleware\CustomRedirectIfAuthenticated;
use App\Livewire\Manager\Addresses;
use App\Livewire\Manager\AddressForm;
use App\Livewire\Manager\ArticleForm;
use App\Livewire\Manager\Articles;
use App\Livewire\Manager\AttributeForm;
use App\Livewire\Manager\Attributes;
use App\Livewire\Manager\AttributeValueForm;
use App\Livewire\Manager\AttributeValues;
use App\Livewire\Manager\Carts;
use App\Livewire\Manager\Categories;
use App\Livewire\Manager\CategoryForm;
use App\Livewire\Manager\Contacts;
use App\Livewire\Manager\Countries;
use App\Livewire\Manager\CountryForm;
use App\Livewire\Manager\Currencies;
use App\Livewire\Manager\CurrencyForm;
use App\Livewire\Manager\Dashboard;
use App\Livewire\Manager\LanguageForm;
use App\Livewire\Manager\Languages;
use App\Livewire\Manager\Login;
use App\Livewire\Manager\ManagerForm;
use App\Livewire\Manager\Managers;
use App\Livewire\Manager\Notifications;
use App\Livewire\Manager\OrderDetail;
use App\Livewire\Manager\Orders;
use App\Livewire\Manager\ProductForm;
use App\Livewire\Manager\ProductReviews;
use App\Livewire\Manager\Products;
use App\Livewire\Manager\PromotionDetail;
use App\Livewire\Manager\PromotionForm;
use App\Livewire\Manager\Promotions;
use App\Livewire\Manager\SpecificationForm;
use App\Livewire\Manager\Specifications;
use App\Livewire\Manager\SpecificationValueForm;
use App\Livewire\Manager\SpecificationValues;
use App\Livewire\Manager\TrafficStatistics;
use App\Livewire\Manager\UserForm;
use App\Livewire\Manager\Users;
use App\Livewire\Manager\ZoneForm;
use App\Livewire\Manager\Zones;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::prefix('manager')->group(function () {
    // 认证路由（访客）
    Route::middleware([CustomRedirectIfAuthenticated::class.':manager'])->group(function () {
        Route::livewire('login', Login::class)->name('manager.login');
    });

    // 需要认证的路由
    Route::middleware('auth:manager')->group(function () {
        Route::livewire('/', Dashboard::class)->name('manager.dashboard');
        Route::livewire('notifications', Notifications::class)->name('manager.notifications');
        Route::livewire('traffic-statistics', TrafficStatistics::class)->name('manager.traffic-statistics');

        // 语言管理
        Route::livewire('languages', Languages::class)->name('manager.languages');
        Route::livewire('languages/create', LanguageForm::class)->name('manager.languages.create');
        Route::livewire('languages/{id}/edit', LanguageForm::class)->name('manager.languages.edit');

        // 货币管理
        Route::livewire('currencies', Currencies::class)->name('manager.currencies');
        Route::livewire('currencies/create', CurrencyForm::class)->name('manager.currencies.create');
        Route::livewire('currencies/{id}/edit', CurrencyForm::class)->name('manager.currencies.edit');

        // 国家管理
        Route::livewire('countries', Countries::class)->name('manager.countries');
        Route::livewire('countries/create', CountryForm::class)->name('manager.countries.create');
        Route::livewire('countries/{id}/edit', CountryForm::class)->name('manager.countries.edit');

        // 地区管理
        Route::livewire('zones', Zones::class)->name('manager.zones');
        Route::livewire('zones/create', ZoneForm::class)->name('manager.zones.create');
        Route::livewire('zones/{id}/edit', ZoneForm::class)->name('manager.zones.edit');

        // 文章管理
        Route::livewire('articles', Articles::class)->name('manager.articles');
        Route::livewire('articles/create', ArticleForm::class)->name('manager.articles.create');
        Route::livewire('articles/{id}/edit', ArticleForm::class)->name('manager.articles.edit');

        // 促销管理
        Route::livewire('promotions', Promotions::class)->name('manager.promotions');
        Route::livewire('promotions/create', PromotionForm::class)->name('manager.promotions.create');
        Route::livewire('promotions/{id}/edit', PromotionForm::class)->name('manager.promotions.edit');
        Route::livewire('promotions/{id}/detail', PromotionDetail::class)->name('manager.promotions.detail');

        // 地址管理
        Route::livewire('addresses', Addresses::class)->name('manager.addresses');
        Route::livewire('addresses/create', AddressForm::class)->name('manager.addresses.create');
        Route::livewire('addresses/{id}/edit', AddressForm::class)->name('manager.addresses.edit');

        // 商品管理
        Route::livewire('products', Products::class)->name('manager.products');
        Route::livewire('products/create', ProductForm::class)->name('manager.products.create');
        Route::livewire('products/{id}/edit', ProductForm::class)->name('manager.products.edit');
        Route::livewire('products/{productId}/reviews', ProductReviews::class)->name('manager.products.reviews');

        // 属性管理
        Route::livewire('attributes', Attributes::class)->name('manager.attributes');
        Route::livewire('attributes/create', AttributeForm::class)->name('manager.attributes.create');
        Route::livewire('attributes/{id}/edit', AttributeForm::class)->name('manager.attributes.edit');

        // 属性值管理
        Route::livewire('attribute-values', AttributeValues::class)->name('manager.attribute-values');
        Route::livewire('attribute-values/create', AttributeValueForm::class)->name('manager.attribute-values.create');
        Route::livewire('attribute-values/{id}/edit', AttributeValueForm::class)->name('manager.attribute-values.edit');

        // 规格管理
        Route::livewire('specifications', Specifications::class)->name('manager.specifications');
        Route::livewire('specifications/create', SpecificationForm::class)->name('manager.specifications.create');
        Route::livewire('specifications/{id}/edit', SpecificationForm::class)->name('manager.specifications.edit');

        // 规格值管理
        Route::livewire('specification-values', SpecificationValues::class)->name('manager.specification-values');
        Route::livewire('specification-values/create', SpecificationValueForm::class)->name('manager.specification-values.create');
        Route::livewire('specification-values/{id}/edit', SpecificationValueForm::class)->name('manager.specification-values.edit');

        // 购物车管理
        Route::livewire('carts', Carts::class)->name('manager.carts');

        // 分类管理
        Route::livewire('categories', Categories::class)->name('manager.categories');
        Route::livewire('categories/create', CategoryForm::class)->name('manager.categories.create');
        Route::livewire('categories/{id}/edit', CategoryForm::class)->name('manager.categories.edit');

        // 联系人管理
        Route::livewire('contacts', Contacts::class)->name('manager.contacts');

        // 订单管理
        Route::livewire('orders', Orders::class)->name('manager.orders');
        Route::livewire('orders/create', \App\Livewire\Manager\OrderForm::class)->name('manager.orders.create');
        Route::livewire('orders/{id}/edit', \App\Livewire\Manager\OrderForm::class)->name('manager.orders.edit');
        Route::livewire('orders/{id}', OrderDetail::class)->name('manager.orders.detail');

        // 管理员管理
        Route::livewire('managers', Managers::class)->name('manager.managers');
        Route::livewire('managers/create', ManagerForm::class)->name('manager.managers.create');
        Route::livewire('managers/{id}/edit', ManagerForm::class)->name('manager.managers.edit');

        // 用户管理
        Route::livewire('users', Users::class)->name('manager.users');
        Route::livewire('users/create', UserForm::class)->name('manager.users.create');
        Route::livewire('users/{id}/edit', UserForm::class)->name('manager.users.edit');

        // 富文本编辑器图片上传（Quill）
        Route::post('editor-uploads/image', [EditorUploadController::class, 'storeImage'])
            ->name('manager.editor-uploads.image');

        Route::post('logout', function () {
            // 只登出 manager guard
            Auth::guard('manager')->logout();
            // 重新生成会话 ID，但不销毁会话数据（保持 users 会话）
            request()->session()->regenerate();

            return redirect(locaRoute('manager.login'));
        })->name('manager.logout');
    });
});
