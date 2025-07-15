<?php

namespace Database\Seeders;

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

        $shippingMethods = collect();
        foreach ($shippingsData as $sd) {
            $shipping = ShippingMethod::updateOrCreate(
                ['code' => $sd['code']],
                ['active' => $sd['active'], 'api_url' => $sd['api_url'], 'api_token' => $sd['api_token']]
            );
            foreach ($languages as $lang) {
                $code = $lang->code;
                if (isset($sd['translations'][$code])) {
                    ShippingMethodTranslation::updateOrCreate(
                        ['shipping_method_id' => $shipping->id, 'language_id' => $lang->id],
                        ['name' => $sd['translations'][$code]['name'], 'description' => $sd['translations'][$code]['description']]
                    );
                }
            }
            $shippingMethods->push($shipping);
        }

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

        $paymentMethods = collect();
        foreach ($paymentsData as $pd) {
            $payment = PaymentMethod::updateOrCreate(
                ['code' => $pd['code']],
                ['active' => $pd['active'], 'api_url' => $pd['api_url'], 'api_token' => $pd['api_token']]
            );
            foreach ($languages as $lang) {
                $code = $lang->code;
                if (isset($pd['translations'][$code])) {
                    PaymentMethodTranslation::updateOrCreate(
                        ['payment_method_id' => $payment->id, 'language_id' => $lang->id],
                        ['name' => $pd['translations'][$code]['name'], 'description' => $pd['translations'][$code]['description']]
                    );
                }
            }
            $paymentMethods->push($payment);
        }

        // ----------- 生成购物车和订单 -----------
        foreach ($users as $user) {
            $currency = $currencies->random();

            // 创建购物车
            $cart = Cart::create([
                'user_id' => $user->id,
                'session_id' => null,
            ]);

            // 随机添加1-3个商品或变体到购物车
            $cartItemsCount = rand(1, 3);
            for ($i = 0; $i < $cartItemsCount; $i++) {
                $product = $products->random();
                // 50%概率使用变体
                if (rand(0, 1)) {
                    $variant = $productVariants->where('product_id', $product->id)->random();
                    $price = $variant->price ?? rand(10, 100);
                    CartItem::create([
                        'cart_id' => $cart->id,
                        'product_id' => $product->id,
                        'product_variant_id' => $variant->id,
                        'qty' => rand(1, 5),
                        'price' => $price,
                    ]);
                } else {
                    CartItem::create([
                        'cart_id' => $cart->id,
                        'product_id' => $product->id,
                        'product_variant_id' => null,
                        'qty' => rand(1, 5),
                        'price' => rand(10, 100),
                    ]);
                }
            }

            // 创建订单
            $shippingMethod = $shippingMethods->random();
            $paymentMethod = $paymentMethods->random();

            $order = Order::create([
                'user_id' => $user->id,
                'order_no' => 'ORD-' . strtoupper(uniqid()),
                'currency_id' => $currency->id,
                'total' => 0, // 后面计算
                'status' => 'pending', // 或用OrderStatusEnum
                'shipping_method_id' => $shippingMethod->id,
                'payment_method_id' => $paymentMethod->id,
            ]);

            $total = 0;
            // 从购物车复制商品到订单
            foreach ($cart->cartItems as $item) {
                $price = $item->price;
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
        }
    }
}