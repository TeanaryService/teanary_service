<?php

namespace App\Filament\Personal\Pages;

use App\Enums\OrderStatusEnum;
use Filament\Pages\Page;
use App\Models\Order;
use Illuminate\Contracts\Support\Htmlable;

class OrdersPage extends Page
{
    public static function getSlug(): string
    {
        return 'orders';
    }

    public static function getNavigationIcon(): string
    {
        return __('filament.OrderResource.icon');
    }

    public static function getNavigationLabel(): string
    {
        return __('personal.my_orders');
    }

    public function getTitle(): string | Htmlable
    {
        return __('personal.my_orders');
    }

    protected static string $view = 'filament.personal.pages.orders-page';
    public string $status = '';
    public array $statuses = [];

    public function mount(): void
    {
        $this->status = request('status', '');
        $this->statuses = OrderStatusEnum::options();
    }

    public function getOrdersProperty()
    {
        $user = auth()->user();

        $query = Order::with([
            'orderItems.product.productTranslations',
            'orderItems.productVariant.specificationValues.specificationValueTranslations',
        ])
            ->where('user_id', $user->id);

        if ($this->status !== '') {
            $query->where('status', $this->status);
        }

        return $query->latest('id')->paginate(10);
    }

    public function viewDetail($orderId)
    {
        return redirect(OrderDetailPage::getUrl(['id' => $orderId]));
    }
}
