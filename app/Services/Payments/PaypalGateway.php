<?php

namespace App\Services\Payments;

use App\Enums\PaymentMethodEnum;
use App\Services\LocaleCurrencyService;
use App\Services\Payments\Contracts\PaymentGatewayInterface;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;

class PaypalGateway implements PaymentGatewayInterface
{
    protected PayPalHttpClient $client;

    public function __construct()
    {
        $config = PaymentMethodEnum::PAYPAL->apiParams();
        if (app()->environment('production')) {
            $clientId = $config['prod']['client_id'];
            $clientSecret = $config['prod']['secret'];
            $environment =  $config['prod']['mode'] === 'live'
                ? new ProductionEnvironment($clientId, $clientSecret)
                : new SandboxEnvironment($clientId, $clientSecret);
        } else {
            $clientId = $config['sandBox']['client_id'];
            $clientSecret = $config['sandBox']['secret'];
            $environment =  $config['sandBox']['mode'] === 'live'
                ? new ProductionEnvironment($clientId, $clientSecret)
                : new SandboxEnvironment($clientId, $clientSecret);
        }

        $this->client = new PayPalHttpClient($environment);
    }

    public function create(array $order): string
    {
        $request = new OrdersCreateRequest();
        $request->prefer('return=representation');

        //处理金额/货币
        $currencyService = app(LocaleCurrencyService::class);
        $currentCurrency = session('currency', $currencyService->getDefaultCurrencyCode());
        $total = $currencyService->convert($order['total'], $currentCurrency);

        $request->body = [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'amount' => [
                    'currency_code' => $currentCurrency,
                    'value' => $total,
                ],
                'description' => $order['name'],
            ]],
            'application_context' => [
                'cancel_url' => locaRoute('payment.cancel'),
                'return_url' => locaRoute('payment.success'),
            ],
        ];

        $response = $this->client->execute($request);

        foreach ($response->result->links as $link) {
            if ($link->rel === 'approve') {
                return $link->href;
            }
        }

        throw new \Exception('PayPal approval link not found.');
    }
}
