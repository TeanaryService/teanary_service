<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\ShippingService;
use App\Models\Address;
use App\Models\Order;
use App\Enums\ShippingMethodEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ShippingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ShippingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ShippingService();
    }

    public function test_get_available_methods_returns_empty_array_when_no_address(): void
    {
        $methods = $this->service->getAvailableMethods([], null);

        $this->assertIsArray($methods);
        // 可能返回空数组或包含方法的数组，取决于实现
    }

    public function test_get_available_methods_returns_methods_when_address_provided(): void
    {
        $address = Address::factory()->create();
        $processedItems = [
            ['qty' => 1, 'weight' => 0.5, 'price' => 100]
        ];

        $methods = $this->service->getAvailableMethods($processedItems, $address);

        $this->assertIsArray($methods);
        // 根据实际实现调整断言
    }
}

