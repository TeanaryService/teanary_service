<?php

namespace Tests\Unit;

use App\Models\Address;
use App\Models\Country;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddressTest extends TestCase
{
    use RefreshDatabase; // 确保每次测试后数据库状态干净

    /**
     * Test if an Address instance can be created using the factory.
     */
    public function test_address_can_be_created_using_factory()
    {
        $address = Address::factory()->create();

        $this->assertNotNull($address);
        $this->assertInstanceOf(Address::class, $address);
        $this->assertIsString($address->firstname);
        $this->assertIsString($address->email);
        $this->assertIsString($address->address_1);
    }

    /**
     * Test the 'country' relationship.
     */
    public function test_country_relationship()
    {
        $address = new Address;
        $relation = $address->country();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals('country_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getOwnerKeyName());
    }

    /**
     * Test the 'user' relationship.
     */
    public function test_user_relationship()
    {
        $address = new Address;
        $relation = $address->user();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals('user_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getOwnerKeyName());
    }

    /**
     * Test the 'zone' relationship.
     */
    public function test_zone_relationship()
    {
        $address = new Address;
        $relation = $address->zone();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals('zone_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getOwnerKeyName());
    }

    /**
     * Test the 'orders' relationship.
     */
    public function test_orders_relationship()
    {
        $address = new Address;
        $relation = $address->orders();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertEquals('shipping_address_id', $relation->getForeignKeyName());
    }
}
