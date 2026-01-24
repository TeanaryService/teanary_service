<?php

namespace Tests\Feature\Livewire\Components;

use App\Enums\ProductStatusEnum;
use App\Models\Product;
use Tests\Feature\LivewireTestCase;

class FeaturedProductsTest extends LivewireTestCase
{
    public function test_featured_products_can_be_rendered()
    {
        $component = $this->livewire(\App\Livewire\Components\FeaturedProducts::class);
        $component->assertSuccessful();
    }

    public function test_featured_products_displays_products()
    {
        $product = $this->createProduct([
            'status' => ProductStatusEnum::Active,
        ]);

        $component = $this->livewire(\App\Livewire\Components\FeaturedProducts::class);
        $component->assertSuccessful();

        $products = $component->get('products');
        $productIds = $products->pluck('id')->toArray();
        $this->assertContains($product->id, $productIds);
    }

    public function test_featured_products_only_shows_active_products()
    {
        $activeProduct = $this->createProduct([
            'status' => ProductStatusEnum::Active,
        ]);
        $inactiveProduct = $this->createProduct([
            'status' => ProductStatusEnum::Inactive,
        ]);

        $component = $this->livewire(\App\Livewire\Components\FeaturedProducts::class);

        $products = $component->get('products');
        $productIds = $products->pluck('id')->toArray();
        $this->assertContains($activeProduct->id, $productIds);
        $this->assertNotContains($inactiveProduct->id, $productIds);
    }

    public function test_featured_products_limits_to_8_products()
    {
        // 创建超过8个商品
        Product::factory()->count(10)->create([
            'status' => ProductStatusEnum::Active,
        ]);

        $component = $this->livewire(\App\Livewire\Components\FeaturedProducts::class);

        $products = $component->get('products');
        $this->assertLessThanOrEqual(8, $products->count());
    }

    public function test_featured_products_orders_by_latest()
    {
        $product1 = $this->createProduct([
            'status' => ProductStatusEnum::Active,
            'created_at' => now()->subDays(2),
        ]);
        $product2 = $this->createProduct([
            'status' => ProductStatusEnum::Active,
            'created_at' => now()->subDays(1),
        ]);

        $component = $this->livewire(\App\Livewire\Components\FeaturedProducts::class);

        $products = $component->get('products');
        $productIds = $products->pluck('id')->toArray();
        // 最新的应该在前面
        $this->assertTrue(array_search($product2->id, $productIds) < array_search($product1->id, $productIds));
    }
}
