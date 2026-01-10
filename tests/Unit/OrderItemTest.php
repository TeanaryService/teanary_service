<?php

namespace Tests\Unit;

use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_relationship()
    {
        $orderItem = new OrderItem;
        $relation = $orderItem->order();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals('order_id', $relation->getForeignKeyName());
    }

    public function test_product_relationship()
    {
        $orderItem = new OrderItem;
        $relation = $orderItem->product();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals('product_id', $relation->getForeignKeyName());
    }

    public function test_product_variant_relationship()
    {
        $orderItem = new OrderItem;
        $relation = $orderItem->productVariant();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals('product_variant_id', $relation->getForeignKeyName());
    }
}
