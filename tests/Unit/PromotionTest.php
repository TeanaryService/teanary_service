<?php

namespace Tests\Unit;

use App\Enums\PromotionTypeEnum;
use App\Models\Promotion;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromotionTest extends TestCase
{
    use RefreshDatabase;

    public function test_promotion_can_be_created_using_factory()
    {
        $promotion = Promotion::factory()->create();

        $this->assertNotNull($promotion);
        $this->assertInstanceOf(Promotion::class, $promotion);
    }

    public function test_type_attribute_casting()
    {
        $promotion = Promotion::factory()->create([
            'type' => PromotionTypeEnum::Coupon,
        ]);

        $this->assertInstanceOf(PromotionTypeEnum::class, $promotion->type);
        $this->assertEquals(PromotionTypeEnum::Coupon, $promotion->type);
    }

    public function test_active_attribute_casting()
    {
        $promotion = Promotion::factory()->create([
            'active' => true,
        ]);

        $this->assertIsBool($promotion->active);
        $this->assertTrue($promotion->active);
    }

    public function test_product_variants_relationship()
    {
        $promotion = new Promotion;
        $relation = $promotion->productVariants();

        $this->assertInstanceOf(BelongsToMany::class, $relation);
        $this->assertEquals('promotion_product_variant', $relation->getTable());
    }

    public function test_promotion_rules_relationship()
    {
        $promotion = new Promotion;
        $relation = $promotion->promotionRules();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertEquals('promotion_id', $relation->getForeignKeyName());
    }

    public function test_promotion_translations_relationship()
    {
        $promotion = new Promotion;
        $relation = $promotion->promotionTranslations();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertEquals('promotion_id', $relation->getForeignKeyName());
    }

    public function test_user_groups_relationship()
    {
        $promotion = new Promotion;
        $relation = $promotion->userGroups();

        $this->assertInstanceOf(BelongsToMany::class, $relation);
    }
}
