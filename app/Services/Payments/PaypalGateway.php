<?php

namespace App\Services\Payments;

use App\Enums\PaymentMethodEnum;
use App\Services\LocaleCurrencyService;
use App\Services\Payments\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Log;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalHttp\HttpException;

class PaypalGateway implements PaymentGatewayInterface
{
    protected PayPalHttpClient $client;

    public function __construct()
    {
        $config = PaymentMethodEnum::PAYPAL->apiParams();
        if (app()->environment('production')) {
            $clientId = $config['prod']['client_id'];
            $clientSecret = $config['prod']['secret'];
            $environment = new ProductionEnvironment($clientId, $clientSecret);
        } else {
            $clientId = $config['sandBox']['client_id'];
            $clientSecret = $config['sandBox']['secret'];
            $environment = new SandboxEnvironment($clientId, $clientSecret);
        }

        $this->client = new PayPalHttpClient($environment);
    }

    public function create(array $order): string
    {
        try {
            $request = new OrdersCreateRequest;
            $request->prefer('return=representation');

            // 处理金额/货币
            $currencyService = app(LocaleCurrencyService::class);
            $currentCurrency = session('currency', $currencyService->getDefaultCurrencyCode());
            $total = $currencyService->convert($order['total'], 'USD', $currentCurrency);

            $request->body = [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'amount' => [
                        'currency_code' => 'USD',
                        'value' => number_format($total, 2, '.', ''), // 确保是有效格式
                    ],
                    'description' => $order['name'],
                    'custom_id' => $order['order_no'], // 添加订单编号
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
        } catch (HttpException $e) {
            Log::error('PayPal HTTP Exception', [
                'status_code' => $e->statusCode,
                'message' => $e->getMessage(),
                'headers' => $e->headers,
                'response' => $e->getMessage(),
            ]);

            throw new \Exception('PayPal API error: '.$e->getMessage());
        } catch (\Throwable $e) {
            Log::error('General PayPal Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new \Exception('Something went wrong with PayPal order creation.');
        }
    }
}
