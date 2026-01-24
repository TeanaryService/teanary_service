<?php

namespace Tests\Feature\Livewire\Components;

use App\Enums\ProductStatusEnum;
use Tests\Feature\LivewireTestCase;

class RecommendProductsTest extends LivewireTestCase
{
    public function test_recommend_products_can_be_rendered()
    {
        $product = $this->createProduct([
            'status' => ProductStatusEnum::Active,
        ]);
        $category = $this->createCategory();
        $product->productCategories()->attach($category->id);

        $component = $this->livewire(\App\Livewire\Components\RecommendProducts::class, [
            'currentProductId' => $product->id,
            'categoryIds' => [$category->id],
        ]);
        $component->assertSuccessful();
    }

    public function test_recommend_products_loads_products()
    {
        $product1 = $this->createProduct([
            'status' => ProductStatusEnum::Active,
        ]);
        $product2 = $this->createProduct([
            'status' => ProductStatusEnum::Active,
        ]);
        $category = $this->createCategory();
        $product1->productCategories()->attach($category->id);
        $product2->productCategories()->attach($category->id);

        $component = $this->livewire(\App\Livewire\Components\RecommendProducts::class, [
            'currentProductId' => $product1->id,
            'categoryIds' => [$category->id],
        ])
            ->call('loadRecommendedProducts');

        $recommendedProducts = $component->get('recommendedProducts');
        $this->assertNotEmpty($recommendedProducts);
    }

    public function test_recommend_products_excludes_current_product()
    {
        $product1 = $this->createProduct([
            'status' => ProductStatusEnum::Active,
        ]);
        $product2 = $this->createProduct([
            'status' => ProductStatusEnum::Active,
        ]);
        $category = $this->createCategory();
        $product1->productCategories()->attach($category->id);
        $product2->productCategories()->attach($category->id);

        $component = $this->livewire(\App\Livewire\Components\RecommendProducts::class, [
            'currentProductId' => $product1->id,
            'categoryIds' => [$category->id],
        ])
            ->call('loadRecommendedProducts');

        $recommendedProducts = $component->get('recommendedProducts');
        $productIds = $recommendedProducts->pluck('id')->toArray();
        $this->assertNotContains($product1->id, $productIds);
    }

    public function test_recommend_products_only_loads_once()
    {
        $product = $this->createProduct([
            'status' => ProductStatusEnum::Active,
        ]);
        $category = $this->createCategory();
        $product->productCategories()->attach($category->id);

        $component = $this->livewire(\App\Livewire\Components\RecommendProducts::class, [
            'currentProductId' => $product->id,
            'categoryIds' => [$category->id],
        ])
            ->call('loadRecommendedProducts')
            ->assertSet('loaded', true)
            ->call('loadRecommendedProducts')
            ->assertSet('loaded', true);
    }

    public function test_recommend_products_only_shows_active_products()
    {
        $activeProduct = $this->createProduct([
            'status' => ProductStatusEnum::Active,
        ]);
        $inactiveProduct = $this->createProduct([
            'status' => ProductStatusEnum::Inactive,
        ]);
        $category = $this->createCategory();
        $activeProduct->productCategories()->attach($category->id);
        $inactiveProduct->productCategories()->attach($category->id);

        $component = $this->livewire(\App\Livewire\Components\RecommendProducts::class, [
            'currentProductId' => $activeProduct->id,
            'categoryIds' => [$category->id],
        ])
            ->call('loadRecommendedProducts');

        $recommendedProducts = $component->get('recommendedProducts');
        $productIds = $recommendedProducts->pluck('id')->toArray();
        $this->assertNotContains($inactiveProduct->id, $productIds);
    }
}
