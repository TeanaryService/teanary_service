<?php

namespace Tests\Feature\Livewire\Manager;

use App\Enums\PromotionTypeEnum;
use App\Enums\TranslationStatusEnum;
use App\Livewire\Manager\Promotions;
use App\Models\Promotion;
use App\Models\PromotionTranslation;
use Tests\Feature\LivewireTestCase;

class PromotionsTest extends LivewireTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->createManager(), 'manager');
    }

    public function test_promotions_page_can_be_rendered()
    {
        $component = $this->livewire(Promotions::class);
        $component->assertSuccessful();
    }

    public function test_promotions_list_displays_promotions()
    {
        $promotion = Promotion::factory()->create();

        $component = $this->livewire(Promotions::class);

        $promotions = $component->get('promotions');
        $promotionIds = $promotions->pluck('id')->toArray();
        $this->assertContains($promotion->id, $promotionIds);
    }

    public function test_can_search_promotions_by_name()
    {
        $promotion1 = Promotion::factory()->create();
        $promotion2 = Promotion::factory()->create();

        $language = $this->createLanguage();
        PromotionTranslation::factory()->create([
            'promotion_id' => $promotion1->id,
            'language_id' => $language->id,
            'name' => '测试促销1',
        ]);
        PromotionTranslation::factory()->create([
            'promotion_id' => $promotion2->id,
            'language_id' => $language->id,
            'name' => '其他促销',
        ]);

        $component = $this->livewire(Promotions::class)
            ->set('search', '测试');

        $promotions = $component->get('promotions');
        $promotionIds = $promotions->pluck('id')->toArray();
        $this->assertContains($promotion1->id, $promotionIds);
        $this->assertNotContains($promotion2->id, $promotionIds);
    }

    public function test_can_filter_promotions_by_type()
    {
        $promotion1 = Promotion::factory()->create(['type' => PromotionTypeEnum::Coupon]);
        $promotion2 = Promotion::factory()->create(['type' => PromotionTypeEnum::Automatic]);

        $component = $this->livewire(Promotions::class)
            ->set('filterTypes', [PromotionTypeEnum::Coupon->value]);

        $promotions = $component->get('promotions');
        $promotionIds = $promotions->pluck('id')->toArray();
        $this->assertContains($promotion1->id, $promotionIds);
        $this->assertNotContains($promotion2->id, $promotionIds);
    }

    public function test_can_filter_promotions_by_active_status()
    {
        $activePromotion = Promotion::factory()->create(['active' => true]);
        $inactivePromotion = Promotion::factory()->create(['active' => false]);

        $component = $this->livewire(Promotions::class)
            ->set('filterActive', '1');

        $promotions = $component->get('promotions');
        $promotionIds = $promotions->pluck('id')->toArray();
        $this->assertContains($activePromotion->id, $promotionIds);
        $this->assertNotContains($inactivePromotion->id, $promotionIds);
    }

    public function test_can_filter_promotions_by_translation_status()
    {
        $completePromotion = Promotion::factory()->create([
            'translation_status' => TranslationStatusEnum::Translated,
        ]);
        $incompletePromotion = Promotion::factory()->create([
            'translation_status' => TranslationStatusEnum::NotTranslated,
        ]);

        $component = $this->livewire(Promotions::class)
            ->set('filterTranslationStatus', [TranslationStatusEnum::Translated->value]);

        $promotions = $component->get('promotions');
        $promotionIds = $promotions->pluck('id')->toArray();
        $this->assertContains($completePromotion->id, $promotionIds);
        $this->assertNotContains($incompletePromotion->id, $promotionIds);
    }

    public function test_can_delete_promotion()
    {
        $promotion = Promotion::factory()->create();

        $component = $this->livewire(Promotions::class)
            ->call('deletePromotion', $promotion->id);

        $this->assertDatabaseMissing('promotions', ['id' => $promotion->id]);
    }

    public function test_reset_filters_clears_all_filters()
    {
        $component = $this->livewire(Promotions::class)
            ->set('search', 'test')
            ->set('filterTypes', [PromotionTypeEnum::Coupon->value])
            ->set('filterActive', '1')
            ->set('filterTranslationStatus', [TranslationStatusEnum::Translated->value])
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('filterTypes', [])
            ->assertSet('filterActive', '')
            ->assertSet('filterTranslationStatus', []);
    }
}
