<?php

namespace App\Filament\Personal\Pages;

use Filament\Pages\Page;
use App\Models\Order;
use App\Services\LocaleCurrencyService;
use Illuminate\Contracts\Support\Htmlable;

class OrderDetailPage extends Page
{
    public static function getNavigationIcon(): string
    {
        return __('filament.OrderResource.icon');
    }

    public static function getNavigationLabel(): string
    {
        return __('personal.order_detail');
    }

    public function getTitle(): string | Htmlable
    {
        return __('personal.order_detail');
    }

    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.personal.pages.order-detail-page';

    public static function getSlug(): string
    {
        return 'orders-detail/{id}';
    }

    public $order;

    public function mount($id)
    {
        $this->order = Order::with([
            'orderItems.product.productTranslations',
            'orderItems.productVariant.specificationValues.specificationValueTranslations',
            'shippingMethod.shippingMethodTranslations',
            'paymentMethod.paymentMethodTranslations',
        ])
            ->where('user_id', auth()->id())
            ->findOrFail($id);
    }
}
