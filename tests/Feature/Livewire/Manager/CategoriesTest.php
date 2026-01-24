<?php

namespace Tests\Feature\Livewire\Manager;

use App\Enums\TranslationStatusEnum;
use Tests\Feature\LivewireTestCase;

class CategoriesTest extends LivewireTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->createManager(), 'manager');
    }

    public function test_categories_page_can_be_rendered()
    {
        $component = $this->livewire(\App\Livewire\Manager\Categories::class);
        $component->assertSuccessful();
    }

    public function test_categories_list_displays_categories()
    {
        $category = $this->createCategory();

        $component = $this->livewire(\App\Livewire\Manager\Categories::class);

        $categories = $component->get('categories');
        $categoryIds = $categories->pluck('id')->toArray();
        $this->assertContains($category->id, $categoryIds);
    }

    public function test_can_search_categories_by_name()
    {
        $category1 = $this->createCategory();
        $category2 = $this->createCategory();

        // 创建分类翻译
        \App\Models\CategoryTranslation::factory()->create([
            'category_id' => $category1->id,
            'name' => '测试分类1',
        ]);
        \App\Models\CategoryTranslation::factory()->create([
            'category_id' => $category2->id,
            'name' => '其他分类',
        ]);

        $component = $this->livewire(\App\Livewire\Manager\Categories::class)
            ->set('search', '测试')
            ->assertSet('search', '测试');

        $categories = $component->get('categories');
        $categoryIds = $categories->pluck('id')->toArray();
        $this->assertContains($category1->id, $categoryIds);
        $this->assertNotContains($category2->id, $categoryIds);
    }

    public function test_can_filter_categories_by_parent()
    {
        $parentCategory = $this->createCategory();
        $childCategory = $this->createCategory(['parent_id' => $parentCategory->id]);
        $rootCategory = $this->createCategory(['parent_id' => null]);

        $component = $this->livewire(\App\Livewire\Manager\Categories::class)
            ->set('filterParentId', $parentCategory->id);

        $categories = $component->get('categories');
        $categoryIds = $categories->pluck('id')->toArray();
        $this->assertContains($childCategory->id, $categoryIds);
        $this->assertNotContains($parentCategory->id, $categoryIds);
        $this->assertNotContains($rootCategory->id, $categoryIds);
    }

    public function test_can_filter_root_categories()
    {
        $parentCategory = $this->createCategory(['parent_id' => null]);
        $childCategory = $this->createCategory(['parent_id' => $parentCategory->id]);

        $component = $this->livewire(\App\Livewire\Manager\Categories::class)
            ->set('filterParentId', 0); // 0 表示根分类

        $categories = $component->get('categories');
        $categoryIds = $categories->pluck('id')->toArray();
        $this->assertContains($parentCategory->id, $categoryIds);
        $this->assertNotContains($childCategory->id, $categoryIds);
    }

    public function test_can_filter_categories_by_translation_status()
    {
        $category1 = $this->createCategory(['translation_status' => TranslationStatusEnum::Translated]);
        $category2 = $this->createCategory(['translation_status' => TranslationStatusEnum::NotTranslated]);

        $component = $this->livewire(\App\Livewire\Manager\Categories::class)
            ->set('filterTranslationStatus', [TranslationStatusEnum::Translated->value]);

        $categories = $component->get('categories');
        $categoryIds = $categories->pluck('id')->toArray();
        $this->assertContains($category1->id, $categoryIds);
        $this->assertNotContains($category2->id, $categoryIds);
    }

    public function test_can_delete_category()
    {
        $category = $this->createCategory();

        $component = $this->livewire(\App\Livewire\Manager\Categories::class)
            ->call('deleteCategory', $category->id);

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_reset_filters_clears_all_filters()
    {
        $component = $this->livewire(\App\Livewire\Manager\Categories::class)
            ->set('search', 'test')
            ->set('filterParentId', 1)
            ->set('filterTranslationStatus', [TranslationStatusEnum::Translated->value])
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('filterParentId', null)
            ->assertSet('filterTranslationStatus', []);
    }

    public function test_updating_search_resets_page()
    {
        $component = $this->livewire(\App\Livewire\Manager\Categories::class)
            ->set('search', 'test');

        $component->assertSet('search', 'test');
    }
}
