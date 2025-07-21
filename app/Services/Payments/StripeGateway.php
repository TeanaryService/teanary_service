<?php

namespace App\Services\Payments;

use App\Enums\PaymentMethodEnum;
use App\Services\LocaleCurrencyService;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Services\Payments\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Str;

class StripeGateway implements PaymentGatewayInterface
{
    public function create(array $order): string
    {
        $config = PaymentMethodEnum::STRIPE->apiParams();

        if (app()->environment('production')) {
            Stripe::setApiKey($config['prod']['secret']);
        } else {
            Stripe::setApiKey($config['sandBox']['secret']);
        }

        //处理金额/货币
        $currencyService = app(LocaleCurrencyService::class);
        $currentCurrency = session('currency', $currencyService->getDefaultCurrencyCode());
        $total = $currencyService->convert($order['total'], $currentCurrency);

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => Str::lower($currentCurrency),
                    'product_data' => ['name' => $order['name']],
                    'unit_amount' => intval($total * 100),
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => locaRoute('payment.success'),
            'cancel_url' => locaRoute('payment.cancel'),
        ]);

        return $session->url;
    }
}
