<?php

namespace Tests\Feature\Livewire\Manager;

use App\Enums\TranslationStatusEnum;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\AttributeValueTranslation;
use Tests\Feature\LivewireTestCase;

class AttributeValuesTest extends LivewireTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->createManager(), 'manager');
        $this->attribute = Attribute::factory()->create();
    }

    public function test_attribute_values_page_can_be_rendered()
    {
        $component = $this->livewire(\App\Livewire\Manager\AttributeValues::class);
        $component->assertSuccessful();
    }

    public function test_attribute_values_list_displays_values()
    {
        $attributeValue = AttributeValue::factory()->create([
            'attribute_id' => $this->attribute->id,
        ]);

        $component = $this->livewire(\App\Livewire\Manager\AttributeValues::class);

        $values = $component->get('attributeValues');
        $valueIds = $values->pluck('id')->toArray();
        $this->assertContains($attributeValue->id, $valueIds);
    }

    public function test_can_search_attribute_values_by_name()
    {
        $value1 = AttributeValue::factory()->create(['attribute_id' => $this->attribute->id]);
        $value2 = AttributeValue::factory()->create(['attribute_id' => $this->attribute->id]);

        AttributeValueTranslation::factory()->create([
            'attribute_value_id' => $value1->id,
            'name' => '测试值1',
        ]);
        AttributeValueTranslation::factory()->create([
            'attribute_value_id' => $value2->id,
            'name' => '其他值',
        ]);

        $component = $this->livewire(\App\Livewire\Manager\AttributeValues::class)
            ->set('search', '测试');

        $values = $component->get('attributeValues');
        $valueIds = $values->pluck('id')->toArray();
        $this->assertContains($value1->id, $valueIds);
        $this->assertNotContains($value2->id, $valueIds);
    }

    public function test_can_filter_attribute_values_by_attribute()
    {
        $attribute2 = Attribute::factory()->create();
        $value1 = AttributeValue::factory()->create(['attribute_id' => $this->attribute->id]);
        $value2 = AttributeValue::factory()->create(['attribute_id' => $attribute2->id]);

        $component = $this->livewire(\App\Livewire\Manager\AttributeValues::class)
            ->set('filterAttributeId', $this->attribute->id);

        $values = $component->get('attributeValues');
        $valueIds = $values->pluck('id')->toArray();
        $this->assertContains($value1->id, $valueIds);
        $this->assertNotContains($value2->id, $valueIds);
    }

    public function test_can_delete_attribute_value()
    {
        $value = AttributeValue::factory()->create(['attribute_id' => $this->attribute->id]);

        $component = $this->livewire(\App\Livewire\Manager\AttributeValues::class)
            ->call('deleteAttributeValue', $value->id);

        $this->assertDatabaseMissing('attribute_values', ['id' => $value->id]);
    }

    public function test_reset_filters_clears_all_filters()
    {
        $component = $this->livewire(\App\Livewire\Manager\AttributeValues::class)
            ->set('search', 'test')
            ->set('filterAttributeId', 1)
            ->set('filterTranslationStatus', [TranslationStatusEnum::Translated->value])
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('filterAttributeId', null)
            ->assertSet('filterTranslationStatus', []);
    }
}
