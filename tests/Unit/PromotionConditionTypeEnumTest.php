<?php

namespace Tests\Unit;

use App\Enums\PromotionConditionTypeEnum;
use Tests\TestCase;

class PromotionConditionTypeEnumTest extends TestCase
{
    /**
     * Test the label method returns the correct localized string.
     */
    public function test_label_returns_correct_string()
    {
        // Mock the __() helper function for testing purposes
        $this->app->instance('translator', new class
        {
            public function get($key)
            {
                $map = [
                    'app.promotion.condition.order_total_min' => '订单总额达到最低',
                    'app.promotion.condition.order_qty_min' => '订单数量达到最低',
                ];

                return $map[$key] ?? $key;
            }
        });

        $this->assertEquals('订单总额达到最低', PromotionConditionTypeEnum::OrderTotalMin->label());
        $this->assertEquals('订单数量达到最低', PromotionConditionTypeEnum::OrderQtyMin->label());
    }

    /**
     * Test the values method returns all enum string values.
     */
    public function test_values_returns_all_enum_values()
    {
        $expectedValues = [
            'order_total_min',
            'order_qty_min',
        ];
        $this->assertEquals($expectedValues, PromotionConditionTypeEnum::values());
    }

    /**
     * Test the options method returns all enum options (value => label).
     */
    public function test_options_returns_all_enum_options()
    {
        // Mock the __() helper function as above
        $this->app->instance('translator', new class
        {
            public function get($key)
            {
                $map = [
                    'app.promotion.condition.order_total_min' => '订单总额达到最低',
                    'app.promotion.condition.order_qty_min' => '订单数量达到最低',
                ];

                return $map[$key] ?? $key;
            }
        });

        $expectedOptions = [
            'order_total_min' => '订单总额达到最低',
            'order_qty_min' => '订单数量达到最低',
        ];
        $this->assertEquals($expectedOptions, PromotionConditionTypeEnum::options());
    }
}
