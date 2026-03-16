<?php

namespace Tests\Feature\Livewire\Manager;

use App\Livewire\Manager\Languages;
use Tests\Feature\LivewireTestCase;

class LanguagesTest extends LivewireTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->createManager(), 'manager');
    }

    public function test_languages_page_can_be_rendered()
    {
        $component = $this->livewire(Languages::class);
        $component->assertSuccessful();
    }

    public function test_languages_list_displays_languages()
    {
        $language = $this->createLanguage();

        $component = $this->livewire(Languages::class);

        $languages = $component->get('languages');
        $languageIds = $languages->pluck('id')->toArray();
        $this->assertContains($language->id, $languageIds);
    }

    public function test_can_search_languages_by_code()
    {
        // 使用更独特的语言代码，避免与其他测试数据冲突
        $language1 = $this->createLanguage(['code' => 'test_en_search']);
        $language2 = $this->createLanguage(['code' => 'test_fr_search']);

        $component = $this->livewire(Languages::class)
            ->set('search', 'test_en');

        $languages = $component->get('languages');
        $languageIds = $languages->pluck('id')->toArray();
        $this->assertContains($language1->id, $languageIds, 'Language 1 should be found when searching for "test_en"');
        $this->assertNotContains($language2->id, $languageIds, 'Language 2 should not be found when searching for "test_en"');
    }

    public function test_can_search_languages_by_name()
    {
        $language1 = $this->createLanguage(['name' => 'English']);
        $language2 = $this->createLanguage(['name' => '中文']);

        $component = $this->livewire(Languages::class)
            ->set('search', 'English');

        $languages = $component->get('languages');
        $languageIds = $languages->pluck('id')->toArray();
        $this->assertContains($language1->id, $languageIds);
        $this->assertNotContains($language2->id, $languageIds);
    }

    public function test_can_filter_languages_by_default()
    {
        $defaultLanguage = $this->createLanguage(['default' => true]);
        $nonDefaultLanguage = $this->createLanguage(['default' => false]);

        $component = $this->livewire(Languages::class)
            ->set('filterDefault', '1');

        $languages = $component->get('languages');
        $languageIds = $languages->pluck('id')->toArray();
        $this->assertContains($defaultLanguage->id, $languageIds);
        $this->assertNotContains($nonDefaultLanguage->id, $languageIds);
    }

    public function test_can_delete_language()
    {
        $language = $this->createLanguage();

        $component = $this->livewire(Languages::class)
            ->call('deleteLanguage', $language->id);

        $this->assertDatabaseMissing('languages', ['id' => $language->id]);
    }

    public function test_reset_filters_clears_all_filters()
    {
        $component = $this->livewire(Languages::class)
            ->set('search', 'test')
            ->set('filterDefault', '1')
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('filterDefault', '');
    }
}
