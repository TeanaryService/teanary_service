<?php

namespace Tests\Feature\Livewire\Manager;

use App\Models\ProductVariant;
use Tests\Feature\LivewireTestCase;

class ProductVariantsTest extends LivewireTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->createManager(), 'manager');
        $this->product = $this->createProduct();
    }

    public function test_product_variants_page_can_be_rendered()
    {
        $component = $this->livewire(\App\Livewire\Manager\ProductVariants::class, ['productId' => $this->product->id]);
        $component->assertSuccessful();
    }

    public function test_product_variants_list_displays_variants()
    {
        $variant = ProductVariant::factory()->create([
            'product_id' => $this->product->id,
        ]);

        $component = $this->livewire(\App\Livewire\Manager\ProductVariants::class, ['productId' => $this->product->id]);

        $variants = $component->get('variants');
        $variantIds = $variants->pluck('id')->toArray();
        $this->assertContains($variant->id, $variantIds);
    }

    public function test_product_variants_only_shows_variants_for_specific_product()
    {
        $product2 = $this->createProduct();
        $variant1 = ProductVariant::factory()->create(['product_id' => $this->product->id]);
        $variant2 = ProductVariant::factory()->create(['product_id' => $product2->id]);

        $component = $this->livewire(\App\Livewire\Manager\ProductVariants::class, ['productId' => $this->product->id]);

        $variants = $component->get('variants');
        $variantIds = $variants->pluck('id')->toArray();
        $this->assertContains($variant1->id, $variantIds);
        $this->assertNotContains($variant2->id, $variantIds);
    }

    public function test_can_search_variants_by_sku()
    {
        $variant1 = ProductVariant::factory()->create([
            'product_id' => $this->product->id,
            'sku' => 'SKU-001',
        ]);
        $variant2 = ProductVariant::factory()->create([
            'product_id' => $this->product->id,
            'sku' => 'SKU-002',
        ]);

        $component = $this->livewire(\App\Livewire\Manager\ProductVariants::class, ['productId' => $this->product->id])
            ->set('search', 'SKU-001');

        $variants = $component->get('variants');
        $variantIds = $variants->pluck('id')->toArray();
        $this->assertContains($variant1->id, $variantIds);
        $this->assertNotContains($variant2->id, $variantIds);
    }

    public function test_can_delete_variant()
    {
        $variant = ProductVariant::factory()->create([
            'product_id' => $this->product->id,
        ]);

        $component = $this->livewire(\App\Livewire\Manager\ProductVariants::class, ['productId' => $this->product->id])
            ->call('deleteVariant', $variant->id);

        $this->assertDatabaseMissing('product_variants', ['id' => $variant->id]);
    }
}
