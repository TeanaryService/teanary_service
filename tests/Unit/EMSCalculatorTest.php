<?php

namespace Tests\Unit;

use App\Models\Address;
use App\Models\Country;
use App\Services\Shipping\Calculators\EMSCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EMSCalculatorTest extends TestCase
{
    use RefreshDatabase;

    protected EMSCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new EMSCalculator;
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
        $country = Country::factory()->create(['iso_code_2' => 'HK']);
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

    public function test_get_delivery_days()
    {
        $reflection = new \ReflectionClass($this->calculator);
        $method = $reflection->getMethod('getDeliveryDays');
        $method->setAccessible(true);

        $this->assertEquals('10-15', $method->invoke($this->calculator, 1));
        $this->assertEquals('10-15', $method->invoke($this->calculator, 2));
        $this->assertEquals('15-30', $method->invoke($this->calculator, 3));
        $this->assertEquals('15-30', $method->invoke($this->calculator, 6));
    }
}
