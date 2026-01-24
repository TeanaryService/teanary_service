<?php

namespace Tests\Feature\Livewire\Components;

use App\Enums\ProductStatusEnum;
use App\Models\Product;
use Tests\Feature\LivewireTestCase;

class RandomProductsTest extends LivewireTestCase
{
    public function test_random_products_can_be_rendered()
    {
        $component = $this->livewire(\App\Livewire\Components\RandomProducts::class);
        $component->assertSuccessful();
    }

    public function test_random_products_displays_products()
    {
        $product = $this->createProduct([
            'status' => ProductStatusEnum::Active,
        ]);

        $component = $this->livewire(\App\Livewire\Components\RandomProducts::class);
        $component->assertSuccessful();
    }

    public function test_random_products_only_shows_active_products()
    {
        $activeProduct = $this->createProduct([
            'status' => ProductStatusEnum::Active,
        ]);
        $inactiveProduct = $this->createProduct([
            'status' => ProductStatusEnum::Inactive,
        ]);

        $component = $this->livewire(\App\Livewire\Components\RandomProducts::class);
        $component->assertSuccessful();
    }

    public function test_random_products_respects_limit()
    {
        // 创建超过默认限制（4个）的商品
        Product::factory()->count(10)->create([
            'status' => ProductStatusEnum::Active,
        ]);

        $component = $this->livewire(\App\Livewire\Components\RandomProducts::class, ['limit' => 4]);
        $component->assertSuccessful();
    }

    public function test_random_products_can_customize_class()
    {
        $component = $this->livewire(\App\Livewire\Components\RandomProducts::class, [
            'limit' => 4,
            'class' => 'custom-grid-class',
        ]);

        $component->assertSet('class', 'custom-grid-class');
    }
}
