<?php

namespace Tests\Feature\Livewire\Manager;

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
        $component = $this->livewire(\App\Livewire\Manager\Languages::class);
        $component->assertSuccessful();
    }

    public function test_languages_list_displays_languages()
    {
        $language = $this->createLanguage();

        $component = $this->livewire(\App\Livewire\Manager\Languages::class);

        $languages = $component->get('languages');
        $languageIds = $languages->pluck('id')->toArray();
        $this->assertContains($language->id, $languageIds);
    }

    public function test_can_search_languages_by_code()
    {
        $language1 = $this->createLanguage(['code' => 'en']);
        $language2 = $this->createLanguage(['code' => 'fr']);

        $component = $this->livewire(\App\Livewire\Manager\Languages::class)
            ->set('search', 'en');

        $languages = $component->get('languages');
        $languageIds = $languages->pluck('id')->toArray();
        $this->assertContains($language1->id, $languageIds);
        $this->assertNotContains($language2->id, $languageIds);
    }

    public function test_can_search_languages_by_name()
    {
        $language1 = $this->createLanguage(['name' => 'English']);
        $language2 = $this->createLanguage(['name' => '中文']);

        $component = $this->livewire(\App\Livewire\Manager\Languages::class)
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

        $component = $this->livewire(\App\Livewire\Manager\Languages::class)
            ->set('filterDefault', '1');

        $languages = $component->get('languages');
        $languageIds = $languages->pluck('id')->toArray();
        $this->assertContains($defaultLanguage->id, $languageIds);
        $this->assertNotContains($nonDefaultLanguage->id, $languageIds);
    }

    public function test_can_delete_language()
    {
        $language = $this->createLanguage();

        $component = $this->livewire(\App\Livewire\Manager\Languages::class)
            ->call('deleteLanguage', $language->id);

        $this->assertDatabaseMissing('languages', ['id' => $language->id]);
    }

    public function test_reset_filters_clears_all_filters()
    {
        $component = $this->livewire(\App\Livewire\Manager\Languages::class)
            ->set('search', 'test')
            ->set('filterDefault', '1')
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('filterDefault', '');
    }
}
