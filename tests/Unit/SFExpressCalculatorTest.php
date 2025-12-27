<?php

namespace Tests\Unit;

use App\Models\Address;
use App\Models\Country;
use App\Services\Shipping\Calculators\SFExpressCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SFExpressCalculatorTest extends TestCase
{
    use RefreshDatabase;

    protected SFExpressCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new SFExpressCalculator;
    }

    public function test_calculate_returns_empty_array_when_no_address()
    {
        $processedItems = [
            ['weight' => 100, 'qty' => 1],
        ];

        $result = $this->calculator->calculate($processedItems, null);

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    public function test_calculate_returns_empty_array_when_country_not_in_zones()
    {
        $country = Country::factory()->create(['iso_code_2' => 'XX']);
        $address = Address::factory()->create(['country_id' => $country->id]);
        $processedItems = [
            ['weight' => 100, 'qty' => 1],
        ];

        $result = $this->calculator->calculate($processedItems, $address);

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    public function test_calculate_returns_fee_for_valid_country()
    {
        $country = Country::factory()->create(['iso_code_2' => 'KR']);
        $address = Address::factory()->create(['country_id' => $country->id]);
        $processedItems = [
            ['weight' => 100, 'qty' => 1],
        ];

        $result = $this->calculator->calculate($processedItems, $address);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('description', $result);
        $this->assertArrayHasKey('fee', $result);
        $this->assertIsFloat($result['fee']);
        $this->assertGreaterThan(0, $result['fee']);
    }

    public function test_calculate_total_weight()
    {
        $processedItems = [
            ['weight' => 100, 'qty' => 2],
            ['weight' => 200, 'qty' => 1],
        ];

        $reflection = new \ReflectionClass($this->calculator);
        $method = $reflection->getMethod('calculateTotalWeight');
        $method->setAccessible(true);

        $result = $method->invoke($this->calculator, $processedItems);

        $this->assertEquals(400, $result);
    }

    public function test_calculate_fee_for_weight_under500g()
    {
        $zone = ['base_item' => 124, 'per_kg' => 54];
        $weight = 400;

        $reflection = new \ReflectionClass($this->calculator);
        $method = $reflection->getMethod('calculateFee');
        $method->setAccessible(true);

        $result = $method->invoke($this->calculator, $zone, $weight);

        $this->assertEquals(124, $result);
    }

    public function test_calculate_fee_for_weight_over500g()
    {
        $zone = ['base_item' => 124, 'per_kg' => 54];
        $weight = 1000;

        $reflection = new \ReflectionClass($this->calculator);
        $method = $reflection->getMethod('calculateFee');
        $method->setAccessible(true);

        $result = $method->invoke($this->calculator, $zone, $weight);

        $this->assertGreaterThan(124, $result);
    }
}
