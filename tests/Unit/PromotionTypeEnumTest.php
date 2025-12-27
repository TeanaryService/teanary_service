<?php

namespace Tests\Unit;

use App\Enums\PromotionTypeEnum;
use Tests\TestCase;

class PromotionTypeEnumTest extends TestCase
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
                    'promotion.type.coupon' => '优惠券',
                    'promotion.type.automatic' => '自动应用',
                ];

                return $map[$key] ?? $key;
            }
        });

        $this->assertEquals('优惠券', PromotionTypeEnum::Coupon->label());
        $this->assertEquals('自动应用', PromotionTypeEnum::Automatic->label());
    }

    /**
     * Test the values method returns all enum string values.
     */
    public function test_values_returns_all_enum_values()
    {
        $expectedValues = [
            'coupon',
            'automatic',
        ];
        $this->assertEquals($expectedValues, PromotionTypeEnum::values());
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
                    'promotion.type.coupon' => '优惠券',
                    'promotion.type.automatic' => '自动应用',
                ];

                return $map[$key] ?? $key;
            }
        });

        $expectedOptions = [
            'coupon' => '优惠券',
            'automatic' => '自动应用',
        ];
        $this->assertEquals($expectedOptions, PromotionTypeEnum::options());
    }
}
