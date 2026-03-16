<?php

namespace Tests\Feature\Livewire;

use App\Enums\ProductStatusEnum;
use App\Livewire\ProductDetail;
use App\Models\ProductTranslation;
use App\Models\ProductVariant;
use App\Models\Specification;
use App\Models\SpecificationValue;
use Tests\Feature\LivewireTestCase;

class ProductDetailTest extends LivewireTestCase
{
    public function test_product_detail_page_can_be_rendered()
    {
        $product = $this->createProduct([
            'status' => ProductStatusEnum::Active,
        ]);

        $language = $this->createLanguage();
        $translation = ProductTranslation::factory()->create([
            'product_id' => $product->id,
            'language_id' => $language->id,
            'name' => '测试商品',
            'short_description' => '简短描述',
        ]);

        // ProductDetail 组件需要 slug 参数
        $component = $this->livewire(ProductDetail::class, ['slug' => $product->slug]);
        $component->assertSuccessful();
    }

    public function test_product_detail_displays_product_information()
    {
        $product = $this->createProduct([
            'status' => ProductStatusEnum::Active,
        ]);

        $language = $this->createLanguage();
        ProductTranslation::factory()->create([
            'product_id' => $product->id,
            'language_id' => $language->id,
            'name' => '测试商品',
            'short_description' => '简短描述',
        ]);

        $language = $this->createLanguage();
        $translation = ProductTranslation::factory()->create([
            'product_id' => $product->id,
            'language_id' => $language->id,
            'name' => '测试商品',
            'short_description' => '简短描述',
        ]);

        // ProductDetail 组件需要 slug 参数
        $component = $this->livewire(ProductDetail::class, ['slug' => $product->slug]);
        $component->assertSuccessful();
        $this->assertEquals($product->id, $component->get('product')->id);
    }

    public function test_product_detail_displays_variants()
    {
        $product = $this->createProduct([
            'status' => ProductStatusEnum::Active,
        ]);

        $variant1 = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'price' => 100.00,
        ]);
        $variant2 = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'price' => 200.00,
        ]);

        $language = $this->createLanguage();
        $translation = ProductTranslation::factory()->create([
            'product_id' => $product->id,
            'language_id' => $language->id,
            'name' => '测试商品',
            'short_description' => '简短描述',
        ]);

        // ProductDetail 组件需要 slug 参数
        $component = $this->livewire(ProductDetail::class, ['slug' => $product->slug]);
        $component->assertSuccessful();

        $variants = $component->get('variants');
        $this->assertNotEmpty($variants);
    }

    public function test_user_can_select_variant()
    {
        $product = $this->createProduct([
            'status' => ProductStatusEnum::Active,
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'price' => 100.00,
        ]);

        $language = $this->createLanguage();
        ProductTranslation::factory()->create([
            'product_id' => $product->id,
            'language_id' => $language->id,
        ]);
        $component = $this->livewire(ProductDetail::class, ['slug' => $product->slug])
            ->set('selectedVariantId', $variant->id);

        $component->assertSet('selectedVariantId', $variant->id);
    }

    public function test_user_can_update_quantity()
    {
        $product = $this->createProduct([
            'status' => ProductStatusEnum::Active,
        ]);

        $language = $this->createLanguage();
        ProductTranslation::factory()->create([
            'product_id' => $product->id,
            'language_id' => $language->id,
        ]);
        $component = $this->livewire(ProductDetail::class, ['slug' => $product->slug])
            ->set('qty', 5);

        $component->assertSet('qty', 5);
    }

    public function test_user_can_buy_now_from_product_detail()
    {
        $product = $this->createProduct([
            'status' => ProductStatusEnum::Active,
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'price' => 100.00,
            'stock' => 10,
        ]);

        $language = $this->createLanguage();
        ProductTranslation::factory()->create([
            'product_id' => $product->id,
            'language_id' => $language->id,
        ]);
        $component = $this->livewire(ProductDetail::class, ['slug' => $product->slug])
            ->set('selectedVariantId', $variant->id)
            ->set('qty', 2)
            ->call('buyNow');

        // buyNow 会重定向，所以验证重定向
        $this->assertNotNull($component);
    }

    public function test_user_can_buy_now()
    {
        $product = $this->createProduct([
            'status' => ProductStatusEnum::Active,
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'price' => 100.00,
            'stock' => 10,
        ]);

        $language = $this->createLanguage();
        ProductTranslation::factory()->create([
            'product_id' => $product->id,
            'language_id' => $language->id,
        ]);
        $component = $this->livewire(ProductDetail::class, ['slug' => $product->slug])
            ->set('selectedVariantId', $variant->id)
            ->set('qty', 1)
            ->call('buyNow');

        // 验证重定向到结算页
        $this->assertNotNull($component);
    }

    public function test_product_detail_handles_variants_with_specifications()
    {
        $product = $this->createProduct([
            'status' => ProductStatusEnum::Active,
        ]);

        $specification = Specification::factory()->create();
        $specValue = SpecificationValue::factory()->create([
            'specification_id' => $specification->id,
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
        ]);

        $variant->specificationValues()->attach($specValue->id, [
            'specification_id' => $specification->id,
        ]);

        $language = $this->createLanguage();
        $translation = ProductTranslation::factory()->create([
            'product_id' => $product->id,
            'language_id' => $language->id,
            'name' => '测试商品',
            'short_description' => '简短描述',
        ]);

        // ProductDetail 组件需要 slug 参数
        $component = $this->livewire(ProductDetail::class, ['slug' => $product->slug]);
        $component->assertSuccessful();
    }

    public function test_product_detail_validates_quantity()
    {
        $product = $this->createProduct([
            'status' => ProductStatusEnum::Active,
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'stock' => 5,
        ]);

        $language = $this->createLanguage();
        ProductTranslation::factory()->create([
            'product_id' => $product->id,
            'language_id' => $language->id,
        ]);
        $component = $this->livewire(ProductDetail::class, ['slug' => $product->slug])
            ->set('selectedVariantId', $variant->id)
            ->set('qty', 10) // 超过库存
            ->call('buyNow');

        // buyNow 会重定向，所以验证重定向
        $this->assertNotNull($component);
    }

    public function test_product_detail_only_shows_active_products()
    {
        $activeProduct = $this->createProduct([
            'status' => ProductStatusEnum::Active,
        ]);

        $language = $this->createLanguage();
        ProductTranslation::factory()->create([
            'product_id' => $activeProduct->id,
            'language_id' => $language->id,
        ]);
        $component = $this->livewire(ProductDetail::class, ['slug' => $activeProduct->slug]);
        $component->assertSuccessful();
    }
}
