<?php

namespace Tests\Unit;

use App\Enums\ProductStatusEnum;
use Tests\TestCase;

class ProductStatusEnumTest extends TestCase
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
                    'app.products.status.active' => '激活',
                    'app.products.status.inactive' => '未激活',
                ];

                return $map[$key] ?? $key;
            }
        });

        $this->assertEquals('激活', ProductStatusEnum::Active->label());
        $this->assertEquals('未激活', ProductStatusEnum::Inactive->label());
    }

    /**
     * Test the default method returns the correct default status.
     */
    public function test_default_returns_active()
    {
        $this->assertEquals(ProductStatusEnum::Active, ProductStatusEnum::default());
    }

    /**
     * Test the values method returns all enum string values.
     */
    public function test_values_returns_all_enum_values()
    {
        $expectedValues = [
            'active',
            'inactive',
        ];
        $this->assertEquals($expectedValues, ProductStatusEnum::values());
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
                    'app.products.status.active' => '激活',
                    'app.products.status.inactive' => '未激活',
                ];

                return $map[$key] ?? $key;
            }
        });

        $expectedOptions = [
            'active' => '激活',
            'inactive' => '未激活',
        ];
        $this->assertEquals($expectedOptions, ProductStatusEnum::options());
    }
}
