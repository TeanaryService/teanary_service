<?php

namespace App\Livewire\User;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class OrderDetail extends Component
{
    public ?Order $order = null;

    public function mount(Order $order): void
    {
        if ($order->user_id != Auth::user()->id) {
            abort(403);
        }

        $this->order = $order->load([
            'orderItems.product.productTranslations',
            'orderShipments',
            'orderItems.productVariant.specificationValues.specificationValueTranslations',
            'shippingAddress.country.countryTranslations',
            'shippingAddress.zone.zoneTranslations',
            'billingAddress',
            'currency',
        ]);
    }

    public function cancelOrder(): void
    {
        if ($this->order->status->canBeCancelled()) {
            $this->order->update(['status' => OrderStatusEnum::Cancelled]);
            $this->dispatch('notify', [
                'message' => __('orders.operation_success'),
                'type' => 'success',
            ]);
        }
    }

    /**
     * 获取产品变体规格字符串
     */
    protected function getProductVariantSpecs($productVariant, $lang): string
    {
        if (!$productVariant) {
            return '';
        }
        
        return $productVariant->specificationValues
            ->map(function ($sv) use ($lang) {
                $trans = $sv->specificationValueTranslations
                    ->where('language_id', $lang?->id)
                    ->first();
                return $trans && $trans->name ? $trans->name : $sv->id;
            })
            ->implode(' / ');
    }

    public function render(): View
    {
        $localeService = app(\App\Services\LocaleCurrencyService::class);
        $lang = $localeService->getLanguageByCode(session('lang'));
        $orderCurrency = $localeService->getCurrencies()->find($this->order->currency_id);

        return view('livewire.user.order-detail', [
            'localeService' => $localeService,
            'lang' => $lang,
            'orderCurrency' => $orderCurrency,
        ]);
    }
}
