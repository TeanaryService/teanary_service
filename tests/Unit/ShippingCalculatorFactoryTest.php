<?php

namespace Tests\Unit;

use App\Enums\ShippingMethodEnum;
use App\Services\Shipping\Calculators\EMSCalculator;
use App\Services\Shipping\Calculators\SFExpressCalculator;
use App\Services\Shipping\Contracts\ShippingCalculatorInterface;
use App\Services\Shipping\ShippingCalculatorFactory;
use Tests\TestCase;

class ShippingCalculatorFactoryTest extends TestCase
{
    public function test_make_returns_sf_express_calculator_for_sf_international()
    {
        $calculator = ShippingCalculatorFactory::make(ShippingMethodEnum::SF_INTERNATIONAL);

        $this->assertInstanceOf(ShippingCalculatorInterface::class, $calculator);
        $this->assertInstanceOf(SFExpressCalculator::class, $calculator);
    }

    public function test_make_returns_ems_calculator_for_ems_international()
    {
        $calculator = ShippingCalculatorFactory::make(ShippingMethodEnum::EMS_INTERNATIONAL);

        $this->assertInstanceOf(ShippingCalculatorInterface::class, $calculator);
        $this->assertInstanceOf(EMSCalculator::class, $calculator);
    }
}
