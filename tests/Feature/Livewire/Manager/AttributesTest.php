<?php

namespace Tests\Feature\Livewire\Manager;

use App\Enums\TranslationStatusEnum;
use App\Livewire\Manager\Attributes;
use App\Models\Attribute;
use App\Models\AttributeTranslation;
use Tests\Feature\LivewireTestCase;

class AttributesTest extends LivewireTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->createManager(), 'manager');
    }

    public function test_attributes_page_can_be_rendered()
    {
        $component = $this->livewire(Attributes::class);
        $component->assertSuccessful();
    }

    public function test_attributes_list_displays_attributes()
    {
        $attribute = Attribute::factory()->create();

        $component = $this->livewire(Attributes::class);

        $attributes = $component->get('attributeList');
        $attributeIds = $attributes->pluck('id')->toArray();
        $this->assertContains($attribute->id, $attributeIds);
    }

    public function test_can_search_attributes_by_name()
    {
        $attribute1 = Attribute::factory()->create();
        $attribute2 = Attribute::factory()->create();

        AttributeTranslation::factory()->create([
            'attribute_id' => $attribute1->id,
            'name' => '测试属性1',
        ]);
        AttributeTranslation::factory()->create([
            'attribute_id' => $attribute2->id,
            'name' => '其他属性',
        ]);

        $component = $this->livewire(Attributes::class)
            ->set('search', '测试');

        $attributes = $component->get('attributeList');
        $attributeIds = $attributes->pluck('id')->toArray();
        $this->assertContains($attribute1->id, $attributeIds);
        $this->assertNotContains($attribute2->id, $attributeIds);
    }

    public function test_can_filter_attributes_by_filterable()
    {
        $filterableAttribute = Attribute::factory()->create(['is_filterable' => true]);
        $nonFilterableAttribute = Attribute::factory()->create(['is_filterable' => false]);

        $component = $this->livewire(Attributes::class)
            ->set('filterIsFilterable', '1');

        $attributes = $component->get('attributeList');
        $attributeIds = $attributes->pluck('id')->toArray();
        $this->assertContains($filterableAttribute->id, $attributeIds);
        $this->assertNotContains($nonFilterableAttribute->id, $attributeIds);
    }

    public function test_can_toggle_filterable()
    {
        $attribute = Attribute::factory()->create(['is_filterable' => false]);

        $component = $this->livewire(Attributes::class)
            ->call('toggleFilterable', $attribute->id);

        $attribute->refresh();
        $this->assertTrue($attribute->is_filterable);
    }

    public function test_can_delete_attribute()
    {
        $attribute = Attribute::factory()->create();

        $component = $this->livewire(Attributes::class)
            ->call('deleteAttribute', $attribute->id);

        $this->assertDatabaseMissing('attributes', ['id' => $attribute->id]);
    }

    public function test_reset_filters_clears_all_filters()
    {
        $component = $this->livewire(Attributes::class)
            ->set('search', 'test')
            ->set('filterIsFilterable', '1')
            ->set('filterTranslationStatus', [TranslationStatusEnum::Translated->value])
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('filterIsFilterable', '')
            ->assertSet('filterTranslationStatus', []);
    }
}
