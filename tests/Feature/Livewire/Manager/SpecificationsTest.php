<?php

namespace Tests\Feature\Livewire\Manager;

use App\Enums\TranslationStatusEnum;
use App\Livewire\Manager\Specifications;
use App\Models\Specification;
use App\Models\SpecificationTranslation;
use Tests\Feature\LivewireTestCase;

class SpecificationsTest extends LivewireTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->createManager(), 'manager');
    }

    public function test_specifications_page_can_be_rendered()
    {
        $component = $this->livewire(Specifications::class);
        $component->assertSuccessful();
    }

    public function test_specifications_list_displays_specifications()
    {
        $specification = Specification::factory()->create();

        $component = $this->livewire(Specifications::class);

        $specifications = $component->get('specifications');
        $specificationIds = $specifications->pluck('id')->toArray();
        $this->assertContains($specification->id, $specificationIds);
    }

    public function test_can_search_specifications_by_name()
    {
        $specification1 = Specification::factory()->create();
        $specification2 = Specification::factory()->create();

        $language = $this->createLanguage();
        SpecificationTranslation::factory()->create([
            'specification_id' => $specification1->id,
            'language_id' => $language->id,
            'name' => '测试规格1',
        ]);
        SpecificationTranslation::factory()->create([
            'specification_id' => $specification2->id,
            'language_id' => $language->id,
            'name' => '其他规格',
        ]);

        $component = $this->livewire(Specifications::class)
            ->set('search', '测试');

        $specifications = $component->get('specifications');
        $specificationIds = $specifications->pluck('id')->toArray();
        $this->assertContains($specification1->id, $specificationIds);
        $this->assertNotContains($specification2->id, $specificationIds);
    }

    public function test_can_filter_specifications_by_translation_status()
    {
        $completeSpec = Specification::factory()->create([
            'translation_status' => TranslationStatusEnum::Translated,
        ]);
        $incompleteSpec = Specification::factory()->create([
            'translation_status' => TranslationStatusEnum::NotTranslated,
        ]);

        $component = $this->livewire(Specifications::class)
            ->set('filterTranslationStatus', [TranslationStatusEnum::Translated->value]);

        $specifications = $component->get('specifications');
        $specificationIds = $specifications->pluck('id')->toArray();
        $this->assertContains($completeSpec->id, $specificationIds);
        $this->assertNotContains($incompleteSpec->id, $specificationIds);
    }

    public function test_can_delete_specification()
    {
        $specification = Specification::factory()->create();

        $component = $this->livewire(Specifications::class)
            ->call('deleteSpecification', $specification->id);

        $this->assertDatabaseMissing('specifications', ['id' => $specification->id]);
    }

    public function test_reset_filters_clears_all_filters()
    {
        $component = $this->livewire(Specifications::class)
            ->set('search', 'test')
            ->set('filterTranslationStatus', [TranslationStatusEnum::Translated->value])
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('filterTranslationStatus', []);
    }
}
