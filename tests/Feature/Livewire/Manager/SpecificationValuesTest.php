<?php

namespace Tests\Feature\Livewire\Manager;

use App\Enums\TranslationStatusEnum;
use App\Models\Specification;
use App\Models\SpecificationValue;
use App\Models\SpecificationValueTranslation;
use Tests\Feature\LivewireTestCase;

class SpecificationValuesTest extends LivewireTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->createManager(), 'manager');
        $this->specification = Specification::factory()->create();
    }

    public function test_specification_values_page_can_be_rendered()
    {
        $component = $this->livewire(\App\Livewire\Manager\SpecificationValues::class);
        $component->assertSuccessful();
    }

    public function test_specification_values_list_displays_values()
    {
        $value = SpecificationValue::factory()->create([
            'specification_id' => $this->specification->id,
        ]);

        $component = $this->livewire(\App\Livewire\Manager\SpecificationValues::class);

        $values = $component->get('specificationValues');
        $valueIds = $values->pluck('id')->toArray();
        $this->assertContains($value->id, $valueIds);
    }

    public function test_can_search_specification_values_by_name()
    {
        $value1 = SpecificationValue::factory()->create(['specification_id' => $this->specification->id]);
        $value2 = SpecificationValue::factory()->create(['specification_id' => $this->specification->id]);

        SpecificationValueTranslation::factory()->create([
            'specification_value_id' => $value1->id,
            'name' => '测试值1',
        ]);
        SpecificationValueTranslation::factory()->create([
            'specification_value_id' => $value2->id,
            'name' => '其他值',
        ]);

        $component = $this->livewire(\App\Livewire\Manager\SpecificationValues::class)
            ->set('search', '测试');

        $values = $component->get('specificationValues');
        $valueIds = $values->pluck('id')->toArray();
        $this->assertContains($value1->id, $valueIds);
        $this->assertNotContains($value2->id, $valueIds);
    }

    public function test_can_filter_specification_values_by_specification()
    {
        $specification2 = Specification::factory()->create();
        $value1 = SpecificationValue::factory()->create(['specification_id' => $this->specification->id]);
        $value2 = SpecificationValue::factory()->create(['specification_id' => $specification2->id]);

        $component = $this->livewire(\App\Livewire\Manager\SpecificationValues::class)
            ->set('filterSpecificationId', $this->specification->id);

        $values = $component->get('specificationValues');
        $valueIds = $values->pluck('id')->toArray();
        $this->assertContains($value1->id, $valueIds);
        $this->assertNotContains($value2->id, $valueIds);
    }

    public function test_can_delete_specification_value()
    {
        $value = SpecificationValue::factory()->create(['specification_id' => $this->specification->id]);

        $component = $this->livewire(\App\Livewire\Manager\SpecificationValues::class)
            ->call('deleteSpecificationValue', $value->id);

        $this->assertDatabaseMissing('specification_values', ['id' => $value->id]);
    }

    public function test_reset_filters_clears_all_filters()
    {
        $component = $this->livewire(\App\Livewire\Manager\SpecificationValues::class)
            ->set('search', 'test')
            ->set('filterSpecificationId', 1)
            ->set('filterTranslationStatus', [TranslationStatusEnum::Translated->value])
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('filterSpecificationId', null)
            ->assertSet('filterTranslationStatus', []);
    }
}
