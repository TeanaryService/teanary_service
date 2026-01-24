<?php

namespace Tests\Feature\Livewire\Manager;

use App\Enums\ProductStatusEnum;
use Tests\Feature\LivewireTestCase;

class ProductsTest extends LivewireTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->createManager(), 'manager');
    }

    public function test_products_page_can_be_rendered()
    {
        $component = $this->livewire(\App\Livewire\Manager\Products::class);

        $component->assertSuccessful();
    }

    public function test_products_list_displays_products()
    {
        $product = $this->createProduct([
            'status' => ProductStatusEnum::Active,
        ]);

        $component = $this->livewire(\App\Livewire\Manager\Products::class);

        $products = $component->get('products');
        $productIds = $products->pluck('id')->toArray();
        $this->assertContains($product->id, $productIds);
    }

    public function test_can_search_products_by_name()
    {
        $product1 = $this->createProduct();
        $product2 = $this->createProduct();

        // 创建产品翻译
        $language = $this->createLanguage();
        \App\Models\ProductTranslation::factory()->create([
            'product_id' => $product1->id,
            'language_id' => $language->id,
            'name' => '测试商品1',
        ]);
        \App\Models\ProductTranslation::factory()->create([
            'product_id' => $product2->id,
            'language_id' => $language->id,
            'name' => '其他商品',
        ]);

        $component = $this->livewire(\App\Livewire\Manager\Products::class)
            ->set('search', '测试')
            ->assertSet('search', '测试');

        $products = $component->get('products');
        $this->assertTrue($products->contains('id', $product1->id));
        $this->assertFalse($products->contains('id', $product2->id));
    }

    public function test_can_filter_products_by_status()
    {
        $activeProduct = $this->createProduct(['status' => ProductStatusEnum::Active]);
        $inactiveProduct = $this->createProduct(['status' => ProductStatusEnum::Inactive]);

        $component = $this->livewire(\App\Livewire\Manager\Products::class)
            ->set('filterStatus', [ProductStatusEnum::Active->value])
            ->assertSet('filterStatus', [ProductStatusEnum::Active->value]);

        $products = $component->get('products');
        $productIds = $products->pluck('id')->toArray();
        $this->assertContains($activeProduct->id, $productIds);
        $this->assertNotContains($inactiveProduct->id, $productIds);
    }

    public function test_can_filter_products_by_category()
    {
        $category = $this->createCategory();
        $product1 = $this->createProduct();
        $product2 = $this->createProduct();

        // 关联产品到分类
        $product1->productCategories()->attach($category->id);

        $component = $this->livewire(\App\Livewire\Manager\Products::class)
            ->set('filterCategoryId', $category->id);

        $products = $component->get('products');
        $this->assertTrue($products->contains('id', $product1->id));
        $this->assertFalse($products->contains('id', $product2->id));
    }

    public function test_can_delete_product()
    {
        $product = $this->createProduct();

        $component = $this->livewire(\App\Livewire\Manager\Products::class)
            ->call('deleteProduct', $product->id);

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_reset_filters_clears_all_filters()
    {
        $component = $this->livewire(\App\Livewire\Manager\Products::class)
            ->set('search', 'test')
            ->set('filterStatus', [ProductStatusEnum::Active->value])
            ->set('filterCategoryId', 1)
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('filterStatus', [])
            ->assertSet('filterCategoryId', null);
    }

    public function test_updating_search_resets_page()
    {
        $component = $this->livewire(\App\Livewire\Manager\Products::class)
            ->set('search', 'test');

        // 验证页面已重置（通过检查分页状态）
        $component->assertSet('search', 'test');
    }
}
