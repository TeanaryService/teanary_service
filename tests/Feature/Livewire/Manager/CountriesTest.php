<?php

namespace Tests\Feature\Livewire\Manager;

use App\Enums\TranslationStatusEnum;
use App\Livewire\Manager\Countries;
use App\Models\Country;
use App\Models\CountryTranslation;
use Tests\Feature\LivewireTestCase;

class CountriesTest extends LivewireTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->createManager(), 'manager');
    }

    public function test_countries_page_can_be_rendered()
    {
        $component = $this->livewire(Countries::class);
        $component->assertSuccessful();
    }

    public function test_countries_list_displays_countries()
    {
        $country = Country::factory()->create();

        $component = $this->livewire(Countries::class);

        $countries = $component->get('countries');
        $countryIds = $countries->pluck('id')->toArray();
        $this->assertContains($country->id, $countryIds);
    }

    public function test_can_search_countries_by_name()
    {
        $country1 = Country::factory()->create();
        $country2 = Country::factory()->create();

        CountryTranslation::factory()->create([
            'country_id' => $country1->id,
            'name' => '测试国家1',
        ]);
        CountryTranslation::factory()->create([
            'country_id' => $country2->id,
            'name' => '其他国家',
        ]);

        $component = $this->livewire(Countries::class)
            ->set('search', '测试');

        $countries = $component->get('countries');
        $countryIds = $countries->pluck('id')->toArray();
        $this->assertContains($country1->id, $countryIds);
        $this->assertNotContains($country2->id, $countryIds);
    }

    public function test_can_filter_countries_by_active_status()
    {
        $activeCountry = Country::factory()->create(['active' => true]);
        $inactiveCountry = Country::factory()->create(['active' => false]);

        $component = $this->livewire(Countries::class)
            ->set('filterActive', '1');

        $countries = $component->get('countries');
        $countryIds = $countries->pluck('id')->toArray();
        $this->assertContains($activeCountry->id, $countryIds);
        $this->assertNotContains($inactiveCountry->id, $countryIds);
    }

    public function test_can_filter_countries_by_translation_status()
    {
        $completeCountry = Country::factory()->create([
            'translation_status' => TranslationStatusEnum::Translated,
        ]);
        $incompleteCountry = Country::factory()->create([
            'translation_status' => TranslationStatusEnum::NotTranslated,
        ]);

        $component = $this->livewire(Countries::class)
            ->set('filterTranslationStatus', [TranslationStatusEnum::Translated->value]);

        $countries = $component->get('countries');
        $countryIds = $countries->pluck('id')->toArray();
        $this->assertContains($completeCountry->id, $countryIds);
        $this->assertNotContains($incompleteCountry->id, $countryIds);
    }

    public function test_can_delete_country()
    {
        $country = Country::factory()->create();

        $component = $this->livewire(Countries::class)
            ->call('deleteCountry', $country->id);

        $this->assertDatabaseMissing('countries', ['id' => $country->id]);
    }

    public function test_reset_filters_clears_all_filters()
    {
        $component = $this->livewire(Countries::class)
            ->set('search', 'test')
            ->set('filterActive', '1')
            ->set('filterTranslationStatus', [TranslationStatusEnum::Translated->value])
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('filterActive', '')
            ->assertSet('filterTranslationStatus', []);
    }
}
