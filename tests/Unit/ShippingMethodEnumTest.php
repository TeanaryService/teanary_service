<?php

namespace Tests\Unit;

use App\Enums\ShippingMethodEnum;
use Tests\TestCase;

class ShippingMethodEnumTest extends TestCase
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
                    'shipping.method.sf_international' => '顺丰国际',
                    'shipping.method.ems_international' => 'EMS国际',
                ];

                return $map[$key] ?? $key;
            }
        });

        $this->assertEquals('顺丰国际', ShippingMethodEnum::SF_INTERNATIONAL->label());
        $this->assertEquals('EMS国际', ShippingMethodEnum::EMS_INTERNATIONAL->label());
    }

    /**
     * Test the values method returns all enum string values.
     */
    public function test_values_returns_all_enum_values()
    {
        $expectedValues = [
            'sf_international',
            'ems_international',
        ];
        $this->assertEquals($expectedValues, ShippingMethodEnum::values());
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
                    'shipping.method.sf_international' => '顺丰国际',
                    'shipping.method.ems_international' => 'EMS国际',
                ];

                return $map[$key] ?? $key;
            }
        });

        $expectedOptions = [
            'sf_international' => '顺丰国际',
            'ems_international' => 'EMS国际',
        ];
        $this->assertEquals($expectedOptions, ShippingMethodEnum::options());
    }

    /**
     * Test the apiParams method for SF_INTERNATIONAL.
     */
    public function test_api_params_sf_international()
    {
        $params = ShippingMethodEnum::SF_INTERNATIONAL->apiParams();

        $this->assertIsArray($params);
        $this->assertArrayHasKey('zones', $params);
        $this->assertIsArray($params['zones']);
        $this->assertCount(9, $params['zones']); // Check number of zones

        // Test all zones' data structure
        $expectedZones = [
            1 => ['base_doc' => 112, 'base_item' => 124, 'per_kg' => 54, 'countries' => ['KR']],
            2 => ['base_doc' => 129, 'base_item' => 143, 'per_kg' => 60, 'countries' => ['SG', 'MY', 'VN', 'TH']],
            3 => ['base_doc' => 177, 'base_item' => 195, 'per_kg' => 65, 'countries' => ['JP']],
            4 => ['base_doc' => 204, 'base_item' => 221, 'per_kg' => 113, 'countries' => ['NZ', 'AU']],
            5 => ['base_doc' => 209, 'base_item' => 233, 'per_kg' => 95, 'countries' => ['PH', 'BD', 'IN', 'NP']],
            6 => ['base_doc' => 266, 'base_item' => 285, 'per_kg' => 131, 'countries' => ['US', 'CA', 'MX']],
            7 => ['base_doc' => 276, 'base_item' => 295, 'per_kg' => 117, 'countries' => ['GB', 'FR', 'DE', 'IT', 'ES', 'NL', 'BE', 'SE', 'CH', 'AT', 'DK', 'NO', 'FI', 'IE', 'PT', 'GR']],
            8 => ['base_doc' => 288, 'base_item' => 306, 'per_kg' => 148, 'countries' => ['AE', 'BR', 'CL', 'KE']],
            9 => ['base_doc' => 314, 'base_item' => 334, 'per_kg' => 216, 'countries' => ['HR', 'BG', 'LT', 'SI', 'TZ']],
        ];

        foreach ($expectedZones as $zoneId => $expectedZone) {
            $this->assertArrayHasKey($zoneId, $params['zones'], "Zone {$zoneId} should exist");
            $this->assertArrayHasKey('base_doc', $params['zones'][$zoneId], "Zone {$zoneId} should have base_doc");
            $this->assertArrayHasKey('base_item', $params['zones'][$zoneId], "Zone {$zoneId} should have base_item");
            $this->assertArrayHasKey('per_kg', $params['zones'][$zoneId], "Zone {$zoneId} should have per_kg");
            $this->assertArrayHasKey('countries', $params['zones'][$zoneId], "Zone {$zoneId} should have countries");
            $this->assertIsArray($params['zones'][$zoneId]['countries'], "Zone {$zoneId} countries should be an array");
            $this->assertEquals($expectedZone, $params['zones'][$zoneId], "Zone {$zoneId} data should match");
        }
    }

    /**
     * Test the apiParams method for EMS_INTERNATIONAL.
     */
    public function test_api_params_ems_international()
    {
        $params = ShippingMethodEnum::EMS_INTERNATIONAL->apiParams();

        $this->assertIsArray($params);
        $this->assertArrayHasKey('zones', $params);
        $this->assertIsArray($params['zones']);
        $this->assertCount(9, $params['zones']); // Check number of zones

        // Test all zones' data structure
        $expectedZones = [
            1 => ['base_doc' => 90, 'base_item' => 130, 'additional' => 30, 'countries' => ['MO', 'TW', 'HK']],
            2 => ['base_doc' => 115, 'base_item' => 180, 'additional' => 40, 'countries' => ['KP', 'KR', 'JP']],
            3 => ['base_doc' => 130, 'base_item' => 190, 'additional' => 45, 'countries' => ['PH', 'KH', 'MY', 'MN', 'TH', 'SG', 'ID', 'AM']],
            4 => ['base_doc' => 160, 'base_item' => 210, 'additional' => 55, 'countries' => ['AU', 'PG', 'NZ']],
            5 => ['base_doc' => 180, 'base_item' => 240, 'additional' => 75, 'countries' => ['US']],
            6 => ['base_doc' => 220, 'base_item' => 280, 'additional' => 75, 'countries' => ['IE', 'AT', 'BE', 'DK', 'FI', 'FR', 'CA', 'LU', 'MT', 'NO', 'PT', 'SE', 'CH', 'ES', 'GR', 'IT', 'GB']],
            7 => ['base_doc' => 240, 'base_item' => 300, 'additional' => 80, 'countries' => ['PK', 'LA', 'BD', 'NP', 'LK', 'TR', 'IN']],
            8 => ['base_doc' => 260, 'base_item' => 335, 'additional' => 100, 'countries' => ['AE', 'PA', 'BR', 'BY', 'PL', 'RU', 'CO', 'CU', 'VE', 'CZ', 'SY', 'MX', 'UA', 'HU', 'IL', 'JO']],
            9 => ['base_doc' => 280, 'base_item' => 350, 'additional' => 110, 'countries' => ['OM', 'EG', 'ET', 'EE', 'BH', 'BG', 'BW', 'ZA', 'ZW', 'KM', 'CG', 'CD', 'KZ', 'KG', 'GN', 'GA', 'GH', 'QA', 'CI', 'KW', 'LV', 'LT', 'MG', 'MW', 'MR', 'MU', 'NE', 'NG', 'RS', 'SL', 'SN', 'SD', 'TJ', 'TZ', 'TN', 'UG', 'UZ', 'YE', 'ZM', 'IR', 'TD']],
        ];

        foreach ($expectedZones as $zoneId => $expectedZone) {
            $this->assertArrayHasKey($zoneId, $params['zones'], "Zone {$zoneId} should exist");
            $this->assertArrayHasKey('base_doc', $params['zones'][$zoneId], "Zone {$zoneId} should have base_doc");
            $this->assertArrayHasKey('base_item', $params['zones'][$zoneId], "Zone {$zoneId} should have base_item");
            $this->assertArrayHasKey('additional', $params['zones'][$zoneId], "Zone {$zoneId} should have additional");
            $this->assertArrayHasKey('countries', $params['zones'][$zoneId], "Zone {$zoneId} should have countries");
            $this->assertIsArray($params['zones'][$zoneId]['countries'], "Zone {$zoneId} countries should be an array");
            $this->assertEquals($expectedZone, $params['zones'][$zoneId], "Zone {$zoneId} data should match");
        }
    }

    /**
     * Test the random method returns a ShippingMethodEnum instance.
     */
    public function test_random_method_returns_enum_instance()
    {
        $randomEnum = ShippingMethodEnum::random();
        $this->assertInstanceOf(ShippingMethodEnum::class, $randomEnum);
        $this->assertContains($randomEnum, ShippingMethodEnum::cases());
    }
}
