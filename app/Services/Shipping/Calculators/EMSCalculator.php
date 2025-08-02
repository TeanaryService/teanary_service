<?php

namespace App\Services\Shipping\Calculators;

use App\Models\Address;
use App\Enums\ShippingMethodEnum;
use App\Services\LocaleCurrencyService;
use App\Services\Shipping\Contracts\ShippingCalculatorInterface;
use Illuminate\Support\Facades\Log;

class EMSCalculator implements ShippingCalculatorInterface
{
    private array $zones;

    public function __construct()
    {
        $this->zones = ShippingMethodEnum::EMS_INTERNATIONAL->apiParams()['zones'];
    }

    public function calculate(array $processedItems, ?Address $address): array
    {
        if (!$address) {
            return [];
        }

        try {
            // 获取国家所属区域
            $zone = $this->getZoneForCountry($address->country->iso_code_2);
            
            if (!$zone) {
                return [];
            }

            $totalWeight = $this->calculateTotalWeight($processedItems);

            // 计算运费
            $fee = $this->calculateFee($zone, $totalWeight);

            $currencyService = app(LocaleCurrencyService::class);
            $forCode = 'CNY';
            $fee = $currencyService->convert($fee, $currencyService->getDefaultCurrencyCode(), $forCode);

            return [
                'description' => __('shipping.description.ems', ['days' => $this->getDeliveryDays($zone)]),
                'fee' => $fee,
            ];
        } catch (\Throwable $e) {
            Log::error('EMS calculation error', ['error' => $e->getMessage()]);
            return [];
        }
    }

    protected function getZoneForCountry(string $countryCode): ?int
    {
        foreach ($this->zones as $zone => $countries) {
            if (in_array($countryCode, $countries['countries'])) {
                return $zone;
            }
        }

        return null;
    }

    protected function calculateFee(int $zone, float $totalWeight): float
    {
        $rate = $this->zones[$zone];

        // 计算基础费用和额外重量费用
        $basePrice = $rate['base_item'];  // 按物品计费
        $additionalWeight = max(0, ceil($totalWeight / 500) - 1);
        $additionalFee = $additionalWeight * $rate['additional'];

        return $basePrice + $additionalFee;
    }

    protected function getDeliveryDays(int $zone): string
    {
        return match ($zone) {
            1 => '10-15',
            2 => '10-15',
            3, 4, 5 => '15-30',
            default => '15-30'
        };
    }

    protected function calculateTotalWeight(array $processedItems): float
    {
        return array_reduce($processedItems, function ($carry, $item) {
            return $carry + (($item['weight'] ?? 0) * ($item['qty'] ?? 1));
        }, 0.0);
    }
}
