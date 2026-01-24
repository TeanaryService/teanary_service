<?php

namespace Tests\Unit;

use App\Enums\PromotionConditionTypeEnum;
use App\Enums\PromotionDiscountTypeEnum;
use App\Enums\PromotionTypeEnum;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Promotion;
use App\Models\PromotionRule;
use App\Services\PromotionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PromotionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PromotionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PromotionService;
        PromotionService::clearPromotionCache();
    }

    public function test_get_all_promotions_cached()
    {
        $promotion = Promotion::factory()->create();
        PromotionService::clearPromotionCache();

        $promotions = PromotionService::getAllPromotionsCached();

        $this->assertCount(1, $promotions);
        $this->assertTrue(Cache::has('promotions.all'));
    }

    public function test_clear_promotion_cache()
    {
        Cache::put('promotions.all', collect([]));
        $this->assertTrue(Cache::has('promotions.all'));

        PromotionService::clearPromotionCache();

        $this->assertFalse(Cache::has('promotions.all'));
    }

    public function test_calculate_variant_price_without_promotion()
    {
        $variant = ProductVariant::factory()->create(['price' => 100]);

        $result = $this->service->calculateVariantPrice($variant, 1);

        $this->assertEquals(100, $result['final_price']);
        $this->assertNull($result['promotion']);
    }

    public function test_calculate_variant_price_with_fixed_discount()
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 100]);
        $promotion = Promotion::factory()->create([
            'type' => PromotionTypeEnum::Automatic,
            'active' => true,
        ]);
        $promotion->productVariants()->attach($variant->id, ['product_id' => $product->id]);
        $rule = PromotionRule::factory()->create([
            'promotion_id' => $promotion->id,
            'condition_type' => PromotionConditionTypeEnum::OrderTotalMin,
            'condition_value' => 50,
            'discount_type' => PromotionDiscountTypeEnum::Fixed,
            'discount_value' => 20,
        ]);
        PromotionService::clearPromotionCache();

        $result = $this->service->calculateVariantPrice($variant, 1);

        $this->assertEquals(80, $result['final_price']);
        $this->assertNotNull($result['promotion']);
        $this->assertEquals(20, $result['promotion']['discount']);
    }

    public function test_calculate_variant_price_with_percentage_discount()
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 100]);
        $promotion = Promotion::factory()->create([
            'type' => PromotionTypeEnum::Automatic,
            'active' => true,
        ]);
        $promotion->productVariants()->attach($variant->id, ['product_id' => $product->id]);
        $rule = PromotionRule::factory()->create([
            'promotion_id' => $promotion->id,
            'condition_type' => PromotionConditionTypeEnum::OrderTotalMin,
            'condition_value' => 50,
            'discount_type' => PromotionDiscountTypeEnum::Percentage,
            'discount_value' => 10,
        ]);
        PromotionService::clearPromotionCache();

        $result = $this->service->calculateVariantPrice($variant, 1);

        $this->assertEquals(90, $result['final_price']);
        $this->assertNotNull($result['promotion']);
        $this->assertEquals(10, $result['promotion']['discount']);
    }

    public function test_get_available_promotions()
    {
        $promotion = Promotion::factory()->create([
            'active' => true,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);
        PromotionService::clearPromotionCache();

        $promotions = $this->service->getAvailablePromotions();

        $this->assertGreaterThan(0, $promotions->count());
    }

    public function test_get_available_promotions_for_variant()
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);
        $promotion = Promotion::factory()->create([
            'active' => true,
        ]);
        $promotion->productVariants()->attach($variant->id, ['product_id' => $product->id]);
        PromotionService::clearPromotionCache();

        $promotions = $this->service->getAvailablePromotionsForVariant($variant);

        $this->assertGreaterThan(0, $promotions->count());
    }
}
