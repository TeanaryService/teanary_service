<?php

namespace Tests\Unit;

use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductVariantTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_variant_can_be_created_using_factory()
    {
        $variant = ProductVariant::factory()->create();

        $this->assertNotNull($variant);
        $this->assertInstanceOf(ProductVariant::class, $variant);
        $this->assertIsString($variant->sku);
    }

    public function test_product_relationship()
    {
        $variant = new ProductVariant;
        $relation = $variant->product();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals('product_id', $relation->getForeignKeyName());
    }

    public function test_cart_items_relationship()
    {
        $variant = new ProductVariant;
        $relation = $variant->cartItems();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertEquals('product_variant_id', $relation->getForeignKeyName());
    }

    public function test_order_items_relationship()
    {
        $variant = new ProductVariant;
        $relation = $variant->orderItems();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertEquals('product_variant_id', $relation->getForeignKeyName());
    }

    public function test_product_reviews_relationship()
    {
        $variant = new ProductVariant;
        $relation = $variant->productReviews();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertEquals('product_variant_id', $relation->getForeignKeyName());
    }

    public function test_specifications_relationship()
    {
        $variant = new ProductVariant;
        $relation = $variant->specifications();

        $this->assertInstanceOf(BelongsToMany::class, $relation);
        $this->assertEquals('product_variant_specification_value', $relation->getTable());
    }

    public function test_specification_values_relationship()
    {
        $variant = new ProductVariant;
        $relation = $variant->specificationValues();

        $this->assertInstanceOf(BelongsToMany::class, $relation);
        $this->assertEquals('product_variant_specification_value', $relation->getTable());
    }

    public function test_promotions_relationship()
    {
        $variant = new ProductVariant;
        $relation = $variant->promotions();

        $this->assertInstanceOf(BelongsToMany::class, $relation);
        $this->assertEquals('promotion_product_variant', $relation->getTable());
    }
}
