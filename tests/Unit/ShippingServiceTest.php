<?php

namespace Tests\Unit;

use App\Models\Address;
use App\Models\Country;
use App\Services\ShippingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShippingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ShippingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ShippingService;
    }

    public function test_get_available_methods()
    {
        $country = Country::factory()->create(['iso_code_2' => 'US']);
        $address = Address::factory()->create(['country_id' => $country->id]);
        $processedItems = [
            ['weight' => 100, 'qty' => 1],
        ];

        $methods = $this->service->getAvailableMethods($processedItems, $address);

        $this->assertIsArray($methods);
        $this->assertGreaterThan(0, count($methods));
        $this->assertArrayHasKey('value', $methods[0]);
        $this->assertArrayHasKey('label', $methods[0]);
        $this->assertArrayHasKey('description', $methods[0]);
        $this->assertArrayHasKey('fee', $methods[0]);
    }

    public function test_get_available_methods_returns_empty_when_no_address()
    {
        $processedItems = [
            ['weight' => 100, 'qty' => 1],
        ];

        $methods = $this->service->getAvailableMethods($processedItems, null);

        $this->assertIsArray($methods);
        $this->assertCount(0, $methods);
    }
}
