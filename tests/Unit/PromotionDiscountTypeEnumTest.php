<?php

namespace Tests\Unit;

use App\Enums\PromotionDiscountTypeEnum;
use Tests\TestCase;

class PromotionDiscountTypeEnumTest extends TestCase
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
                    'app.promotion.discount_type.fixed' => '固定金额',
                    'app.promotion.discount_type.percentage' => '百分比',
                ];

                return $map[$key] ?? $key;
            }
        });

        $this->assertEquals('固定金额', PromotionDiscountTypeEnum::Fixed->label());
        $this->assertEquals('百分比', PromotionDiscountTypeEnum::Percentage->label());
    }

    /**
     * Test the values method returns all enum string values.
     */
    public function test_values_returns_all_enum_values()
    {
        $expectedValues = [
            'fixed',
            'percentage',
        ];
        $this->assertEquals($expectedValues, PromotionDiscountTypeEnum::values());
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
                    'app.promotion.discount_type.fixed' => '固定金额',
                    'app.promotion.discount_type.percentage' => '百分比',
                ];

                return $map[$key] ?? $key;
            }
        });

        $expectedOptions = [
            'fixed' => '固定金额',
            'percentage' => '百分比',
        ];
        $this->assertEquals($expectedOptions, PromotionDiscountTypeEnum::options());
    }
}
