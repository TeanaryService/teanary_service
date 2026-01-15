<?php

namespace App\Filament\User\Pages;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Services\LocaleCurrencyService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class OrderDetail extends Page
{
    protected static string $view = 'filament.user.pages.order-detail';

    protected static ?string $navigationLabel = null;

    protected static ?string $slug = 'orders/detail';

    public ?Order $order = null;

    protected LocaleCurrencyService $localeService;

    public function mount(?int $record = null): void
    {
        if (!$record) {
            $record = request()->query('record');
        }

        if (!$record) {
            abort(404);
        }

        $this->localeService = app(LocaleCurrencyService::class);

        $this->order = Order::query()
            ->where('user_id', Auth::id())
            ->with([
                'orderItems.product.productTranslations',
                'orderItems.product.media',
                'orderShipments',
                'orderItems.productVariant.specificationValues.specificationValueTranslations',
                'orderItems.productVariant.media',
                'shippingAddress.country.countryTranslations',
                'shippingAddress.zone.zoneTranslations',
                'billingAddress',
                'currency',
            ])
            ->findOrFail($record);
    }

    public function cancelOrder(): void
    {
        if ($this->order->status->canBeCancelled()) {
            $this->order->update(['status' => OrderStatusEnum::Cancelled]);
            Notification::make()
                ->title(__('orders.operation_success'))
                ->success()
                ->send();
            
            $this->order->refresh();
        } else {
            Notification::make()
                ->title(__('orders.cannot_cancel'))
                ->danger()
                ->send();
        }
    }

    public function payOrder(): void
    {
        if ($this->order->status->canBePaid()) {
            $this->redirect(route('payment.checkout', ['orderId' => $this->order->id]));
        }
    }

    public function getTitle(): string
    {
        return __('orders.order_details') . ' - ' . ($this->order?->order_no ?? '');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label(__('app.back'))
                ->icon('heroicon-o-arrow-left')
                ->url(Orders::getUrl())
                ->color('gray'),
            Action::make('pay')
                ->label(__('orders.pay_now'))
                ->icon('heroicon-o-lock-closed')
                ->color('success')
                ->visible(fn () => $this->order && $this->order->status->canBePaid())
                ->action('payOrder'),
            Action::make('cancel')
                ->label(__('orders.cancel_order'))
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn () => $this->order && $this->order->status->canBeCancelled())
                ->action('cancelOrder'),
        ];
    }
}
