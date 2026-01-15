<?php

namespace App\Filament\User\Pages;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class OrderDetail extends Page
{
    protected static string $view = 'filament.user.pages.order-detail';

    protected static ?string $navigationLabel = null;

    protected static ?string $slug = 'orders/detail';

    public function getTitle(): string
    {
        return __('orders.order_details');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public ?Order $order = null;

    public function mount(?int $record = null): void
    {
        if (!$record) {
            $record = request()->query('record');
        }

        if (!$record) {
            abort(404);
        }

        $this->order = Order::query()
            ->where('user_id', Auth::id())
            ->with([
                'orderItems.product.productTranslations',
                'orderShipments',
                'orderItems.productVariant.specificationValues.specificationValueTranslations',
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
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cancel')
                ->label(__('orders.cancel'))
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn () => $this->order && $this->order->status->canBeCancelled())
                ->action('cancelOrder'),
        ];
    }
}
