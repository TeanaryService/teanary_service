<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\PromotionService;
use App\Models\ProductVariant;
use App\Models\Order;
use App\Models\Promotion;
use App\Models\PromotionRule;
use App\Models\User;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

class PromotionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PromotionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PromotionService();
        Cache::flush();
    }

    public function test_calculate_variant_price_returns_base_price_when_no_promotions(): void
    {
        $variant = ProductVariant::factory()->create(['price' => 100.0]);

        $result = $this->service->calculateVariantPrice($variant, 1, null);

        $this->assertEquals(100.0, $result['final_price']);
        $this->assertNull($result['promotion']);
    }

    public function test_calculate_variant_price_applies_fixed_discount(): void
    {
        $product = \App\Models\Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 100.0]);
        $promotion = Promotion::factory()->create(['active' => true]);
        $rule = PromotionRule::factory()->create([
            'promotion_id' => $promotion->id,
            'condition_type' => \App\Enums\PromotionConditionTypeEnum::OrderQtyMin,
            'condition_value' => 1,
            'discount_type' => \App\Enums\PromotionDiscountTypeEnum::Fixed,
            'discount_value' => 10.0,
        ]);
        $promotion->productVariants()->attach($variant->id, ['product_id' => $product->id]);

        $result = $this->service->calculateVariantPrice($variant, 1, null);

        $this->assertEquals(90.0, $result['final_price']);
        $this->assertNotNull($result['promotion']);
    }

    public function test_calculate_variant_price_applies_percentage_discount(): void
    {
        $product = \App\Models\Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 100.0]);
        $promotion = Promotion::factory()->create(['active' => true]);
        $rule = PromotionRule::factory()->create([
            'promotion_id' => $promotion->id,
            'condition_type' => \App\Enums\PromotionConditionTypeEnum::OrderQtyMin,
            'condition_value' => 1,
            'discount_type' => \App\Enums\PromotionDiscountTypeEnum::Percentage,
            'discount_value' => 20.0, // 20%
        ]);
        $promotion->productVariants()->attach($variant->id, ['product_id' => $product->id]);

        $result = $this->service->calculateVariantPrice($variant, 1, null);

        $this->assertEquals(80.0, $result['final_price']);
        $this->assertNotNull($result['promotion']);
    }

    public function test_calculate_variant_price_respects_minimum_quantity_condition(): void
    {
        $product = \App\Models\Product::factory()->create();
        $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'price' => 100.0]);
        $promotion = Promotion::factory()->create(['active' => true]);
        $rule = PromotionRule::factory()->create([
            'promotion_id' => $promotion->id,
            'condition_type' => \App\Enums\PromotionConditionTypeEnum::OrderQtyMin,
            'condition_value' => 5, // 需要至少5件
            'discount_type' => \App\Enums\PromotionDiscountTypeEnum::Fixed,
            'discount_value' => 10.0,
        ]);
        $promotion->productVariants()->attach($variant->id, ['product_id' => $product->id]);

        $result = $this->service->calculateVariantPrice($variant, 1, null); // 只有1件

        $this->assertEquals(100.0, $result['final_price']); // 不应用折扣
        $this->assertNull($result['promotion']);
    }

    public function test_calculate_order_total_returns_base_total_when_no_promotions(): void
    {
        $order = Order::factory()->create();
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'qty' => 2,
            'price' => 50.0,
        ]);
        
        // 重新加载 orderItems 关系
        $order->load('orderItems');

        $result = $this->service->calculateOrderTotal($order);

        $this->assertEquals(100.0, $result['final_total']);
        $this->assertNull($result['promotion']);
    }

    public function test_calculate_order_total_applies_promotion(): void
    {
        $order = Order::factory()->create();
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'qty' => 2,
            'price' => 50.0,
        ]);
        
        // 重新加载 orderItems 关系
        $order->load('orderItems');

        $promotion = Promotion::factory()->create(['active' => true]);
        $rule = PromotionRule::factory()->create([
            'promotion_id' => $promotion->id,
            'condition_type' => \App\Enums\PromotionConditionTypeEnum::OrderTotalMin,
            'condition_value' => 50.0,
            'discount_type' => \App\Enums\PromotionDiscountTypeEnum::Fixed,
            'discount_value' => 20.0,
        ]);

        $result = $this->service->calculateOrderTotal($order);

        $this->assertEquals(80.0, $result['final_total']);
        $this->assertNotNull($result['promotion']);
    }

    public function test_get_available_promotions_filters_by_active_status(): void
    {
        Promotion::factory()->create(['active' => true]);
        Promotion::factory()->create(['active' => false]);

        $promotions = $this->service->getAvailablePromotions();

        $this->assertCount(1, $promotions);
        $this->assertTrue($promotions->first()['id'] > 0);
    }

    public function test_get_available_promotions_filters_by_date_range(): void
    {
        Promotion::factory()->create([
            'active' => true,
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDays(2),
        ]);
        Promotion::factory()->create([
            'active' => true,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);

        $promotions = $this->service->getAvailablePromotions();

        $this->assertCount(1, $promotions);
    }

    public function test_clear_promotion_cache(): void
    {
        Cache::shouldReceive('forget')
            ->once()
            ->with('promotions.all');

        PromotionService::clearPromotionCache();
    }
}

