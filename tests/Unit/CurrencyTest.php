<?php

namespace Tests\Unit;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_currency_can_be_created_using_factory()
    {
        $currency = Currency::factory()->create();

        $this->assertNotNull($currency);
        $this->assertInstanceOf(Currency::class, $currency);
        $this->assertIsString($currency->code);
        $this->assertIsString($currency->name);
        $this->assertIsString($currency->symbol);
    }

    public function test_exchange_rate_attribute_casting()
    {
        $currency = Currency::factory()->create(['exchange_rate' => 7.5]);

        $this->assertIsFloat($currency->exchange_rate);
        $this->assertEquals(7.5, $currency->exchange_rate);
    }

    public function test_default_attribute_casting()
    {
        $currency = Currency::factory()->create(['default' => true]);

        $this->assertIsBool($currency->default);
        $this->assertTrue($currency->default);
    }

    public function test_orders_relationship()
    {
        $currency = new Currency;
        $relation = $currency->orders();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertEquals('currency_id', $relation->getForeignKeyName());
    }

    public function test_product_variants_relationship()
    {
        $currency = new Currency;
        $relation = $currency->productVariants();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertEquals('currency_id', $relation->getForeignKeyName());
    }
}
