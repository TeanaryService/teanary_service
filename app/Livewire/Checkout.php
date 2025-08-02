<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Enums\OrderStatusEnum;
use App\Enums\PaymentMethodEnum;
use App\Models\Cart;
use App\Models\CartItem;
use App\Services\LocaleCurrencyService;
use App\Services\PromotionService;
use App\Services\PaymentService;
use App\Services\ShippingService;
use Illuminate\Support\Str;
use Livewire\Component;

class Checkout extends Component
{
    public $checkoutItems = [];
    public $processedItems = [];
    public $total = 0;
    public $shippingAddress;
    public $billingAddress;
    public $paymentMethod;
    public $addresses;
    public $showAddressForm = false;

    public $orderPromotion;

    // 新增地址表单数据
    public $address = [
        'firstname' => '',
        'lastname' => '',
        'telephone' => '',
        'address_1' => '',
        'address_2' => '',
        'city' => '',
        'postcode' => '',
        'country_id' => '',
        'zone_id' => ''
    ];

    public $countries = [];
    public $zones = [];
    public $paymentMethods = [];
    public $shippingMethods = [];

    public $shippingMethod;
    public $shippingFee = 0;
    public $shippingDescription = '';

    // 添加loading状态属性
    public $loadingShippingMethods = false;
    public $loadingPaymentMethods = false;

    protected $rules = [
        'address.firstname' => 'required|string|max:255',
        'address.lastname' => 'required|string|max:255',
        'address.email' => 'nullable|email|max:255',
        'address.telephone' => 'required|string|max:255',
        'address.company' => 'nullable|string|max:255',
        'address.address_1' => 'required|string|max:255',
        'address.address_2' => 'nullable|string|max:255',
        'address.city' => 'required|string|max:255',
        'address.postcode' => 'required|string|max:20',
        'address.country_id' => 'required|exists:countries,id',
    ];

    public function mount()
    {
        $this->checkoutItems = session('checkout_items', []);

        if (empty($this->checkoutItems)) {
            return redirect()->route('cart', ['locale' => app()->getLocale()]);
        }

        $this->processCheckoutItems();

        // 订单促销处理
        $orderModel = new \App\Models\Order();
        $orderModel->user_id = auth()->id();
        $orderModel->orderItems = collect($this->processedItems)->map(function ($item) {
            return (object)[
                'qty' => $item['qty'],
                'price' => $item['price'],
            ];
        });
        $promoService = app(PromotionService::class);
        $orderPromo = $promoService->calculateOrderTotal($orderModel);
        $this->total = $orderPromo['final_total'];
        $this->orderPromotion = $orderPromo['promotion'] ?? null;

        // 地址获取（支持未登录）
        $this->loadAddresses();

        // 使用缓存获取国家列表
        $locale = app()->getLocale();
        $lang = app(LocaleCurrencyService::class)->getLanguageByCode($locale);
        $this->countries = \App\Models\Country::getCountriesByLanguage($lang?->id);

        // 如果已有地址的国家ID，加载对应的地区数据
        if (!empty($this->address['country_id'])) {
            $this->updatedAddressCountryId($this->address['country_id']);
        }
    }

    // 添加初始化方法
    public function initCheckoutMethods()
    {
        $this->loadingPaymentMethods = true;
        $this->loadingShippingMethods = true;

        try {
            $this->updatePaymentMethods();
            $this->updateShippingMethods();
        } finally {
            $this->loadingPaymentMethods = false;
            $this->loadingShippingMethods = false;
        }
    }

    protected function loadAddresses()
    {
        if (auth()->check()) {
            $this->addresses = \App\Models\Address::where('user_id', auth()->id())->where('deleted', false)->get();
            $this->shippingAddress = $this->addresses->first()?->id;
        } else {
            $this->addresses = \App\Models\Address::where('session_id', session()->getId())->where('deleted', false)->get();
            $this->shippingAddress = $this->addresses->first()?->id;
        }
    }

    protected function processCheckoutItems()
    {
        $this->processedItems = [];
        $lang = app(LocaleCurrencyService::class)->getLanguageByCode(session('lang'));
        $promoService = app(PromotionService::class);
        $user = auth()->user();
        foreach ($this->checkoutItems as $item) {
            $variant = ProductVariant::with([
                'product.productTranslations',
                'specificationValues.specificationValueTranslations',
                'media'
            ])->find($item['product_variant_id']);

            if ($variant) {
                $product = $variant->product;
                $translation = $product->productTranslations->where('language_id', $lang?->id)->first();
                $name = $translation && $translation->name ? $translation->name : ($product->productTranslations->first()->name ?? $product->slug);

                $specs = $variant->specificationValues->map(function ($sv) use ($lang) {
                    $trans = $sv->specificationValueTranslations->where('language_id', $lang?->id)->first();
                    return $trans && $trans->name ? $trans->name : $sv->id;
                })->implode(' / ');

                $promo = $promoService->calculateVariantPrice($variant, $item['qty'], $user);

                $this->processedItems[] = [
                    'weight' => $variant->weight,
                    'length' => $variant->length,
                    'width' => $variant->width,
                    'height' => $variant->height,

                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'],
                    'qty' => $item['qty'],
                    'price' => $promo['final_price'],
                    'product_name' => $name,
                    'specs' => $specs,
                    'image' => $variant->getFirstMediaUrl('image', 'thumb') ?: asset('logo.svg'),
                    'subtotal' => $promo['final_price'] * $item['qty'],
                    'promotion' => $promo['promotion'],
                    'original_price' => $variant->price,
                ];
            }
        }
    }

    public function updatedAddressCountryId($value)
    {
        if (!$value) {
            $this->zones = [];
            $this->address['zone_id'] = '';
            return;
        }

        $locale = app()->getLocale();
        $lang = app(LocaleCurrencyService::class)->getLanguageByCode($locale);
        $this->zones = \App\Models\Zone::getZonesByCountryAndLanguage($value, $lang?->id);

        // 重置地区选择
        $this->address['zone_id'] = '';

        // 如果只有一个地区，自动选中
        if (count($this->zones) === 1) {
            $this->address['zone_id'] = $this->zones[0]['id'];
        }
    }

    public function toggleAddressForm()
    {
        $this->showAddressForm = !$this->showAddressForm;
    }

    public function updatedShippingAddress($value)
    {
        $this->updatePaymentMethods();
        $this->updateShippingMethods();

        // 重新计算订单优惠
        $this->recalculateOrderTotal();
    }

    public function updatedShippingMethod($value)
    {
        $method = collect($this->shippingMethods)->firstWhere('value', $value);
        $this->shippingFee = $method['fee'] ?? 0;
        $this->shippingDescription = $method['description'] ?? '';
        $this->recalculateOrderTotal();
    }

    protected function updatePaymentMethods()
    {
        $address = null;
        if ($this->shippingAddress && $this->addresses) {
            $address = $this->addresses->where('id', $this->shippingAddress)->first();
        }
        $this->paymentMethods = app(\App\Services\PaymentService::class)->getAvailableMethods($address);
    }

    protected function updateShippingMethods()
    {
        $address = null;
        if ($this->shippingAddress && $this->addresses) {
            $address = $this->addresses->where('id', $this->shippingAddress)->first();
        }
        $this->shippingMethods = app(ShippingService::class)->getAvailableMethods($this->processedItems, $address);
        
        // 若当前选中的配送方式不存在于新列表，重置为第一个
        if (!$this->shippingMethod || !collect($this->shippingMethods)->pluck('value')->contains($this->shippingMethod)) {
            $this->shippingMethod = $this->shippingMethods[0]['value'] ?? null;
        }
        $this->updatedShippingMethod($this->shippingMethod);
    }

    protected function recalculateOrderTotal()
    {
        $orderModel = new \App\Models\Order();
        $orderModel->user_id = auth()->id();
        $orderModel->orderItems = collect($this->processedItems)->map(function ($item) {
            return (object)[
                'qty' => $item['qty'],
                'price' => $item['price'],
            ];
        });
        $promoService = app(PromotionService::class);
        $orderPromo = $promoService->calculateOrderTotal($orderModel);
        $this->total = $orderPromo['final_total'] + floatval($this->shippingFee);
        $this->orderPromotion = $orderPromo['promotion'] ?? null;
    }

    protected function getAddressRules()
    {
        $rules = $this->rules;

        // 根据国家设置邮编验证规则
        if ($this->address['country_id']) {
            $country = \App\Models\Country::find($this->address['country_id']);
            $rules['address.postcode'] = $country && $country->postcode_required
                ? 'required|string|max:20'
                : 'nullable|string|max:20';
        }

        // 根据国家设置地区验证规则
        $zones = \App\Models\Zone::getZonesByCountryAndLanguage($this->address['country_id'] ?? null);
        if (!empty($zones)) {
            $rules['address.zone_id'] = 'required|exists:zones,id';
        } else {
            $rules['address.zone_id'] = 'nullable|exists:zones,id';
        }

        return $rules;
    }

    public function saveAddress()
    {
        try {
            $this->validate($this->getAddressRules());

            $data = $this->address;
            $data['session_id'] = session()->getId();
            if (auth()->check()) {
                $data['user_id'] = auth()->id();
            }

            $address = \App\Models\Address::create($data);

            $this->loadAddresses();
            $this->shippingAddress = $address->id;
            $this->showAddressForm = false;
            $this->address = [
                'firstname' => '',
                'lastname' => '',
                'email' => '',
                'telephone' => '',
                'company' => '',
                'address_1' => '',
                'address_2' => '',
                'city' => '',
                'postcode' => '',
                'country_id' => '',
                'zone_id' => ''
            ];

            $this->updatePaymentMethods();
            $this->updateShippingMethods();
            $this->recalculateOrderTotal();
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    // 修正更新配送方式的方法
    public function changeShippingMethod($value)
    {
        $this->shippingMethod = $value;
        $method = collect($this->shippingMethods)->firstWhere('value', $value);
        $this->shippingFee = $method['fee'] ?? 0;
        $this->shippingDescription = $method['description'] ?? '';
        $this->recalculateOrderTotal();
    }

    public function createOrder()
    {
        // 验证收货地址
        if (!$this->shippingAddress) {
            session()->flash('error', __('app.error_no_shipping_address'));
            return;
        }

        // 验证支付方式
        if (!$this->paymentMethod) {
            session()->flash('error', __('app.error_no_payment_method'));
            return;
        }

        // 验证配送方式
        if (!$this->shippingMethod) {
            session()->flash('error', __('app.error_no_shipping_method'));
            return;
        }

        if (empty($this->processedItems)) {
            return redirect()->route('cart');
        }

        try {
            $currency = app(LocaleCurrencyService::class)->getCurrencyByCode(session('currency'));
            $order = Order::create([
                'user_id' => auth()->id(),
                'order_no' => 'ORD-' . Str::upper(Str::random(8)),
                'shipping_address_id' => $this->shippingAddress,
                'billing_address_id' => $this->billingAddress,
                'payment_method' => $this->paymentMethod,
                'shipping_method' => $this->shippingMethod,
                'shipping_fee' => $this->shippingFee,
                'total' => $this->total,
                'status' => OrderStatusEnum::Pending,
                'currency_id' => $currency->id,
            ]);

            foreach ($this->processedItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'],
                    'qty' => $item['qty'],
                    'price' => $item['price']
                ]);
            }

            //下单后删除购物车
            $cartItemIds = collect($this->checkoutItems)->pluck('cart_item_id')->all();
            CartItem::whereIn('id', $cartItemIds)->delete();

            return redirect()->route('payment.checkout', ['locale' => app()->getLocale(), 'orderId' => $order->id]);
        } catch (\Exception $e) {
            session()->flash('error', __('app.error_order_create_failed'));
            return;
        }
    }

    protected function getAddressLabel($address)
    {
        $locale = app()->getLocale();
        $lang = app(LocaleCurrencyService::class)->getLanguageByCode($locale);

        // 获取国家多语言名称
        $countryName = '';
        if ($address->country) {
            $translation = $address->country->countryTranslations->where('language_id', $lang?->id)->first();
            $countryName = $translation && $translation->name
                ? $translation->name
                : ($address->country->countryTranslations->first()->name ?? $address->country->name);
        }

        // 获取地区多语言名称
        $zoneName = '';
        if ($address->zone) {
            $translation = $address->zone->zoneTranslations->where('language_id', $lang?->id)->first();
            $zoneName = $translation && $translation->name
                ? $translation->name
                : ($address->zone->zoneTranslations->first()->name ?? $address->zone->name);
        }

        return [
            'country' => $countryName,
            'zone' => $zoneName
        ];
    }

    public function render()
    {
        $addresses = $this->addresses ? $this->addresses->map(function ($address) {
            $translations = $this->getAddressLabel($address);
            $address->country_name = $translations['country'];
            $address->zone_name = $translations['zone'];
            return $address;
        }) : collect();

        return view('livewire.checkout', [
            'items' => $this->processedItems,
            'total' => $this->total,
            'lang' => app(LocaleCurrencyService::class)->getLanguageByCode(session('lang')),
            'addresses' => $addresses,
            'countries' => $this->countries,
            'zones' => $this->zones,
            'orderPromotion' => $this->orderPromotion ?? null,
            'paymentMethods' => $this->paymentMethods,
            'shippingMethods' => $this->shippingMethods,
        ]);
    }
}
