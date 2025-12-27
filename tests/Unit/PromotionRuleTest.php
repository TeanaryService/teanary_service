<?php

namespace Tests\Unit;

use App\Enums\PromotionConditionTypeEnum;
use App\Enums\PromotionDiscountTypeEnum;
use App\Models\PromotionRule;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromotionRuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_promotion_rule_can_be_created_using_factory()
    {
        $rule = PromotionRule::factory()->create();

        $this->assertNotNull($rule);
        $this->assertInstanceOf(PromotionRule::class, $rule);
    }

    public function test_condition_type_attribute_casting()
    {
        $rule = PromotionRule::factory()->create([
            'condition_type' => PromotionConditionTypeEnum::OrderTotalMin,
        ]);

        $this->assertInstanceOf(PromotionConditionTypeEnum::class, $rule->condition_type);
        $this->assertEquals(PromotionConditionTypeEnum::OrderTotalMin, $rule->condition_type);
    }

    public function test_discount_type_attribute_casting()
    {
        $rule = PromotionRule::factory()->create([
            'discount_type' => PromotionDiscountTypeEnum::Fixed,
        ]);

        $this->assertInstanceOf(PromotionDiscountTypeEnum::class, $rule->discount_type);
        $this->assertEquals(PromotionDiscountTypeEnum::Fixed, $rule->discount_type);
    }

    public function test_promotion_relationship()
    {
        $rule = new PromotionRule;
        $relation = $rule->promotion();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals('promotion_id', $relation->getForeignKeyName());
    }
}
