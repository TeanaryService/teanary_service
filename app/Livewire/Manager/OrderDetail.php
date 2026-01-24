<?php

namespace App\Livewire\Manager;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\ShippingMethodEnum;
use App\Models\Order;
use App\Models\User;
use App\Models\Currency;
use App\Models\Address;
use App\Services\LocaleCurrencyService;
use Livewire\Component;

class OrderDetail extends Component
{
    public Order $order;
    public $status;
    public $paymentMethod;
    public $shippingMethod;
    public $userId;
    public $shippingAddressId;
    public $billingAddressId;
    public $currencyId;
    public $total;

    protected LocaleCurrencyService $localeService;

    protected $rules = [
        'status' => 'required',
        'paymentMethod' => 'nullable',
        'shippingMethod' => 'nullable',
        'userId' => 'nullable|exists:users,id',
        'shippingAddressId' => 'nullable|exists:addresses,id',
        'billingAddressId' => 'nullable|exists:addresses,id',
        'currencyId' => 'nullable|exists:currencies,id',
        'total' => 'required|numeric|min:0',
    ];

    public function mount($order): void
    {
        $this->order = Order::with([
            'user',
            'currency',
            'shippingAddress',
            'billingAddress',
            'orderItems.product.productTranslations',
            'orderItems.productVariant',
            'orderShipments',
        ])->findOrFail($order);

        $this->status = $this->order->status->value;
        $this->paymentMethod = $this->order->payment_method?->value;
        $this->shippingMethod = $this->order->shipping_method?->value;
        $this->userId = $this->order->user_id;
        $this->shippingAddressId = $this->order->shipping_address_id;
        $this->billingAddressId = $this->order->billing_address_id;
        $this->currencyId = $this->order->currency_id;
        $this->total = $this->order->total;

        $this->localeService = app(LocaleCurrencyService::class);
    }

    public function updatedUserId(): void
    {
        $this->shippingAddressId = null;
        $this->billingAddressId = null;
    }

    public function save(): void
    {
        $this->validate();

        $this->order->update([
            'status' => OrderStatusEnum::from($this->status),
            'payment_method' => $this->paymentMethod ? PaymentMethodEnum::from($this->paymentMethod) : null,
            'shipping_method' => $this->shippingMethod ? ShippingMethodEnum::from($this->shippingMethod) : null,
            'user_id' => $this->userId,
            'shipping_address_id' => $this->shippingAddressId,
            'billing_address_id' => $this->billingAddressId,
            'currency_id' => $this->currencyId,
            'total' => $this->total,
        ]);

        session()->flash('message', __('app.save_success'));
    }

    public function getUsersProperty()
    {
        return User::orderBy('name')->get();
    }

    public function getCurrenciesProperty()
    {
        return Currency::orderBy('name')->get();
    }

    public function getShippingAddressesProperty()
    {
        if (!$this->userId) {
            return collect();
        }
        return Address::where('user_id', $this->userId)->get();
    }

    public function getBillingAddressesProperty()
    {
        if (!$this->userId) {
            return collect();
        }
        return Address::where('user_id', $this->userId)->get();
    }

    public function getStatusOptionsProperty(): array
    {
        return OrderStatusEnum::options();
    }

    public function getPaymentMethodOptionsProperty(): array
    {
        return PaymentMethodEnum::options();
    }

    public function getShippingMethodOptionsProperty(): array
    {
        return ShippingMethodEnum::options();
    }

    public function render()
    {
        return view('livewire.manager.order-detail', [
            'users' => $this->users,
            'currencies' => $this->currencies,
            'shippingAddresses' => $this->shippingAddresses,
            'billingAddresses' => $this->billingAddresses,
            'statusOptions' => $this->statusOptions,
            'paymentMethodOptions' => $this->paymentMethodOptions,
            'shippingMethodOptions' => $this->shippingMethodOptions,
        ])->layout('components.layouts.manager');
    }
}
