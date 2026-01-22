<?php

namespace App\Livewire\Manager;

use App\Enums\OrderStatusEnum;
use App\Enums\ShippingMethodEnum;
use App\Models\Order;
use App\Models\OrderShipment;
use App\Services\LocaleCurrencyService;
use Livewire\Component;

class OrderDetail extends Component
{
    public int $orderId;
    public Order $order;
    
    // 发货表单
    public string $shippingMethod = '';
    public ?string $trackingNumber = null;
    public ?string $notes = null;
    public bool $showShipmentForm = false;

    protected array $rules = [
        'shippingMethod' => 'required',
        'trackingNumber' => 'nullable|max:255',
        'notes' => 'nullable|string',
    ];

    protected array $messages = [
        'shippingMethod.required' => '请选择配送方式',
        'trackingNumber.max' => '运单号不能超过255个字符',
    ];

    public function mount(int $id): void
    {
        $this->orderId = $id;
        $this->loadOrder();
    }

    public function loadOrder(): void
    {
        $this->order = Order::with([
            'user',
            'currency',
            'shippingAddress.country.countryTranslations',
            'shippingAddress.zone.zoneTranslations',
            'billingAddress.country.countryTranslations',
            'billingAddress.zone.zoneTranslations',
            'orderItems.product.productTranslations',
            'orderItems.productVariant.specificationValues.specificationValueTranslations',
            'orderShipments',
        ])->findOrFail($this->orderId);
    }

    public function updateStatus(string $status): void
    {
        $this->order->update(['status' => OrderStatusEnum::from($status)]);
        $this->loadOrder();
        session()->flash('message', __('app.updated_successfully'));
    }

    public function toggleShipmentForm(): void
    {
        $this->showShipmentForm = !$this->showShipmentForm;
        if (!$this->showShipmentForm) {
            $this->resetShipmentForm();
        }
    }

    public function resetShipmentForm(): void
    {
        $this->shippingMethod = '';
        $this->trackingNumber = null;
        $this->notes = null;
        $this->resetErrorBag();
    }

    public function createShipment(): void
    {
        $this->validate();

        OrderShipment::create([
            'order_id' => $this->order->id,
            'shipping_method' => ShippingMethodEnum::from($this->shippingMethod),
            'tracking_number' => $this->trackingNumber,
            'notes' => $this->notes,
        ]);

        // 如果订单状态是已支付，自动更新为已发货
        if ($this->order->status === OrderStatusEnum::Paid) {
            $this->order->update(['status' => OrderStatusEnum::Shipped]);
            $this->loadOrder();
        }

        $this->loadOrder();
        $this->resetShipmentForm();
        $this->showShipmentForm = false;
        session()->flash('message', '发货记录已创建');
    }

    public function deleteShipment(int $shipmentId): void
    {
        OrderShipment::findOrFail($shipmentId)->delete();
        $this->loadOrder();
        session()->flash('message', __('app.deleted_successfully'));
    }

    public function getProductName($product, $lang)
    {
        if (!$product) {
            return '-';
        }
        $translation = $product->productTranslations->where('language_id', $lang?->id)->first();
        if ($translation && $translation->name) {
            return $translation->name;
        }
        $first = $product->productTranslations->first();
        return $first ? $first->name : $product->id;
    }

    public function getVariantSpecs($variant, $lang)
    {
        if (!$variant) {
            return __('manager.order_item.no_variant');
        }
        $specNames = [];
        foreach ($variant->specificationValues as $specValue) {
            $translation = $specValue->specificationValueTranslations->where('language_id', $lang?->id)->first();
            $specNames[] = $translation && $translation->name
                ? $translation->name
                : ($specValue->specificationValueTranslations->first()->name ?? '');
        }
        return implode(' / ', array_filter($specNames)) ?: ($variant->sku ?? $variant->id);
    }

    public function getStatusBadgeColor($status): string
    {
        return match ($status) {
            OrderStatusEnum::Pending => 'bg-gray-100 text-gray-800',
            OrderStatusEnum::Paid => 'bg-blue-100 text-blue-800',
            OrderStatusEnum::Shipped => 'bg-yellow-100 text-yellow-800',
            OrderStatusEnum::Completed => 'bg-green-100 text-green-800',
            OrderStatusEnum::Cancelled => 'bg-red-100 text-red-800',
            OrderStatusEnum::AfterSale => 'bg-purple-100 text-purple-800',
            OrderStatusEnum::AfterSaleCompleted => 'bg-indigo-100 text-indigo-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }


    public function render()
    {
        $service = app(LocaleCurrencyService::class);
        $locale = app()->getLocale();
        $lang = $service->getLanguageByCode($locale);
        $currentCurrencyCode = session('currency') ?? $service->getDefaultCurrencyCode();

        return view('livewire.manager.order-detail', [
            'order' => $this->order,
            'lang' => $lang,
            'currentCurrencyCode' => $currentCurrencyCode,
            'service' => $service,
            'statusOptions' => OrderStatusEnum::options(),
            'shippingMethodOptions' => ShippingMethodEnum::options(),
        ])->layout('components.layouts.manager');
    }
}
