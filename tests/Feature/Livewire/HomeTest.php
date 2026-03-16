<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Home;
use App\Models\CategoryTranslation;
use Illuminate\Support\Collection;
use Tests\Feature\LivewireTestCase;

class HomeTest extends LivewireTestCase
{
    public function test_home_page_can_be_rendered()
    {
        $component = $this->livewire(Home::class);
        $component->assertSuccessful();
    }

    public function test_home_page_displays_categories()
    {
        $category = $this->createCategory();

        // 创建分类翻译以确保 getCategoriesForLanguage 返回数据
        $language = $this->createLanguage();
        CategoryTranslation::factory()->create([
            'category_id' => $category->id,
            'language_id' => $language->id,
            'name' => '测试分类',
        ]);

        $component = $this->livewire(Home::class);
        $component->assertSuccessful();

        $categories = $component->get('categories');
        // getCategoriesForLanguage 返回 Collection，不是数组
        $this->assertInstanceOf(Collection::class, $categories);
    }

    public function test_home_page_loads_categories_for_current_language()
    {
        $category = $this->createCategory();

        // 创建分类翻译
        $language = $this->createLanguage();
        CategoryTranslation::factory()->create([
            'category_id' => $category->id,
            'language_id' => $language->id,
            'name' => '测试分类',
        ]);

        $component = $this->livewire(Home::class);
        $component->assertSuccessful();

        $categories = $component->get('categories');
        // getCategoriesForLanguage 返回 Collection
        $this->assertInstanceOf(Collection::class, $categories);
    }

    public function test_home_page_handles_empty_categories()
    {
        // 不创建任何分类
        $component = $this->livewire(Home::class);
        $component->assertSuccessful();

        $categories = $component->get('categories');
        // getCategoriesForLanguage 返回 Collection
        $this->assertInstanceOf(Collection::class, $categories);
    }
}
