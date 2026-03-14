<?php

namespace App\Services\Shipping\Calculators;

use App\Enums\ShippingMethodEnum;
use App\Models\Address;
use App\Models\Warehouse;
use App\Services\LocaleCurrencyService;
use App\Services\Shipping\Contracts\ShippingCalculatorInterface;
use Illuminate\Support\Facades\Log;

class SFExpressCalculator implements ShippingCalculatorInterface
{
    private array $zones;

    public function __construct()
    {
        $this->zones = ShippingMethodEnum::SF_INTERNATIONAL->apiParams()['zones'];
    }

    public function calculate(array $processedItems, ?Address $address, ?Warehouse $originWarehouse = null): array
    {
        if (! $address) {
            return [];
        }

        try {
            // 获取国家所属区域
            $zone = $this->getZoneForCountry($address->country->iso_code_2);
            if (! $zone) {
                return [];
            }

            $totalWeight = $this->calculateTotalWeight($processedItems);

            // 计算运费
            $fee = $this->calculateFee($zone, $totalWeight);

            $currencyService = app(LocaleCurrencyService::class);
            $forCode = 'CNY';
            $fee = $currencyService->convert($fee, $currencyService->getDefaultCurrencyCode(), $forCode);

            return [
                'description' => __('shipping.description.sf', ['days' => '15-30']),
                'fee' => $fee,
            ];
        } catch (\Throwable $e) {
            Log::error('SF Express calculation error', ['error' => $e->getMessage()]);

            return [];
        }
    }

    protected function getZoneForCountry(string $countryCode): ?array
    {
        foreach ($this->zones as $zone) {
            if (in_array($countryCode, $zone['countries'])) {
                return $zone;
            }
        }

        return null;
    }

    protected function calculateTotalWeight(array $processedItems): float
    {
        return array_reduce($processedItems, function ($carry, $item) {
            return $carry + (($item['weight'] ?? 0) * ($item['qty'] ?? 1));
        }, 0.0);
    }

    protected function calculateFee(array $zone, float $totalWeight): float
    {
        // 按物品计费
        $basePrice = $zone['base_item'];

        // 如果重量超过500g,计算续重费用
        if ($totalWeight > 500) {
            $additionalKg = ceil($totalWeight - 500);

            return $basePrice + ($additionalKg * $zone['per_kg']);
        }

        return $basePrice;
    }
}
