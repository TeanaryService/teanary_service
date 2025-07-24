<?php

namespace App\Services\Shipping\Calculators;

use App\Enums\ShippingMethodEnum;
use App\Models\Address;
use App\Services\LocaleCurrencyService;
use App\Services\Shipping\Contracts\ShippingCalculatorInterface;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class SFExpressCalculator implements ShippingCalculatorInterface
{
    protected string $partnerId;
    protected string $checkword;
    protected string $endpoint;

    public function __construct()
    {
        $config = ShippingMethodEnum::SF_INTERNATIONAL->apiParams();
        if (app()->environment('production')) {
            // 生产环境的代码
            $this->partnerId = $config['prod']['partnerId'];
            $this->checkword = $config['prod']['checkword'];
            $this->endpoint = $config['prod']['endpoint'];
        } else {
            $this->partnerId = $config['sandBox']['partnerId'];
            $this->checkword = $config['sandBox']['checkword'];
            $this->endpoint = $config['sandBox']['endpoint'];
        }
    }

    public function calculate(array $processedItems, ?Address $address): array
    {
        if (!$address) {
            return [];
        }
        try {
            $requestData = $this->buildRequestData($processedItems, $address);
            $result = $this->request('COM_RECE_IUOP_ESTIMATE_FEE', $requestData);
            $data = json_decode($result['apiResultData'] ?? '', true, 512, JSON_THROW_ON_ERROR);

            if (empty($data['success'])) {
                return [];
            }

            $currencyService = app(LocaleCurrencyService::class);
            $forCode = $data['msgData']['currency'];
            $fee = $currencyService->convert((float)$data['msgData']['totalFee'], $currencyService->getDefaultCurrencyCode(), $forCode);
            return [
                'description' => __('shipping.description.sf', ['days' => '15-30']),
                'fee' => $fee,
            ];
        } catch (\Throwable $e) {
            // 可选：记录日志
            // Log::error('SFExpress parse error', ['error' => $e->getMessage(), 'result' => $result]);
            return [];
        }
    }

    /**
     * 构建顺丰预估运费请求参数
     */
    protected function buildRequestData(array $processedItems, Address $address): array
    {
        $totalQty = 0;
        $totalWeight = 0.0;
        $declaredValue = 0.0;

        foreach ($processedItems as $item) {
            $qty = $item['qty'] ?? 1;
            $weight = $item['weight'] ?? 0;
            $price = $item['price'] ?? 0;

            $totalQty += $qty;
            $totalWeight += $weight * $qty;
            $declaredValue += $price * $qty;
        }

        return [
            'customerCode'      => $this->partnerId,
            'interProductCode'  => 'INT0014',
            'parcelQuantity'    => $totalQty,
            'parcelTotalWeight' => round($totalWeight, 2),
            'declaredValue'     => round($declaredValue, 2),
            'senderInfo' => [
                'country'  => 'CN',
                'postCode' => '650200',
            ],
            'receiverInfo' => [
                'country'  => $address->country->code,
                'postCode' => $address->country->postcode ?? '',
            ],
        ];
    }

    /**
     * 构建请求签名
     */
    protected function makeSignature(string $msgData, int $timestamp): string
    {
        return base64_encode(md5(urlencode($msgData . $timestamp . $this->checkword), true));
    }

    /**
     * 统一请求方法
     */
    protected function request(string $serviceCode, array|string $data): array
    {
        $msgData = is_array($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : $data;
        $timestamp = time();
        $requestID = (string) Str::uuid();
        $msgDigest = $this->makeSignature($msgData, $timestamp);

        $payload = [
            'partnerID'  => $this->partnerId,
            'requestID'  => $requestID,
            'serviceCode' => $serviceCode,
            'timestamp'  => $timestamp,
            'msgDigest'  => $msgDigest,
            'msgData'    => $msgData,
        ];

        $response = Http::asForm()
            ->timeout(30)
            ->post($this->endpoint, $payload);

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('顺丰接口请求失败: ' . $response->body());
    }
}
