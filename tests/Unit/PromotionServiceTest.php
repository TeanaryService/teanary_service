<?php

namespace Tests\Unit;

use App\Enums\PromotionConditionTypeEnum;
use App\Enums\PromotionDiscountTypeEnum;
use App\Models\ProductVariant;
use App\Models\Promotion;
use App\Models\PromotionRule;
use App\Services\PromotionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PromotionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PromotionService $promotionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->promotionService = new PromotionService();
        Cache::flush();
    }

    #[Test]
    public function it_calculates_fixed_discount_correctly()
    {
        $variant = ProductVariant::factory()->create(['price' => 100]);
        $promotion = Promotion::factory()->create(['active' => true]);
        
        PromotionRule::factory()->create([
            'promotion_id' => $promotion->id,
            'condition_type' => PromotionConditionTypeEnum::OrderTotalMin,
            'condition_value' => 50,
            'discount_type' => PromotionDiscountTypeEnum::Fixed,
            'discount_value' => 20,
        ]);

        $promotion->productVariants()->attach($variant->id);
        PromotionService::clearPromotionCache();

        $result = $this->promotionService->calculateVariantPrice($variant, 1);

        $this->assertEquals(80, $result['final_price']);
        $this->assertNotNull($result['promotion']);
        $this->assertEquals(20, $result['promotion']['discount']);
    }

    #[Test]
    public function it_calculates_percentage_discount_correctly()
    {
        $variant = ProductVariant::factory()->create(['price' => 100]);
        $promotion = Promotion::factory()->create(['active' => true]);
        
        PromotionRule::factory()->create([
            'promotion_id' => $promotion->id,
            'condition_type' => PromotionConditionTypeEnum::OrderTotalMin,
            'condition_value' => 50,
            'discount_type' => PromotionDiscountTypeEnum::Percentage,
            'discount_value' => 15, // 15%
        ]);

        $promotion->productVariants()->attach($variant->id);
        PromotionService::clearPromotionCache();

        $result = $this->promotionService->calculateVariantPrice($variant, 1);

        $this->assertEquals(85, $result['final_price']);
        $this->assertNotNull($result['promotion']);
        $this->assertEquals(15, $result['promotion']['discount']);
    }

    #[Test]
    public function it_does_not_apply_promotion_when_condition_not_met()
    {
        $variant = ProductVariant::factory()->create(['price' => 30]);
        $promotion = Promotion::factory()->create(['active' => true]);
        
        PromotionRule::factory()->create([
            'promotion_id' => $promotion->id,
            'condition_type' => PromotionConditionTypeEnum::OrderTotalMin,
            'condition_value' => 50, // 需要至少50
            'discount_type' => PromotionDiscountTypeEnum::Fixed,
            'discount_value' => 20,
        ]);

        $promotion->productVariants()->attach($variant->id);
        PromotionService::clearPromotionCache();

        $result = $this->promotionService->calculateVariantPrice($variant, 1);

        $this->assertEquals(30, $result['final_price']);
        $this->assertNull($result['promotion']);
    }

    #[Test]
    public function it_applies_promotion_based_on_quantity_condition()
    {
        $variant = ProductVariant::factory()->create(['price' => 10]);
        $promotion = Promotion::factory()->create(['active' => true]);
        
        PromotionRule::factory()->create([
            'promotion_id' => $promotion->id,
            'condition_type' => PromotionConditionTypeEnum::OrderQtyMin,
            'condition_value' => 3, // 至少3件
            'discount_type' => PromotionDiscountTypeEnum::Percentage,
            'discount_value' => 10, // 10%
        ]);

        $promotion->productVariants()->attach($variant->id);
        PromotionService::clearPromotionCache();

        $result = $this->promotionService->calculateVariantPrice($variant, 3);

        $this->assertEquals(9, $result['final_price']);
        $this->assertNotNull($result['promotion']);
    }

    #[Test]
    public function it_returns_base_price_when_no_promotion_applies()
    {
        $variant = ProductVariant::factory()->create(['price' => 100]);
        PromotionService::clearPromotionCache();

        $result = $this->promotionService->calculateVariantPrice($variant, 1);

        $this->assertEquals(100, $result['final_price']);
        $this->assertNull($result['promotion']);
    }

    #[Test]
    public function it_clears_promotion_cache()
    {
        Cache::put('promotions.all', collect([]), now()->addDay());

        PromotionService::clearPromotionCache();

        $this->assertFalse(Cache::has('promotions.all'));
    }

    #[Test]
    public function it_returns_cached_promotions()
    {
        $promotion = Promotion::factory()->create(['active' => true]);
        PromotionService::clearPromotionCache();

        $cached = PromotionService::getAllPromotionsCached();

        $this->assertTrue($cached->contains('id', $promotion->id));
        $this->assertTrue(Cache::has('promotions.all'));
    }
}

