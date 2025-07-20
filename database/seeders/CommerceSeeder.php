<?php

namespace Database\Seeders;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\ShippingMethodEnum;
use Illuminate\Database\Seeder;
use App\Models\Language;
use App\Models\ShippingMethod;
use App\Models\ShippingMethodTranslation;
use App\Models\PaymentMethod;
use App\Models\PaymentMethodTranslation;
use App\Models\User;
use App\Models\Currency;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Address;
use App\Models\Country;
use App\Models\OrderShipment;
use App\Models\Zone;

class CommerceSeeder extends Seeder
{
    public function run(): void
    {
        $languages = Language::all();
        $users = User::all();
        $currencies = Currency::all();
        $products = Product::all();
        $productVariants = ProductVariant::all();

        // ----------- 物流方式 -----------
        $shippingsData = [
            [
                'code' => 'dhl',
                'active' => true,
                'api_url' => 'https://api.dhl.com',
                'api_token' => 'dhl-token',
                'translations' => [
                    'en' => ['name' => 'DHL', 'description' => 'DHL Express Shipping'],
                    'zh' => ['name' => 'DHL', 'description' => 'DHL 快递'],
                ],
            ],
            [
                'code' => 'fedex',
                'active' => true,
                'api_url' => 'https://api.fedex.com',
                'api_token' => 'fedex-token',
                'translations' => [
                    'en' => ['name' => 'FedEx', 'description' => 'FedEx Shipping Service'],
                    'zh' => ['name' => '联邦快递', 'description' => 'FedEx 快递服务'],
                ],
            ],
        ];

        // ----------- 支付方式 -----------
        $paymentsData = [
            [
                'code' => 'alipay',
                'active' => true,
                'api_url' => 'https://openapi.alipay.com',
                'api_token' => 'alipay-token',
                'translations' => [
                    'en' => ['name' => 'Alipay', 'description' => 'Alipay Payment'],
                    'zh' => ['name' => '支付宝', 'description' => '支付宝支付'],
                ],
            ],
            [
                'code' => 'paypal',
                'active' => true,
                'api_url' => 'https://api.paypal.com',
                'api_token' => 'paypal-token',
                'translations' => [
                    'en' => ['name' => 'PayPal', 'description' => 'PayPal Payment Gateway'],
                    'zh' => ['name' => '贝宝', 'description' => 'PayPal 支付网关'],
                ],
            ],
        ];

        // ----------- 生成购物车、地址、订单 -----------
        foreach ($users as $user) {
            $currency = $currencies->random();

            // 创建用户地址
            // 从数据库随机取一个国家
            $country = Country::inRandomOrder()->first();

            // 如果该国家有 zone，就随机选一个，否则 zone_id 为 null
            $zone = $country
                ? Zone::where('country_id', $country->id)->inRandomOrder()->first()
                : null;

            $address = Address::create([
                'user_id'    => $user->id,
                'firstname'  => fake()->firstName,
                'lastname'   => fake()->lastName,
                'email'      => $user->email,
                'telephone'  => fake()->phoneNumber,
                'company'    => fake()->company,
                'address_1'  => fake()->streetAddress,
                'address_2'  => fake()->streetAddress,
                'city'       => fake()->city,
                'postcode'   => fake()->postcode,
                'country_id' => $country?->id,
                'zone_id'    => $zone?->id,
            ]);

            // 创建购物车
            $cart = Cart::create([
                'user_id' => $user->id,
                'session_id' => null,
            ]);

            // 随机添加 1-3 个商品或变体到购物车
            $cartItemsCount = rand(1, 3);
            for ($i = 0; $i < $cartItemsCount; $i++) {
                $product = $products->random();

                $variant = $productVariants->where('product_id', $product->id)->random();
                $price = $variant->price ?? rand(10, 100);
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'product_variant_id' => $variant->id,
                    'qty' => rand(1, 5),
                ]);
            }

            // 创建订单
            $shippingMethod = ShippingMethodEnum::random();
            $paymentMethod = PaymentMethodEnum::random();

            $order = Order::create([
                'user_id' => $user->id,
                'shipping_address_id' => $address->id,
                'billing_address_id' => $address->id,
                'order_no' => 'ORD-' . strtoupper(uniqid()),
                'currency_id' => $currency->id,
                'total' => 0, // 后面更新
                'status' => OrderStatusEnum::default(),
                'payment_method' => $paymentMethod,
            ]);

            $total = 0;
            foreach ($cart->cartItems as $item) {
                $price = $item->productVariant->price;
                $qty = $item->qty;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'qty' => $qty,
                    'price' => $price,
                ]);

                $total += $price * $qty;
            }

            $order->update(['total' => $total]);

            OrderShipment::create([
                'order_id' => $order->id,
                'shipping_method' => ShippingMethodEnum::random(),
            ]);
        }
    }
}
