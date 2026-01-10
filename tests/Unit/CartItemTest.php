<?php

namespace Tests\Unit;

use App\Models\CartItem;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_relationship()
    {
        $cartItem = new CartItem;
        $relation = $cartItem->cart();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals('cart_id', $relation->getForeignKeyName());
    }

    public function test_product_relationship()
    {
        $cartItem = new CartItem;
        $relation = $cartItem->product();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals('product_id', $relation->getForeignKeyName());
    }

    public function test_product_variant_relationship()
    {
        $cartItem = new CartItem;
        $relation = $cartItem->productVariant();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals('product_variant_id', $relation->getForeignKeyName());
    }
}
