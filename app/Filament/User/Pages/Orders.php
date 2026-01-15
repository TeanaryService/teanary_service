<?php

namespace App\Filament\User\Pages;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Services\LocaleCurrencyService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class Orders extends Page
{
    use WithPagination;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.user.pages.orders';

    protected static ?string $navigationLabel = null;

    protected static ?string $title = null;

    protected LocaleCurrencyService $localeService;

    public function mount(): void
    {
        $this->localeService = app(LocaleCurrencyService::class);
    }

    public function getOrdersProperty(): LengthAwarePaginator
    {
        return Order::query()
            ->where('user_id', Auth::id())
            ->with([
                'orderItems.product.productTranslations',
                'orderItems.productVariant.specificationValues.specificationValueTranslations',
                'orderItems.productVariant.media',
                'orderItems.product.media',
                'currency',
            ])
            ->latest()
            ->paginate(10);
    }

    public function cancelOrder(int $orderId): void
    {
        $order = Order::query()
            ->where('user_id', Auth::id())
            ->findOrFail($orderId);

        if ($order->status->canBeCancelled()) {
            $order->update(['status' => OrderStatusEnum::Cancelled]);
            Notification::make()
                ->title(__('orders.operation_success'))
                ->success()
                ->send();
            
            $this->resetPage();
        } else {
            Notification::make()
                ->title(__('orders.cannot_cancel'))
                ->danger()
                ->send();
        }
    }

    public function payOrder(int $orderId): void
    {
        $order = Order::query()
            ->where('user_id', Auth::id())
            ->findOrFail($orderId);

        if ($order->status->canBePaid()) {
            $this->redirect(route('payment.checkout', ['orderId' => $order->id]));
        }
    }

    public static function getNavigationLabel(): string
    {
        return __('orders.my_orders');
    }

    public function getTitle(): string
    {
        return __('orders.my_orders');
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
